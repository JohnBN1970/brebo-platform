<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Ventilation;

/**
 * Resolves whether ventilation through the frame is actually required.
 *
 * Room type is context only and can never by itself trigger a ventilation
 * requirement. Positive decisions require explicit, verified evidence.
 */
final class VentilationRequirementResolver {

  public const VERSION = '1.0.0';

  /**
   * @param array<string, mixed> $context
   *   Verified facts about the room, existing provision and project scope.
   *
   * @return array<string, mixed>
   *   Requirement decision for the frame ventilation chain.
   */
  public function resolve(array $context): array {
    $base = [
      'engine_version' => self::VERSION,
      'status' => 'needs_assessment',
      'frame_supply_required' => NULL,
      'required_capacity_dm3_s' => NULL,
      'authority' => NULL,
      'source_reference' => NULL,
      'message' => 'Nog niet vastgesteld of ventilatietoevoer via het kozijn nodig is.',
    ];

    $result = static fn(array $override): array => array_replace($base, $override);

    $existingVerified = ($context['existing_provision_verified'] ?? FALSE) === TRUE;
    $existingSufficient = ($context['existing_provision_sufficient'] ?? NULL);

    if ($existingVerified && $existingSufficient === TRUE) {
      return $result([
        'status' => 'existing_provision_sufficient',
        'frame_supply_required' => FALSE,
        'authority' => $context['existing_provision_authority'] ?? NULL,
        'source_reference' => $context['existing_provision_source_reference'] ?? NULL,
        'message' => 'De bestaande geverifieerde ventilatievoorziening is toereikend; een extra rooster via het kozijn is niet nodig.',
      ]);
    }

    $requirementVerified = ($context['ventilation_requirement_verified'] ?? FALSE) === TRUE;
    if (!$requirementVerified) {
      return $base;
    }

    $required = $context['ventilation_required'] ?? NULL;
    if ($required === FALSE) {
      return $result([
        'status' => 'not_required',
        'frame_supply_required' => FALSE,
        'authority' => $context['requirement_authority'] ?? NULL,
        'source_reference' => $context['requirement_source_reference'] ?? NULL,
        'message' => 'Op basis van de geverifieerde situatie is geen aanvullende ventilatie-eis vastgesteld.',
      ]);
    }

    if ($required !== TRUE) {
      return $base;
    }

    $routeVerified = ($context['frame_supply_route_verified'] ?? FALSE) === TRUE;
    if (!$routeVerified) {
      return $result([
        'status' => 'requirement_verified_route_unknown',
        'authority' => $context['requirement_authority'] ?? NULL,
        'source_reference' => $context['requirement_source_reference'] ?? NULL,
        'message' => 'Een ventilatie-eis is geverifieerd, maar nog niet vastgesteld dat die via het kozijn moet worden ingevuld.',
      ]);
    }

    $viaFrame = $context['frame_supply_required'] ?? NULL;
    if ($viaFrame === FALSE) {
      return $result([
        'status' => 'required_elsewhere',
        'frame_supply_required' => FALSE,
        'authority' => $context['requirement_authority'] ?? NULL,
        'source_reference' => $context['requirement_source_reference'] ?? NULL,
        'message' => 'Er is een geverifieerde ventilatie-eis, maar die wordt niet via het kozijn ingevuld.',
      ]);
    }

    if ($viaFrame !== TRUE) {
      return $base;
    }

    return $result([
      'status' => 'required',
      'frame_supply_required' => TRUE,
      'authority' => $context['requirement_authority'] ?? NULL,
      'source_reference' => $context['requirement_source_reference'] ?? NULL,
      'message' => 'Ventilatietoevoer via het kozijn is geverifieerd vereist; de capaciteit mag nu in de volgende regelstap worden bepaald.',
    ]);
  }

}
