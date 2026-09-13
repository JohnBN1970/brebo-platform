<?php

namespace Drupal\brebo_europakozijn\Service;

/**
 * Presents Office results. Does not recognise, classify or infer project scope.
 */
final class ProjectResultPresenter {

  public function __construct(private readonly ProjectResultThumbnail $thumbnail) {}

  public function present(array $result, string $directory): array {
    $rows = [];
    $reported = 0;
    $handoffFailed = FALSE;
    $files = is_array($result['files'] ?? NULL) ? $result['files'] : [];
    foreach ($files as $file) {
      if (!is_array($file)) {
        continue;
      }
      $office = is_array($file['office'] ?? NULL) ? $file['office'] : [];
      $recognition = is_array($office['recognition'] ?? NULL) ? $office['recognition'] : [];
      $handoffFailed = $handoffFailed || (($office['ok'] ?? FALSE) !== TRUE);
      $reported += $this->count($recognition['document_count'] ?? 0);
      $documents = is_array($recognition['documents'] ?? NULL) ? $recognition['documents'] : [];
      $documents = array_values(array_filter($documents, 'is_array'));
      // A received archive is one upload, not a list of invented child files.
      if ($documents === []) {
        $documents = [['filename' => $file['name'] ?? 'Projectstuk', 'status' => 'pending']];
      }
      foreach ($documents as $document) {
        $name = $this->text($document['filename'] ?? '', 'Projectstuk');
        $status = $this->text($document['status'] ?? '');
        [$label, $tone] = match ($status) {
          'extracted' => ['Uitgelezen', 'read'],
          'provider_error', 'extraction_failed', 'failed', 'error', 'unsupported',
          'unsupported_format', 'unsupported_type', 'unreadable', 'empty' => ['Uitlezen niet gelukt', 'failed'],
          default => ['Nog geen uitleesresultaat', 'pending'],
        };
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $format = match ($this->text($document['mime_type'] ?? '')) {
          'application/pdf' => 'PDF',
          'image/jpeg' => 'JPG',
          'image/png' => 'PNG',
          'image/webp' => 'WEBP',
          'image/heic' => 'HEIC',
          'image/heif' => 'HEIF',
          default => in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'zip', 'doc', 'docx', 'xls', 'xlsx'], TRUE) ? strtoupper($extension) : 'BESTAND',
        };
        $rows[] = [
          'id' => 'project-document-' . (count($rows) + 1),
          'number' => count($rows) + 1,
          'name' => $name,
          'format' => $format,
          'status' => $label,
          'tone' => $tone,
          'excerpt' => $this->text($document['excerpt'] ?? ''),
          // Thumbnails are presentation only, independently of extraction status.
          'thumbnail' => count($rows) < 5 ? $this->thumbnail->create($directory, $file, $document) : '',
        ];
      }
    }
    $summary = is_array($result['office_summary'] ?? NULL) ? $result['office_summary'] : [];
    $total = max(count($rows), $reported, $this->count($summary['documents_indexed'] ?? 0));
    $read = count(array_filter($rows, static fn (array $row): bool => $row['tone'] === 'read'));
    $failed = count(array_filter($rows, static fn (array $row): bool => $row['tone'] === 'failed'));
    $pending = $total - $read - $failed;
    $state = $handoffFailed ? 'Overdracht niet volledig' : ($failed > 0 ? ($read > 0 ? 'Deels uitgelezen' : 'Uitlezen niet gelukt') : ($pending > 0 ? 'Nog niet volledig uitgelezen' : ($read > 0 ? 'Eerste uitlezing gereed' : 'Nog geen uitleesresultaat')));
    return [
      'reference' => $this->text($result['intake_id'] ?? ''),
      'documents' => $rows,
      'gallery' => array_slice($rows, 0, 5),
      'total' => $total,
      'read' => $read,
      'failed' => $failed,
      'pending' => $pending,
      'missing_details' => max(0, $total - count($rows)),
      'handoff_failed' => $handoffFailed,
      'state' => $state,
      'state_tone' => $handoffFailed || $failed > 0 ? 'failed' : ($pending > 0 || $total === 0 ? 'pending' : 'read'),
    ];
  }

  private function text(mixed $value, string $fallback = ''): string {
    return is_string($value) && trim($value) !== '' ? trim($value) : $fallback;
  }

  private function count(mixed $value): int {
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]) ?: 0;
  }

}
