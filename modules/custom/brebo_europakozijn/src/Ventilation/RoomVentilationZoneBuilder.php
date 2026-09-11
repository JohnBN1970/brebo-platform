<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Ventilation;

/**
 * Builds room-scoped ventilation zones without inventing normative airflow.
 *
 * This class is deliberately limited to structural facts already present in
 * the configurator: room identity, saved frames and fixed glazed fields.
 * Whether ventilation is required, its capacity and product selection belong
 * to separate verified rule layers.
 */
final class RoomVentilationZoneBuilder {

  public const VERSION = '1.0.1';

  /**
   * @param array<int, array<string, mixed>> $rooms
   *   Canonical room records.
   * @param array<int, array<string, mixed>> $frames
   *   Saved frame-state records.
   *
   * @return array<int, array<string, mixed>>
   *   One potential ventilation zone per room.
   */
  public function build(array $rooms, array $frames): array {
    $zones = [];

    foreach ($rooms as $room) {
      $roomId = trim((string) ($room['id'] ?? ''));
      if ($roomId === '') {
        continue;
      }

      $zones[$roomId] = [
        'engine_version' => self::VERSION,
        'room_id' => $roomId,
        'room' => [
          'type' => $room['type'] ?? NULL,
          'name' => $room['name'] ?? NULL,
          'area_m2' => isset($room['area_m2']) ? (float) $room['area_m2'] : NULL,
          'ventilation_system' => $room['ventilation_system'] ?? NULL,
        ],
        'frame_count' => 0,
        'frame_unit_count' => 0,
        'fixed_glass_candidate_count' => 0,
        'fixed_glass_candidates' => [],
        'assessment' => [
          'status' => 'needs_requirement_assessment',
          'frame_supply_required' => NULL,
          'required_capacity_dm3_s' => NULL,
          'authority' => NULL,
          'message' => 'Ruimte en vaste glasvakken zijn bekend. Eerst moet worden vastgesteld of ventilatietoevoer via het kozijn voor deze situatie nodig is.',
        ],
      ];
    }

    foreach ($frames as $frame) {
      $roomId = trim((string) ($frame['room_id'] ?? ''));
      if ($roomId === '' || !isset($zones[$roomId])) {
        continue;
      }

      $configuration = is_array($frame['configuration'] ?? NULL) ? $frame['configuration'] : [];
      $geometry = is_array($configuration['geometry'] ?? NULL) ? $configuration['geometry'] : [];
      $fields = is_array($geometry['fields'] ?? NULL) ? $geometry['fields'] : [];
      $quantity = max(1, (int) ($frame['quantity'] ?? 1));

      $zones[$roomId]['frame_count']++;
      $zones[$roomId]['frame_unit_count'] += $quantity;

      foreach ($fields as $field) {
        if (!is_array($field) || ($field['function'] ?? NULL) !== 'vast') {
          continue;
        }

        $candidate = [
          'frame_id' => $frame['id'] ?? NULL,
          'frame_position' => $frame['position'] ?? NULL,
          'frame_quantity' => $quantity,
          'field_id' => $field['id'] ?? NULL,
          'width_mm' => isset($field['width_mm']) ? (int) $field['width_mm'] : NULL,
          'height_mm' => isset($field['height_mm']) ? (int) $field['height_mm'] : NULL,
          'function' => 'vast',
        ];

        $zones[$roomId]['fixed_glass_candidates'][] = $candidate;
        $zones[$roomId]['fixed_glass_candidate_count'] += $quantity;
      }
    }

    return array_values($zones);
  }

}
