<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Wind\WindLoadCalculator;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** Validates explicit wind inputs without inventing missing technical data. */
final class EuropakozijnWindValidationController extends ControllerBase {

  public function __construct(
    private readonly WindLoadCalculator $calculator,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self($container->get('brebo_europakozijn.wind_load_calculator'));
  }

  public function validate(Request $request): JsonResponse {
    $payload = json_decode($request->getContent(), TRUE);
    if (!is_array($payload)) {
      return new JsonResponse(['state' => 'blocked', 'message' => 'Ongeldige windinvoer.'], 400);
    }

    $required = [
      'peak_velocity_pressure_kpa',
      'external_pressure_coefficient',
      'internal_pressure_coefficient',
      'partial_factor',
      'standard_reference',
      'calculation_reference',
      'verified',
    ];
    $missing = array_values(array_filter($required, static fn(string $key): bool => !array_key_exists($key, $payload)));
    if ($missing !== []) {
      return new JsonResponse([
        'state' => 'needs_input',
        'missing' => $missing,
        'message' => 'Niet alle traceerbare windgegevens zijn beschikbaar.',
      ], 422);
    }

    try {
      $result = $this->calculator->calculate(
        (float) $payload['peak_velocity_pressure_kpa'],
        (float) $payload['external_pressure_coefficient'],
        (float) $payload['internal_pressure_coefficient'],
        (float) $payload['partial_factor'],
        (string) $payload['standard_reference'],
        (string) $payload['calculation_reference'],
        (bool) $payload['verified'],
      );
    }
    catch (\InvalidArgumentException $exception) {
      return new JsonResponse([
        'state' => 'blocked',
        'message' => $exception->getMessage(),
      ], 422);
    }

    return new JsonResponse($result, $result['state'] === 'passed' ? 200 : 422);
  }

}
