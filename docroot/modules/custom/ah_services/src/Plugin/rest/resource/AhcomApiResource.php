<?php
/**
 * Provides a resource to get bundles by entity.
 *
 * @RestResource(
 *   id = "ahcom_api",
 *   label = @Translation("AHCOM Rest API"),
 *   uri_paths = {
 *     "canonical" = "/ahcomapi/{ahurl}"
 *   }
 * )
 */
namespace Drupal\ah_services\Plugin\rest\resource;

use Drupal\Core\Entity\EntityManagerInterface;
use \Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\ah_services\Services\CacheResponse;
use Drupal\Core\Path\AliasManager;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Utility\Token;
use Drupal\Core\Site\Settings;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\media\Entity\Media;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\ahd_custom\Form\getEditableConfigNames;
use Drupal\Core\Database\Connection;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Path\PathMatcher;

class AhcomApiResource extends ResourceBase {
  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityManagerInterface $entity_manager,
    AccountProxyInterface $current_user,
    CacheResponse $cache_response,
    AliasManager $alias_manager,
    Token $token_service,
    $ah_service,
    MenuLinkTreeInterface $menu_tree,
    $ah_node_field,
    $reference_replace,
    PathMatcher $path_matcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
    $this->cacheResponse = $cache_response;
    $this->aliasManager = $alias_manager;
    $this->tokenService = $token_service;
    $this->ahService = $ah_service;
    $this->menuTree = $menu_tree;
    $this->nodeField = $ah_node_field;
    $this->referenceReplace = $reference_replace;
    $this->pathMatcher = $path_matcher;
  }

  /**
   *  A curent user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   *  A instance of entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity.manager'),
      $container->get('current_user'),
      $container->get('ah_services.cache_response'),
      $container->get('path.alias_manager'),
      $container->get('token'),
      $container->get('ah_services.reference_replace'),
      $container->get('menu.link_tree'),
      $container->get('ah_services.node_field'),
      $container->get('ah_services.reference_replace'),
      $container->get('path.matcher')
    );
  }
  public function generateSubMenuTree(&$output, &$input, $parent = FALSE) {
    $input = array_values($input);
    foreach($input as $key => $item) {
      //If menu element disabled skip this branch
      if ($item->link->isEnabled()) {
        $key = 'menu-' . $key;
        $name = $item->link->getTitle();
        $description = $item->link->getDescription();
        $url = $item->link->getUrlObject();
        $url_string = $url->toString(true);
        $url_string = $url_string->getGeneratedUrl();

        $menu_fields = $this->getMenuFields($item->link->getMetadata()['entity_id']);

        $unique_name = ($menu_fields->unique_name) ? $menu_fields->unique_name : '';
        $is_default = ($menu_fields->is_default) ? $menu_fields->is_default : '0';
        $unique_name_footer = ($menu_fields->unique_name_footer) ? $menu_fields->unique_name_footer : '';

        // If not root element, add as child
        if ($parent === FALSE) {

          $output[$key] = [
            'name' => $name,
            'tid' => $key,
            'url_str' => $url_string,
            'description' => $description,
            'unique_name' => $unique_name,
            'unique_name_footer' => $unique_name_footer,
            'isdefault' => $is_default
          ];
        } else {
          $parent = 'submenu-' . $parent;
          $output['child'][$key] = [
            'name' => $name,
            'tid' => $key,
            'url_str' => $url_string,
            'description' => $description,
            'unique_name' => $unique_name,
            'unique_name_footer' => $unique_name_footer,
            'isdefault' => $is_default
          ];
        }

        if ($item->hasChildren) {
          if ($item->depth == 1) {
            $this->generateSubMenuTree($output[$key], $item->subtree, $key);
          } else {
            $this->generateSubMenuTree($output['child'][$key], $item->subtree, $key);
          }
        }
      }
    }
  }

  /**
   * Method to return menu additional fields.
   *
   * @params int $menu_id
   *   Menu Id.
   *
   * @codeCoverageIgnore
   */
  public function getMenuFields($menu_id) {
    return \Drupal::database()->
      query('SELECT (SELECT field_unique_name_value FROM menu_link_content__field_unique_name WHERE entity_id = :menu_id) as unique_name, (SELECT field_isdefaultservice_value FROM menu_link_content__field_isdefaultservice WHERE entity_id = :menu_id) as is_default, (SELECT field_unique_name_f_value FROM menu_link_content__field_unique_name_f WHERE entity_id = :menu_id) as unique_name_footer', [':menu_id' => $menu_id])->fetchAll()[0];
  }

  public function getTermDetails($block_data, $fieldname, $vid, &$cacheable_dependencies){
    $tax = [];
    if ($block_data) {
      $fieldcontent = $this->getValue($block_data, $fieldname);
      if(!empty($fieldcontent)){
        foreach ($fieldcontent as $tm) {
          $term = $this->getTerm($tm['target_id']);
          if($term){
            $name = $term->getName();
            $tax_spec['name'] = $name;
            if ($vid == 'cta') {
              $tax_spec['field_cta_behaviour'] = $term->get('field_cta_behaviour')->value;
              $tax_spec['field_cta_color'] = $term->get('field_cta_color')->value;
              $tax_spec['field_cta_size'] = $term->get('field_cta_size')->value;
              $tax_spec['field_cta_type'] = $term->get('field_cta_type')->value;
            }
            else if ($fieldname == 'field_segment') {
              $tax_spec['service_url'] = array();
              $tax_spec['field_unique_name'] = $term->get('field_unique_name')->value;
              $tax_spec['field_isdefault'] = $term->get('field_isdefault')->value;
              if ($vid == 'modal_popup') {
                $ser_arr = $this->getValue($term, 'field_service_url');
                if (count($ser_arr)) {
                  foreach ($ser_arr as $value) {
                    $serv = !empty($value['target_id']) ? $this->getTerm($value['target_id']) : '';
                    if ($serv && $serv->get('field_uniquename_serv')->value) {
                      $hash = $serv->get('field_uniquename_serv')->value;
                    }
                    else if ($serv != '') {
                      $hash = $serv->getName();
                    }
                    $cacheable_dependencies[] = $serv;
                    $tax_spec['service_url'][$hash] = $value['value'];
                  }
                }
              }
            }
            else if ($fieldname == 'field_service') {
              $tax_spec['field_uniquename_serv'] = $term->get('field_uniquename_serv')->value;
            }
            $cacheable_dependencies[] = $term;
            array_push($tax, $tax_spec);
          }
        }
      }
    }

    return $tax;
  }

  public function getImageData($media_targetid, &$cacheable_dependencies){
    $img_details = array();
    if(!empty($media_targetid)){
      $media = $this->getMedia($media_targetid);
      if(!$media || empty($media)){
        return null;
      }
      $media_field = $this->getValueByKey($media, 'image');
      $file = $this->getFile($media_field['target_id']);
      if ($file) {
        $img_file = $this->createFileUri($file);
        $img_details['url'] = $this->fileCreateUrl($img_file);
        $cacheable_dependencies[] = $file;
        $img_url;
      }
      $img_details['img_data'] = $media_field;
      $cacheable_dependencies[] = $media;
    }

    return $img_details;
  }

  /**
   * @codeCoverageIgnore
   */
  public function getConfig($parameter = NULL) {
    $ret = \Drupal::config($parameter);
    return $ret;
  }

  /**
   * @codeCoverageIgnore
   */
  public function getStorageWrapper($parameter = NULL) {
    return $this->entityManager->getStorage($parameter);
  }

  /**
   * @codeCoverageIgnore
   */
  public function termLoad($tid = NULL) {
    return Term::load($tid);
  }

  /**
   * @codeCoverageIgnore
   */
  public function fileLoad($log = NULL) {
    return File::load($log[0]);
  }

  /**
   * @codeCoverageIgnore
   */
  public function getModalID() {
    $ret = \Drupal::entityQuery('block_content')
     ->condition('type', 'modal_popup')
     ->execute();
     return $ret;
  }

  /**
   * @codeCoverageIgnore
   */
  public function getBlockData($ids = NULL) {
    return \Drupal\block_content\Entity\BlockContent::load(reset($ids));
  }

  /**
   * @codeCoverageIgnore
   */
  public function getTids() {
    $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'personali')->execute();
    return \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
  }
   /*
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing a list of bundle names.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($ahurl = NULL) {
    $api = [];
    $cacheable_dependencies = [];
    $file = null;
    //Adding Header Logo
    $config = $this->getConfig('simple.settings');
    $cacheable_dependencies[] = $config;

    if ($config) {
      $log = $config->get('simple.logo_image');
      if (isset($log[0]) && $log[0]) {
        $log = $config->get('simple.logo_image');
        $file = $this->fileLoad($log[0]);

        if ($file) {
          $path = $file->getFileUri();
          $gdata['logo']['path'] = $path;
          $cacheable_dependencies[] = $file;
        }
      }
    }

    $parameters = new MenuTreeParameters();
    //$parameters->onlyEnabledLinks();
    $menu_tree = $this->menuTree->load('menu', $parameters);

    $cacheable_dependencies[] = $menu_tree;
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $menu_tree = $this->menuTree->transform($menu_tree, $manipulators);
    // Limit header menu item count to 3.
    $menu_tree = array_slice($menu_tree,0,3,true);

    $this->generateSubMenuTree($menu_tree_with_sub, $menu_tree);
    if ($menu_tree_with_sub) {
      $gdata['menu'] = $menu_tree_with_sub;
    }

    //Adding Footer Menu
    $parameters = new MenuTreeParameters();
    $parameters->onlyEnabledLinks();
    $footer_menu_tree = $this->menuTree->load('footer-menu', $parameters);
    $cacheable_dependencies[] = $footer_menu_tree;
    if($footer_menu_tree){
      $manipulators = array(
        array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
      );


      $tree = $this->menuTree->transform($footer_menu_tree, $manipulators);
      $menu_tmp = $this->menuTree->build($tree);
      $menu_par = array();
      foreach ($menu_tmp['#items'] as $item) {
        if ($item) {
          $menu_par[] = $item['title'];
        }
      }
      $this->generateSubMenuTree($footer_menu_tree_with_sub, $footer_menu_tree);
      $marray_tmp = array();
      foreach ($footer_menu_tree_with_sub as $item){
        $marray_tmp[$item['name']] = $item;
      }
      $marray = array();
      foreach ($menu_par as $ptitle){
        $marray[$marray_tmp[$ptitle]['tid']][] = $marray_tmp[$ptitle];
      }
      $gdata['footer_menu'] = $marray;
    }
    //Adding Footer Menu Ends

    //Adding Footer Bottom Menu
    $footer_bottom_menu_links = array();
    $menu_name = 'footer-bottom-menu';
    $storage = $this->getStorageWrapper('menu_link_content');
    if($storage) {
      $footer_bottom_menu_links = $storage->loadByProperties(['menu_name' => $menu_name]);
    }
    $gdata['footer_bottom_menu']['links'] = $footer_bottom_menu_links;
    $cacheable_dependencies[] = $footer_bottom_menu_links;
    //Adding Footer Bottom Menu Ends
    // Schedula a Meeting Link Config
    $config = $this->getConfig('ahd_custom.adminsettings');
    $cacheable_dependencies[] = $config;
    //$sm_cta_type = Term::load($config->get('footer_sm_cta_type'));
    $sm_cta_type = $this->termLoad($config->get('footer_sm_cta_type'));
    $cacheable_dependencies[] = $sm_cta_type;
    $sm_array['sm_label'] = $config->get('ah_schedule_meeting_label');
    $sm_array['sm_link'] = $config->get('ah_schedule_meeting_link');
    $footer_sm_cta_type = $config->get('footer_sm_cta_type');
    if ($footer_sm_cta_type) {
      $footer_sm_cta_type_term = $this->termLoad($footer_sm_cta_type);
      if ($footer_sm_cta_type_term) {
        $sm_array['sm_cta_details'] = $footer_sm_cta_type_term->toArray();
        $cacheable_dependencies[] = $footer_sm_cta_type_term;
      }
    }
    // Schedula a Meeting Link Config ends
    //$data['footer_bottom_menu']['links'] = $menu_links;
    $gdata['footer_bottom_menu']['schedule_meeting'] = $sm_array;
    $gdata['webform_config'] = [
      'webservice_url' => $config->get('webform_service_url'),
      'schedule_meeting' => $config->get('schedule_meeting_form'),
      'schedule_meeting_label' => $config->get('ah_schedule_meeting_label'),
      'schedule_meeting_page_url' => $config->get('ah_schedule_meeting_link'),
    ];
    $vid = 'social_media_links';
    $mapping = $this->getStorageWrapper("taxonomy_term")->loadTree($vid, $parent = 0, $max_depth = NULL, $load_entities = TRUE);
    $social_data = array();
    if(!empty($mapping)){
      foreach($mapping  as $term){
        $t_array = $term->toArray();
        $img = $this->getImageData($t_array['field_social_media_icon'][0]['target_id'], $cacheable_dependencies);
        $t_array['field_social_media_icon']['image_details'] = $img;
        $social_data[$t_array['name'][0]['value']][] = $t_array;
        $cacheable_dependencies[] = $term;
      }
    }
    $gdata['footer_bottom_menu']['footer_call_us'] = $config->get('footer_bottom_call_us');
    $gdata['footer_bottom_menu']['ah_social_media_links'] = $social_data;
    //Adding Footer Bottom Menu Ends

    // Adding Cookie Notification bacnkend config as API starts here

    $gdata['cookie_configuration'] = array();
    $my_config = $this->getConfig('eu_cookie_compliance.settings');
    $cacheable_dependencies[] = $my_config;
    $gdata['cookie_configuration']['popup_info']= $my_config->get('popup_info') ? $my_config->get('popup_info') : null;
    $gdata['cookie_configuration']['popup_agree_button_message']= $my_config->get('popup_agree_button_message') ? $my_config->get('popup_agree_button_message') : null;
    $gdata['cookie_configuration']['privacy_policy_label']= $my_config->get('privacy_policy_label') ? $my_config->get('privacy_policy_label') : null;
    $gdata['cookie_configuration']['privacy_policy_link']= $my_config->get('popup_link') ? $my_config->get('popup_link') : null;
    $gdata['cookie_configuration']['popup_bg_hex']= $my_config->get('popup_bg_hex') ? $my_config->get('popup_bg_hex') : null;
    $gdata['cookie_configuration']['popup_text_hex']= $my_config->get('popup_text_hex') ? $my_config->get('popup_text_hex') : null;

    // Adding Cookie Notification bacnkend config as API ends here

    //Adding Modal Popup Configuration
    $ids = $this->getModalID();
    $block = $this->getBlockData($ids);
    $cacheable_dependencies[] = $block;
    $tax = $this->getTermDetails($block, 'field_speciality', 'modal_popup', $cacheable_dependencies);
    $seg = $this->getTermDetails($block, 'field_segment', 'modal_popup', $cacheable_dependencies);
    $services_arr = array();
    $terms_serv = $this->getStorageWrapper('taxonomy_term')->loadTree('services');
    foreach ($terms_serv as $term) {
      $service_term = array();
      $term_load = Term::load($term->tid);
      $cacheable_dependencies[] = $term_load;
      $service_term['name'] = $term->name;
      $service_term['unique_name'] = $term_load->field_uniquename_serv->value;
      $service_term['isdefaultservice'] = $term_load->field_isdefaultservice->value;
      array_push($services_arr, $service_term);
    }
    $vid = 'specialty_services_mapping';
    $mapping = $this->getStorageWrapper("taxonomy_term")->loadTree($vid, $parent = 0, $max_depth = NULL, $load_entities = TRUE);
    $profile_mapping = array();
    foreach($mapping as $term){
      $segm_name = '';
      $spec_name = '';
      $segm = $this->referenceReplace->getTermsDetails($term, 'field_segment_field', $cacheable_dependencies);
      $spec = $this->referenceReplace->getTermsDetails($term, 'field_specialty', $cacheable_dependencies);
      $services = $this->referenceReplace->getTermsDetails($term, 'field_services', $cacheable_dependencies);
      foreach($services as $service){
        $serv_icon = $this->getImageData($service['field_service_icon'][0]['target_id'], $cacheable_dependencies);
        $service['field_service_icon'][0][] = $serv_icon;
        $uri = $service['field_service_link'][0]['uri'];
        $url = str_replace('/internal%3A','',file_create_url($uri));
        $service['field_service_link'][0]['url'] = $url;
        if (is_array($segm)) {
          if (isset($segm[0]['name'][0]['value'])) {
            $segm_name = $segm[0]['name'][0]['value'];
            $segm_name = str_replace(' ','_',$segm_name);
          }
        }
        if (is_array($spec)) {
          if (isset($spec[0]['name'][0]['value'])) {
            $spec_name = $spec[0]['name'][0]['value'];
            $spec_name = str_replace(' ','_',$spec_name);
          }
        }
        if ($segm_name != '') {
          $profile_mapping[$segm_name][$spec_name] = $service;
        }
      }
      $cacheable_dependencies[] = $term;
    }
    $gdata['profile_mapping'] = $profile_mapping;

    $block_array = [];
    $block_array['field_speciality'] = $tax;
    $block_array['field_backgroundcolor'] = $block->get('field_backgroundcolor')->color;
    $block_array['field_main_question'] = $block->get('field_main_question')->value;
    $block_array['field_question_tag'] = $block->get('field_question_tag')->value;
    $block_array['field_right_side_text'] = $block->get('field_right_side_text')->value;
    $block_array['field_top_copy'] = $block->get('field_top_copy')->value;
    $block_array['field_cta_label'] = $block->get('field_cta_label')->value;
    $block_array['field_segment'] = $seg;
    $block_array['services'] = $services_arr;
    if ($block_array) {
      $gdata['modal_popup'] = $block_array;
    }

    //Adding Personalization mapping
    //$tids = $this->getTids();
    //echo "<pre>";print_r($tids);exit;
    // $query->condition('vid', 'personali');
    // $tids = $query->execute(); // Get terms Ids.
    $block_pers = [];
    $terms = $this->getTids();

    foreach ($terms as $tid => $term) {
      $cta = $this->getTermDetails($term, 'field_cta_style', 'cta', $cacheable_dependencies);
      $cta['cta_link'] = str_replace('internal:', '', $term->get('field_ctalink')->uri);
      $cta['title'] = $term->get('field_ctalink')->title;
      $name = str_replace(" ", "_", $term->get('name')->value);
      $block_pers[$name]['title'] = $term->get('field_title')->value;
      $block_pers[$name]['layout'] = $term->get('field_layout')->value;
      $block_pers[$name]['textcolor'] = $term->get('field_text_color')->color;
      $block_pers[$name]['backgroundcolor'] = $term->get('field_ba')->color;
      $block_pers[$name]['image'] = $this->getImageData($term->get('field_b')->target_id, $cacheable_dependencies);
      $block_pers[$name]['mobile_image'] = $this->getImageData($term->get('field_bmobile')->target_id, $cacheable_dependencies);
      $block_pers[$name]['cta'] = $cta;
      $block_pers[$name]['segment'] = $this->getTermDetails($term, 'field_segment', 'segment', $cacheable_dependencies);
      $block_pers[$name]['speciality'] = $this->getTermDetails($term, 'field_speciality', 'speciality', $cacheable_dependencies);
      $block_pers[$name]['service'] = $this->getTermDetails($term, 'field_service', 'services', $cacheable_dependencies);
      $cacheable_dependencies[] = $term;
    }
    if ($block_pers) {
      $gdata['personalization_mapping'] = $block_pers;
    }

    $api['globaldata'] = $gdata;

    //Page level data
    if ($ahurl) {
      $ahurl = str_replace("--","/",$ahurl);

      //$alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$ahurl);
      $path = $this->aliasManager->getPathByAlias($ahurl);
      // DTM script based on page visibility configuration.
      $api['globaldata']['dtmscript_config'] = $this->getDtmScript($ahurl, $path, $config);

      $is_landing_page = FALSE;
      $preview = $this->isPreview();
      if($preview == 'latest') {
        $node = $this->getNodeLatestRevision($path);
        if ($node && $node->getType() == 'landing_page' || $node->getType() == 'blog') {
          $is_landing_page = TRUE;
        }
      }
      else if(preg_match('/node\/(\d+)/', $path, $matches)) {
        $node = $this->getNode($matches[1]);

        if ($node && ($node->getType() == 'landing_page' || $node->getType() == 'blog')) {
          $is_landing_page = TRUE;
        }
      }
      else {
        $node = '';
      }
      // Page redirection logic.
      if (!$is_landing_page) {
        //Look up the URL in Drupal re-direct system.
        $this->getRedirectUrl($ahurl, $data);
        // if url not found in Drupal redirect system then look up in vanity service.
        if (!isset($data['status_name'])) {
          $this->getVanityUrl($ahurl, $data);
        }
        // If url not found in vanity service then throw 404.
        if (!isset($data['status_name'])) {
          $data['status_name'] = 'Not a valid node';
          $data['status_code'] = '404';
        }
        // Clear cache for vanity and 404.
        $this->cacheResponse->clearCache('vanity-and-404');
      }
      else {
        $data['status_name'] = 'Valid node';
        $data['status_code'] = '200';
        $bundles =[];
        // @todo - implement - node load by url alias
        // $path = \Drupal::service('path.alias_manager')->getPathByAlias($ahurl);
        //$node = \Drupal\node\Entity\Node::load(1);
        $block_content = [];
        $meta_info = $this->getValue($node, 'field_meta_tags');
        $field_services_crawl_text = $this->getValue($node, 'field_services_crawl_text');
        $data['title'] = ($node->hasField('title')) ? $node->getTitle() : '';
        $page_type = $this->getValue($node, 'field_page_type');
        $data['page_type'] = isset($page_type[0]) ? (isset($page_type[0]['value']) ? $page_type[0]['value'] : "Landing Page") : "Landing Page";

        //Replacing the tokens in the meta information
        $token_replaced = [];
        $data['meta_info'] = [];
        if (isset($meta_info[0]['value'])) {
          $meta_info =  unserialize($meta_info[0]['value']);
          if ($meta_info) {
            foreach ($meta_info as $key => $value) {
              $token_replaced[$key] = $this->replaceToken($value, ['node' => $node]);
            }
            $data['meta_info'] = $token_replaced;
          }
        }
        if (isset($data['meta_info']['canonical_url'])) {
          $data['meta_info']['canonical'] = $data['meta_info']['canonical_url'];
          unset($data['meta_info']['canonical_url']);
        }
        else {
          $data['meta_info']['canonical'] = $this->aliasManager->getAliasByPath('/node/' . $node->id());
        }
        $data['meta_info']['service_crawl'] = ($field_services_crawl_text) ? $field_services_crawl_text[0]['value'] : '';
        $i=1;
        $data['custom_blocks'] = [];
        if ($panelizer = $this->getPanelizer($node)) {

          if (isset($panelizer['panels_display']['blocks'])) {

            /* Sort $blocks array based on the block weight(Order) */
            $b_keys = array();
            $inventory = $panelizer['panels_display']['blocks'];
            foreach ($inventory as $key => $row) {
                $b_keys[$key] = $row['weight'];
            }
            array_multisort($b_keys, SORT_ASC, $inventory);
            $sorted_blocks = array();
            foreach($b_keys as $uuid => $weight) {
                $sorted_blocks[$uuid] = $panelizer['panels_display']['blocks'][$uuid];
            }

            /* Sort $blocks array based on the block weight(Order) */
            foreach($sorted_blocks as $uuid => $block) {
              //@todo - implement a custom array formatter for each custom block
              if($block['provider'] == 'block_content') {
                $block_key = explode(":",$block['id'] );
                // $data['custom_blocks'][$i]['panel_info']  = $block;
                $block_data['data'] = $this->getEntityByUuid('block_content',$block_key[1]);
                if ($block_data['data']) {
                  $machine_name = $this->getValue($block_data['data'], 'type');
                  $data['custom_blocks'][$i]['panel_info']['machine_name'] = $machine_name[0]['target_id'];
                  $block_content[] = $block_data ;
                  $data['custom_blocks'][$i]['data'] = $this->ahService->processBlocks($block_data['data'], $cacheable_dependencies);
                  $cacheable_dependencies[] = $block_data['data'];
                  $i++;
                }
              }
              else if ($block['provider'] == 'ctools_block') {
                $field_name = str_replace('entity_field:node:', '', $block['id']);
                // For listing lander pages images should be part of content list.
                if ($data['page_type'] == 'Case Study List') {
                  if ($field_name == 'field_list_image') {
                    continue;
                  }
                }
                else if ($data['page_type'] == 'Case Study') {
                  if ($field_name == 'field_content_list' || $field_name == 'field_contents_per_page') {
                    continue;
                  }
                }
                if ($node->hasField($field_name)) {
                  $field_data = $this->nodeField->getFieldValue($node, $field_name, $cacheable_dependencies);
                  if (isset($field_data['key']) && isset($field_data['value']) && !empty($field_data['value'])) {
                    $data['custom_blocks'][$i] = [
                      'panel_info' => ['machine_name' => 'fields'],
                      'data' => [
                        $field_data['key'] => $field_data['value'],
                        'analytics_component' => $field_data['ac'],
                      ],
                    ];
                    $i++;
                  }
                }
              }
            }
          }

          // Clear cache based on request header and tags.
          $this->cacheResponse->clearCache(['node:' . $node->id()]);
        }
      }
      $api['pagedata'] = $data;
      $api['globaldata']['time'] = time();

      if (!empty($api)) {
        // Non- cacheble response
        //return new ResourceResponse( $data);
        // Experimental - cacheble thing
        $response = new ResourceResponse($api);
        if ($is_landing_page) {
          $cacheable_dependencies[] = ($node) ? $node: '';
          $this->cacheResponse->addCacheDependencies($response, $cacheable_dependencies);
        }
        else {
          $response
            ->addCacheableDependency(
              CacheableMetadata::createFromRenderArray([
                '#cache' => [
                  'tags' => [
                    'vanity-and-404',
                  ],
                ],
              ])
            );
        }
        return $response;
      }
    }

    throw new ServiceNotFoundException($this->t('Entity wasn\'t provided'));
  }

  /**
   * Method to load node using node id.
   *
   * @param int $nid
   *   Node id.
   *
   * @return Drupal\node\Entity\Node
   *   Node object if it's published.
   *
   * @codeCoverageIgnore
   */
  protected function getNode($nid) {
    $node_load = Node::load($nid);
    if($node_load->isPublished()){
      return $node_load;
    }
    else{
      return '';
    }
  }
    /**
   * Method to load node using node id .
   *
   * @param int $nid
   *   Node id.
   *
   * @return Drupal\node\Entity\Node
   *   Node object.
   *
   * @codeCoverageIgnore
   */
  protected function getPreviewNode($nid) {
    $node_load = Node::load($nid);
    return $node_load;
  }
 /**
   * Method to load media using media id.
   *
   * @param int $mid
   *   Media id.
   *
   * @return Drupal\media\Entity\Media
   *   Media object.
   *
   * @codeCoverageIgnore
   */
  protected function getMedia($mid) {
    return Media::load($mid);
  }

   /**
   * Method to load file using file fid.
   *
   * @param int $fid
   *   File id.
   *
   * @return Drupal\file\Entity\File
   *   File object.
   *
   * @codeCoverageIgnore
   */
  protected function getFile($fid) {
  return file_load($fid);
  }
  /**
  * Method to load file using file fid.
  *
  * @param int $fid
  *   File id.
  *
  * @return Drupal\file\Entity\File
  *   File object.
   *
   * @codeCoverageIgnore
  */
 protected function fileCreateUrl($fid) {
 return file_create_url($fid);
 }
 /**
   * @codeCoverageIgnore
   */
 protected function createFileUri($file){
  return $file->get('uri')->value;
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
   *
   *  @codeCoverageIgnore
   */
  protected function getValueByKey($entity, $key) {
    return $entity->get($key)->first()->getValue();
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
   *
   * @codeCoverageIgnore
   */
  protected function getValue($entity, $key) {
    if ($entity->hasField($key)) {
      return $entity->get($key)->getValue();
    }
    else {
      return;
    }
  }

  /**
   * Method to replcae token in text.
   *
   * @param string $text
   *   Text with tokens.
   * @param $data
   *   Token context
   *
   * @codeCoverageIgnore
   */
  protected function replaceToken($text, $data) {
    return \Drupal::service('renderer')->executeInRenderContext(
      new RenderContext(),
      function () use ($text, $data) {
        return $this->tokenService->replace($text, $data);
      }
    );
  }

  /**
   * Method to get Panelizer from landing page.
   *
   * @param Drupal\node\Entity\Node $node
   *   Node entity.
   *
   * @return array
   *   Panelizer array
   *
   * @codeCoverageIgnore
   */
  protected function getPanelizer($node) {
    $panelizers = $node->panelizer->getValue();
    foreach ($panelizers as $panelizer){
      if ($panelizer['view_mode'] === 'full') {
        if (isset($panelizer['panels_display']['blocks'])) {
          return $panelizer;
        }
      }
    }
    /* For newly created node, panelizer will be empty. So get the default
     entity panels view. */
    if ($node->getType() != 'landing_page') {
      $panelizer['panels_display'] = \Drupal::service('panelizer')->getPanelsDisplay($node,'full')->getConfiguration();
    }

    return $panelizer;
  }

  /**
   * Method to load entity using uuid.
   *
   * @param string $entity_type_id
   *   Entity type machine name.
   * @param string $uuid
   *   Universal unique id of the entity.
   *
   * @codeCoverageIgnore
   */
  protected function getEntityByUuid($entity_type_id, $uuid) {
    return $this->entityManager->loadEntityByUuid($entity_type_id, $uuid);
  }

  /**
   * Method to load term.
   *
   * @codeCoverageIgnore
   */
  protected function getTerm($tid) {
    return Term::load($tid);
  }

  /**
   * Method to get Drupal redirection url.
   *
   * @codeCoverageIgnore
   */
  protected function getRedirectUrl($ahurl, &$data) {
    // trim the slashes in the source URL.
    $source_url = trim($ahurl, '/');
    $redirect_data = \Drupal::service('redirect.repository')->findBySourcePath($source_url);
    $redirect_obj = array_pop($redirect_data);
    if (count($redirect_obj) > 0 ) {
      $rediect_url = $redirect_obj->getRedirect();
      $redirected_to = str_replace('internal:','', $rediect_url['uri']);
      $data['status_name'] = 'Drupal Redirect';
      $data['redirect_url'] = $redirected_to ;
      $data['status_code'] = $redirect_obj->getStatusCode() ;
    }
}
  /**
   * Method to get vanity redirection url.
   *
   * @codeCoverageIgnore
   */
  protected function getVanityUrl($ahurl, &$data) {
    $tasking_api_url = 'https://vanityservice.athenahealth.com/VanityURL/VanityAPI.asmx?wsdl';
    $options = array();
    $options["location"] = $tasking_api_url;
    $options['url'] = $ahurl;
    try {
      $client= new \SoapClient($tasking_api_url, $options);
      $result = $client->RedirectOnItemNotFound($options);
      $xml_result = $result->RedirectOnItemNotFoundResult;
      if ($xml_result != '404 No Page Found Error!!!') {
        $data['status_name'] = 'Vanity Url';
        $data['redirect_url'] = json_decode($xml_result)->LongURL;
        $data['status_code'] = '301';
      }
    }
    catch (\Exception $e) {
      $data['status_name'] = 'Not a valid node';
      $data['status_code'] = '404';
    }
  }

  /**
   * Method to identify page preview.
   *
   * @codeCoverageIgnore
   */
  protected function isPreview() {
    return \Drupal::request()->query->get('preview');
  }

  /**
   * Method to get latest revision of node for preview.
   *
   * @codeCoverageIgnore
   */
  protected function getNodeLatestRevision($path) {
    $split_nid = preg_match('/node\/(\d+)/', $path, $matches);
    $rids = $this->entityManager->getStorage('node')->revisionIds($this->getPreviewNode($matches[1]));
    return $this->entityManager->getStorage('node')->loadRevision(end($rids));
  }

  /*
   * Method to get dtm script based on visibility.
   */
  public function getDtmScript($alias_path, $path, $config) {
    // Firstly check for dev pages match.
    $dev_pages = $config->get('dtm_dev_url_pages');
    foreach(preg_split('/\r\n|[\r\n]/', $dev_pages) as $pattern) {
      if ($this->pathMatcher->matchPath($alias_path, $pattern) || $this->pathMatcher->matchPath($path, $pattern)) {
        return $config->get('dtm_dev_url');
      }
    }
    // Return default production script.
    return $config->get('dtm_prod_url');
  }

}
