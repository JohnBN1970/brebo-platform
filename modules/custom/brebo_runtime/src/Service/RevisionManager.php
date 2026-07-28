<?php

declare(strict_types=1);

namespace Drupal\brebo_runtime\Service;

use Drupal\Core\Entity\RevisionableInterface;

/**
 * Past het gedeelde revisiebeleid toe.
 */
final class RevisionManager {

  public function apply(RevisionableInterface $entity, string $message): void {
    $entity->setNewRevision(TRUE);
    if (trim((string) $entity->getRevisionLogMessage()) === '') {
      $entity->setRevisionLogMessage($message);
    }
  }

}
