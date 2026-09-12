<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class EuropakozijnProjectUploadController extends ControllerBase {

  private const MAX_FILES = 20;
  private const MAX_FILE_BYTES = 26214400;
  private const MAX_TOTAL_BYTES = 104857600;
  private const MANAGED_EXTRACTION_MAX_BYTES = 15728640;

  private const ALLOWED_EXTENSIONS = [
    'pdf', 'png', 'jpg', 'jpeg', 'webp', 'zip',
    'xls', 'xlsx', 'doc', 'docx',
  ];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly UuidInterface $uuid,
    private readonly ClientInterface $httpClient,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('file_system'),
      $container->get('uuid'),
      $container->get('http_client'),
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
    $recognizedCount = 0;
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

      $recognition = $this->recognizeDocument($destination, $extension, $original);
      if (!empty($recognition['signals'])) {
        $recognizedCount++;
      }

      $manifest[] = [
        'name' => $original,
        'stored_name' => $filename,
        'extension' => $extension,
        'size' => (int) filesize($destination),
        'sha256' => hash_file('sha256', $destination),
        'package' => $extension === 'zip',
        'document_type' => $this->documentType($extension),
        'recognition' => $recognition,
      ];
    }

    $state = $recognizedCount > 0 ? 'recognized_proposals' : 'received_needs_review';
    $result = [
      'intake_id' => $intakeId,
      'created_at' => gmdate('c'),
      'state' => $state,
      'files' => $manifest,
      'principle' => 'Herkende informatie is een voorstel en nooit automatisch canonieke waarheid.',
    ];

    if (file_put_contents($directoryPath . DIRECTORY_SEPARATOR . 'recognition.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === FALSE) {
      return new JsonResponse(['ok' => FALSE, 'message' => 'De herkenningsresultaten konden niet worden opgeslagen.'], 500);
    }

    return new JsonResponse([
      'ok' => TRUE,
      'intake_id' => $intakeId,
      'files' => $manifest,
      'state' => $state,
      'result_url' => '/europakozijn/projectstukken/resultaat/' . $intakeId,
      'message' => $recognizedCount > 0
        ? 'Projectstukken ontvangen en eerste informatie herkend. Controleer de voorstellen op de resultaatpagina.'
        : 'Projectstukken ontvangen. De herkenner heeft nog geen betrouwbare kozijninformatie gevonden; controleer de resultaatpagina.',
    ], 201);
  }

  private function recognizeDocument(string $path, string $extension, string $original): array {
    $text = '';
    $extractor = 'none';
    $extractionStatus = 'not_supported';
    $extractionConfidence = 0.0;
    $metadata = [];

    if (in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'webp'], TRUE)) {
      $managed = $this->managedExtract($path, $extension, $original);
      $text = $managed['text'];
      $extractor = $managed['extractor'];
      $extractionStatus = $managed['status'];
      $extractionConfidence = $managed['confidence'];
      $metadata = $managed['metadata'];
    }
    elseif (in_array($extension, ['docx', 'xlsx'], TRUE)) {
      $local = $this->extractOpenXmlText($path, $extension);
      $text = $local['text'];
      $extractor = 'openxml_text_v1';
      $extractionStatus = $text !== '' ? 'extracted' : $local['status'];
      $extractionConfidence = $text !== '' ? 0.95 : 0.0;
    }
    elseif ($extension === 'zip') {
      $metadata['package_entries'] = $this->inspectZipPackage($path);
      $extractor = 'zip_manifest_v1';
      $extractionStatus = 'package_inspected';
      $extractionConfidence = 0.9;
    }
    elseif (in_array($extension, ['doc', 'xls'], TRUE)) {
      $extractionStatus = 'legacy_format_needs_conversion';
      $extractor = 'legacy_document_marker_v1';
    }

    $signals = $this->findKozijnSignals(trim($original . "\n" . $text));
    $signalConfidence = $signals ? min(0.95, max(0.55, $extractionConfidence)) : 0.0;

    return [
      'status' => $signals ? 'proposal_available' : 'no_reliable_signal',
      'extraction_status' => $extractionStatus,
      'extractor' => $extractor,
      'confidence' => round($signalConfidence, 2),
      'signals' => $signals,
      'excerpt' => $text !== '' ? mb_substr(preg_replace('/\s+/u', ' ', $text) ?: $text, 0, 500) : '',
      'metadata' => $metadata,
      'canonical_truth' => FALSE,
    ];
  }

  private function managedExtract(string $path, string $extension, string $filename): array {
    $size = (int) filesize($path);
    if ($size <= 0 || $size > self::MANAGED_EXTRACTION_MAX_BYTES) {
      return $this->emptyExtraction('document_too_large_for_managed_extraction');
    }

    $endpoint = trim((string) Settings::get('brebo_document_extraction_endpoint', ''));
    if ($endpoint === '') {
      $endpoint = trim((string) (getenv('BREBO_DOCUMENT_EXTRACTION_ENDPOINT') ?: ''));
    }
    $token = trim((string) Settings::get('brebo_document_extraction_token', ''));
    if ($token === '') {
      $token = trim((string) (getenv('DOCUMENT_EXTRACTION_TOKEN') ?: ''));
    }
    if (!str_starts_with($endpoint, 'https://') || $token === '') {
      return $this->emptyExtraction('managed_extraction_not_configured');
    }

    $mime = match ($extension) {
      'pdf' => 'application/pdf',
      'png' => 'image/png',
      'webp' => 'image/webp',
      default => 'image/jpeg',
    };

    $contents = file_get_contents($path);
    if ($contents === FALSE) {
      return $this->emptyExtraction('document_read_failed');
    }

    try {
      $response = $this->httpClient->request('POST', $endpoint, [
        'headers' => [
          'Accept' => 'application/json',
          'Authorization' => 'Bearer ' . $token,
          'X-BREBO-Extraction-Contract' => 'v1',
        ],
        'multipart' => [[
          'name' => 'document',
          'contents' => $contents,
          'filename' => $filename,
          'headers' => ['Content-Type' => $mime],
        ]],
        'connect_timeout' => 5.0,
        'timeout' => 30.0,
        'http_errors' => FALSE,
      ]);
    }
    catch (\Throwable) {
      return $this->emptyExtraction('managed_extraction_unreachable');
    }

    if ($response->getStatusCode() !== 200) {
      return $this->emptyExtraction('managed_extraction_error', ['provider_status' => $response->getStatusCode()]);
    }

    $decoded = json_decode((string) $response->getBody(), TRUE);
    if (!is_array($decoded)) {
      return $this->emptyExtraction('invalid_extraction_response');
    }

    $text = trim((string) ($decoded['text'] ?? ''));
    return [
      'status' => (string) ($decoded['status'] ?? ($text !== '' ? 'extracted' : 'no_text')),
      'text' => $text,
      'extractor' => trim((string) ($decoded['extractor'] ?? 'brebo_managed_extraction_v1')) ?: 'brebo_managed_extraction_v1',
      'confidence' => isset($decoded['confidence']) && is_numeric($decoded['confidence']) ? max(0.0, min(1.0, (float) $decoded['confidence'])) : ($text !== '' ? 0.8 : 0.0),
      'metadata' => ['provider' => 'brebo_managed', 'contract' => 'v1'],
    ];
  }

  private function extractOpenXmlText(string $path, string $extension): array {
    if (!class_exists(\ZipArchive::class)) {
      return ['status' => 'zip_extension_unavailable', 'text' => ''];
    }

    $zip = new \ZipArchive();
    if ($zip->open($path) !== TRUE) {
      return ['status' => 'openxml_open_failed', 'text' => ''];
    }

    $parts = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $name = (string) $zip->getNameIndex($i);
      $wanted = $extension === 'docx'
        ? preg_match('#^word/(document|header\d+|footer\d+)\.xml$#i', $name)
        : preg_match('#^xl/(sharedStrings|worksheets/[^/]+)\.xml$#i', $name);
      if (!$wanted) {
        continue;
      }
      $xml = $zip->getFromIndex($i);
      if (!is_string($xml) || $xml === '') {
        continue;
      }
      $xml = preg_replace('/<w:tab\b[^>]*\/>/i', "\t", $xml) ?? $xml;
      $xml = preg_replace('/<\/(w:p|row|si)>/i', "\n", $xml) ?? $xml;
      $plain = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
      if (trim($plain) !== '') {
        $parts[] = trim($plain);
      }
    }
    $zip->close();

    return ['status' => $parts ? 'extracted' : 'no_text', 'text' => implode("\n", $parts)];
  }

  private function inspectZipPackage(string $path): array {
    if (!class_exists(\ZipArchive::class)) {
      return [['status' => 'zip_extension_unavailable']];
    }

    $zip = new \ZipArchive();
    if ($zip->open($path) !== TRUE) {
      return [['status' => 'zip_open_failed']];
    }

    $entries = [];
    $limit = min($zip->numFiles, 200);
    for ($i = 0; $i < $limit; $i++) {
      $stat = $zip->statIndex($i);
      if (!is_array($stat)) {
        continue;
      }
      $name = (string) ($stat['name'] ?? '');
      if ($name === '' || str_ends_with($name, '/')) {
        continue;
      }
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $entries[] = [
        'name' => $name,
        'extension' => $ext,
        'document_type' => $this->documentType($ext),
        'size' => (int) ($stat['size'] ?? 0),
      ];
    }
    $zip->close();
    return $entries;
  }

  private function findKozijnSignals(string $text): array {
    if ($text === '') {
      return [];
    }

    $signals = [];

    preg_match_all('/\b(\d{3,4})\s*(?:x|×)\s*(\d{3,4})\b/iu', $text, $dimensionMatches, PREG_SET_ORDER);
    $dimensions = [];
    foreach ($dimensionMatches as $match) {
      $width = (int) $match[1];
      $height = (int) $match[2];
      if ($width >= 300 && $width <= 6000 && $height >= 300 && $height <= 6000) {
        $dimensions[] = ['width_mm' => $width, 'height_mm' => $height, 'basis' => $match[0]];
      }
    }
    if ($dimensions) {
      $signals['dimensions'] = array_slice($dimensions, 0, 50);
    }

    preg_match_all('/\b(?:kozijn\s*(?:nr\.?\s*)?|K[-\s]?)([A-Z]?\d{1,3}[A-Z]?)\b/iu', $text, $referenceMatches);
    $references = array_values(array_unique(array_map(static fn ($value): string => strtoupper((string) $value), $referenceMatches[1] ?? [])));
    if ($references) {
      $signals['references'] = array_slice($references, 0, 50);
    }

    $materials = [];
    foreach (['kunststof', 'aluminium', 'hout'] as $material) {
      if (preg_match('/\b' . preg_quote($material, '/') . '\b/iu', $text)) {
        $materials[] = $material;
      }
    }
    if ($materials) {
      $signals['materials'] = $materials;
    }

    $openingTerms = [
      'vast' => ['vast', 'vastglas'],
      'draaikiep' => ['draaikiep', 'draai-kiep', 'draaikiepraam'],
      'draai' => ['draairaam'],
      'deur' => ['deur', 'balkondeur', 'tuindeur'],
      'schuif' => ['schuifpui', 'schuifdeur'],
    ];
    $openings = [];
    foreach ($openingTerms as $label => $terms) {
      foreach ($terms as $term) {
        if (mb_stripos($text, $term) !== FALSE) {
          $openings[] = $label;
          break;
        }
      }
    }
    if ($openings) {
      $signals['opening_types'] = array_values(array_unique($openings));
    }

    return $signals;
  }

  private function documentType(string $extension): string {
    return match ($extension) {
      'xls', 'xlsx' => 'spreadsheet',
      'doc', 'docx' => 'word_document',
      'pdf' => 'pdf',
      'png', 'jpg', 'jpeg', 'webp' => 'image',
      'zip' => 'project_package',
      default => 'unknown',
    };
  }

  private function emptyExtraction(string $status, array $metadata = []): array {
    return [
      'status' => $status,
      'text' => '',
      'extractor' => 'brebo_managed_extraction_v1',
      'confidence' => 0.0,
      'metadata' => ['provider' => 'brebo_managed', 'contract' => 'v1'] + $metadata,
    ];
  }

}
