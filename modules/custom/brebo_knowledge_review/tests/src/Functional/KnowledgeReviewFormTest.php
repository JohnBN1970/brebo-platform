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
   * Proves access control, bundle scoping and revisioned saves.
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
      'revision_log' => 'Nuance techniek, doorzicht en esthetiek toegevoegd.',
    ], 'Correcties opslaan als nieuwe revisie');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('De KnowledgeItem-correcties zijn als nieuwe revisie opgeslagen.');

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
  }

}
