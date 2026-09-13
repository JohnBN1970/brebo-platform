<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Service\ProjectResultPresenter;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EuropakozijnProjectResultController extends ControllerBase {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly ProjectResultPresenter $presenter,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('file_system'),
      $container->get('brebo_europakozijn.project_result_presenter'),
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

    // Office is the source of truth for website-originating project intakes.
    // Never re-run a second local recognition pipeline on the result page.
    if (($decoded['source_of_truth'] ?? '') === 'BREBO Office') {
      unset(
        $decoded['context_analyses'],
        $decoded['context_analysis_version'],
        $decoded['context_analysis_created_at'],
      );
    }
    $decoded['presentation'] = $this->presenter->present($decoded, $path);

    return [
      '#theme' => 'brebo_europakozijn_project_result',
      '#result' => $decoded,
      '#attached' => [
        'library' => [
          'brebo_europakozijn/project_result',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

}
