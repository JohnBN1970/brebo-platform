<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Glass;

/**
 * Versioned numeric dataset contract for Kenniscentrum Glas wind tables.
 *
 * IMPORTANT: numeric rows are admitted only after exact verification against
 * the published source document. Search snippets, OCR and inferred values are
 * never technical truth. This first dataset version therefore establishes the
 * provenance and admission gate without inventing any composition values.
 */
final class GlassWindTableDataset {

  public const DATASET_VERSION = '2026-09-09.1';

  public function __construct(
    private readonly GlassWindTableSource $source,
  ) {}

  /**
   * Returns dataset provenance and current verification state.
   *
   * @return array<string, mixed>
   */
  public function metadata(): array {
    return [
      'dataset_version' => self::DATASET_VERSION,
      'source' => $this->source->metadata(),
      'numeric_rows_verified' => FALSE,
      'admission_rule' => 'exact_source_verification_required',
      'pressure_semantics' => 'kcg_windstuwdruk',
      'warning' => 'KCG windstuwdruk is not automatically equivalent to BREBO design pressure.',
    ];
  }

  /**
   * Returns verified rows for a family.
   *
   * Empty by design until the complete header/row relationship has been
   * verified from the original KCG PDF. This fail-closed state prevents a
   * plausible-looking but technically unproven glass composition becoming an
   * orderable product.
   *
   * @return array<int, array<string, mixed>>
   */
  public function rows(string $family): array {
    if (!in_array($family, $this->source->metadata()['families'], TRUE)) {
      throw new \InvalidArgumentException(sprintf('Unknown KCG glass family: %s', $family));
    }

    return [];
  }

  /**
   * Whether this dataset may currently produce an automatic composition.
   */
  public function canSelectComposition(): bool {
    return FALSE;
  }

}
