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
      $path = (string) $alias->get('alias')->value;
      if ($this->isPublicAlias($path)) {
        $paths[] = $path;
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
  private function isPublicAlias(string $path): bool {
    if ($path === '' || $path === '/') {
      return FALSE;
    }

    foreach (['/admin', '/user', '/search', '/node/', '/media/', '/taxonomy/'] as $blocked) {
      if (str_starts_with($path, $blocked)) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
