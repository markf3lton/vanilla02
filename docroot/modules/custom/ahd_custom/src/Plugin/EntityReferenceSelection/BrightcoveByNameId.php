<?php

namespace Drupal\ahd_custom\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Provides brightcove video selection based on name or id.
 *
 * @EntityReferenceSelection(
 *   id = "default:brightcove_by_name_id",
 *   label = @Translation("Brightcove by name or id."),
 *   entity_types = {"brightcove_video"},
 *   group = "default",
 *   weight = 3
 * )
 */
class  BrightcoveByNameId extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $configuration = $this->getConfiguration();
    $target_type = $configuration['target_type'];
    $entity_type = $this->entityManager->getDefinition($target_type);

    $query = $this->entityManager->getStorage($target_type)->getQuery();

    // If 'target_bundles' is NULL, all bundles are referenceable, no further
    // conditions are needed.
    if (is_array($configuration['target_bundles'])) {
      // If 'target_bundles' is an empty array, no bundle is referenceable,
      // force the query to never return anything and bail out early.
      if ($configuration['target_bundles'] === []) {
        $query->condition($entity_type->getKey('id'), NULL, '=');
        return $query;
      }
      else {
        $query->condition($entity_type->getKey('bundle'), $configuration['target_bundles'], 'IN');
      }
    }
    // Add match condition starting with video id.
    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      $group = $query->orConditionGroup()
        ->condition($label_key, $match, $match_operator)
        ->condition('video_id', $match, 'STARTS_WITH');
      $query->condition($group);
    }

    // Add entity-access tag.
    $query->addTag($target_type . '_access');

    // Add the Selection handler for system_query_entity_reference_alter().
    $query->addTag('entity_reference');
    $query->addMetaData('entity_reference_selection_handler', $this);

    // Add the sort option.
    if ($configuration['sort']['field'] !== '_none') {
      $query->sort($configuration['sort']['field'], $configuration['sort']['direction']);
    }

    return $query;
  }

}
