<?php

declare(strict_types=1);

namespace Drupal\brebo_core\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionListModule;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Leest de platformstatus zonder configuratie of content te wijzigen.
 */
final class PlatformInspector {

  private const REQUIRED_MODULES = [
    'brebo_content_model',
    'brebo_context',
    'brebo_taxonomy',
    'brebo_projects',
    'brebo_lens',
    'brebo_cta',
    'brebo_hero',
    'brebo_page_banner',
    'brebo_pillars',
    'brebo_benefits',
  ];

  private const VOCABULARIES = [
    'doelgroepen',
    'gebouwdelen',
    'gebouwtypen',
    'materialen',
    'resultaattypen',
    'vraagstukken',
    'werkzaamheden',
  ];

  private const DIENST_FIELDS = [
    'field_building_issue',
    'field_signals',
    'field_causes',
    'field_consequences',
    'field_solution_directions',
    'field_results',
    'field_urgency',
    'field_faq',
    'field_building_types',
    'field_building_parts',
    'field_issue_types',
    'field_activities',
    'field_audiences',
    'field_materials',
    'field_result_types',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly Connection $database,
    private readonly ExtensionListModule $moduleList,
  ) {}

  /**
   * Geeft algemene gezondheidscontroles terug.
   */
  public function doctor(): array {
    $checks = [];

    $checks[] = $this->check('Drupal', \Drupal::VERSION, version_compare(\Drupal::VERSION, '11.0.0', '>='));
    $checks[] = $this->check('PHP', PHP_VERSION, version_compare(PHP_VERSION, '8.3.0', '>='));

    try {
      $this->database->query('SELECT 1')->fetchField();
      $checks[] = $this->check('Database', 'verbonden', TRUE);
    }
    catch (\Throwable $exception) {
      $checks[] = $this->check('Database', $exception->getMessage(), FALSE, 'critical');
    }

    foreach (self::REQUIRED_MODULES as $module) {
      $exists = $this->moduleExists($module);
      $enabled = $exists && $this->moduleHandler->moduleExists($module);
      $checks[] = $this->check(
        'Module ' . $module,
        !$exists ? 'ontbreekt' : ($enabled ? 'ingeschakeld' : 'uitgeschakeld'),
        $enabled,
        $exists ? 'warning' : 'critical',
      );
    }

    foreach ($this->integrity() as $item) {
      $checks[] = $item;
    }

    return $checks;
  }

  /**
   * Controleert alleen verwachte configuratie-objecten.
   */
  public function integrity(): array {
    $checks = [];
    $vocabularyStorage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
    foreach (self::VOCABULARIES as $vocabulary) {
      $checks[] = $this->check(
        'Taxonomie ' . $vocabulary,
        $vocabularyStorage->load($vocabulary) ? 'aanwezig' : 'ontbreekt',
        (bool) $vocabularyStorage->load($vocabulary),
      );
    }

    $fieldStorage = $this->entityTypeManager->getStorage('field_config');
    foreach (self::DIENST_FIELDS as $fieldName) {
      $id = 'node.dienst.' . $fieldName;
      $checks[] = $this->check(
        'Veld ' . $fieldName,
        $fieldStorage->load($id) ? 'aanwezig' : 'ontbreekt',
        (bool) $fieldStorage->load($id),
      );
    }

    return $checks;
  }

  /**
   * Geeft aantallen voor het kennisplatform terug.
   */
  public function statistics(): array {
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    $statistics = [
      'Dienstpagina’s' => (int) $nodeStorage->getQuery()->accessCheck(FALSE)->condition('type', 'dienst')->count()->execute(),
      'Projecten' => (int) $nodeStorage->getQuery()->accessCheck(FALSE)->condition('type', 'project')->count()->execute(),
    ];

    foreach (self::VOCABULARIES as $vocabulary) {
      $statistics['Termen: ' . $vocabulary] = (int) $termStorage->getQuery()->accessCheck(FALSE)->condition('vid', $vocabulary)->count()->execute();
    }

    return $statistics;
  }

  /**
   * Meet hoeveel Dienst-content nog onvoldoende is verrijkt.
   */
  public function readiness(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'dienst')->execute();
    $nodes = $storage->loadMultiple($ids);

    $result = [
      'Totaal Dienstpagina’s' => count($nodes),
      'Zonder FAQ' => 0,
      'Zonder resultaten' => 0,
      'Zonder metadata' => 0,
      'Zonder gerelateerde projecten' => 0,
    ];

    foreach ($nodes as $node) {
      if ($this->fieldEmpty($node, 'field_faq')) {
        $result['Zonder FAQ']++;
      }
      if ($this->fieldEmpty($node, 'field_results')) {
        $result['Zonder resultaten']++;
      }
      if ($this->fieldEmpty($node, 'field_related_projects')) {
        $result['Zonder gerelateerde projecten']++;
      }

      $metadataFields = [
        'field_building_types',
        'field_building_parts',
        'field_issue_types',
        'field_activities',
        'field_audiences',
      ];
      $hasMetadata = FALSE;
      foreach ($metadataFields as $fieldName) {
        if (!$this->fieldEmpty($node, $fieldName)) {
          $hasMetadata = TRUE;
          break;
        }
      }
      if (!$hasMetadata) {
        $result['Zonder metadata']++;
      }
    }

    return $result;
  }

  /**
   * Berekent een eenvoudige gezondheidsscore.
   */
  public function score(array $checks): int {
    if ($checks === []) {
      return 0;
    }
    $passed = count(array_filter($checks, static fn(array $check): bool => $check['ok']));
    return (int) round(($passed / count($checks)) * 100);
  }

  private function moduleExists(string $module): bool {
    try {
      return $this->moduleList->exists($module);
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

  private function fieldEmpty(object $entity, string $fieldName): bool {
    return !$entity->hasField($fieldName) || $entity->get($fieldName)->isEmpty();
  }

  private function check(string $name, string $value, bool $ok, string $failureLevel = 'warning'): array {
    return [
      'controle' => $name,
      'status' => $ok ? 'OK' : strtoupper($failureLevel),
      'waarde' => $value,
      'ok' => $ok,
    ];
  }

}
