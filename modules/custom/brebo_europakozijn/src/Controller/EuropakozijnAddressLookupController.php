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

/** Resolves a public configurator address against official PDOK BAG data. */
final class EuropakozijnAddressLookupController extends ControllerBase {

  private const ADDRESSES_URL = 'https://api.pdok.nl/kadaster/bag/ogc/v2/collections/adres/items';
  private const VBO_URL = 'https://api.pdok.nl/kadaster/bag/ogc/v2/collections/verblijfsobject/items';
  private const BAG_V2_BASE = 'https://api.pdok.nl/kadaster/bag/ogc/v2/';
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

    $filter = sprintf("postcode='%s' AND huisnummer=%d", str_replace("'", "''", $postcode), $houseNumber);

    try {
      $response = $this->httpClient->request('GET', self::ADDRESSES_URL, [
        'query' => [
          'limit' => 25,
          'f' => 'json',
          'filter' => $filter,
          'filter-lang' => 'cql2-text',
        ],
        'headers' => ['Accept' => 'application/geo+json, application/json'],
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
    foreach (($payload['features'] ?? []) as $feature) {
      $properties = $feature['properties'] ?? [];
      if ((int) ($properties['huisnummer'] ?? 0) !== $houseNumber) {
        continue;
      }

      $houseLetter = strtoupper(trim((string) ($properties['huisletter'] ?? '')));
      $addition = strtoupper(trim((string) ($properties['toevoeging'] ?? '')));
      $candidateSuffix = preg_replace('/[^A-Z0-9]/', '', $houseLetter . $addition) ?? '';
      if ($suffix !== '' && $candidateSuffix !== $suffix) {
        continue;
      }

      $geometry = $feature['geometry']['coordinates'] ?? NULL;
      $candidates[] = [
        'street' => $properties['openbare_ruimte_naam'] ?? NULL,
        'house_number' => (string) $houseNumber,
        'house_letter' => $properties['huisletter'] ?? NULL,
        'addition' => $properties['toevoeging'] ?? NULL,
        'postal_code' => $properties['postcode'] ?? $postcode,
        'city' => $properties['woonplaats_naam'] ?? NULL,
        'bag_nummeraanduiding_id' => $properties['identificatie'] ?? NULL,
        'bag_adresseerbaar_object_id' => $properties['adresseerbaar_object_identificatie'] ?? NULL,
        'coordinates' => is_array($geometry) && count($geometry) >= 2 ? [
          'x' => $geometry[0],
          'y' => $geometry[1],
        ] : NULL,
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
      // Building enrichment is deliberately best-effort. An unavailable
      // secondary BAG request must never make a valid address unusable.
      'building_context' => $this->resolveBuildingContext((string) ($address['bag_adresseerbaar_object_id'] ?? '')),
    ];
    $this->cache->set($cacheId, $result, time() + self::CACHE_TTL);

    return new JsonResponse($result, 200, ['X-BREBO-PDOK-Cache' => 'MISS']);
  }

  /**
   * Returns source facts about the BAG verblijfsobject and related pand.
   *
   * No customer-facing building type is inferred here: corner/terrace/detached
   * is not a canonical BAG fact. Those choices remain separate until proven.
   *
   * @return array<string, mixed>|null
   */
  private function resolveBuildingContext(string $adresseerbaarObjectId): ?array {
    if ($adresseerbaarObjectId === '') {
      return NULL;
    }

    try {
      $filter = sprintf("identificatie='%s'", str_replace("'", "''", $adresseerbaarObjectId));
      $response = $this->httpClient->request('GET', self::VBO_URL, [
        'query' => [
          'limit' => 1,
          'f' => 'json',
          'filter' => $filter,
          'filter-lang' => 'cql2-text',
        ],
        'headers' => ['Accept' => 'application/geo+json, application/json'],
        'timeout' => 10,
      ]);
      $payload = json_decode((string) $response->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
      $feature = $payload['features'][0] ?? NULL;
      if (!is_array($feature)) {
        return NULL;
      }

      $properties = $feature['properties'] ?? [];
      $context = [
        'bag_verblijfsobject_id' => $properties['identificatie'] ?? $adresseerbaarObjectId,
        'gebruiksdoel' => $properties['gebruiksdoel'] ?? NULL,
        'oppervlakte_m2' => isset($properties['oppervlakte']) ? (int) $properties['oppervlakte'] : NULL,
        'verblijfsobject_status' => $properties['status'] ?? NULL,
        'bag_pand_id' => NULL,
        'bouwjaar' => NULL,
        'aantal_verblijfsobjecten' => NULL,
        'pand_status' => NULL,
      ];

      $pandHref = NULL;
      foreach (($properties['pand'] ?? []) as $relation) {
        if (is_array($relation) && isset($relation['href']) && is_string($relation['href'])) {
          $pandHref = $relation['href'];
          break;
        }
      }

      if ($pandHref === NULL || !str_starts_with($pandHref, self::BAG_V2_BASE . 'collections/pand/items/')) {
        return $context;
      }

      $pandResponse = $this->httpClient->request('GET', $pandHref, [
        'query' => ['f' => 'json'],
        'headers' => ['Accept' => 'application/geo+json, application/json'],
        'timeout' => 10,
      ]);
      $pandPayload = json_decode((string) $pandResponse->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
      $pandProperties = $pandPayload['properties'] ?? [];

      $context['bag_pand_id'] = $pandProperties['identificatie'] ?? NULL;
      $context['bouwjaar'] = isset($pandProperties['bouwjaar']) ? (int) $pandProperties['bouwjaar'] : NULL;
      $context['aantal_verblijfsobjecten'] = isset($pandProperties['aantal_verblijfsobjecten']) ? (int) $pandProperties['aantal_verblijfsobjecten'] : NULL;
      $context['pand_status'] = $pandProperties['status'] ?? NULL;

      return $context;
    }
    catch (Throwable $exception) {
      return NULL;
    }
  }

}
