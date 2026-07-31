<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_knowledge\Kernel;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\MemoryStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Utility\UpdateException;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/** Tests the fail-closed KnowledgeItem Mapping 1.2 reconciler. */
#[RunTestsInSeparateProcesses]
final class KnowledgeItemRuntimeTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'brebo_knowledge'];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'node']);
    $this->container->get('module_handler')->loadInclude('brebo_knowledge', 'install');
  }

  public function testSuccessfulExecutionIsExactAndIdempotent(): void {
    brebo_knowledge_update_11001();
    $first = $this->snapshot();
    self::assertSame(_brebo_knowledge_config_names(), array_values(array_intersect(_brebo_knowledge_config_names(), array_keys($first))));
    self::assertCount(16, _brebo_knowledge_relevant_active_names($this->active()));
    brebo_knowledge_update_11001();
    self::assertSame($first, $this->snapshot());
  }

  public function testPartialCanonicalConfigurationCompletesSafely(): void {
    brebo_knowledge_update_11001();
    foreach (array_slice(_brebo_knowledge_config_names(), 1) as $name) {
      $this->active()->delete($name);
    }
    _brebo_knowledge_reconcile_configuration();
    self::assertCount(16, _brebo_knowledge_relevant_active_names($this->active()));
  }

  public function testMissingSourceFailsWriteFree(): void {
    $source = $this->memorySource();
    $source->delete('field.storage.node.field_knowledge_basis');
    $this->assertRejectedWithoutWrites($source, NULL, 'bronobject ontbreekt');
  }

  public function testInvalidSchemaFailsWriteFree(): void {
    $validator = static fn(string $name): ?string => str_contains($name, 'field_knowledge_basis') ? 'geldig configuratieschema ontbreekt' : NULL;
    $this->assertRejectedWithoutWrites($this->source(), $validator, 'configuratieschema ontbreekt');
  }

  public function testMissingModuleDependencyFailsWriteFree(): void {
    $source = $this->memorySource();
    $data = $source->read('field.storage.node.field_knowledge_basis');
    $data['dependencies']['module'][] = 'missing_module';
    $source->write('field.storage.node.field_knowledge_basis', $data);
    $this->assertRejectedWithoutWrites($source, NULL, 'missing_module');
  }

  public function testMissingConfigDependencyFailsWriteFree(): void {
    $source = $this->memorySource();
    $data = $source->read('field.field.node.brebo_knowledge_item.field_knowledge_basis');
    $data['dependencies']['config'][] = 'node.type.missing_bundle';
    $source->write('field.field.node.brebo_knowledge_item.field_knowledge_basis', $data);
    $this->assertRejectedWithoutWrites($source, NULL, 'node.type.missing_bundle');
  }

  public function testDependenciesDifferenceFailsWriteFree(): void {
    $this->installCanonical();
    $this->mutateActive('field.storage.node.field_knowledge_basis', static function (array &$data): void { $data['dependencies']['module'][] = 'system'; });
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'dependencies.module');
  }

  public function testHiddenDifferenceFailsWriteFree(): void {
    $this->installCanonical();
    $this->mutateActive('core.entity_form_display.node.brebo_knowledge_item.default', static function (array &$data): void { $data['hidden']['field_knowledge_basis'] = TRUE; });
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'hidden');
  }

  public function testRelevantPropertyDifferenceFailsWriteFree(): void {
    $this->installCanonical();
    $this->mutateActive('field.field.node.brebo_knowledge_item.field_knowledge_basis', static function (array &$data): void { $data['required'] = FALSE; });
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'required');
  }

  public function testLegacyConfigurationFailsWriteFree(): void {
    $this->active()->write('field.storage.node.field_brebo_legacy', ['id' => 'node.field_brebo_legacy']);
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'legacy-');
  }

  public function testExtraConfigurationFailsWriteFree(): void {
    $this->active()->write('field.storage.node.field_knowledge_extra', ['id' => 'node.field_knowledge_extra']);
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'extra-');
  }

  public function testPartialDifferentConfigurationFailsWriteFree(): void {
    $data = $this->source()->read('node.type.brebo_knowledge_item');
    $data['name'] = 'Afwijkend';
    $this->active()->write('node.type.brebo_knowledge_item', $data);
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'name');
  }

  public function testConflictingBundleLinkFailsWriteFree(): void {
    $source = $this->memorySource();
    $name = 'field.field.node.brebo_knowledge_item.field_knowledge_basis';
    $data = $source->read($name);
    $data['bundle'] = 'wrong_bundle';
    $source->write($name, $data);
    $this->assertRejectedWithoutWrites($source, NULL, 'koppeling');
  }

  public function testOrphanConfigurationFailsWriteFree(): void {
    $this->active()->write('field.field.node.brebo_knowledge_item.field_brebo_orphan', ['id' => 'node.brebo_knowledge_item.field_brebo_orphan']);
    $this->assertRejectedWithoutWrites($this->source(), NULL, 'orphanconfiguratie');
  }

  private function assertRejectedWithoutWrites(StorageInterface $source, ?callable $validator, string $message): void {
    $before = $this->snapshot();
    try {
      _brebo_knowledge_reconcile_configuration($source, $this->active(), $validator);
      self::fail('Preflight had moeten blokkeren.');
    }
    catch (UpdateException $exception) {
      self::assertStringContainsString($message, $exception->getMessage());
    }
    self::assertSame($before, $this->snapshot(), 'Afgewezen preflight moet write-free zijn.');
  }

  private function installCanonical(): void { brebo_knowledge_update_11001(); }
  private function active(): StorageInterface { return $this->container->get('config.storage'); }
  private function source(): StorageInterface {
    $path = $this->container->get('extension.list.module')->getPath('brebo_knowledge') . '/config/install';
    return new FileStorage($path);
  }
  private function memorySource(): MemoryStorage {
    $memory = new MemoryStorage();
    foreach (_brebo_knowledge_config_names() as $name) { $memory->write($name, $this->source()->read($name)); }
    return $memory;
  }
  private function snapshot(): array {
    $data = $this->active()->readMultiple($this->active()->listAll());
    ksort($data);
    return $data;
  }
  private function mutateActive(string $name, callable $mutator): void {
    $data = $this->active()->read($name);
    $mutator($data);
    $this->active()->write($name, $data);
  }

}
