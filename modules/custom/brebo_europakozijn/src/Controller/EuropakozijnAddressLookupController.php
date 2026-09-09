<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/** Resolves a public configurator address against official PDOK BAG data. */
final class EuropakozijnAddressLookupController extends ControllerBase {

  /**
   * PDOK introduced CQL2 filtering on the BAG v2 demo endpoint in September 2026.
   * Keep this endpoint isolated here so switching to production v2 later is one line.
   */
  private const ADDRESSES_URL = 'https://api.pdok.nl/kadaster/bag/ogc/v2-demo/collections/adres/items';

  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self($container->get('http_client'));
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

    return new JsonResponse([
      'found' => TRUE,
      'source' => 'PDOK/BAG',
      'address' => $address,
    ]);
  }

}
