<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_knowledge_review\Functional;

use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the BREBO Knowledge bulk review cockpit.
 *
 * @group brebo_knowledge_review
 */
#[RunTestsInSeparateProcesses]
final class BulkKnowledgeReviewFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'text',
    'brebo_knowledge',
    'brebo_knowledge_review',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that bulk approval requires explicit confirmation.
   */
  public function testBulkApprovalPublishesOnlyAfterExplicitConfirmation(): void {
    $knowledgeItem = $this->createKnowledgeItem();

    $path = '/admin/content/brebo-knowledge/review';
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);

    $reviewer = $this->drupalCreateUser(['review brebo knowledge items']);
    $this->drupalLogin($reviewer);
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Bulk-reviewcockpit');
    $this->assertSession()->pageTextContains('Condens tussen de glasbladen');
    $this->assertSession()->pageTextContains('Controle nodig');

    $this->submitForm([
      'items[' . $knowledgeItem->id() . '][select]' => TRUE,
      'action' => 'approve_publish',
      'sources' => 'BREBO technische beoordeling',
      'validity_date' => '2026-09-08',
      'review_note' => 'Eerste gecontroleerde publieke kennisbatch.',
    ], 'Bulkbesluit toepassen');
    $this->assertSession()->pageTextContains('Bevestig eerst dat alle KnowledgeItems binnen dit bereik inhoudelijk zijn beoordeeld.');

    $this->submitForm([
      'items[' . $knowledgeItem->id() . '][select]' => TRUE,
      'action' => 'approve_publish',
      'sources' => 'BREBO technische beoordeling',
      'validity_date' => '2026-09-08',
      'review_note' => 'Eerste gecontroleerde publieke kennisbatch.',
      'confirmed' => TRUE,
    ], 'Bulkbesluit toepassen');

    $this->assertSession()->pageTextContains('1 KnowledgeItem bijgewerkt.');

    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $storage->resetCache([$knowledgeItem->id()]);
    $reloaded = $storage->load($knowledgeItem->id());
    $this->assertNotNull($reloaded);
    $this->assertTrue($reloaded->isPublished());

    $basis = (string) $reloaded->get('field_knowledge_basis')->value;
    $this->assertStringContainsString('Status: approved', $basis);
    $this->assertStringContainsString('Bronnen: BREBO technische beoordeling', $basis);
    $this->assertStringContainsString('Geldigheid: 2026-09-08', $basis);
    $this->assertStringContainsString('Publieke vrijgave: ja', $basis);
    $this->assertStringContainsString('AI-vrijgave: nee', $basis);

    $decision = $this->container->get('brebo_knowledge_review.status_storage')->load((int) $knowledgeItem->id());
    $this->assertNotNull($decision);
    $this->assertSame('approved', $decision['status']);
  }

  /**
   * Tests that approval rejects a revision changed after rendering.
   */
  public function testBulkApprovalRejectsRevisionChangedAfterRender(): void {
    $knowledgeItem = $this->createKnowledgeItem();
    $reviewer = $this->drupalCreateUser(['review brebo knowledge items']);
    $this->drupalLogin($reviewer);

    $path = '/admin/content/brebo-knowledge/review';
    $this->drupalGet($path);

    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $storage->resetCache([$knowledgeItem->id()]);
    $changed = $storage->load($knowledgeItem->id());
    $this->assertNotNull($changed);
    $changed->set('field_knowledge_observation', 'Inhoud gewijzigd nadat de cockpit was geopend.');
    $changed->setNewRevision(TRUE);
    $changed->save();

    $this->submitForm([
      'items[' . $knowledgeItem->id() . '][select]' => TRUE,
      'action' => 'approve_publish',
      'sources' => 'BREBO technische beoordeling',
      'validity_date' => '2026-09-08',
      'confirmed' => TRUE,
    ], 'Bulkbesluit toepassen');

    $this->assertSession()->pageTextContains('revisie is gewijzigd sinds deze cockpit is geopend');
    $storage->resetCache([$knowledgeItem->id()]);
    $reloaded = $storage->load($knowledgeItem->id());
    $this->assertNotNull($reloaded);
    $this->assertFalse($reloaded->isPublished());
  }

  /**
   * Tests shared source and validity metadata during in-review.
   */
  public function testInReviewStoresSharedSourceAndValidityMetadata(): void {
    $knowledgeItem = $this->createKnowledgeItem();
    $reviewer = $this->drupalCreateUser(['review brebo knowledge items']);
    $this->drupalLogin($reviewer);

    $this->drupalGet('/admin/content/brebo-knowledge/review');
    $this->submitForm([
      'items[' . $knowledgeItem->id() . '][select]' => TRUE,
      'action' => 'in_review',
      'sources' => 'Technische bron voor voorcontrole',
      'validity_date' => '2026-09-09',
      'review_note' => 'Bron en geldigheid alvast geregistreerd.',
    ], 'Bulkbesluit toepassen');

    $this->assertSession()->pageTextContains('1 KnowledgeItem bijgewerkt.');

    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $storage->resetCache([$knowledgeItem->id()]);
    $reloaded = $storage->load($knowledgeItem->id());
    $this->assertNotNull($reloaded);
    $this->assertFalse($reloaded->isPublished());

    $basis = (string) $reloaded->get('field_knowledge_basis')->value;
    $this->assertStringContainsString('Status: review', $basis);
    $this->assertStringContainsString('Bronnen: Technische bron voor voorcontrole', $basis);
    $this->assertStringContainsString('Geldigheid: 2026-09-09', $basis);
    $this->assertStringContainsString('Publieke vrijgave: nee', $basis);
    $this->assertStringContainsString('AI-vrijgave: nee', $basis);
  }

  /**
   * Creates a canonical KnowledgeItem fixture for the tests.
   */
  private function createKnowledgeItem(): Node {
    $knowledgeItem = Node::create([
      'type' => 'brebo_knowledge_item',
      'title' => 'Condens tussen de glasbladen',
      'status' => 0,
      'field_knowledge_observation' => 'Er is blijvende condens of waas tussen de glasbladen zichtbaar.',
      'field_knowledge_meaning' => 'Dit kan wijzen op verlies van de randafdichting van de isolatieglaseenheid.',
      'field_knowledge_risk' => 'Technische prestatie, doorzicht en esthetische kwaliteit moeten afzonderlijk worden beoordeeld.',
      'field_knowledge_next_step' => 'Stel vast waar de condens zich bevindt en beoordeel glas, sponning en kozijn.',
      'field_knowledge_basis' => "BREBO-WEB-SEED:condens-tussen-glasbladen\nStatus: editorial\nOnderwerp: glas\nBronnen: nog niet vastgesteld\nGeldigheid: nog niet gecontroleerd\nDeskundige controle: nog niet uitgevoerd\nPublieke vrijgave: nee\nAI-vrijgave: nee",
      'field_knowledge_regie' => '',
      'field_knowledge_realization' => '',
    ]);
    $knowledgeItem->save();
    return $knowledgeItem;
  }

}
