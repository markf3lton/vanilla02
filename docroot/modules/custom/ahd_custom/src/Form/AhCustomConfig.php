<?php
/**
 * @file
 * Contains Drupal\ahd_custom\Form\AhCustomConfig.
 */
namespace Drupal\ahd_custom\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityManagerInterface;

class AhCustomConfig extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ahd_custom.adminsettings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ahd_custom_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ahd_custom.adminsettings');
    $form['insight'] = [
      '#type' => 'fieldset',
      '#title' => t('Insight'),
      'ah_insight_bridge_url' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('Feeds  service URL'),
        '#description' => $this->t('Service URL for athenainsight feeds. This will be used for getting the feeds.'),
        '#default_value' => $config->get('ah_insight_bridge_url'),
      ],
      'ah_insight_base_url' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('Base URL'),
        '#description' => $this->t('Base URL for athenainsight feeds. This will be used for linking the articles'),
        '#default_value' => $config->get('ah_insight_base_url'),
      ],
      'ah_insight_files_url' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('Files URL'),
        '#description' => $this->t('Files URL for athenainsight feeds. This will be used for getting the media files'),
        '#default_value' => $config->get('ah_insight_files_url'),
      ],
    ];
    $query = db_query("SELECT id, info FROM block_content_field_data where type = 'ah_webform' and status = 1");
    $results = $query->fetchAll();
    $webforms = [];
    foreach ($results as $result) {
      $webforms[$result->id] = $result->info;
    }
    $form['schedule_meeting'] = [
      '#type' => 'fieldset',
      '#title' => t('Schedule a meeting'),
      'ah_schedule_meeting_label' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('Label'),
        '#description' => $this->t('CTA label for the Schedula a meeting Button'),
        '#default_value' => $config->get('ah_schedule_meeting_label'),
      ],
      'ah_schedule_meeting_link' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('Link URL'),
        '#description' => $this->t('CTA link for the Schedula a meeting Button'),
        '#default_value' => $config->get('ah_schedule_meeting_link'),
      ],
      'schedule_meeting_form' => [
        '#type' => 'select',
        '#required' => 'required',
        '#title' => $this->t('Form'),
        '#description' => $this->t('Specify the schedule a meeting form.'),
        '#default_value' => $config->get('schedule_meeting_form'),
        '#options' => $webforms,
      ],
    ];
    $tag_terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('cta');
    $tags = array();
    foreach ($tag_terms as $tag_term) {
        $tags[$tag_term->tid] = $tag_term->name;
    }
    $form['footer'] = [
      '#type' => 'fieldset',
      '#title' => t('Footer'),
      'footer_sm_cta_type' => [
        '#type' => 'select',
        '#options' => $tags,
        '#required' => 'required',
        '#title' => t('Schedule a Meeting CTA Type'),
        '#default_value' => $config->get('footer_sm_cta_type')
      ],
      'footer_bottom_call_us' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('Bottom Call us Details'),
        '#description' => $this->t('Footer Bottom Call us Details'),
        '#default_value' => $config->get('footer_bottom_call_us'),
      ],
    ];
    $form['webform_service_url'] = [
      '#type' => 'textfield',
      '#required' => 'required',
      '#title' => $this->t('Webform Service URL'),
      '#description' => $this->t('Specify the form submission end point.'),
      '#default_value' => $config->get('webform_service_url'),
    ];
    $form['dtm_script'] = [
      '#type' => 'fieldset',
      '#title' => t('DTM script'),
    ];
    $form['dtm_script']['dtm_script_prod'] = [
      '#type' => 'fieldset',
      '#title' => t('Production script'),
      'dtm_prod_url' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('DTM URL'),
        '#description' => $this->t('DTM production embed code url for this environment'),
        '#default_value' => $config->get('dtm_prod_url'),
      ],
    ];
    $form['dtm_script']['dtm_script_dev'] = [
      '#type' => 'fieldset',
      '#title' => t('Development script'),
      'dtm_dev_url' => [
        '#type' => 'textfield',
        '#required' => 'required',
        '#title' => $this->t('DTM URL'),
        '#description' => $this->t('DTM development embed code url for this environment'),
        '#default_value' => $config->get('dtm_dev_url'),
      ],
      'dtm_dev_url_pages' => [
        '#type' => 'textarea',
        '#title' => 'Pages for which development script need to emebedded instead of above production script.',
        '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is /user/* for every user page. &lt;front&gt; is the front page."),
        '#default_value' => $config->get('dtm_dev_url_pages'),
      ],
    ];
    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ahd_custom.adminsettings')
      ->set('ah_insight_bridge_url', $form_state->getValue('ah_insight_bridge_url'));
    $this->config('ahd_custom.adminsettings')
      ->set('ah_insight_base_url', $form_state->getValue('ah_insight_base_url'));
    $this->config('ahd_custom.adminsettings')
      ->set('ah_insight_files_url', $form_state->getValue('ah_insight_files_url'));
    $this->config('ahd_custom.adminsettings')
      ->set('ah_schedule_meeting_label', $form_state->getValue('ah_schedule_meeting_label'));
    $this->config('ahd_custom.adminsettings')
      ->set('ah_schedule_meeting_link', $form_state->getValue('ah_schedule_meeting_link'));
    $this->config('ahd_custom.adminsettings')
      ->set('footer_sm_cta_type', $form_state->getValue('footer_sm_cta_type'));
    $this->config('ahd_custom.adminsettings')
        ->set('footer_bottom_call_us', $form_state->getValue('footer_bottom_call_us'));
    $this->config('ahd_custom.adminsettings')
            ->set('webform_service_url', $form_state->getValue('webform_service_url'));
    $this->config('ahd_custom.adminsettings')
            ->set('schedule_meeting_form', $form_state->getValue('schedule_meeting_form'));
    $this->config('ahd_custom.adminsettings')
      ->set('dtm_prod_url', $form_state->getValue('dtm_prod_url'));
    $this->config('ahd_custom.adminsettings')
      ->set('dtm_prod_url_pages', $form_state->getValue('dtm_prod_url_pages'));
    $this->config('ahd_custom.adminsettings')
      ->set('dtm_prod_url_visibility', $form_state->getValue('dtm_prod_url_visibility'));
    $this->config('ahd_custom.adminsettings')
      ->set('dtm_dev_url', $form_state->getValue('dtm_dev_url'));
    $this->config('ahd_custom.adminsettings')
      ->set('dtm_dev_url_pages', $form_state->getValue('dtm_dev_url_pages'));
    $this->config('ahd_custom.adminsettings')
      ->set('dtm_dev_url_visibility', $form_state->getValue('dtm_dev_url_visibility'));

    $this->config('ahd_custom.adminsettings')->save();
  }

}
