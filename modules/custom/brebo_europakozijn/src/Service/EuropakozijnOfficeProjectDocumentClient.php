<?php

namespace Drupal\brebo_europakozijn\Service;

use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;

final class EuropakozijnOfficeProjectDocumentClient {

  private const PATH = '/brebo-internal/intake/v1/project-document';
  private const SOURCE = 'brebo-platform.europakozijn';

  public function __construct(private readonly ClientInterface $httpClient) {}

  public function send(string $path, string $originalName, string $requestId, array $metadata = []): array {
    $baseUrl = rtrim(trim((string) Settings::get('brebo_office_base_url', getenv('BREBO_OFFICE_BASE_URL') ?: '')), '/');
    $secret = trim((string) Settings::get('brebo_shared_secret', getenv('BREBO_SHARED_SECRET') ?: ''));
    if (!str_starts_with($baseUrl, 'https://') || $secret === '') {
      return ['ok' => FALSE, 'status' => 'office_not_configured'];
    }

    $contents = file_get_contents($path);
    if ($contents === FALSE || $contents === '') {
      return ['ok' => FALSE, 'status' => 'document_read_failed'];
    }

    $sha256 = hash('sha256', $contents);
    $timestamp = (string) time();
    $canonical = implode("\n", ['POST', self::PATH, $sha256, $timestamp, $requestId]);
    $signature = 'v1=' . hash_hmac('sha256', $canonical, $secret);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $mime = match ($extension) {
      'pdf' => 'application/pdf',
      'png' => 'image/png',
      'jpg', 'jpeg' => 'image/jpeg',
      'webp' => 'image/webp',
      'heic' => 'image/heic',
      'heif' => 'image/heif',
      'zip' => 'application/zip',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'doc' => 'application/msword',
      'xls' => 'application/vnd.ms-excel',
      default => 'application/octet-stream',
    };

    $metadata = ['source' => self::SOURCE, 'source_label' => 'Website - Europakozijn'] + $metadata;
    $multipart = [[
      'name' => 'document',
      'contents' => $contents,
      'filename' => $originalName,
      'headers' => ['Content-Type' => $mime],
    ], [
      'name' => 'metadata',
      'contents' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
      'headers' => ['Content-Type' => 'application/json'],
    ]];

    try {
      $response = $this->httpClient->request('POST', $baseUrl . self::PATH, [
        'headers' => [
          'Accept' => 'application/json',
          'X-BREBO-Request-Id' => $requestId,
          'X-BREBO-Content-SHA256' => $sha256,
          'X-BREBO-Timestamp' => $timestamp,
          'X-BREBO-Signature' => $signature,
        ],
        'multipart' => $multipart,
        'connect_timeout' => 5.0,
        'timeout' => 120.0,
        'http_errors' => FALSE,
      ]);
    }
    catch (\Throwable) {
      return ['ok' => FALSE, 'status' => 'office_unavailable'];
    }

    $decoded = json_decode((string) $response->getBody(), TRUE);
    if ($response->getStatusCode() !== 202 || !is_array($decoded)) {
      return [
        'ok' => FALSE,
        'status' => 'office_rejected',
        'http_status' => $response->getStatusCode(),
      ];
    }

    $recognition = is_array($decoded['recognition'] ?? NULL) ? $decoded['recognition'] : [];

    return [
      'ok' => TRUE,
      'status' => (string) ($decoded['status'] ?? 'ok'),
      'request_id' => (string) ($decoded['request_id'] ?? $requestId),
      'recognition' => $recognition,
      'document_count' => (int) ($recognition['document_count'] ?? 0),
      'extracted_count' => (int) ($recognition['extracted_count'] ?? 0),
      'review_required' => (($decoded['intake']['status'] ?? '') === 'review_required'),
    ];
  }
}
