<?php

namespace Drupal\ahd_custom\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BrightcoveNameOrIdAutocompleteWidget.
 *
 * @FieldWidget(
 *   id = "brightcove_name_id_autocomplete_widget",
 *   label = @Translation("Brightcove Autocomplete by name and id"),
 *   description = @Translation("Brightcove for entity reference fields"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class BrightcoveNameOrIdAutocompleteWidget extends EntityReferenceAutocompleteWidget {

    /**
     * {@inheritdoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
      $form_element = parent::formElement($items, $delta, $element, $form, $form_state);
      $form_element['target_id']['#selection_handler'] = 'default:brightcove_by_name_id';
      return $form_element;
    }

    /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    // Hide autocomplete matching form element.
    $element['match_operator']['#title'] = t('Video name autocomplete matching (For video id always starts with matching rule will be applied)');

    return $element;
  }
}
