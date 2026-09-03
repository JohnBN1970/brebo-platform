<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Knowledge;

/**
 * Central publication and AI approval policy for public BREBO knowledge.
 *
 * The catalog may contain useful editorial material, but editorial content is
 * never an authoritative BREBO AI source by default. Approval must be explicit
 * and supported by source, validity and human review metadata.
 */
final class KnowledgeApproval {

  public const STATUS_EDITORIAL = 'editorial';
  public const STATUS_REVIEW = 'review';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';

  public static function isPublic(array $item): bool {
    return !empty($item['public']);
  }

  public static function isAiApproved(array $item): bool {
    if (($item['status'] ?? self::STATUS_EDITORIAL) !== self::STATUS_APPROVED) {
      return FALSE;
    }

    if (empty($item['ai_approved']) || empty($item['reviewed_by']) || empty($item['reviewed_at'])) {
      return FALSE;
    }

    $basis = $item['basis'] ?? [];
    return !empty($basis['sources']) && !empty($basis['validity_checked_at']);
  }

  public static function statusLabel(array $item): string {
    return match ($item['status'] ?? self::STATUS_EDITORIAL) {
      self::STATUS_APPROVED => 'Gevalideerde BREBO-kennis',
      self::STATUS_REVIEW => 'In deskundige beoordeling',
      self::STATUS_REJECTED => 'Niet vrijgegeven',
      default => 'Redactionele kennis',
    };
  }

  public static function aiReason(array $item): string {
    if (self::isAiApproved($item)) {
      return 'Vrijgegeven als bron voor BREBO AI.';
    }
    return 'Niet vrijgegeven als bron voor BREBO AI. Bronverwijzing, geldigheid en menselijke goedkeuring moeten eerst aantoonbaar compleet zijn.';
  }

}
