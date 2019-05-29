<?php
/**
 * @file
 * Contains \Drupal\ahd_custom\Controller\AhdController.
 */
namespace Drupal\ahd_custom\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

class AhdController extends ControllerBase {
 /**
 * Entity query factory.
 *
 * @var \Drupal\Core\Entity\Query\QueryFactory
 */
 protected $entityQuery;

 /**
 * Constructs a new CustomRestController object.

 * @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
 * The entity query factory.
 */
 public function __construct(QueryFactory $entity_query) {
   //$this->entityQuery = $entity_query;
 }
  /**
  * {@inheritdoc}
  */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }
  public function content() {
    $bid = 2 ;// Get the block id through config, SQL or some other means
    $block = \Drupal\block_content\Entity\BlockContent::load($bid);
    $render = \Drupal::entityTypeManager()->
      getViewBuilder('block_content')->view($block);
   // return $render;
      $block_content = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', 'db04a214-dce4-4811-bf55-cb2e0e383fac');
      echo '<pre>';
      print_r($render);
      echo '</pre>';
      exit('END');
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello, World!'),
    );
  }
}