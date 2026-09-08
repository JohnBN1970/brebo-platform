<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Product;

final readonly class ProductRuleResult {

  public function __construct(
    public bool $allowed,
    public string $code,
    public string $message,
    public string $authority,
  ) {}

  public static function allowed(): self {
    return new self(TRUE, 'allowed', '', 'none');
  }

  public static function blocked(string $code, string $message, string $authority): self {
    return new self(FALSE, $code, $message, $authority);
  }

}
