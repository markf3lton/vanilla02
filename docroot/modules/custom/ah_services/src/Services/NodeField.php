<?php
/**
* @file providing the service for replacing the reference items.
*
*/
namespace  Drupal\ah_services\Services;

use Drupal\ah_services\Services\ReferenceReplace;
use Drupal\Core\Path\AliasManager;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class NodeField {
  
  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    ReferenceReplace $reference_replace,
    AliasManager $alias_manager) {
    $this->referenceReplace = $reference_replace;
    $this->aliasManager = $alias_manager;
  }
  
  /**
   * Method to return node field values.
   */
  public function  getFieldValue($node, $field_name, &$cacheable_dependencies) {
    $field_value = [];
    switch ($field_name) {
      case 'field_title':
      case 'field_read_more_text':
      case 'field_more_resources_title':
        if ($field_name === 'field_read_more_text') {
          $has_read_more = $this->getNodeFieldValue($node, 'field_add_read_more');
          if ($has_read_more[0]['value'] === '1') {
            $field_value['key'] = $field_name;
            $field_value['value'] = $this->getNodeFieldValue($node, $field_name);
          }
        }
        else {
          $field_value['key'] = $field_name;
          $field_value['value'] = $this->getNodeFieldValue($node, $field_name);
        }
        if ($field_name === 'field_title') {
          $field_value['ac'] = '1';
        }
        break;
      case 'field_subtitle':
        $field_value['key'] = $field_name;
        $field_value['value'] = $this->getNodeFieldValue($node, $field_name);
        $field_value['value'][0]['divider'] = $this->getNodeFieldValue($node, 'field_enable_title_divider')[0]['value'];
        $field_value['ac'] = '1';
        break;
      case 'field_list_image':
        if ($node->get($field_name)->target_id) {
          $field_value['key'] = $field_name;
          $field_value['value'] = $this->referenceReplace->getMediaDetails($node->get($field_name)->target_id, $cacheable_dependencies);
        }
        break;
      case 'field_content_list':
        $field_value['key'] = $field_name;
        foreach ($this->getNodeFieldValue($node, $field_name) as $content) {
          $content = $this->loadEntity('Drupal\node\Entity\Node', $content['target_id']);
          $list_image = $this->getFieldValue($content, 'field_list_image', $cacheable_dependencies);
          if (isset($list_image['key']) && isset($list_image['value'])) {
            $list_image = $list_image['value'];
          }
          else {
            $list_image = null;
          }
          $summary = $this->trimByCharCount($this->getNodeFieldValue($content, 'body')[0]['summary']);
          if (empty($summary)) {
            $summary = $this->trimByCharCount($this->getNodeFieldValue($content, 'body')[0]['value']);
          }
          $field_value['value'][] = [
            'list_image' => $list_image,
            'title' => $this->getNodeFieldValue($content, 'field_title'),
            'sub_title' => $this->getNodeFieldValue($content, 'field_subtitle'),
            'body' => $summary,
            'link' => $this->aliasManager->getAliasByPath('/node/' . $this->getEntityId($content)),
            'read_more' => $this->getNodeFieldValue($content, 'field_read_more_text_for_summary')[0]['value']
          ];
        }
        if (!empty($this->getNodeFieldValue($node, 'field_contents_per_page'))) {
          $contents_per_page = $this->getNodeFieldValue($node, 'field_contents_per_page')[0]['value'];
          if (!$contents_per_page) {
            $contents_per_page = 5;
          }
          $field_value['value']['contents_per_page'] = $contents_per_page;
          $field_value['ac'] = '1';
        }
        break;
      case 'field_statistic_icons_title':
        $field_value['key'] = 'field_stat_icon_title';
        $field_value['value'] = $this->getNodeFieldValue($node, $field_name);
        break;
      case 'field_statistic_icons':
      case 'field_more_resources':
        if (!empty($node->get($field_name))) {
          $field_value['key'] = $field_name;
          foreach ($node->get($field_name) as $icon) {
            if ($icon->target_id) {
              $paragraph = $this->loadEntity('Drupal\paragraphs\Entity\Paragraph', $icon->target_id);
              $field_icon_type = $paragraph->get('field_icon_type')->value;
              if ($field_icon_type === 'Image') {
                $field_value['value'][] = [
                  'icon_description' => $paragraph->get('field_ico')->value,
                  'icon_type' => $field_icon_type,
                  'icon_image' => $this->referenceReplace->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies),
                ];
              }
              else if($field_icon_type === 'GIF') {
                $field_value['value'][] = [
                  'icon_description' => $paragraph->get('field_ico')->value,
                  'icon_type' => $field_icon_type,
                  'icon_image' => $this->referenceReplace->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies),
                  'icon_gif' => $this->referenceReplace->getMediaDetails($paragraph->get('field_gif')->target_id, $cacheable_dependencies)
                ];
              }
            }
          }
          $field_value['ac'] = '1';
        }
        break;
      case 'field_highlights':
        if (!empty($node->get('field_highlights'))) {
          $field_value['key'] = $field_name;
          foreach ($node->get('field_highlights') as $icon) {
            if ($icon->target_id) {
              $paragraph = $this->loadEntity('Drupal\paragraphs\Entity\Paragraph', $icon->target_id);
              $field_value['value'][] = [
                'highlights_title' => $paragraph->get('field_article_highlights_title')->value,
                'highlights_content' => $paragraph->get('field_article_highlights_bullets')->value
              ];
            }
          }
          $field_value['ac'] = '1';
        }
        break;
      case 'body':
        $field_value['key'] = 'article_content';
        $field_value['value'] = $this->getNodeFieldValue($node, $field_name);
        $field_value['ac'] = '1';
        
        //Replace all brightcove video tags with video id tokens
        preg_match_all('/<drupal-entity(.*?)<\/drupal-entity>/s', $field_value['value'][0]['value'], $matches);
        
        foreach ($matches[1] as $key => $match) {
          preg_match('/data-entity-uuid=\"(.*?)\"/',$matches[1][$key],$matches1);
          preg_match('/data-entity-type="(.*?)\"/',$matches[1][$key],$matches2);
          $replacePattern = '/<drupal-entity(.*?)<\/drupal-entity>/';
          if ($matches2[1] === 'brightcove_video') {
            $v_id = \Drupal::entityManager()->loadEntityByUuid($matches2[1], $matches1[1]);
            $replaceWith = '*brightcove_video='.$v_id->get('video_id')->value.'*';
          }
          else if ($matches2[1] === 'media') {
            $v_id = \Drupal::entityManager()->loadEntityByUuid($matches2[1], $matches1[1]);
            $img = $this->referenceReplace->getMediaDetails($v_id->get('mid')->value, $cacheable_dependencies);
            $replaceWith = '*inline_image='.$img['rel_url'].'*';
          }
          $output = preg_replace($replacePattern, $replaceWith, $field_value['value'][0]['value'], 1);
          $field_value['value'][0]['value'] = $output;
        }
        break;
     case 'field_upload_white_papers':
        $pdf = $this->getNodeFieldValue($node, 'field_upload_white_papers');
        $image_file = \Drupal\file\Entity\File::load($pdf[0]['target_id']);
        $uri = $image_file->uri->value;
        $field_value['key'] = 'field_upload_white_papers';
        $field_value['value'] = file_create_url($uri);
        break;
     case 'field_campaign_id':
         if ($field_name === 'field_campaign_id') {
          $campaign_id = $this->getNodeFieldValue($node, 'field_campaign_id');
          if (is_array($campaign_id) && !empty($campaign_id)) {
            $field_value['key'] = 'field_campaign_id';
            $field_value['value'] = $campaign_id;
          }
        }
        break;
      case 'field_gated_form':
         if ($field_name === 'field_gated_form') {
          $field_gated_form = $this->getNodeFieldValue($node, 'field_gated_form');
          if (is_array($field_gated_form) && !empty($field_gated_form)) {
            $field_value['key'] = 'field_gated_form';
            $field_value['value'] = $field_gated_form;
          }
        }
        break;
      default:
        break;
    }

    return $field_value;
  }

  /**
   * Method to get summary using number of words.
   */
  public function trimByCharCount($sentence, $count = 350) {
    preg_match("/^.{1,$count}\b/s", $sentence, $matches);
    if (isset($matches[0])) {
      return trim($matches[0]);
    }
  }

  /**
   * Method get node field value.
   *
   * @codeCoverageIgnore
   */
  public function getNodeFieldValue($node, $field) {
    return $node->get($field)->getValue();
  }

  /**
   * Method to load node
   *
   * @codeCoverageIgnore
   */
  public function loadEntity($entity, $id) {
    return call_user_func($entity . '::load', $id);
  }

  /**
   * Method to get entity id.
   *
   * @codeCoverageIgnore
   */
  public function getEntityId($entity) {
    return $entity->id();
  }
}
