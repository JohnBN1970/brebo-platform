<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_knowledge_review\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the bounded BREBO Knowledge review form.
 *
 * @group brebo_knowledge_review
 */
#[RunTestsInSeparateProcesses]
final class KnowledgeReviewFormTest extends BrowserTestBase {

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
   * Proves access, revisioned saves and revision-bound review status.
   */
  public function testReviewFormIsBoundedAndRevisioned(): void {
    $knowledgeItem = Node::create([
      'type' => 'brebo_knowledge_item',
      'title' => 'Condens tussen de glasbladen',
      'field_knowledge_observation' => 'Bestaande waarneming.',
      'field_knowledge_meaning' => 'Bestaande betekenis.',
      'field_knowledge_risk' => 'Bestaand risico.',
      'field_knowledge_next_step' => 'Bestaande volgende stap.',
      'field_knowledge_basis' => 'Bestaande basis.',
      'field_knowledge_regie' => '',
      'field_knowledge_realization' => '',
    ]);
    $knowledgeItem->save();

    $path = '/admin/content/brebo-knowledge/' . $knowledgeItem->id() . '/review';
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);

    $reviewer = $this->drupalCreateUser(['review brebo knowledge items']);
    $this->drupalLogin($reviewer);
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Condens tussen de glasbladen');
    $this->assertSession()->pageTextContains('Te beoordelen');

    NodeType::create([
      'type' => 'review_test_page',
      'name' => 'Review test page',
    ])->save();
    $otherNode = Node::create([
      'type' => 'review_test_page',
      'title' => 'Geen KnowledgeItem',
    ]);
    $otherNode->save();
    $this->drupalGet('/admin/content/brebo-knowledge/' . $otherNode->id() . '/review');
    $this->assertSession()->statusCodeEquals(403);

    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $beforeRevisionIds = $storage->revisionIds($knowledgeItem);

    $this->drupalGet($path);
    $this->submitForm([
      'field_knowledge_observation' => 'Blijvende condens of waas bevindt zich aantoonbaar tussen de glasbladen.',
      'field_knowledge_meaning' => 'Sterke aanwijzing voor een niet meer intacte randafdichting; thermische noodzaak moet afzonderlijk worden beoordeeld.',
      'field_knowledge_risk' => 'Beoordeel technische prestatie, comfort, functioneel doorzicht en esthetische kwaliteit afzonderlijk.',
      'field_knowledge_next_step' => 'Controleer positie van het vocht, glasopbouw, gebruiksfunctie, doorzicht en toestand van kozijn en sponning.',
      'field_knowledge_basis' => 'Gebaseerd op technische bronnen; deskundige controle blijft nodig voor projectspecifieke maatregelkeuze.',
      'field_knowledge_regie' => 'Prioriteer op impact en gebruiksfunctie, niet alleen op de aanwezigheid van het gebrek.',
      'field_knowledge_realization' => 'Behoud waar verantwoord of vervang de isolatieglaseenheid vanwege techniek, zicht of esthetiek; kozijnvervanging volgt niet automatisch.',
      'review_status' => 'approved',
      'review_note' => 'Inhoud gecontroleerd en bruikbaar als referentie-item.',
      'revision_log' => 'Nuance techniek, doorzicht en esthetiek toegevoegd.',
    ], 'Correcties en reviewbesluit opslaan');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('De KnowledgeItem-correcties en reviewstatus zijn opgeslagen.');
    $this->assertSession()->pageTextContains('Goedgekeurd');

    $storage->resetCache([$knowledgeItem->id()]);
    $reloaded = $storage->load($knowledgeItem->id());
    $this->assertNotNull($reloaded);
    $this->assertSame(
      'Beoordeel technische prestatie, comfort, functioneel doorzicht en esthetische kwaliteit afzonderlijk.',
      $reloaded->get('field_knowledge_risk')->value,
    );

    $afterRevisionIds = $storage->revisionIds($reloaded);
    $this->assertCount(count($beforeRevisionIds) + 1, $afterRevisionIds);
    $this->assertSame(
      'Nuance techniek, doorzicht en esthetiek toegevoegd.',
      $reloaded->getRevisionLogMessage(),
    );

    $decision = $this->container->get('brebo_knowledge_review.status_storage')->load((int) $reloaded->id());
    $this->assertNotNull($decision);
    $this->assertSame('approved', $decision['status']);
    $this->assertSame((int) $reloaded->getRevisionId(), $decision['revision_id']);

    $reloaded->set('field_knowledge_observation', 'Latere inhoudelijke wijziging.');
    $reloaded->setNewRevision(TRUE);
    $reloaded->save();

    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Herziening nodig');
  }

}
