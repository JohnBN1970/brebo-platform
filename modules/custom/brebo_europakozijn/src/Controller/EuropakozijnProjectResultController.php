<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Recognition\ProjectPackageContextAnalyzer;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EuropakozijnProjectResultController extends ControllerBase {

  private const CONTEXT_ANALYSIS_VERSION = 2;

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly ProjectPackageContextAnalyzer $contextAnalyzer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('file_system'),
      $container->get('brebo_europakozijn.project_package_context_analyzer'),
    );
  }

  public function build(string $intake_id): array {
    if (!preg_match('/^[0-9a-f-]{36}$/i', $intake_id)) {
      throw new NotFoundHttpException();
    }

    $directory = 'temporary://brebo-europakozijn-intake/' . $intake_id;
    $path = $this->fileSystem->realpath($directory);
    if (!$path) {
      throw new NotFoundHttpException();
    }

    $resultFile = $path . DIRECTORY_SEPARATOR . 'recognition.json';
    if (!is_file($resultFile)) {
      throw new NotFoundHttpException();
    }

    $decoded = json_decode((string) file_get_contents($resultFile), TRUE);
    if (!is_array($decoded)) {
      throw new NotFoundHttpException();
    }

    if ((int) ($decoded['context_analysis_version'] ?? 0) < self::CONTEXT_ANALYSIS_VERSION) {
      $contextAnalyses = [];
      foreach (($decoded['files'] ?? []) as $file) {
        if (($file['extension'] ?? '') !== 'zip') {
          continue;
        }
        $storedName = basename((string) ($file['stored_name'] ?? ''));
        if ($storedName === '') {
          continue;
        }
        $packagePath = $path . DIRECTORY_SEPARATOR . $storedName;
        if (!is_file($packagePath)) {
          continue;
        }
        $analysis = $this->contextAnalyzer->analyze($packagePath);
        $analysis['package_name'] = (string) ($file['name'] ?? $storedName);
        $contextAnalyses[] = $analysis;
      }

      $decoded['context_analysis_version'] = self::CONTEXT_ANALYSIS_VERSION;
      $decoded['context_analyses'] = $contextAnalyses;
      $decoded['context_analysis_created_at'] = gmdate('c');

      @file_put_contents(
        $resultFile,
        json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      );
    }

    return [
      '#theme' => 'brebo_europakozijn_project_result',
      '#result' => $decoded,
      '#attached' => [
        'library' => [
          'brebo_europakozijn/configurator',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

}
