<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Service\EuropakozijnOfficeProjectDocumentClient;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class EuropakozijnProjectUploadController extends ControllerBase {

  private const MAX_FILES = 20;
  private const MAX_FILE_BYTES = 26214400;
  private const MAX_TOTAL_BYTES = 104857600;

  private const ALLOWED_EXTENSIONS = [
    'pdf', 'png', 'jpg', 'jpeg', 'webp', 'heic', 'heif', 'zip',
    'xls', 'xlsx', 'doc', 'docx',
  ];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly UuidInterface $uuid,
    private readonly EuropakozijnOfficeProjectDocumentClient $officeClient,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('file_system'),
      $container->get('uuid'),
      $container->get('brebo_europakozijn.office_project_document_client'),
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
    $directoryPath = $this->fileSystem->realpath($directory);
    if (!$directoryPath) {
      return new JsonResponse(['ok' => FALSE, 'message' => 'De intake-opslag kon niet worden geopend.'], 503);
    }

    $manifest = [];
    $officeOk = TRUE;
    $documentCount = 0;
    $extractedCount = 0;
    foreach ($files as $index => $file) {
      $extension = strtolower((string) $file->getClientOriginalExtension());
      $original = (string) $file->getClientOriginalName();
      $safeBase = preg_replace('/[^A-Za-z0-9._-]+/', '-', pathinfo($original, PATHINFO_FILENAME)) ?: 'projectstuk';
      $safeBase = trim($safeBase, '.-_') ?: 'projectstuk';
      $filename = sprintf('%02d-%s.%s', $index + 1, substr($safeBase, 0, 80), $extension);
      $destination = $directoryPath . DIRECTORY_SEPARATOR . $filename;

      try {
        $file->move(dirname($destination), basename($destination));
      }
      catch (\Throwable) {
        return new JsonResponse(['ok' => FALSE, 'message' => 'Opslaan van een projectstuk is mislukt.'], 500);
      }

      $office = $this->officeClient->send($destination, $original, $intakeId, [
        'channel' => 'website',
        'journey' => 'europakozijn_projectstukken',
        'project_name' => trim((string) $request->request->get('project_name', '')),
      ]);
      $officeOk = $officeOk && !empty($office['ok']);
      $documentCount += (int) ($office['document_count'] ?? 0);
      $extractedCount += (int) ($office['extracted_count'] ?? 0);

      $manifest[] = [
        'name' => $original,
        'stored_name' => $filename,
        'extension' => $extension,
        'size' => (int) filesize($destination),
        'sha256' => hash_file('sha256', $destination),
        'package' => $extension === 'zip',
        'document_type' => $this->documentType($extension),
        'office' => $office,
      ];
    }

    $state = $officeOk ? 'office_received_review_required' : 'office_handoff_failed';
    $result = [
      'intake_id' => $intakeId,
      'created_at' => gmdate('c'),
      'state' => $state,
      'source_of_truth' => 'BREBO Office',
      'files' => $manifest,
      'office_summary' => [
        'documents_indexed' => $documentCount,
        'documents_read' => $extractedCount,
      ],
      'principle' => 'Herkende informatie is een voorstel en nooit automatisch canonieke waarheid.',
    ];
    if (file_put_contents($directoryPath . DIRECTORY_SEPARATOR . 'recognition.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === FALSE) {
      return new JsonResponse(['ok' => FALSE, 'message' => 'De intake-resultaten konden niet worden opgeslagen.'], 500);
    }

    if (!$officeOk) {
      return new JsonResponse([
        'ok' => FALSE,
        'intake_id' => $intakeId,
        'state' => $state,
        'message' => 'De projectstukken zijn veilig ontvangen, maar konden nog niet aan BREBO Office worden overgedragen. Er wordt geen herkenning gegokt.',
      ], 503);
    }

    return new JsonResponse([
      'ok' => TRUE,
      'intake_id' => $intakeId,
      'files' => $manifest,
      'state' => $state,
      'result_url' => '/europakozijn/projectstukken/resultaat/' . $intakeId,
      'message' => 'Projectstukken ontvangen door BREBO Office. De eerste uitlezing staat klaar voor controle.',
    ], 201);
  }

  private function documentType(string $extension): string {
    return match ($extension) {
      'pdf' => 'pdf_document',
      'png', 'jpg', 'jpeg', 'webp', 'heic', 'heif' => 'image',
      'zip' => 'project_package',
      'xls', 'xlsx' => 'spreadsheet',
      'doc', 'docx' => 'text_document',
      default => 'unknown',
    };
  }
}
