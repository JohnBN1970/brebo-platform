<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** Sends a structured website request to the BREBO Office intake. */
final class EuropakozijnOfficeHandoffController extends ControllerBase {

  public function __construct(private readonly ClientInterface $httpClient) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('http_client'));
  }

  public function submit(Request $request): JsonResponse {
    $input = json_decode($request->getContent(), TRUE);
    if (!is_array($input)) {
      return $this->error(400, 'invalid_json');
    }

    $requestId = trim((string) ($input['request_id'] ?? ''));
    $observed = is_array($input['observed'] ?? NULL) ? $input['observed'] : [];
    $detected = is_array($input['detected'] ?? NULL) ? $input['detected'] : [];
    $calculated = is_array($input['calculated'] ?? NULL) ? $input['calculated'] : [];
    $selected = is_array($input['selected'] ?? NULL) ? $input['selected'] : [];

    if (!preg_match('/^[0-9a-f-]{36}$/i', $requestId)) {
      return $this->error(422, 'invalid_request_id');
    }
    if (!is_array($observed['building'] ?? NULL) || !is_array($observed['rooms'] ?? NULL) || $observed['rooms'] === [] || !is_array($observed['frames'] ?? NULL) || $observed['frames'] === []) {
      return $this->error(422, 'incomplete_request');
    }

    $payload = [
      'schema_version' => '1.0',
      'request_id' => $requestId,
      'source' => 'brebo-platform-europakozijn',
      'observed' => $observed,
      'detected' => $detected,
      'calculated' => $calculated,
      'selected' => $selected,
    ];
    $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $officeBaseUrl = rtrim(trim((string) Settings::get('brebo_office_base_url', getenv('BREBO_OFFICE_BASE_URL') ?: '')), '/');
    $secret = trim((string) Settings::get('brebo_shared_secret', getenv('BREBO_SHARED_SECRET') ?: ''));
    if ($officeBaseUrl === '' || $secret === '') {
      return $this->error(503, 'office_handoff_not_configured');
    }

    $path = '/brebo-internal/intake/europakozijn';
    $timestamp = (string) time();
    $canonical = implode("\n", ['POST', $path, hash('sha256', $body), $timestamp, $requestId]);
    $signature = hash_hmac('sha256', $canonical, $secret);

    try {
      $response = $this->httpClient->request('POST', $officeBaseUrl . $path, [
        'headers' => [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
          'X-BREBO-Timestamp' => $timestamp,
          'X-BREBO-Request-Id' => $requestId,
          'X-BREBO-Signature' => 'v1=' . $signature,
        ],
        'body' => $body,
        'timeout' => 10,
        'http_errors' => FALSE,
      ]);
    }
    catch (\Throwable) {
      return $this->error(502, 'office_unavailable');
    }

    $status = $response->getStatusCode();
    $officeResponse = json_decode((string) $response->getBody(), TRUE);
    if ($status < 200 || $status >= 300) {
      return $this->error(502, 'office_rejected_request');
    }

    return new JsonResponse([
      'status' => 'ok',
      'request_id' => $requestId,
      'office' => is_array($officeResponse) ? $officeResponse : ['status' => 'accepted'],
    ], 202, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
  }

  private function error(int $status, string $code): JsonResponse {
    return new JsonResponse(['status' => 'error', 'error' => ['code' => $code]], $status, ['Cache-Control' => 'private, no-store']);
  }

}
