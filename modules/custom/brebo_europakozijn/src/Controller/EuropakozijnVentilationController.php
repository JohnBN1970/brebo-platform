<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Ventilation\VentilationRequirementResolver;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** Public-safe ventilation requirement assessment for the configurator. */
final class EuropakozijnVentilationController extends ControllerBase {

  public function __construct(
    private readonly VentilationRequirementResolver $resolver,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self($container->get('brebo_europakozijn.ventilation_requirement_resolver'));
  }

  public function assess(Request $request): JsonResponse {
    $payload = json_decode($request->getContent(), TRUE);
    if (!is_array($payload)) {
      return new JsonResponse(['state' => 'blocked', 'message' => 'Ongeldige ventilatie-invoer.'], 400);
    }

    $room = is_array($payload['room_context'] ?? NULL) ? $payload['room_context'] : [];
    $result = $this->resolver->resolve([
      'room_type' => $room['room_type'] ?? NULL,
      'room_area_m2' => $room['area_m2'] ?? NULL,
      'ventilation_system' => $room['ventilation_system'] ?? NULL,
      'existing_provision_verified' => ($room['existing_provision_verified'] ?? FALSE) === TRUE,
      'existing_provision_sufficient' => $room['existing_provision_sufficient'] ?? NULL,
      'existing_provision_authority' => $room['existing_provision_authority'] ?? NULL,
      'existing_provision_source_reference' => $room['existing_provision_source_reference'] ?? NULL,
      'ventilation_requirement_verified' => ($room['ventilation_requirement_verified'] ?? FALSE) === TRUE,
      'ventilation_required' => $room['ventilation_required'] ?? NULL,
      'frame_supply_route_verified' => ($room['frame_supply_route_verified'] ?? FALSE) === TRUE,
      'frame_supply_required' => $room['frame_supply_required'] ?? NULL,
      'requirement_authority' => $room['requirement_authority'] ?? NULL,
      'requirement_source_reference' => $room['requirement_source_reference'] ?? NULL,
    ]);

    return new JsonResponse($result, 200, ['Cache-Control' => 'private, no-store']);
  }

}
