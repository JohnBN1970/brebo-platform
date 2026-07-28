<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_knowledge\Kernel;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Session\UserSession;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test de minimale KnowledgeItem-runtime.
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
    'options',
    'node',
    'brebo_knowledge',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'node', 'user']);
    $this->container->get('module_handler')->loadInclude('brebo_knowledge', 'install');
    brebo_knowledge_install();
    $this->setCurrentAccount(1, []);
  }

  public function testBundleFieldsIdentityLifecycleAndRevisions(): void {
    $node = $this->createKnowledgeItem('concept');

    self::assertSame('KI-000001', $node->get('field_brebo_stable_id')->value);
    self::assertFalse($node->isPublished());

    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    self::assertCount(1, $storage->revisionIds($node));

    $node->set('field_brebo_lifecycle_status', 'published');
    $node->save();
    self::assertTrue($node->isPublished());
    self::assertSame('KI-000001', $node->get('field_brebo_stable_id')->value);
    self::assertCount(2, $storage->revisionIds($node));

    $node->set('field_brebo_lifecycle_status', 'archived');
    $node->save();
    self::assertFalse($node->isPublished());
    self::assertCount(3, $storage->revisionIds($node));
  }

  public function testDeletedStableIdIsNeverReused(): void {
    $first = $this->createKnowledgeItem('concept');
    self::assertSame('KI-000001', $first->get('field_brebo_stable_id')->value);
    $first->delete();

    $second = $this->createKnowledgeItem('concept', 'Tweede kennisbijdrage');
    self::assertSame('KI-000002', $second->get('field_brebo_stable_id')->value);
  }

  public function testStableIdCannotBeChangedOrDuplicated(): void {
    $first = $this->createKnowledgeItem('concept');

    $first->set('field_brebo_stable_id', 'KI-000999');
    try {
      $first->save();
      self::fail('Wijzigen van een stable ID had moeten mislukken.');
    }
    catch (EntityStorageException $exception) {
      self::assertStringContainsString('onveranderlijk', $exception->getMessage());
    }

    $duplicate = Node::create([
      'type' => 'brebo_knowledge_item',
      'title' => 'Duplicaat',
      'status' => FALSE,
      'field_brebo_stable_id' => 'KI-000001',
      'field_brebo_observation' => 'Observatie',
      'field_brebo_meaning' => 'Betekenis',
      'field_brebo_urgency' => 'normal',
      'field_brebo_first_step' => 'Eerste stap',
      'field_brebo_lifecycle_status' => 'concept',
    ]);

    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('bestaat al');
    $duplicate->save();
  }

  public function testProgrammaticPublicationRequiresPermission(): void {
    $node = $this->createKnowledgeItem('concept');
    $this->setCurrentAccount(2, []);

    $node->set('field_brebo_lifecycle_status', 'published');
    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('Geen toestemming');
    $node->save();
  }

  public function testPublishedContentCannotBeChangedWithoutPublishPermission(): void {
    $node = $this->createKnowledgeItem('published');
    $this->setCurrentAccount(2, ['edit any brebo knowledge items']);

    $node->setTitle('Onbevoegd gewijzigde publieke titel');
    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('Geen toestemming');
    $node->save();
  }

  public function testAuthorizedPublisherCanChangePublishedContent(): void {
    $node = $this->createKnowledgeItem('published');
    $this->setCurrentAccount(3, [
      'edit any brebo knowledge items',
      'publish brebo knowledge items',
    ]);

    $node->setTitle('Geautoriseerd gewijzigde publieke titel');
    $node->save();

    self::assertSame('Geautoriseerd gewijzigde publieke titel', $node->label());
    self::assertTrue($node->isPublished());
  }

  private function createKnowledgeItem(string $lifecycle, string $title = 'Testkennisbijdrage'): Node {
    $node = Node::create([
      'type' => 'brebo_knowledge_item',
      'title' => $title,
      'status' => $lifecycle === 'published',
      'field_brebo_observation' => 'Er is vochtdoorslag zichtbaar.',
      'field_brebo_meaning' => 'De gevelschil moet nader worden onderzocht.',
      'field_brebo_urgency' => 'high',
      'field_brebo_first_step' => 'Voer een gerichte inspectie uit.',
      'field_brebo_lifecycle_status' => $lifecycle,
    ]);
    $node->save();
    return $node;
  }

  private function setCurrentAccount(int $uid, array $permissions): void {
    $role_id = 'runtime_test_' . $uid;
    $role = Role::load($role_id) ?: Role::create([
      'id' => $role_id,
      'label' => 'Runtime test ' . $uid,
    ]);
    $role->set('permissions', $permissions);
    $role->save();

    $account = new UserSession([
      'uid' => $uid,
      'name' => 'test-user-' . $uid,
      'roles' => ['authenticated', $role_id],
    ]);
    $this->container->get('current_user')->setAccount($account);
  }

}
