<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_knowledge\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;

/**
 * Test de minimale KnowledgeItem-runtime.
 *
 * @group brebo_knowledge
 */
final class KnowledgeItemRuntimeTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'options',
    'node',
    'brebo_knowledge',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'node']);
    brebo_knowledge_install();
  }

  public function testBundleFieldsIdentityAndLifecycle(): void {
    $node = Node::create([
      'type' => 'brebo_knowledge_item',
      'title' => 'Testkennisbijdrage',
      'field_brebo_observation' => 'Er is vochtdoorslag zichtbaar.',
      'field_brebo_meaning' => 'De gevelschil moet nader worden onderzocht.',
      'field_brebo_urgency' => 'high',
      'field_brebo_first_step' => 'Voer een gerichte inspectie uit.',
      'field_brebo_lifecycle_status' => 'concept',
    ]);
    $node->save();

    self::assertSame('KI-000001', $node->get('field_brebo_stable_id')->value);
    self::assertFalse($node->isPublished());
    self::assertTrue($node->isNewRevision());

    $node->set('field_brebo_lifecycle_status', 'published');
    $node->save();
    self::assertTrue($node->isPublished());
    self::assertSame('KI-000001', $node->get('field_brebo_stable_id')->value);

    $second = Node::create([
      'type' => 'brebo_knowledge_item',
      'title' => 'Tweede kennisbijdrage',
      'field_brebo_observation' => 'Kozijnen vertonen slijtage.',
      'field_brebo_meaning' => 'Onderhoud of vervanging moet worden afgewogen.',
      'field_brebo_urgency' => 'normal',
      'field_brebo_first_step' => 'Inspecteer aansluitingen en materiaalconditie.',
      'field_brebo_lifecycle_status' => 'archived',
    ]);
    $second->save();

    self::assertSame('KI-000002', $second->get('field_brebo_stable_id')->value);
    self::assertFalse($second->isPublished());
  }

}
