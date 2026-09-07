<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Validation\ConfigurationValidator;
use Drupal\brebo_europakozijn\ValueObject\FrameConfiguration;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Validates public configurator payloads server-side.
 */
final class EuropakozijnValidationController extends ControllerBase {

  public function __construct(
    private readonly ConfigurationValidator $validator,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self($container->get('brebo_europakozijn.configuration_validator'));
  }

  public function validate(Request $request): JsonResponse {
    $payload = json_decode($request->getContent(), TRUE);
    if (!is_array($payload)) {
      return new JsonResponse([
        'valid' => FALSE,
        'errors' => [['field' => '_payload', 'code' => 'invalid_json', 'message' => 'Ongeldige configuratie.']],
      ], 400);
    }

    $configuration = FrameConfiguration::fromArray($payload);
    $result = $this->validator->validate($configuration);
    $result['configuration'] = $configuration->toArray();

    return new JsonResponse($result, $result['valid'] ? 200 : 422);
  }

}
