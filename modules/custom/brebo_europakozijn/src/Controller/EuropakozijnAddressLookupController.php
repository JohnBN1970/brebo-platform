<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/** Resolves a public configurator address against official PDOK data. */
final class EuropakozijnAddressLookupController extends ControllerBase {

  private const LOCATION_URL = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free';
  private const CACHE_TTL = 86400;

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly CacheBackendInterface $cache,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('http_client'),
      $container->get('cache.default'),
    );
  }

  public function lookup(Request $request): JsonResponse {
    $postcode = strtoupper(preg_replace('/\s+/', '', (string) $request->query->get('postcode', '')) ?? '');
    $houseNumberInput = trim((string) $request->query->get('house_number', ''));

    if (!preg_match('/^[1-9][0-9]{3}[A-Z]{2}$/', $postcode) || !preg_match('/^(\d+)(.*)$/u', $houseNumberInput, $match)) {
      return new JsonResponse([
        'found' => FALSE,
        'message' => 'Vul een geldige postcode en huisnummer in.',
      ], 400);
    }

    $houseNumber = (int) $match[1];
    $suffix = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim((string) ($match[2] ?? ''))) ?? '');
    $cacheId = 'brebo_europakozijn:pdok_address:' . hash('sha256', $postcode . '|' . $houseNumber . '|' . $suffix);

    if ($cached = $this->cache->get($cacheId)) {
      return new JsonResponse($cached->data, 200, ['X-BREBO-PDOK-Cache' => 'HIT']);
    }

    try {
      $response = $this->httpClient->request('GET', self::LOCATION_URL, [
        'query' => [
          'q' => trim($postcode . ' ' . $houseNumberInput),
          'fq' => 'type:adres',
          'rows' => 25,
        ],
        'headers' => ['Accept' => 'application/json'],
        'timeout' => 10,
      ]);
      $payload = json_decode((string) $response->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
    }
    catch (Throwable $exception) {
      return new JsonResponse([
        'found' => FALSE,
        'message' => 'De officiële adrescontrole is tijdelijk niet beschikbaar.',
      ], 503);
    }

    $candidates = [];
    foreach (($payload['response']['docs'] ?? []) as $doc) {
      if (!is_array($doc)) {
        continue;
      }

      $candidatePostcode = strtoupper(preg_replace('/\s+/', '', (string) ($doc['postcode'] ?? '')) ?? '');
      $candidateNumber = (int) ($doc['huisnummer'] ?? 0);
      if ($candidatePostcode !== $postcode || $candidateNumber !== $houseNumber) {
        continue;
      }

      $houseLetter = strtoupper(trim((string) ($doc['huisletter'] ?? '')));
      $addition = strtoupper(trim((string) ($doc['huisnummertoevoeging'] ?? '')));
      $candidateSuffix = preg_replace('/[^A-Z0-9]/', '', $houseLetter . $addition) ?? '';
      if ($suffix !== '' && $candidateSuffix !== $suffix) {
        continue;
      }

      $coordinates = NULL;
      if (preg_match('/^POINT\(([-0-9.]+)\s+([-0-9.]+)\)$/', (string) ($doc['centroide_rd'] ?? ''), $point)) {
        $coordinates = ['x' => (float) $point[1], 'y' => (float) $point[2]];
      }

      $candidates[] = [
        'street' => $doc['straatnaam'] ?? NULL,
        'house_number' => (string) $houseNumber,
        'house_letter' => $doc['huisletter'] ?? NULL,
        'addition' => $doc['huisnummertoevoeging'] ?? NULL,
        'postal_code' => $candidatePostcode,
        'city' => $doc['woonplaatsnaam'] ?? NULL,
        'bag_nummeraanduiding_id' => $doc['nummeraanduiding_id'] ?? NULL,
        'bag_adresseerbaar_object_id' => $doc['adresseerbaarobject_id'] ?? NULL,
        'coordinates' => $coordinates,
      ];
    }

    if ($candidates === []) {
      return new JsonResponse([
        'found' => FALSE,
        'message' => 'Dit adres is niet gevonden in de officiële BAG.',
      ], 404);
    }

    $address = $candidates[0];
    $displayNumber = $address['house_number'] . ($address['house_letter'] ?? '') . (($address['addition'] ?? '') !== '' ? '-' . $address['addition'] : '');
    $address['display'] = trim(sprintf('%s %s, %s %s', $address['street'] ?? '', $displayNumber, $address['postal_code'] ?? '', $address['city'] ?? ''));

    $result = [
      'found' => TRUE,
      'source' => 'PDOK/BAG',
      'address' => $address,
      // Richer building facts are deliberately resolved later. The website
      // must not block a valid official address on optional enrichment.
      'building_context' => NULL,
    ];
    $this->cache->set($cacheId, $result, time() + self::CACHE_TTL);

    return new JsonResponse($result, 200, ['X-BREBO-PDOK-Cache' => 'MISS']);
  }

}
