<?php

namespace Drupal\brebo_seo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds a lightweight public sitemap for the BREBO website.
 */
final class SitemapController extends ControllerBase {

  /**
   * Returns the XML sitemap.
   */
  public function build(): Response {
    $paths = [
      '/',
      '/projecten',
      '/bouwbegeleiding',
      '/europakozijn/samenstellen',
      '/europakozijn/projectstukken',
      '/contact',
    ];

    $alias_storage = $this->entityTypeManager()->getStorage('path_alias');
    $ids = $alias_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->execute();

    foreach ($alias_storage->loadMultiple($ids) as $alias) {
      $public_path = (string) $alias->get('alias')->value;
      $internal_path = (string) $alias->get('path')->value;
      if ($this->isPublicAlias($public_path, $internal_path)) {
        $paths[] = $public_path;
      }
    }

    $paths = array_values(array_unique($paths));
    sort($paths, SORT_STRING);

    $base = 'https://brebobv.nl';
    $urls = [];
    foreach ($paths as $path) {
      $urls[] = '  <url><loc>' . htmlspecialchars($base . ($path === '/' ? '/' : $path), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    $xml .= implode("\n", $urls) . "\n";
    $xml .= "</urlset>\n";

    $response = new Response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    $response->headers->set('X-Robots-Tag', 'noindex, follow');
    return $response;
  }

  /**
   * Determines whether an alias belongs in the public sitemap.
   */
  private function isPublicAlias(string $public_path, string $internal_path): bool {
    if ($public_path === '' || $public_path === '/') {
      return FALSE;
    }

    foreach (['/admin', '/user', '/search', '/media/', '/taxonomy/'] as $blocked) {
      if (str_starts_with($public_path, $blocked)) {
        return FALSE;
      }
    }

    if (preg_match('@^/node/(\d+)$@', $internal_path, $matches)) {
      $node = $this->entityTypeManager()->getStorage('node')->load((int) $matches[1]);
      return $node !== NULL && $node->isPublished();
    }

    return TRUE;
  }

}
