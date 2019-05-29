<?php

namespace Drupal\ahd_custom\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\simple_sitemap\Form\FormHelper;
use Drupal\simple_sitemap\Form\SimplesitemapFormBase;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Config\ConfigFactory;

/**
 * Class NonDrupalLinks
 * @package Drupal\ahd_custom\Form
 */
class NonDrupalLinks extends SimplesitemapFormBase {

  /**
   * @var \Drupal\Core\Path\PathValidator
   */
  protected $pathValidator;

  /**
   * SimplesitemapCustomLinksForm constructor.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   * @param \Drupal\simple_sitemap\Form\FormHelper $form_helper
   * @param \Drupal\Core\Path\PathValidator $path_validator
   */
  public function __construct(
    Simplesitemap $generator,
    FormHelper $form_helper,
    PathValidator $path_validator,
    ConfigFactory $config_factory
  ) {
    parent::__construct(
      $generator,
      $form_helper
    );
    $this->pathValidator = $path_validator;
    $this->configFactory = $config_factory;
  } 

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.form_helper'),
      $container->get('path.validator'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'non_drupal_custom_links_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['non_drupal_links'] = [
      '#title' => $this->t('Non Drupal links'),
      '#type' => 'fieldset',
      '#markup' => '<div class="description">' . $this->t('Add non-drupal 8 URLs to the XML sitemap.') . '</div>',
      '#prefix' => $this->getDonationText(),
    ];
    $custom_links = $this->configFactory
      ->get('ahd_custom.url_settings')
      ->get('non_drupal_links');
    $form['non_drupal_links']['non_drupal_links'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Non-Drupal 8 URLs'),
      '#default_value' => $custom_links,
      '#description' => $this->t("Please specify only Non drupal paths, one per line.<br/>Optionally link priority <em>(0.0 - 1.0)</em> can be added by appending it after a space.<br/> Optionally link change frequency <em>(always / hourly / daily / weekly / monthly / yearly / never)</em> can be added by appending it after a space.<br/><br/><strong>Examples:</strong><br/><em>https://www.athenahealth.com/schedule-meeting 1.0 yearly</em> -> Drupal 7 URL with the highest priority and yearly change frequency<br/><em>https://www.athenahealth.com/careers</em> -> careers page with the default priority and no change frequency information"),
    ];


    $this->formHelper->displayRegenerateNow($form['non_drupal_links']);

    return parent::buildForm($form, $form_state);
  }

  protected function negotiateVariant() {

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $links = $form_state->getValue('non_drupal_links');
      $this->configFactory->getEditable('ahd_custom.url_settings')
      ->set('non_drupal_links', $links)->save();
    parent::submitForm($form, $form_state);

    // Regenerate sitemaps according to user setting.
    if ($form_state->getValue('simple_sitemap_regenerate_now')) {
      $this->generator->rebuildQueue()->generateSitemap();
    }

  }
}
