<?php

namespace Drupal\ah_custom_ipe;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

class FilterIpeBlocks {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  public $entityRepository;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * Custom filter for IPE tray components.
   */
  public function filterReusable(array $blocks, PanelsDisplayVariant $panels_display) {
    $node = NULL;
    if (!empty($panels_display->getContexts())) {
      foreach($panels_display->getContexts() as $context) {
        if ($context->getContextData()->getValue() instanceof NodeInterface) {
          $node = $context->getContextData()->getValue();
          break;
        }
      }
    }
    $is_fields_required = FALSE;
    if ($node instanceof NodeInterface) {
      // Fields are required only for hybrid pages.
      $is_fields_required = $node->bundle() === 'blog';
    }
    // Only show blocks that were classified as reusable.
    foreach ($blocks as $key => $block) {
      // Filter out non components.
      if ($block['provider'] !== 'block_content') {
        // Don't filter hybrid pages fields.
        if ($block['provider'] === 'ctools_block') {
          if (!$is_fields_required) {
            unset($blocks[$key]);
          }
          else {
            continue;
          }
        }
        unset($blocks[$key]);
      }
      // Filter out non reusable components.
      else if ($block['provider'] === 'block_content') {
        $plugin_id = $block['plugin_id'];
        $block_content_uuid = explode(':', $plugin_id)[1];
        $block_content = $this->entityRepository->loadEntityByUuid('block_content', $block_content_uuid);

        if ($block_content->hasField('component_type')) {
          $component_type = $block_content->component_type->first() ? $block_content->component_type->first()->value : '0';
          /* Check whether the component is been created using IPE tray,
           if then don't filter the component only one time. */
          if (isset($_SESSION['current_component'])) {
            if ($_SESSION['current_component']['uuid'] == $block_content_uuid) {
              if ($component_type != '1') {
                $blocks[$key]['category'] = new TranslatableMarkup('Place New Component');
              }
              unset($_SESSION['current_component']);
              continue;
            }
          }
          if ($component_type != '1') {
            unset($blocks[$key]);
          }
        }
      }
    }

    return $blocks;
  }

}
