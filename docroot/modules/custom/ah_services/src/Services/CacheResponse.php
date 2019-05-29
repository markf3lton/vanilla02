<?php

namespace Drupal\ah_services\Services;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ImmutableConfig;

/**
 * @codeCoverageIgnore
 */
class CacheResponse {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Helper method to add cache context and tags on the fly.
   *
   * @param CacheableResponseInterface $response
   *   Response object
   * @param mixed $cacheable_dependencies
   *   Entity based on which cache need to be set.
   */
  public function addCacheDependencies(CacheableResponseInterface &$response, $cacheable_dependencies = []) {
    if (!is_array($cacheable_dependencies)) {
      $cacheable_dependencies = [$cacheable_dependencies];
    }
    foreach ($cacheable_dependencies as $data) {
      // Add cache tags & context for all Entities and Configurations.
      if ($data instanceof Entity || $data instanceof ImmutableConfig) {
        $response->addCacheableDependency($data);
      }
      elseif (is_array($data)) {
        foreach($data as $value) {
          // Add cache tags & context for Menu tree.
          if ($value instanceof MenuLinkTreeElement) {
            if ($value->access instanceof AccessResultInterface) {
              $response->addCacheableDependency($value->access);
            }
            $response->addCacheableDependency($value->link);
            $this->addMenuLinkCacheDependencies($value->link, $response);
            if ($value->subtree) {
              $this->addCacheDependencies($response, [$value->subtree]);
            }
          }
          // Special case: cache tags & context for MenuLinkContent.
          elseif ($value instanceof Entity) {
            $response->addCacheableDependency($value);
          }
        }
      }
    }
  }

  /**
   * Method to clear API cache.
   *
   * @param array
   *   Cache tags to be invalided.
   */
  public function clearCache($tags) {
    $request_headers = \Drupal::request()->headers;
    if (preg_match('/^clear/i', $request_headers->get('cache_disallow')) === 1) {
      $scope = $request_headers->get('cache_disallow');
    }
    elseif ($tags != 'vanity-and-404') {
      return;
    }
    if ($scope == 'clear-all') {
      $tags = ['config:rest.settings'];
    }
    else if(!is_array($tags)) {
      $tags = [$tags];
    }
    \Drupal::service('cache_tags.invalidator')
      ->invalidateTags($tags);
  }

  /**
   * Add Cache Tags.
   */
  protected function addMenuLinkCacheDependencies(MenuLinkInterface $link, CacheableResponseInterface $response) {
    $entity_type = NULL;
    $entity_type_id = $link->getBaseId();
    $uuid = $link->getDerivativeId();

    if ($link instanceof EntityInterface) {
      $entity_type = $link->getEntityType();
    }
    else {
      try {
        $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      }
      catch (\Exception $e) {
        // Silence is golden.
      }
    }

    if (!$entity_type) {
      return;
    }

    // Add the list cache tags.
    $cache = new CacheableMetadata();
    $cache->addCacheTags($entity_type->getListCacheTags());
    $response->addCacheableDependency($cache);

    // If the link is an entity already, the cache tags were added in
    // ::addCacheDependencies().
    if ($link instanceof EntityInterface) {
      return;
    }

    // Get the entity.
    $entity = NULL;
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $metadata = $link->getMetaData();

    if (!empty($metadata['entity_id'])) {
      $entity = $storage->load($metadata['entity_id']);
    }
    else {
      $entities = $storage->loadByProperties([
        $entity_type->getKey('uuid') => $uuid,
      ]);
      $entity = reset($entities);
    }

    if (!$entity) {
      return;
    }

    $response->addCacheableDependency($entity);
  }
}
