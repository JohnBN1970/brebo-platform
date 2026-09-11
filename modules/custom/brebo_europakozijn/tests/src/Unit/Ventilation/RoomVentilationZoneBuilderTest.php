<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit\Ventilation;

use Drupal\brebo_europakozijn\Ventilation\RoomVentilationZoneBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\brebo_europakozijn\Ventilation\RoomVentilationZoneBuilder
 * @group brebo_europakozijn
 */
final class RoomVentilationZoneBuilderTest extends TestCase {

  /**
   * @covers ::build
   */
  public function testGroupsFramesAndFixedFieldsByRoom(): void {
    $builder = new RoomVentilationZoneBuilder();

    $rooms = [
      [
        'id' => 'room-living',
        'type' => 'woonkamer',
        'name' => 'Woonkamer',
        'area_m2' => 32.5,
        'ventilation_system' => 'natural_supply',
      ],
      [
        'id' => 'room-kitchen',
        'type' => 'keuken',
        'name' => 'Keuken',
        'area_m2' => 14,
        'ventilation_system' => 'mechanical_extract',
      ],
    ];

    $frames = [
      [
        'id' => 'frame-1',
        'position' => 'K01',
        'room_id' => 'room-living',
        'quantity' => 1,
        'configuration' => [
          'geometry' => [
            'fields' => [
              ['id' => 'r0c0', 'function' => 'vast', 'width_mm' => 900, 'height_mm' => 1200],
              ['id' => 'r0c1', 'function' => 'draaikiep', 'width_mm' => 800, 'height_mm' => 1200],
            ],
          ],
        ],
      ],
      [
        'id' => 'frame-2',
        'position' => 'K02',
        'room_id' => 'room-living',
        'quantity' => 2,
        'configuration' => [
          'geometry' => [
            'fields' => [
              ['id' => 'r0c0', 'function' => 'vast', 'width_mm' => 700, 'height_mm' => 1000],
            ],
          ],
        ],
      ],
      [
        'id' => 'frame-3',
        'position' => 'K03',
        'room_id' => 'room-kitchen',
        'quantity' => 1,
        'configuration' => [
          'geometry' => [
            'fields' => [
              ['id' => 'r0c0', 'function' => 'deur', 'width_mm' => 900, 'height_mm' => 2200],
            ],
          ],
        ],
      ],
    ];

    $zones = $builder->build($rooms, $frames);

    self::assertCount(2, $zones);
    self::assertSame('room-living', $zones[0]['room_id']);
    self::assertSame(2, $zones[0]['frame_count']);
    self::assertSame(3, $zones[0]['frame_unit_count']);
    self::assertSame(3, $zones[0]['fixed_glass_candidate_count']);
    self::assertCount(2, $zones[0]['fixed_glass_candidates']);
    self::assertSame('needs_verified_requirement', $zones[0]['assessment']['status']);
    self::assertNull($zones[0]['assessment']['required_capacity_dm3_s']);

    self::assertSame('room-kitchen', $zones[1]['room_id']);
    self::assertSame(1, $zones[1]['frame_count']);
    self::assertSame(0, $zones[1]['fixed_glass_candidate_count']);
  }

  /**
   * @covers ::build
   */
  public function testIgnoresUnknownRoomRelationsAndRoomsWithoutId(): void {
    $builder = new RoomVentilationZoneBuilder();

    $zones = $builder->build(
      [['id' => 'room-a', 'name' => 'A'], ['name' => 'No id']],
      [[
        'id' => 'frame-x',
        'room_id' => 'missing-room',
        'configuration' => ['geometry' => ['fields' => [['id' => 'r0c0', 'function' => 'vast']]]],
      ]],
    );

    self::assertCount(1, $zones);
    self::assertSame('room-a', $zones[0]['room_id']);
    self::assertSame(0, $zones[0]['frame_count']);
    self::assertSame(0, $zones[0]['fixed_glass_candidate_count']);
  }

}
