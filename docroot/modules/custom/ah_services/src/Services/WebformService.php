<?php

namespace  Drupal\ah_services\Services;

use Drupal\webform\Entity\Webform;
use Drupal\block_content\Entity\BlockContent;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Config\ConfigFactory;

class WebformService {

  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Get webform elements initialized as an associative array.
   *
   * @param string $webform_id
   *   Webform ID.
   *
   * @return array
   *   Elements as an associative array.
   */
  public function getElements($webform_id, &$cacheable_dependencies) {
    if (empty($webform_id)) {
      return [];
    }

    // Load the webform.
    $webform = Webform::load($webform_id);

    if ($webform) {
      $cacheable_dependencies[] = $webform;
      // Return only the form elements.
      $elements = $webform->getElementsInitialized();
      foreach ($elements as $key => &$element) {
        if ($element['#type'] == 'radios') {
          $element['#options'] = array_flip($element['#options']);
        }
      }
      return $elements;
    }

    return [];
  }
  /**
   * Returns the block content
   * 
   * @param int $block_id
   *  custom webform block id
   * 
   * @return object
   *  Webform objects with all the field information attached to it.
   * @codeCoverageIgnore
   */
  public function getBlockContent ($block_id) {
      return  BlockContent::load($block_id);
  }

    /**
   * Methdo to return field value from an object.
   *
   * @param object $entity
   *   Entity object like node or block.
   * @param string $key
   *   Field machine name.
   *
   * @return mixed
   *   Entity field value.
   *  @codeCoverageIgnore
   */
  protected function getValueByKey($entity, $key) {
    return $entity->get($key)->first();
  }

  /**
   * Get complete form elements as an associated array.
   *
   * @param int $form_id
   *  Custom block ID
   *
   * @return array
   *  Taxonomies and field elements as an associative array.
   */
  public function getFormArray($form_id, &$cacheable_dependencies){
    $form_array = [];
    if ($form_id) {
      //Get the entire block data - Fields & Taxonomies.
      $block_data = $this->getBlockContent($form_id);
      if ($block_data) {
        $cacheable_dependencies[] = $block_data;
        //Fetch webform ID and data.
        // $webform_id = $block_data->get('field_webform_template')->first()->target_id;
        $webform_id = $this->getValueByKey($block_data,'field_webform_template')->target_id;
        if ($webform_id) {
          $webform_data = $this->getElements($webform_id, $cacheable_dependencies);
          $form_array['fields'] =  $webform_data;
          // Define taxonomy reference field machine names.
          $form_taxonomy_ref_fields = [
            'field_ah_form_title',
            'field_ah_form_subheader',
            'field_ah_form_post_text',
            'field_ah_form_button_labels',
            'field_ah_confirmation_header',
            'field_forms_confirmation_text',
            'field_ah_forms_error_title',
            'field_form_error_message',
            'field_ah_forms_task_dtls',
          ];
          $hidden_fields = [];
          $term_data = [];
          foreach ($form_taxonomy_ref_fields as $form_taxonomy_ref_field) {
            if ($block_data->get($form_taxonomy_ref_field) && $block_data->get($form_taxonomy_ref_field)->first()) {
              $term = $this->getTermDetails($block_data->get($form_taxonomy_ref_field)->first()->target_id, $cacheable_dependencies);
              $term_callback = 'get' . str_replace('_', '', ucwords($form_taxonomy_ref_field, '_'));
              if($form_taxonomy_ref_field  === 'field_ah_forms_task_dtls'){
                $hidden_fields = $this->$term_callback($term, $block_data) ;
                continue;
              }
              $term_data[$form_taxonomy_ref_field] = $this->$term_callback($term);
            }
          }
          // Move WFFM hidden field from fields to hidden fields.
          if (isset($form_array['fields']['formItem'])) {
            $wffm = $form_array['fields']['formItem'];
            $wffm_value = '';
            if (isset($wffm['#default_value'])) {
              $wffm_term = Term::load($wffm['#default_value']);
              if ($wffm_term) {
                $cacheable_dependencies[] = $wffm_term;
                $wffm_value = $wffm_term->getName();
              }
            }
            $hidden_fields[] = [
              'name' => $form_array['fields']['formItem']['#webform_key'],
              'value' => $wffm_value,
            ];
            unset($form_array['fields']['formItem']);
          }
          $form_array['hidden_fields'] = $hidden_fields;
          $form_array['elements'] = $term_data;
          //Adding the global form config.
          $form_array['config'] = $this->getFormGlobalConfig($block_data, $cacheable_dependencies);
        }
      }
    }
    return $form_array;
  }

  /**
   * Method to get global config.
   *
   * @param object block object
   *   block data.
   *
   * @return form config array.
   */
  protected function getFormGlobalConfig($block_data, &$cacheable_dependencies){
    $form_config = [];
    if ($block_data->get('field_ah_form_name')
    && is_array($block_data->get('field_ah_form_name')->getValue())
    && isset($block_data->get('field_ah_form_name')->getValue()[0]['value'])) {
      $form_name =  $block_data->get('field_ah_form_name')->getValue()[0]['value'];
      $ah_form_name = strtolower(str_replace([" ",".",",", "*"], "_", trim($form_name)));
      $form_config['form_name'] = $ah_form_name;
      $form_config['form_id'] = $ah_form_name;
      $form_config['class'] = $ah_form_name . ' ' . $form_name ;
      $form_config['method'] = 'POST';
      $form_config['action'] = $this->getWebformServiceUrl($cacheable_dependencies);
      return $form_config;
    }
  }

  /**
   * Method to load term.
   *
   * @param int $tid
   *   Term id.
   *
   * @return Drupal\taxonomy\Entity\Term or null.
   */
  protected function getTermDetails($tid = false, &$cacheable_dependencies){
    if($tid){
        $term_data = Term::load($tid);
        if(isset($term_data) || !empty($term_data) ) {
          $cacheable_dependencies[] = $term_data;
          return $term_data;
        }
        else {
          return null;
        }
    }
    else {
        return null;
    }
  }

  /**
   * Method to fetch form title term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhFormTitle($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form sub header term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhFormSubheader($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form post text term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhFormPostText($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form button label term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhFormButtonLabels($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form confirmation header term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhConfirmationHeader($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form confirmation text term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldFormsConfirmationText($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form error title term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhFormsErrorTitle($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }

  /**
   * Method to fetch form error message term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldFormErrorMessage($term) {
    $fields = [
      'description' => 'description',
    ];
    $term_data = [];

    foreach($fields as $key => $field) {
      if ($term && $term->get($field)
      && is_array($term->get($field)->getValue())) {
      $term_data[$key] = $term->get($field)->getValue()[0]['value'];
    }
    }

    return $term_data;
  }


  /**
   * Method to fetch form task details term fields.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   Term object.
   *
   * @return array
   *   Term fields as an array.
   */
  protected function getFieldAhFormsTaskDtls($term, $block_data){
    $fields = [
      'Latest_TaskPriority' => 'field_ah_forms_task_priority',
      'Latest_QualifiedForNurture' => 'field_ah_forms_qualified_nurture',
      'Latest_EmailSubmitFlag__c' => 'field_ah_forms_elq_email_flag',
      'ImportNurture_Task_Subject' => 'field_ah_forms_elq_sub',
      'ImportNurture_Task_Description' => 'field_ah_forms_elq_des',
    ];

    $term_data = [];

    foreach($fields as $key => $field) {
      $term_data[] = [
        'name' => $key,
        'value' => $term->get($field)->getValue()[0]['value']
      ];
    }

    $term_data[] = [
      'name' => 'addgoalitem',
      'value' => ''
    ];
    $term_data[] = [
      'name' => 'showvideourl',
      'value' => ''
    ];
    $term_data[] = [
      'name' => 'elqCustomerGUID',
      'value' => ''
    ];
    $term_data[] = [
      'name' => 'CurrentPage',
      'value' => ''
    ];
    $term_data[] = [
      'name' => 'kenshooid',
      'value' => ''
    ];

    $constants = $term->get('field_ah_forms_term_const')->getValue();
    foreach ($constants as $constant) {
      $term_data[] = [
        'name' => $constant['first'],
        'value' => $constant['second']
      ];
    }

    $term_data[] = [
      'name' => 'Web_Form__c',
      'value' => $block_data->get('field_ah_form_name')->getValue()[0]['value']
    ];
    $term_data[] = [
      'name' => 'elqFormName',
      'value' => ($block_data->get('field_ah_form_category')->getValue()[0]['value'] == 'eloqua' ?  $block_data->get('field_ah_form_eloqua_name')->getValue()[0]['value'] : "" )
    ];
    $term_data[] = [
      'name' => 'ahf_dataflow',
      'value' => $block_data->get('field_ah_form_category')->getValue()[0]['value']
    ];

    return $term_data;

  }

  /**
   * Method to fetch form action url.
   *
   * @return string
   *   Url to which form need to be submitted.
   */
  protected function getWebformServiceUrl(&$cacheable_dependencies) {
    $service_config = $this->configFactory
      ->get('ahd_custom.adminsettings');
    $cacheable_dependencies[] = $service_config;
    $service_url = $service_config->get('webform_service_url');

    if (empty($service_url)) {
      $service_url = 'https://webformservice.athenahealth.com/WebServices/wfs.svc/WebFormHandler';
    }

    return $service_url;
  }

}
