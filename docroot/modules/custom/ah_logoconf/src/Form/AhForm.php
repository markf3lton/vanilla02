<?php
 
/**
 * @file
 * Contains \Drupal\simple\Form\SimpleConfigForm.
 */
 
namespace Drupal\ah_logoconf\Form;
 
use Drupal\Core\Form\ConfigFormBase;
 
use Drupal\Core\Form\FormStateInterface;
 
class AhForm extends ConfigFormBase {
 
  /**
   * {@inheritdoc}
   */
 
  public function getFormId() {
 
    return 'simple_config_form';
 
  }
 
  /**
   * {@inheritdoc}
   */
 
  public function buildForm(array $form, FormStateInterface $form_state) {
 //print_r('hiiiiiii');exit;
    $form = parent::buildForm($form, $form_state);
 
    $config = $this->config('simple.settings');
 
    $form['logo_image'] = array(
    '#type' => 'managed_file',
    '#name' => 'logo_image',
    '#title' => t('Logo image'),
    '#size' => 40,
    '#description' => t("Image should be less than 400 pixels wide and in JPG format."),
    '#upload_location' => 'public://'
  );
 
    $node_types = \Drupal\node\Entity\NodeType::loadMultiple();
 
    $node_type_titles = array();
 
    foreach ($node_types as $machine_name => $val) {
 
      $node_type_titles[$machine_name] = $val->label();
 
    }
 
 
    return $form;
 
  }
 
  /**
 
   * {@inheritdoc}
 
   */
 
  public function submitForm(array &$form, FormStateInterface $form_state) {
 
    $config = $this->config('simple.settings');
 
    $config->set('simple.email', $form_state->getValue('email'));
 
    $config->set('simple.node_types', $form_state->getValue('node_types'));
 
    $config->save();
 
    return parent::submitForm($form, $form_state);
 
  }
 
  /**
 
   * {@inheritdoc}
 
   */
 
  protected function getEditableConfigNames() {
 
    return [
 
      'simple.settings',
 
    ];
 
  }
 
}
