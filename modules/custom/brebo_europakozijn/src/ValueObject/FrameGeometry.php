<?php

namespace Drupal\brebo_europakozijn\ValueObject;

/**
 * Canonical simple grid geometry for mullion/transom calibration.
 *
 * V1 deliberately supports evenly distributed full-height/full-width members.
 * More complex local divisions can be added later without inventing pricing.
 */
final readonly class FrameGeometry {

  public function __construct(
    public int $widthMm,
    public int $heightMm,
    public int $verticalMullionCount = 0,
    public int $horizontalTransomCount = 0,
  ) {
    if ($this->widthMm <= 0 || $this->heightMm <= 0) {
      throw new \InvalidArgumentException('Frame dimensions must be positive.');
    }
    if ($this->verticalMullionCount < 0 || $this->horizontalTransomCount < 0) {
      throw new \InvalidArgumentException('Member counts cannot be negative.');
    }
  }

  public function fieldCount(): int {
    return ($this->verticalMullionCount + 1) * ($this->horizontalTransomCount + 1);
  }

  public function verticalMullions(): array {
    return array_map(
      fn (int $position): array => ['position_mm' => $position, 'length_mm' => $this->heightMm],
      $this->positions($this->verticalMullionCount, $this->widthMm),
    );
  }

  public function horizontalTransoms(): array {
    return array_map(
      fn (int $position): array => ['position_mm' => $position, 'length_mm' => $this->widthMm],
      $this->positions($this->horizontalTransomCount, $this->heightMm),
    );
  }

  public function toArray(): array {
    return [
      'width_mm' => $this->widthMm,
      'height_mm' => $this->heightMm,
      'vertical_mullions' => $this->verticalMullions(),
      'horizontal_transoms' => $this->horizontalTransoms(),
      'field_count' => $this->fieldCount(),
    ];
  }

  private function positions(int $count, int $spanMm): array {
    $positions = [];
    for ($i = 1; $i <= $count; $i++) {
      $positions[] = (int) round(($spanMm / ($count + 1)) * $i);
    }
    return $positions;
  }

}
