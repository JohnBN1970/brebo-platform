<?php

namespace Drupal\views\Plugin\views\cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\views\Plugin\views\PluginBase;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\views\ResultRow;

/**
 * @defgroup views_cache_plugins Views cache plugins
 * @{
 * Plugins to handle Views caches.
 *
 * Cache plugins control how caching is done in Views.
 *
 * Cache plugins extend \Drupal\views\Plugin\views\cache\CachePluginBase.
 * They must be annotated with \Drupal\views\Annotation\ViewsCache
 * annotation, and must be in namespace directory Plugin\views\cache.
 *
 * @ingroup views_plugins
 * @see plugin_api
 */

/**
 * The base plugin to handle caching.
 */
abstract class CachePluginBase extends PluginBase {

  /**
   * Contains all data that should be written/read from cache.
   *
   * @var array
   */
  public $storage = [];

  /**
   * Which cache bin to store query results in.
   *
   * @var string
   */
  protected $resultsBin = 'data';

  /**
   * Stores the cache ID used for the results cache.
   *
   * @var string
   */
  protected $resultsKey;

  /**
   * Returns the resultsKey property.
   */
  public function getResultsKey() {
    return $this->resultsKey;
  }

  /**
   * Returns a string to display as the clickable title for the access control.
   */
  public function summaryTitle() {
    return $this->t('Unknown');
  }

  /**
   * Determine the expiration time of the cache type, or NULL if no expire.
   *
   * @deprecated in drupal:11.4.0 and is removed from drupal:13.0.0.
   */
  protected function cacheExpire($type) {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.4.0 and is removed from drupal:13.0.0. There is no replacement. See https://www.drupal.org/node/3576855', E_USER_DEPRECATED);
  }

  /**
   * Determines cache expiration time based on its type.
   */
  protected function cacheSetMaxAge($type) {
    return Cache::PERMANENT;
  }

  /**
   * Save data to the cache.
   */
  public function cacheSet($type) {
    switch ($type) {
      case 'query':
        break;

      case 'results':
        $data = [
          'result' => $this->prepareViewResult($this->view->result),
          'total_rows' => $this->view->total_rows ?? 0,
          'current_page' => $this->view->getCurrentPage(),
        ];
        $expire = ($this->cacheSetMaxAge($type) === Cache::PERMANENT) ? Cache::PERMANENT : (int) $this->view->getRequest()->server->get('REQUEST_TIME') + $this->cacheSetMaxAge($type);
        \Drupal::cache($this->resultsBin)->set($this->generateResultsKey(), $data, $expire, $this->getCacheTags());
        break;
    }
  }

  /**
   * Retrieve data from the cache.
   */
  public function cacheGet($type) {
    switch ($type) {
      case 'query':
        return FALSE;

      case 'results':
        if ($cache = \Drupal::cache($this->resultsBin)->get($this->generateResultsKey())) {
          $this->view->result = $cache->data['result'];
          $this->view->query->loadEntities($this->view->result);
          $this->view->total_rows = $cache->data['total_rows'];
          $this->view->setCurrentPage($cache->data['current_page']);
          $this->view->execute_time = 0;
          return TRUE;
        }
        return FALSE;
    }
  }

  /**
   * Clear out cached data for a view.
   */
  public function cacheFlush() {
    Cache::invalidateTags($this->view->storage->getCacheTagsToInvalidate());
  }

  /**
   * Post process any rendered data.
   */
  public function postRender(&$output) {}

  /**
   * Calculates and sets a cache ID used for the result cache.
   */
  public function generateResultsKey() {
    if (!isset($this->resultsKey)) {
      $build_info = $this->view->build_info;

      foreach (['query', 'count_query'] as $index) {
        if ($build_info[$index] instanceof SelectInterface) {
          $query = clone $build_info[$index];
          $query->preExecute();
          $build_info[$index] = [
            'query' => (string) $query,
            'arguments' => $query->getArguments(),
          ];
        }
      }

      $key_data = ['build_info' => $build_info];
      $key_data['pager'] = [
        'page' => $this->view->getCurrentPage(),
        'items_per_page' => $this->view->getItemsPerPage(),
        'offset' => $this->view->getOffset(),
      ];
      $key_data += \Drupal::service('cache_contexts_manager')->convertTokensToKeys($this->displayHandler->getCacheMetadata()->getCacheContexts())->getKeys();

      $this->resultsKey = $this->view->storage->id() . ':' . $this->displayHandler->display['id'] . ':results:' . hash('sha256', serialize($key_data));
    }

    return $this->resultsKey;
  }

  /**
   * Gets an array of cache tags for the current view.
   */
  public function getCacheTags() {
    $tags = $this->view->storage->getCacheTags();
    $entity_information = $this->view->getQuery()->getEntityTableInfo();

    if (!empty($entity_information)) {
      foreach ($entity_information as $metadata) {
        $tags = Cache::mergeTags($tags, \Drupal::entityTypeManager()->getDefinition($metadata['entity_type'])->getListCacheTags());
      }
    }

    $tags = Cache::mergeTags($tags, $this->view->getQuery()->getCacheTags());
    return $tags;
  }

  /**
   * Gets the max age for the current view.
   */
  public function getCacheMaxAge() {
    $max_age = $this->getDefaultCacheMaxAge();
    $max_age = Cache::mergeMaxAges($max_age, $this->view->getQuery()->getCacheMaxAge());
    return $max_age;
  }

  /**
   * Returns the default cache max age.
   */
  protected function getDefaultCacheMaxAge() {
    return 0;
  }

  /**
   * Prepares the view result before putting it into cache.
   */
  protected function prepareViewResult(array $result) {
    $return = [];
    foreach ($result as $key => $row) {
      $clone = clone $row;
      $clone->resetEntityData();
      $return[$key] = $clone;
    }
    return $return;
  }

  /**
   * Alters the cache metadata of a display upon saving a view.
   */
  public function alterCacheMetadata(CacheableMetadata $cache_metadata) {
  }

  /**
   * Returns the row cache tags.
   */
  public function getRowCacheTags(ResultRow $row) {
    $tags = !empty($row->_entity) ? $row->_entity->getCacheTags() : [];
    if (!empty($row->_relationship_entities)) {
      foreach ($row->_relationship_entities as $entity) {
        $tags = Cache::mergeTags($tags, $entity->getCacheTags());
      }
    }
    return $tags;
  }

  /**
   * Returns the row cache keys.
   *
   * @deprecated in drupal:11.4.0 and is removed from drupal:13.0.0.
   */
  public function getRowCacheKeys(ResultRow $row) {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.4.0 and is removed from drupal:13.0.0. There is no replacement. See https://www.drupal.org/node/3564958', E_USER_DEPRECATED);
    return [
      'views',
      'fields',
      $this->view->id(),
      $this->view->current_display,
      $this->getRowId($row),
    ];
  }

  /**
   * Returns a unique identifier for the specified row.
   *
   * @deprecated in drupal:11.4.0 and is removed from drupal:13.0.0.
   */
  public function getRowId(ResultRow $row) {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:11.4.0 and is removed from drupal:13.0.0. There is no replacement. See https://www.drupal.org/node/3564958', E_USER_DEPRECATED);
    $row_data = array_diff_key((array) $row, array_flip(['index', '_entity', '_relationship_entities'])) + $this->getRowCacheTags($row);
    $field_ids = array_keys($this->view->field);
    $row_data += array_flip($field_ids);
    return hash('sha256', serialize($row_data));
  }

}

/**
 * @}
 */
