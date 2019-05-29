<?php

namespace Drupal\ahd_custom\Controller;

use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\ahd_custom\AutocompleteMatcher\EntityAutocompleteMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\system\Controller\EntityAutocompleteController as EntityAutocompleteControllerBase;

class EntityAutocompleteController extends EntityAutocompleteControllerBase {

  /**
   * The autocomplete matcher for entity references.
   */
  protected $matcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityAutocompleteMatcher $matcher, KeyValueStoreInterface $key_value) {
    $this->matcher = $matcher;
    $this->keyValue = $key_value;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ahd_alter_entity_autocomplete.autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }

}
