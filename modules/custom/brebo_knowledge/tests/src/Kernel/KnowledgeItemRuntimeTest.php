<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_knowledge\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies the canonical KnowledgeItem Mapping 1.2 configuration.
 *
 * @group brebo_knowledge
 */
#[RunTestsInSeparateProcesses]
final class KnowledgeItemRuntimeTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'node',
    'brebo_knowledge',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'node']);
    $this->container->get('module_handler')->loadInclude('brebo_knowledge', 'install');
  }

  public function testUpdateCreatesExactConfigurationAndIsIdempotent(): void {
    brebo_knowledge_update_11001();
    brebo_knowledge_update_11001();

    $type = NodeType::load('brebo_knowledge_item');
    self::assertNotNull($type);
    self::assertSame('Probleemgerichte kennisbijdrage', $type->label());
    self::assertTrue($type->shouldCreateNewRevision());

    $required = [
      'field_knowledge_observation',
      'field_knowledge_meaning',
      'field_knowledge_risk',
      'field_knowledge_next_step',
      'field_knowledge_basis',
    ];
    $optional = [
      'field_knowledge_regie',
      'field_knowledge_realization',
    ];
    $fields = [...$required, ...$optional];

    foreach ($fields as $field_name) {
      $storage = FieldStorageConfig::loadByName('node', $field_name);
      self::assertNotNull($storage, $field_name);
      self::assertSame('text_long', $storage->getType());
      self::assertSame(1, $storage->getCardinality());
      self::assertFalse($storage->isTranslatable());

      $field = FieldConfig::loadByName('node', 'brebo_knowledge_item', $field_name);
      self::assertNotNull($field, $field_name);
      self::assertSame(in_array($field_name, $required, TRUE), $field->isRequired());
      self::assertFalse($field->isTranslatable());
    }

    $display = EntityFormDisplay::load('node.brebo_knowledge_item.default');
    self::assertNotNull($display);
    foreach ($fields as $index => $field_name) {
      $component = $display->getComponent($field_name);
      self::assertNotNull($component, $field_name);
      self::assertSame('text_textarea', $component['type']);
      self::assertSame(($index + 1) * 10, $component['weight']);
    }

    self::assertNull(
      $this->container->get('entity_type.manager')
        ->getStorage('entity_view_display')
        ->load('node.brebo_knowledge_item.default'),
    );

    $source = $this->container->get('extension.list.module')->getPath('brebo_knowledge') . '/config/install';
    $names = (new \Drupal\Core\Config\FileStorage($source))->listAll();
    self::assertCount(16, $names);
  }

}
