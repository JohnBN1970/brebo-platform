<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class EuropakozijnProjectUploadController extends ControllerBase {

  private const MAX_FILES = 20;
  private const MAX_FILE_BYTES = 26214400;
  private const MAX_TOTAL_BYTES = 104857600;

  private const ALLOWED_EXTENSIONS = [
    'pdf', 'png', 'jpg', 'jpeg', 'webp', 'zip',
  ];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly UuidInterface $uuid,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('file_system'),
      $container->get('uuid'),
    );
  }

  public function upload(Request $request): JsonResponse {
    $files = $request->files->all('project_files');
    if (!is_array($files)) {
      $files = $files instanceof UploadedFile ? [$files] : [];
    }

    $files = array_values(array_filter($files, static fn ($file): bool => $file instanceof UploadedFile));
    if (!$files) {
      return new JsonResponse(['ok' => FALSE, 'message' => 'Geen projectstukken ontvangen.'], 400);
    }
    if (count($files) > self::MAX_FILES) {
      return new JsonResponse(['ok' => FALSE, 'message' => 'Selecteer maximaal 20 bestanden per intake.'], 413);
    }

    $total = 0;
    foreach ($files as $file) {
      if (!$file->isValid()) {
        return new JsonResponse(['ok' => FALSE, 'message' => 'Een van de bestanden kon niet veilig worden ontvangen.'], 400);
      }
      $size = (int) $file->getSize();
      $total += $size;
      if ($size <= 0 || $size > self::MAX_FILE_BYTES || $total > self::MAX_TOTAL_BYTES) {
        return new JsonResponse(['ok' => FALSE, 'message' => 'De upload is te groot. Maximaal 25 MB per bestand en 100 MB totaal.'], 413);
      }
      $extension = strtolower((string) $file->getClientOriginalExtension());
      if (!in_array($extension, self::ALLOWED_EXTENSIONS, TRUE)) {
        return new JsonResponse(['ok' => FALSE, 'message' => 'Dit bestandstype wordt niet geaccepteerd.'], 415);
      }
    }

    $intakeId = $this->uuid->generate();
    $directory = 'temporary://brebo-europakozijn-intake/' . $intakeId;
    if (!$this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      return new JsonResponse(['ok' => FALSE, 'message' => 'De projectstukken kunnen nu niet veilig worden opgeslagen.'], 503);
    }

    $manifest = [];
    foreach ($files as $index => $file) {
      $extension = strtolower((string) $file->getClientOriginalExtension());
      $original = (string) $file->getClientOriginalName();
      $safeBase = preg_replace('/[^A-Za-z0-9._-]+/', '-', pathinfo($original, PATHINFO_FILENAME)) ?: 'projectstuk';
      $safeBase = trim($safeBase, '.-_') ?: 'projectstuk';
      $filename = sprintf('%02d-%s.%s', $index + 1, substr($safeBase, 0, 80), $extension);
      $destination = $this->fileSystem->realpath($directory) . DIRECTORY_SEPARATOR . $filename;

      try {
        $file->move(dirname($destination), basename($destination));
      }
      catch (\Throwable) {
        return new JsonResponse(['ok' => FALSE, 'message' => 'Opslaan van een projectstuk is mislukt.'], 500);
      }

      $manifest[] = [
        'name' => $original,
        'stored_name' => $filename,
        'extension' => $extension,
        'size' => (int) filesize($destination),
        'sha256' => hash_file('sha256', $destination),
        'package' => $extension === 'zip',
      ];
    }

    return new JsonResponse([
      'ok' => TRUE,
      'intake_id' => $intakeId,
      'files' => $manifest,
      'state' => 'received_unprocessed',
      'message' => 'Projectstukken veilig ontvangen. Herkenning en inhoudelijke controle volgen in de volgende stap.',
    ], 201);
  }

}
