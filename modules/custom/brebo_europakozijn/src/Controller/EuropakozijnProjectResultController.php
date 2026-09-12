<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EuropakozijnProjectResultController extends ControllerBase {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('file_system'));
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
