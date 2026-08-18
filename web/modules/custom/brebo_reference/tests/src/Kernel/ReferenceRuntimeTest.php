<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_reference\Kernel;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Session\UserSession;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test de minimale Reference-runtime.
 *
 * @group brebo_reference
 */
#[RunTestsInSeparateProcesses]
final class ReferenceRuntimeTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'options',
    'datetime',
    'link',
    'file',
    'node',
    'brebo_reference',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installEntitySchema('node');
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'node', 'user']);
    $this->container->get('module_handler')->loadInclude('brebo_reference', 'install');
    brebo_reference_install();
    $this->setCurrentAccount(1, []);
  }

  public function testBundleFieldsIdentityLifecycleAndRevisions(): void {
    $node = $this->createReference('concept');

    self::assertSame('REF-000001', $node->get('field_brebo_reference_id')->value);
    self::assertFalse($node->isPublished());
    self::assertTrue($node->hasField('field_brebo_reference_type'));
    self::assertTrue($node->hasField('field_brebo_reference_url'));
    self::assertTrue($node->hasField('field_brebo_reference_status'));

    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    self::assertCount(1, $storage->revisionIds($node));

    foreach (['verified', 'approved', 'expired', 'archived'] as $expected_revision => $lifecycle) {
      $node->set('field_brebo_reference_status', $lifecycle);
      $node->save();
      self::assertSame($lifecycle === 'approved', $node->isPublished());
      self::assertSame('REF-000001', $node->get('field_brebo_reference_id')->value);
      self::assertCount($expected_revision + 2, $storage->revisionIds($node));
    }
  }

  public function testDeletedStableIdIsNeverReused(): void {
    $first = $this->createReference('concept');
    self::assertSame('REF-000001', $first->get('field_brebo_reference_id')->value);
    $first->delete();

    $second = $this->createReference('concept', 'Tweede referentie');
    self::assertSame('REF-000002', $second->get('field_brebo_reference_id')->value);
  }

  public function testStableIdCannotBeChangedOrDuplicated(): void {
    $first = $this->createReference('concept');

    $first->set('field_brebo_reference_id', 'REF-000999');
    try {
      $first->save();
      self::fail('Wijzigen van een Reference-ID had moeten mislukken.');
    }
    catch (EntityStorageException $exception) {
      self::assertStringContainsString('onveranderlijk', $exception->getMessage());
    }

    $duplicate = Node::create([
      'type' => 'brebo_reference',
      'title' => 'Duplicaat',
      'field_brebo_reference_id' => 'REF-000001',
      'field_brebo_reference_type' => 'standard',
      'field_brebo_reference_summary' => 'Samenvatting.',
      'field_brebo_reference_status' => 'concept',
    ]);

    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('bestaat al');
    $duplicate->save();
  }

  public function testProgrammaticApprovalRequiresPermission(): void {
    $node = $this->createReference('concept');
    $this->setCurrentAccount(2, []);

    $node->set('field_brebo_reference_status', 'approved');
    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('Geen toestemming');
    $node->save();
  }

  public function testApprovedContentCannotBeChangedWithoutApprovalPermission(): void {
    $node = $this->createReference('approved');
    $this->setCurrentAccount(2, ['edit any brebo references']);

    $node->setTitle('Onbevoegd gewijzigde goedgekeurde referentie');
    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('Geen toestemming');
    $node->save();
  }

  public function testAuthorizedApproverCanChangeApprovedContent(): void {
    $node = $this->createReference('approved');
    $this->setCurrentAccount(3, [
      'edit any brebo references',
      'approve brebo references',
    ]);

    $node->setTitle('Geautoriseerd gewijzigde referentie');
    $node->save();

    self::assertSame('Geautoriseerd gewijzigde referentie', $node->label());
    self::assertTrue($node->isPublished());
  }

  private function createReference(string $lifecycle, string $title = 'Testreferentie'): Node {
    $node = Node::create([
      'type' => 'brebo_reference',
      'title' => $title,
      'field_brebo_reference_type' => 'standard',
      'field_brebo_reference_summary' => 'Een gecontroleerde technische bron.',
      'field_brebo_reference_org' => 'BREBO',
      'field_brebo_reference_author' => 'Testauteur',
      'field_brebo_reference_version' => '1.0',
      'field_brebo_reference_date' => '2026-07-28',
      'field_brebo_reference_url' => ['uri' => 'https://example.com/reference'],
      'field_brebo_reference_status' => $lifecycle,
    ]);
    $node->save();
    return $node;
  }

  private function setCurrentAccount(int $uid, array $permissions): void {
    $role_id = 'reference_runtime_test_' . $uid;
    $role = Role::load($role_id) ?: Role::create([
      'id' => $role_id,
      'label' => 'Reference runtime test ' . $uid,
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
