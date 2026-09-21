<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** Server-side adapter to the canonical Office kozijn price engine. */
final class EuropakozijnPriceIndicationController extends ControllerBase {

  public function __construct(private readonly ClientInterface $httpClient) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('http_client'));
  }

  public function calculate(Request $request): JsonResponse {
    $input = json_decode($request->getContent(), TRUE);
    if (!is_array($input) || !is_array($input['configuration'] ?? NULL)) {
      return $this->error(422, 'invalid_payload');
    }

    $officeBaseUrl = rtrim(trim((string) Settings::get('brebo_office_base_url', getenv('BREBO_OFFICE_BASE_URL') ?: '')), '/');
    $secret = trim((string) Settings::get('brebo_shared_secret', getenv('BREBO_SHARED_SECRET') ?: ''));
    if ($officeBaseUrl === '' || $secret === '') {
      return $this->unavailable();
    }

    $requestId = $this->uuid();
    $payload = ['schema_version' => '1.0', 'configuration' => $input['configuration']];
    $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $path = '/brebo-internal/price-indication/kozijn';
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
        'timeout' => 5,
        'http_errors' => FALSE,
      ]);
    }
    catch (\Throwable) {
      return $this->unavailable();
    }

    $data = json_decode((string) $response->getBody(), TRUE);
    if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || !is_array($data)) {
      return $this->unavailable();
    }

    // Office owns the public projection. Platform forwards only that DTO.
    return new JsonResponse([
      'status' => 'ok',
      'indication' => is_array($data['indication'] ?? NULL)
        ? $data['indication']
        : ['status' => 'temporarily_unavailable'],
    ], 200, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
  }

  private function unavailable(): JsonResponse {
    return new JsonResponse([
      'status' => 'ok',
      'indication' => ['status' => 'temporarily_unavailable'],
    ], 200, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
  }

  private function error(int $status, string $code): JsonResponse {
    return new JsonResponse(['status' => 'error', 'error' => ['code' => $code]], $status, ['Cache-Control' => 'private, no-store']);
  }

  private function uuid(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

}
