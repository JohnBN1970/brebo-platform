<?php

namespace Drupal\brebo_europakozijn\Recognition;

use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;

/**
 * Correlates project-package evidence across drawings, schedules and text.
 *
 * The result is always proposal data: it never promotes recognition to
 * canonical truth without human confirmation.
 */
final class ProjectPackageContextAnalyzer {

  private const MAX_ENTRY_BYTES = 15728640;
  private const MAX_ENTRIES = 120;

  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  public function analyze(string $packagePath): array {
    if (!class_exists(\ZipArchive::class)) {
      return $this->emptyResult('zip_extension_unavailable');
    }

    $zip = new \ZipArchive();
    if ($zip->open($packagePath) !== TRUE) {
      return $this->emptyResult('zip_open_failed');
    }

    $documents = [];
    $limit = min($zip->numFiles, self::MAX_ENTRIES);
    for ($i = 0; $i < $limit; $i++) {
      $stat = $zip->statIndex($i);
      if (!is_array($stat)) {
        continue;
      }

      $name = (string) ($stat['name'] ?? '');
      if ($name === '' || str_ends_with($name, '/')) {
        continue;
      }

      $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $size = (int) ($stat['size'] ?? 0);
      $document = [
        'name' => $name,
        'extension' => $extension,
        'size' => $size,
        'role' => $this->classifyRole($name, ''),
        'status' => 'indexed_only',
        'confidence' => 0.0,
        'signals' => $this->extractSignals($name),
        'excerpt' => '',
      ];

      if ($size > 0 && $size <= self::MAX_ENTRY_BYTES && $this->isReadableExtension($extension)) {
        $bytes = $zip->getFromIndex($i);
        if (is_string($bytes) && $bytes !== '') {
          $extraction = $this->extractEntry($bytes, $extension, $name);
          $text = trim((string) ($extraction['text'] ?? ''));
          $document['status'] = (string) ($extraction['status'] ?? 'no_text');
          $document['confidence'] = (float) ($extraction['confidence'] ?? 0.0);
          $document['role'] = $this->classifyRole($name, $text);
          $document['signals'] = $this->mergeSignals(
            $document['signals'],
            $this->extractSignals($text),
          );
          if ($text !== '') {
            $clean = preg_replace('/\s+/u', ' ', $text) ?: $text;
            $document['excerpt'] = mb_substr($clean, 0, 500);
          }
        }
      }

      $documents[] = $document;
    }
    $zip->close();

    $types = $this->correlateByReference($documents);
    $unassigned = $this->collectUnassignedEvidence($documents);
    $conflicts = $this->collectConflicts($types);

    return [
      'status' => $types || $unassigned ? 'context_proposals_available' : 'no_reliable_context',
      'canonical_truth' => FALSE,
      'documents' => $documents,
      'types' => array_values($types),
      'unassigned_evidence' => $unassigned,
      'conflicts' => $conflicts,
      'summary' => [
        'documents_read' => count(array_filter($documents, static fn (array $doc): bool => $doc['status'] !== 'indexed_only')),
        'documents_indexed' => count($documents),
        'types_correlated' => count($types),
        'conflicts' => count($conflicts),
      ],
      'principle' => 'Aantal, geometrie en type mogen uit verschillende bronnen komen. Een afgebeeld voorbeeldkozijn is niet automatisch het werkelijke projectaantal.',
    ];
  }

  private function extractEntry(string $bytes, string $extension, string $filename): array {
    if (in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'webp'], TRUE)) {
      return $this->managedExtract($bytes, $extension, $filename);
    }

    if (in_array($extension, ['docx', 'xlsx'], TRUE)) {
      return $this->extractOpenXml($bytes, $extension);
    }

    return ['status' => 'unsupported_for_text', 'text' => '', 'confidence' => 0.0];
  }

  private function managedExtract(string $bytes, string $extension, string $filename): array {
    $endpoint = trim((string) Settings::get('brebo_document_extraction_endpoint', ''));
    if ($endpoint === '') {
      $endpoint = trim((string) (getenv('BREBO_DOCUMENT_EXTRACTION_ENDPOINT') ?: ''));
    }
    $token = trim((string) Settings::get('brebo_document_extraction_token', ''));
    if ($token === '') {
      $token = trim((string) (getenv('DOCUMENT_EXTRACTION_TOKEN') ?: ''));
    }
    if (!str_starts_with($endpoint, 'https://') || $token === '') {
      return ['status' => 'managed_extraction_not_configured', 'text' => '', 'confidence' => 0.0];
    }

    $mime = match ($extension) {
      'pdf' => 'application/pdf',
      'png' => 'image/png',
      'webp' => 'image/webp',
      default => 'image/jpeg',
    };

    try {
      $response = $this->httpClient->request('POST', $endpoint, [
        'headers' => [
          'Accept' => 'application/json',
          'Authorization' => 'Bearer ' . $token,
          'X-BREBO-Extraction-Contract' => 'v1',
        ],
        'multipart' => [[
          'name' => 'document',
          'contents' => $bytes,
          'filename' => $filename,
          'headers' => ['Content-Type' => $mime],
        ]],
        'connect_timeout' => 5.0,
        'timeout' => 30.0,
        'http_errors' => FALSE,
      ]);
    }
    catch (\Throwable) {
      return ['status' => 'managed_extraction_unreachable', 'text' => '', 'confidence' => 0.0];
    }

    if ($response->getStatusCode() !== 200) {
      return ['status' => 'managed_extraction_error', 'text' => '', 'confidence' => 0.0];
    }

    $decoded = json_decode((string) $response->getBody(), TRUE);
    if (!is_array($decoded)) {
      return ['status' => 'invalid_extraction_response', 'text' => '', 'confidence' => 0.0];
    }

    $text = trim((string) ($decoded['text'] ?? ''));
    $confidence = isset($decoded['confidence']) && is_numeric($decoded['confidence'])
      ? max(0.0, min(1.0, (float) $decoded['confidence']))
      : ($text !== '' ? 0.8 : 0.0);

    return [
      'status' => (string) ($decoded['status'] ?? ($text !== '' ? 'extracted' : 'no_text')),
      'text' => $text,
      'confidence' => $confidence,
    ];
  }

  private function extractOpenXml(string $bytes, string $extension): array {
    $temp = tempnam(sys_get_temp_dir(), 'brebo-openxml-');
    if ($temp === FALSE || file_put_contents($temp, $bytes) === FALSE) {
      return ['status' => 'openxml_temp_failed', 'text' => '', 'confidence' => 0.0];
    }

    $zip = new \ZipArchive();
    if ($zip->open($temp) !== TRUE) {
      @unlink($temp);
      return ['status' => 'openxml_open_failed', 'text' => '', 'confidence' => 0.0];
    }

    $parts = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $name = (string) $zip->getNameIndex($i);
      $wanted = $extension === 'docx'
        ? preg_match('#^word/(document|header\d+|footer\d+|tables/[^/]+)\.xml$#i', $name)
        : preg_match('#^xl/(sharedStrings|worksheets/[^/]+)\.xml$#i', $name);
      if (!$wanted) {
        continue;
      }
      $xml = $zip->getFromIndex($i);
      if (!is_string($xml) || $xml === '') {
        continue;
      }
      $xml = preg_replace('/<w:tab\b[^>]*\/>/i', "\t", $xml) ?? $xml;
      $xml = preg_replace('/<\/(w:p|w:tr|w:tc|row|si)>/i', "\n", $xml) ?? $xml;
      $plain = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
      if (trim($plain) !== '') {
        $parts[] = trim($plain);
      }
    }
    $zip->close();
    @unlink($temp);

    $text = implode("\n", $parts);
    return [
      'status' => $text !== '' ? 'extracted' : 'no_text',
      'text' => $text,
      'confidence' => $text !== '' ? 0.95 : 0.0,
    ];
  }

  private function extractSignals(string $text): array {
    $signals = [
      'references' => [],
      'dimensions' => [],
      'quantities' => [],
      'materials' => [],
      'opening_types' => [],
    ];
    if (trim($text) === '') {
      return $signals;
    }

    preg_match_all('/\b(?:kozijn\s*(?:nr\.?\s*)?|type\s+|K[-\s]?)([A-Z]?\d{1,3}[A-Z]?)\b/iu', $text, $referenceMatches);
    $signals['references'] = array_values(array_unique(array_map(
      static fn ($value): string => strtoupper((string) $value),
      $referenceMatches[1] ?? [],
    )));

    preg_match_all('/\b(\d{3,4})\s*(?:x|×)\s*(\d{3,4})\b/iu', $text, $dimensionMatches, PREG_SET_ORDER);
    foreach ($dimensionMatches as $match) {
      $width = (int) $match[1];
      $height = (int) $match[2];
      if ($width >= 300 && $width <= 6000 && $height >= 300 && $height <= 6000) {
        $signals['dimensions'][] = [
          'width_mm' => $width,
          'height_mm' => $height,
          'basis' => (string) $match[0],
        ];
      }
    }

    $quantityPatterns = [
      '/\b(?:kozijn|type)\s*([A-Z]?\d{1,3}[A-Z]?).{0,50}?\b(?:aantal|qty|stuks?)\s*[:x-]?\s*(\d{1,4})\b/iu',
      '/\b(\d{1,4})\s*(?:stuks?|st\.)\s*(?:van\s+)?(?:kozijn|type)?\s*([A-Z]?\d{1,3}[A-Z]?)\b/iu',
      '/\b([A-Z]?\d{1,3}[A-Z]?)\s*[-:]?\s*(\d{1,4})\s*(?:stuks?|st\.)\b/iu',
    ];
    foreach ($quantityPatterns as $index => $pattern) {
      preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $reference = $index === 1 ? strtoupper((string) $match[2]) : strtoupper((string) $match[1]);
        $quantity = (int) ($index === 1 ? $match[1] : $match[2]);
        if ($quantity > 0 && $quantity <= 10000) {
          $signals['quantities'][] = [
            'reference' => $reference,
            'quantity' => $quantity,
            'basis' => (string) $match[0],
          ];
        }
      }
    }

    foreach (['kunststof', 'aluminium', 'hout'] as $material) {
      if (preg_match('/\b' . preg_quote($material, '/') . '\b/iu', $text)) {
        $signals['materials'][] = $material;
      }
    }

    $openingTerms = [
      'vast' => ['vastglas', 'vast raam', 'vast'],
      'draaikiep' => ['draaikiep', 'draai-kiep', 'draaikiepraam'],
      'draai' => ['draairaam'],
      'deur' => ['balkondeur', 'tuindeur', 'deur'],
      'schuif' => ['schuifpui', 'schuifdeur'],
    ];
    foreach ($openingTerms as $label => $terms) {
      foreach ($terms as $term) {
        if (mb_stripos($text, $term) !== FALSE) {
          $signals['opening_types'][] = $label;
          break;
        }
      }
    }
    $signals['opening_types'] = array_values(array_unique($signals['opening_types']));

    return $signals;
  }

  private function correlateByReference(array $documents): array {
    $types = [];

    foreach ($documents as $document) {
      $signals = $document['signals'] ?? [];
      $references = $signals['references'] ?? [];
      foreach (($signals['quantities'] ?? []) as $quantity) {
        $ref = strtoupper((string) ($quantity['reference'] ?? ''));
        if ($ref !== '' && !in_array($ref, $references, TRUE)) {
          $references[] = $ref;
        }
      }

      foreach ($references as $reference) {
        $reference = strtoupper((string) $reference);
        if ($reference === '') {
          continue;
        }
        if (!isset($types[$reference])) {
          $types[$reference] = [
            'reference' => $reference,
            'quantity_candidates' => [],
            'dimension_candidates' => [],
            'materials' => [],
            'opening_types' => [],
            'sources' => [],
            'actual_quantity' => NULL,
            'drawing_representation_only' => FALSE,
            'needs_review' => TRUE,
          ];
        }

        $types[$reference]['sources'][] = [
          'file' => $document['name'],
          'role' => $document['role'],
          'confidence' => $document['confidence'],
          'excerpt' => $document['excerpt'],
        ];

        foreach (($signals['dimensions'] ?? []) as $dimension) {
          $types[$reference]['dimension_candidates'][] = [
            'width_mm' => $dimension['width_mm'],
            'height_mm' => $dimension['height_mm'],
            'source' => $document['name'],
            'role' => $document['role'],
          ];
        }
        foreach (($signals['quantities'] ?? []) as $quantity) {
          if (strtoupper((string) ($quantity['reference'] ?? '')) !== $reference) {
            continue;
          }
          $types[$reference]['quantity_candidates'][] = [
            'quantity' => (int) $quantity['quantity'],
            'source' => $document['name'],
            'role' => $document['role'],
            'basis' => $quantity['basis'],
          ];
        }
        $types[$reference]['materials'] = array_values(array_unique(array_merge(
          $types[$reference]['materials'],
          $signals['materials'] ?? [],
        )));
        $types[$reference]['opening_types'] = array_values(array_unique(array_merge(
          $types[$reference]['opening_types'],
          $signals['opening_types'] ?? [],
        )));
      }
    }

    foreach ($types as &$type) {
      $quantities = array_values(array_unique(array_map(
        static fn (array $candidate): int => (int) $candidate['quantity'],
        $type['quantity_candidates'],
      )));
      if (count($quantities) === 1) {
        $type['actual_quantity'] = $quantities[0];
      }

      $roles = array_values(array_unique(array_column($type['sources'], 'role')));
      $hasDrawing = in_array('drawing', $roles, TRUE);
      $hasQuantitySource = (bool) array_filter(
        $type['quantity_candidates'],
        static fn (array $candidate): bool => in_array($candidate['role'], ['schedule', 'specification', 'offer'], TRUE),
      );
      $type['drawing_representation_only'] = $hasDrawing && $hasQuantitySource && (int) ($type['actual_quantity'] ?? 0) > 1;

      $dimensionKeys = array_values(array_unique(array_map(
        static fn (array $candidate): string => $candidate['width_mm'] . 'x' . $candidate['height_mm'],
        $type['dimension_candidates'],
      )));
      $type['needs_review'] = count($quantities) !== 1 || count($dimensionKeys) > 1;
    }
    unset($type);

    ksort($types);
    return $types;
  }

  private function collectUnassignedEvidence(array $documents): array {
    $evidence = [];
    foreach ($documents as $document) {
      $signals = $document['signals'] ?? [];
      if (!empty($signals['references'])) {
        continue;
      }
      if (empty($signals['dimensions']) && empty($signals['materials']) && empty($signals['opening_types'])) {
        continue;
      }
      $evidence[] = [
        'file' => $document['name'],
        'role' => $document['role'],
        'dimensions' => $signals['dimensions'] ?? [],
        'materials' => $signals['materials'] ?? [],
        'opening_types' => $signals['opening_types'] ?? [],
      ];
    }
    return $evidence;
  }

  private function collectConflicts(array $types): array {
    $conflicts = [];
    foreach ($types as $type) {
      $quantities = array_values(array_unique(array_map(
        static fn (array $candidate): int => (int) $candidate['quantity'],
        $type['quantity_candidates'],
      )));
      if (count($quantities) > 1) {
        $conflicts[] = [
          'reference' => $type['reference'],
          'field' => 'quantity',
          'values' => $quantities,
          'message' => 'Meerdere aantallen gevonden; menselijke controle nodig.',
        ];
      }

      $dimensions = array_values(array_unique(array_map(
        static fn (array $candidate): string => $candidate['width_mm'] . 'x' . $candidate['height_mm'],
        $type['dimension_candidates'],
      )));
      if (count($dimensions) > 1) {
        $conflicts[] = [
          'reference' => $type['reference'],
          'field' => 'dimensions',
          'values' => $dimensions,
          'message' => 'Meerdere maatcombinaties gevonden; broncontext controleren.',
        ];
      }
    }
    return $conflicts;
  }

  private function classifyRole(string $filename, string $text): string {
    $haystack = mb_strtolower($filename . ' ' . mb_substr($text, 0, 1500));
    $map = [
      'schedule' => ['kozijnstaat', 'elementenstaat', 'staat kozijnen', 'window schedule'],
      'drawing' => ['kozijntekening', 'detailtekening', 'aanzicht', 'doorsnede', 'tekening'],
      'floorplan' => ['plattegrond', 'floor plan'],
      'offer' => ['offerte', 'prijsaanvraag', 'quotation'],
      'specification' => ['bestek', 'werkomschrijving', 'omschrijving', 'scope'],
      'photo' => ['foto', 'image', 'img_'],
    ];
    foreach ($map as $role => $terms) {
      foreach ($terms as $term) {
        if (mb_stripos($haystack, $term) !== FALSE) {
          return $role;
        }
      }
    }
    return 'other';
  }

  private function mergeSignals(array $left, array $right): array {
    foreach (['references', 'materials', 'opening_types'] as $key) {
      $left[$key] = array_values(array_unique(array_merge($left[$key] ?? [], $right[$key] ?? [])));
    }
    $left['dimensions'] = array_merge($left['dimensions'] ?? [], $right['dimensions'] ?? []);
    $left['quantities'] = array_merge($left['quantities'] ?? [], $right['quantities'] ?? []);
    return $left;
  }

  private function isReadableExtension(string $extension): bool {
    return in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'docx', 'xlsx'], TRUE);
  }

  private function emptyResult(string $status): array {
    return [
      'status' => $status,
      'canonical_truth' => FALSE,
      'documents' => [],
      'types' => [],
      'unassigned_evidence' => [],
      'conflicts' => [],
      'summary' => [
        'documents_read' => 0,
        'documents_indexed' => 0,
        'types_correlated' => 0,
        'conflicts' => 0,
      ],
    ];
  }

}
