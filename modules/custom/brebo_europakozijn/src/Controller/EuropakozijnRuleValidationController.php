<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\brebo_europakozijn\Product\ProductRuleEngine;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Evaluates configurator schema v4 against product rules.
 */
final class EuropakozijnRuleValidationController extends ControllerBase {

  public function __construct(
    private readonly ProductRuleEngine $ruleEngine,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self($container->get('brebo_europakozijn.product_rule_engine'));
  }

  public function validate(Request $request): JsonResponse {
    $payload = json_decode($request->getContent(), TRUE);
    if (!is_array($payload) || ($payload['schema_version'] ?? NULL) !== 4) {
      return new JsonResponse([
        'valid' => FALSE,
        'errors' => [[
          'field' => 'schema_version',
          'code' => 'unsupported_schema_version',
          'message' => 'Deze configuratieversie wordt niet ondersteund.',
        ]],
      ], 400);
    }

    $brand = (string) ($payload['product_selection']['brand'] ?? '');
    $system = isset($payload['product_selection']['system']) && is_string($payload['product_selection']['system'])
      ? $payload['product_selection']['system']
      : NULL;
    $fields = $payload['geometry']['fields'] ?? NULL;

    if (!is_array($fields)) {
      return new JsonResponse([
        'valid' => FALSE,
        'errors' => [[
          'field' => 'geometry.fields',
          'code' => 'invalid_fields',
          'message' => 'De vakgeometrie ontbreekt of is ongeldig.',
        ]],
      ], 400);
    }

    $errors = [];
    foreach ($fields as $index => $field) {
      if (!is_array($field)) {
        continue;
      }

      $function = (string) ($field['function'] ?? '');
      $widthMm = (int) ($field['width_mm'] ?? 0);
      $heightMm = (int) ($field['height_mm'] ?? 0);
      $result = $this->ruleEngine->evaluateField($brand, $system, $function, $widthMm, $heightMm);

      if (!$result->allowed) {
        $errors[] = [
          'field' => sprintf('geometry.fields.%d', $index),
          'field_id' => $field['id'] ?? NULL,
          'code' => $result->code,
          'message' => $result->message,
          'authority' => $result->authority,
        ];
      }
    }

    return new JsonResponse([
      'valid' => $errors === [],
      'ruleset_version' => ProductRuleEngine::RULESET_VERSION,
      'errors' => $errors,
    ], $errors === [] ? 200 : 422);
  }

}
