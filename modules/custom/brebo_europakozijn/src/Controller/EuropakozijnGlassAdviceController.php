<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Glass\GlassWindTableSource;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exposes the evidence state for automatic glass advice per configured field.
 */
final class EuropakozijnGlassAdviceController extends ControllerBase {

  public function __construct(
    private readonly GlassWindTableSource $source,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self($container->get('brebo_europakozijn.glass_wind_table_source'));
  }

  public function advise(Request $request): JsonResponse {
    $payload = json_decode((string) $request->getContent(), TRUE);
    if (!is_array($payload) || (int) ($payload['schema_version'] ?? 0) !== 4) {
      return new JsonResponse(['state' => 'blocked', 'message' => 'Configuratie ontbreekt of heeft een niet-ondersteunde schemaversie.'], 400);
    }

    $fields = $payload['geometry']['fields'] ?? NULL;
    if (!is_array($fields) || $fields === []) {
      return new JsonResponse(['state' => 'blocked', 'message' => 'Er zijn geen glasvelden beschikbaar voor beoordeling.'], 400);
    }

    $assessments = [];
    $technicalReview = FALSE;
    foreach ($fields as $field) {
      if (!is_array($field)) {
        continue;
      }
      $assessment = $this->source->assessScope(
        (float) ($field['width_mm'] ?? 0),
        (float) ($field['height_mm'] ?? 0),
      );
      if (($assessment['state'] ?? '') !== 'within_scope') {
        $technicalReview = TRUE;
      }
      $assessments[] = [
        'field_id' => (string) ($field['id'] ?? ''),
        'function' => (string) ($field['function'] ?? ''),
        'width_mm' => (float) ($field['width_mm'] ?? 0),
        'height_mm' => (float) ($field['height_mm'] ?? 0),
        'scope_state' => $assessment['state'] ?? 'blocked',
        'area_m2' => $assessment['area_m2'] ?? NULL,
        'reasons' => $assessment['reasons'] ?? [],
      ];
    }

    if ($assessments === []) {
      return new JsonResponse(['state' => 'blocked', 'message' => 'De glasvelden konden niet worden beoordeeld.'], 400);
    }

    $metadata = $this->source->metadata();
    return new JsonResponse([
      'state' => $technicalReview ? 'technical_review' : 'source_scope_verified',
      'automatic_glass_composition_available' => FALSE,
      'message' => $technicalReview
        ? 'Minimaal één glasveld valt buiten de automatische Kenniscentrum Glas-tabelscope.'
        : 'Alle glasvelden vallen binnen de geverifieerde Kenniscentrum Glas-tabelscope. De numerieke tabeldata en winddruk-koppeling zijn nog vereist voor een glasopbouwadvies.',
      'source' => [
        'source_id' => $metadata['source_id'],
        'source_version' => $metadata['source_version'],
        'publisher' => $metadata['publisher'],
      ],
      'fields' => $assessments,
    ]);
  }

}
