<?php
/**
* @file providing the service for replacing the reference items.
*
*/
namespace  Drupal\ah_services\Services;

use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\ah_services\Plugin\rest\resource\MenuApiResource;
use Drupal\paragraphs\Entity\Paragraph;

class ReferenceReplace {

    public function  processBlocks($block, &$cacheable_dependencies) {
        $component_type = $this->getType($block);
        switch ($component_type) {
            case 'icon_bars_module':
                // @todo move this our to a custom function
                $ribbon_data = $this->blockObjectToArray($block);
                $ribbon_data['analytics_component'] = '1';
                return $this->iconBarModule($ribbon_data, $cacheable_dependencies);
            break;
            case 'priorities_list_points':
                $img_details = array();
                $pl_cta_details = array();
                $video_details = array();
                if($block->get('field_priorities_list_image')->target_id){
                    $img_details = $this->getMediaDetails($block->get('field_priorities_list_image')->target_id, $cacheable_dependencies);
                }
                if($block->get('field_priorities_list_cta')->target_id){
                    $pl_cta_details = $this->getTermDetails($block->get('field_priorities_list_cta')->target_id, $cacheable_dependencies);
                }
                $v_tid = $block->get('field_video')->target_id;
                if($v_tid){
                    $ventity = $this->getBrightcoveVideo($v_tid);
                    if (!empty($ventity)) {
                        $cacheable_dependencies[] = $ventity;
                        $video_details = $ventity->toArray();
                    }
                }
                $block_data = $this->blockObjectToArray($block);
                $block_data['pl_image_details'] = $img_details;
                $block_data['pl_cta_details'] = $pl_cta_details;
                $block_data['pl_video_details'] = $video_details;
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'grid_module':
                $services_terms = $this->getTermsDetails($block, 'field_services', $cacheable_dependencies);
                //$sts = $services_terms->toArray();
                $c = 0;
                foreach($services_terms as $services_term){
                    $img_tarid = $services_term['field_service_icon'][0]['target_id'];
                    if($img_tarid){
                        $img_details = $this->getMediaDetails($img_tarid, $cacheable_dependencies);
                        $services_terms[$c]['field_service_icon']['image_details'][] = $img_details;
                    }
                    $c++;
                }
                $c = 0;
                $priorities_terms =$this->getTermsDetails($block, 'field_priorities', $cacheable_dependencies);
                foreach($priorities_terms as $priorities_term){
                    $img_tarid = $priorities_term['field_priority_icon'][0]['target_id'];
                    if($img_tarid){
                        $img_details = $this->getImageDetails($img_tarid, $cacheable_dependencies);
                        $priorities_terms[$c]['field_priority_icon']['image_details'][] = $img_details;
                    }
                    $c++;
                }
                $block_data = $this->blockObjectToArray($block);
                $block_data['grid_priorities_details'] = $priorities_terms;
                $block_data['grid_services_details'] = $services_terms;
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'hero_banner':
            case 'campaign_banner':
                $block_data = $this->blockObjectToArray($block);
                if ($block->get('field_background_media')->target_id) {
                    $hbimg_details = $this->getMediaDetails($block->get('field_background_media')->target_id, $cacheable_dependencies);
                    $block_data['field_background_media'] = $hbimg_details;
                }
                if ($block->get('field_background_media_mobile')->target_id) {
                    $hbimg_details_mobile = $this->getMediaDetails($block->get('field_background_media_mobile')->target_id, $cacheable_dependencies);
                    $block_data['field_background_media_mobile'] = $hbimg_details_mobile;
                }
                $hbcta_details = $this->getTermDetails($block->get('field_cta_style')->target_id, $cacheable_dependencies);
                $block_data['field_cta_style'] = $hbcta_details;
                $block_data['field_cta_link'][0]['uri'] = str_replace('internal:', '', $block_data['field_cta_link'][0]['uri']);
                $block_data['field_hb_bvideo'] = array();
                $v_tid = $block->get('field_hb_bvideo') ? $block->get('field_hb_bvideo')->target_id : null;
                if ($v_tid) {
                    $ventity = $this->getBrightcoveVideo($v_tid);
                    $cacheable_dependencies[] = $ventity;
                    $video_details = $this->blockObjectToArray($ventity);
                    $block_data['field_hb_bvideo'] = $video_details;
                }
                $block_data['analytics_component'] = '1';
                if ($block->hasField('field_campaign_id')) {
                    $campaign_id = $block->get('field_campaign_id')->value;
                    if (is_array($campaign_id) && !empty($campaign_id)) {
                     $block_data['key'] = 'field_campaign_id';
                     $block_data['value'] = $campaign_id;
                    }
                }
                return $block_data;
                break;
            case 'horizontal_module':
                $block_data = $this->blockObjectToArray($block);

                $cta_details = $this->getTermDetails($block->get('field_ctastyle')->target_id, $cacheable_dependencies);
                $block_data['field_ctastyle'] = $cta_details;
                $block_data['field_ctalink'][0]['uri'] = str_replace('internal:', '', $block_data['field_ctalink'][0]['uri']);
                $block_data['field_hb_bvideo'] = [];
                $v_tid = $block->get('field_hb_bvideo') ? $block->get('field_hb_bvideo')->target_id : null;
                if($v_tid){
                    $video_entity = $this->getBrightcoveVideo($v_tid);
                    $cacheable_dependencies[] = $video_entity;
                    $video_details = $this->blockObjectToArray($video_entity);
                    $block_data['field_hb_bvideo'] = $video_details;
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'fs_module':
                $block_data = $this->blockObjectToArray($block);
                $tids = $block->get('field_checklistitems')->getString();
                $tids = str_replace(' ', '', $tids);
                $tids = explode(',', $tids);
                $checklist_details = array();
                foreach($tids as $tid){
                    $checklist_details[] = $this->getTermDetails($tid, $cacheable_dependencies);
                }
                $block_data['field_checklistitems'] = $checklist_details;
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'faq':
                $block_data = $this->blockObjectToArray($block);
                $cta_details = $this->getTermDetails($block->get('field_faqcta_style')->target_id, $cacheable_dependencies);
                $block_data['field_faqcta_style'] = $cta_details;
                $block_data['field_faq_video'] = array();
                    $v_tid = $block->get('field_faq_video') ? $block->get('field_faq_video')->target_id : null;
                    if($v_tid){
                        $ventity = $this->getBrightcoveVideo($v_tid);
                        $cacheable_dependencies[] = $ventity;
                        $video_details = $this->blockObjectToArray($ventity);
                        $block_data['field_faq_video'] = $video_details;
                    }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'services_demo':
                $block_data = $this->blockObjectToArray($block);
                if ($block->get('field_media')->target_id) {
                    $sdimg_details = $this->getMediaDetails($block->get('field_media')->target_id, $cacheable_dependencies);
                    $block_data['field_media'] = $sdimg_details;
                }
                $sdcta_details = $this->getTermDetails($block->get('field_sd_cta')->target_id, $cacheable_dependencies);
                $block_data['field_sd_cta'] = $sdcta_details;
                if ($block->get('field_cta_brightcove_video')->target_id) {
                    $dat = $this->getBrightcoveVideo($block->get('field_cta_brightcove_video')->target_id);
                    $cacheable_dependencies[] = $dat;
                    $block_data['field_cta_brightcove_video'][0]['value'] = $dat->get('video_id')->value;
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'ah_ai_bridge':
                $block_data = $this->blockObjectToArray($block);
                $ah_admin_settings = $this->getConfig('ahd_custom.adminsettings');
                $cacheable_dependencies[] = $ah_admin_settings;
                $block_data['field_insight_feed_url']['base_api'] = $ah_admin_settings->get('ah_insight_bridge_url');
                $block_data['field_insight_feed_url']['base_url'] = $ah_admin_settings->get('ah_insight_base_url');
                $block_data['field_insight_feed_url']['files_url'] = $ah_admin_settings->get('ah_insight_files_url');
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'page_title_block':
                $block_data = $this->blockObjectToArray($block);
                if (isset($block_data['field_cta_link'][0]['uri'])) {
                    $block_data['field_cta_link'][0]['uri'] = str_replace('internal:', '', $block_data['field_cta_link'][0]['uri']);
                }
                if ($block->get('field_cta_style')->target_id) {
                    $block_data['field_cta_style'] = $this->getTermDetails($block->get('field_cta_style')->target_id, $cacheable_dependencies);
                }
                $v_tid = $block->get('field_gated_form')->target_id;
                $video_details = [];
                if($v_tid){
                    $ventity = $this->getBrightcoveVideo($v_tid);
                    if (!empty($ventity)) {
                        $cacheable_dependencies[] = $ventity;
                        $video_details = $ventity->toArray();
                    }
                }
                $block_data['analytics_component'] = '1';
                $block_data['field_gated_form'] = $video_details;
                return $block_data;

            case 'priorities_details_module':
                $block_data = $this->blockObjectToArray($block);
                $term_data = $this->getTermDetails($block_data['field_stat_cta'][0]['target_id'], $cacheable_dependencies);
                if(!empty($term_data)){
                    $term_data_array = $term_data->toArray();
                    $block_data['field_stat_cta']  = $term_data_array;
                }
                if(!empty($block_data['field_stat_image'])){
                    $block_data['field_stat_image']  = $this->getMediaDetails($block_data['field_stat_image'][0]['target_id'], $cacheable_dependencies);
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;

            case 'ah_webform':
                $block_data = ['form_id' => $block->id()];
                return $block_data;
                break;
            case 'wwa_content_blocks': // Who we are like content blocks.
                $block_data = $this->blockObjectToArray($block);
                if($block->get('field_wwa_content_block')){
                    foreach ($block->get('field_wwa_content_block')  as $key => $content_block) {
                        $paragraph = $this->loadParagraph($content_block->target_id);
                        $cacheable_dependencies[] =  $paragraph;
                        $block_data['wwa'][$key]['field_wwa_cb_title'] = $paragraph->get('field_wwa_cb_title')->value;
                        $block_data['wwa'][$key]['field_article_highlights_title'] = $paragraph->get('field_article_highlights_title')->value;
                        $block_data['wwa'][$key]['field_article_highlights_bullets'] = $paragraph->get('field_article_highlights_bullets')->value;
                        $block_data['wwa'][$key]['field_wwa_cb_is_video'] = $paragraph->get('field_wwa_cb_is_video')->value;
                        $block_data['wwa'][$key]['field_wwa_cb_title_color'] = $paragraph->get('field_wwa_cb_title_color')->color;
                        $block_data['wwa'][$key]['field_wwa_cb_video']  = null;
                        if($paragraph->get('field_wwa_cb_video')->target_id){
                          $video_data = $this->getBrightcoveVideo($paragraph->get('field_wwa_cb_video')->target_id);
                          $cacheable_dependencies[] = $video_data;
                          $block_data['wwa'][$key]['field_wwa_cb_video'] = $video_data->get('video_id')->value;
                        }
                        $block_data['wwa'][$key]['field_img'] = $paragraph->get('field_img')->target_id ? $this->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies) :  null;
                        $block_data['wwa'][$key]['field_image'] = $paragraph->get('field_image')->target_id ? $this->getMediaDetails($paragraph->get('field_image')->target_id, $cacheable_dependencies) : null;
                    }
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
                case 'icon_bar_stack': // Icon Bar Stack Component.
                    $block_data = $this->blockObjectToArray($block);
                    if($block->get('field_icon_bar')){
                        foreach ($block->get('field_icon_bar')  as $key => $icon_bar) {
                            $paragraph = $this->loadParagraph($icon_bar->target_id);
                            $cacheable_dependencies[] =  $paragraph;
                            $block_data['ibs'][$key]['field_gif'] = $paragraph->get('field_gif')->target_id ? $this->getMediaDetails($paragraph->get('field_gif')->target_id, $cacheable_dependencies) :  null;
                            $block_data['ibs'][$key]['field_wwa_cb_title'] = $paragraph->get('field_wwa_cb_title')->value;
                            $block_data['ibs'][$key]['field_icon_link'] = $paragraph->get('field_icon_link')->value;
                        }
                        // $block_data['field_img'] = $paragraph->get('field_img')->target_id ? $this->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies) :  null;
                        // $block_data['field_image'] = $paragraph->get('field_image')->target_id ? $this->getMediaDetails($paragraph->get('field_image')->target_id, $cacheable_dependencies) : null;

                        $block_data['field_cta_style'] = $block->get('field_cta_style') ? $this->getTermDetails($block->get('field_cta_style')->target_id, $cacheable_dependencies) : null;
                    }
                    $block_data['analytics_component'] = '1';
                    return $block_data;
                    break;
            case 'case_study_statistics':
                $block_data = $this->blockObjectToArray($block);
                if ($block->get('field_stat_icon')) {
                  $ind = 0;
                  foreach ($block->get('field_stat_icon') as $icon) {
                     $paragraph = $this->loadParagraph($icon->target_id);
                     $block_data['field_stat_icon'][$ind]['icon_description'] = $paragraph->get('field_ico')->value;
                     $block_data['field_stat_icon'][$ind]['icon_image'] = $this->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies);
                     $ind++;
                  }
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'article_highlights':
                $block_data = $this->blockObjectToArray($block);
                if ($block->get('field_article_highlights')) {
                $ind = 0;
                foreach ($block->get('field_article_highlights') as $highlight) {
                  $paragraph = $this->loadParagraph($highlight->target_id);
                  $block_data['field_article_highlights'][$ind]['article_highlight_title'] = $paragraph->get('field_article_highlights_title')->value;
                  $block_data['field_article_highlights'][$ind]['article_highlights_bullets'] = $paragraph->get('field_article_highlights_bullets')->value;;
                  $ind++;
                  }
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'onepager_content':
                  $block_data = $this->blockObjectToArray($block);
                  if ($block->get('field_article_content')) {
                    $ind = 0;
                    foreach ($block->get('field_article_content') as $icon) {
                      $paragraph = $this->loadParagraph($icon->target_id);
                      $block_data['field_article_content'][$ind]['text'] = $paragraph->get('field_content')->value;
                      $block_data['field_article_content'][$ind]['image'] = $this->getMediaDetails($paragraph->get('field_image')->target_id, $cacheable_dependencies);
                      $ind++;
                    }
                  }
                return $block_data;
                break;
            case 'wwa_banner':
                $block_data = $this->blockObjectToArray($block);
                if ($block_data['field_banner_background'][0]['value'] == 'color') {
                    unset($block_data['field_bg_image']);
                    unset($block_data['field_mobile_background_image']);
                }
                else if ($block_data['field_banner_background'][0]['value'] == 'image') {
                    $img_details = $this->getMediaDetails($block->get('field_bg_image')->target_id, $cacheable_dependencies);
                    $block_data['field_background_image'] = $img_details;
                    $img_details = $this->getMediaDetails($block->get('field_mobile_background_image')->target_id, $cacheable_dependencies);
                    $block_data['field_mobile_background_image'] = $img_details;
                    unset($block_data['field_backgroundcolor']);
                    unset($block_data['field_bg_image']);
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'white_paper_gated_download':
                  $block_data = $this->blockObjectToArray($block);
                  if ($block->get('field_upload_white_papers')) {
                    $image_file = \Drupal\file\Entity\File::load($block_data['field_upload_white_papers'][0]['target_id']);
                    $uri = $image_file->uri->value;
                    $block_data['field_upload_white_papers'][0]['target_id'] = file_create_url($uri);
                  }
                return $block_data;
                break;
            case 'more_resource':
                $block_data = $this->blockObjectToArray($block);
                if ($block_data['field_more_resource']) {
                    foreach ($block_data['field_more_resource'] as $key => $item) {
                        $paragraph = $this->loadParagraph($item['target_id']);
                        $field_icon_type = $paragraph->get('field_icon_type')->value;
                        if ($field_icon_type === 'Image') {
                            $block_data['field_more_resource'][$key] = [
                                'icon_description' => $paragraph->get('field_ico')->value,
                                'icon_type' => $field_icon_type,
                                'icon_image' => $this->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies),
                            ];
                        }
                        else if($field_icon_type === 'GIF') {
                            $block_data['field_more_resource'][$key] = [
                                'icon_description' => $paragraph->get('field_ico')->value,
                                'icon_type' => $field_icon_type,
                                'icon_image' => $this->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies),
                                'icon_gif' => $this->getMediaDetails($paragraph->get('field_gif')->target_id, $cacheable_dependencies)
                            ];
                        }
                        if ($paragraph->hasField('field_icon_link')) {
                            $block_data['field_more_resource'][$key]['icon_link'] = $paragraph->get('field_icon_link')->value;
                        }
                    }
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'tab_module':
                $block_data = $this->blockObjectToArray($block);
                if ($block->get('field_tabs')) {
                  $ind = 0;
                  foreach ($block->get('field_tabs') as $tab) {
                    $in = 0;
                    $paragraph = $this->loadParagraph($tab->target_id);
                    $block_data['field_tabs'][$ind]['field_tab_name'] = $paragraph->get('field_tab_name')->value;
                    $block_data['field_tabs'][$ind]['field_tab_icon'] = $this->getMediaDetails($paragraph->get('field_tab_icon')->target_id,$cacheable_dependencies);
                    $block_data['field_tabs'][$ind]['field_tab_gif'] = $this->getMediaDetails($paragraph->get('field_tab_gif')->target_id,$cacheable_dependencies);
                    $block_data['field_tabs'][$ind]['field_tab_title'] = $paragraph->get('field_tab_title')->value;
                    $block_data['field_tabs'][$ind]['field_tab_subtitle'] = $paragraph->get('field_tab_subtitle')->value;
                    $block_data['field_tabs'][$ind]['field_hash_tag'] = $paragraph->get('field_hash_tag')->value;
                    foreach ($paragraph->get('field_custom_block') as $par) {
                      $bl = \Drupal\block_content\Entity\BlockContent::load($par->target_id);
                      $block_data['field_tabs'][$ind]['field_custom_block'][$in]['panel_info']['machine_name'] = $bl->get('type')->target_id;
                      $block_data['field_tabs'][$ind]['field_custom_block'][$in]['data'] = $this->processBlocks($bl,$cacheable_dependencies);
                      $in++;
                    }
                    $ind++;
                  }
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'content_block':
                $block_data = $this->blockObjectToArray($block);
                if ($block->get('field_image')->target_id) {
                    $img_details = $this->getMediaDetails($block->get('field_image')->target_id, $cacheable_dependencies);
                    $block_data['field_image'] = $img_details;
                }
                $cta_details = $this->getTermDetails($block->get('field_cta_style_cb')->target_id, $cacheable_dependencies);
                $block_data['field_cta_style_cb'] = $cta_details;
                $block_data['field_cta_link_cb'] = str_replace('internal:', '', $block_data['field_cta_link_cb']);

                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'contact_us_content':
              $block_data = $this->blockObjectToArray($block);
              //Replace all brightcove video tags with video id tokens
              preg_match_all('/<drupal-entity(.*?)<\/drupal-entity>/s', $block_data['body'][0]['value'], $matches);

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
                  $img = $this->getMediaDetails($v_id->get('mid')->value, $cacheable_dependencies);
                  $replaceWith = '*inline_image='.$img['rel_url'].'*';
                }
                $output = preg_replace($replacePattern, $replaceWith, $block_data['body'][0]['value'], 1);
                $block_data['body'][0]['value'] = $output;
              }
              $block_data['analytics_component'] = '1';
              return $block_data;
              break;
            case 'hero_content_block':
                $block_data = $this->blockObjectToArray($block);
                foreach($block_data['field_hero_content_block'] as $key => $item) {
                    $paragraph = $this->loadParagraph($item['target_id']);
                    $cta_button_uri = explode(':', $paragraph->get('field_block_content_cta_button')->uri);
                    $block_data['field_hero_content_block'][$key] = [
                        'block_background' => $paragraph->get('field_hero_content_block_bg')->color,
                        'copy_horizontal_align' => $paragraph->get('field_content_horizontal_align')->value,
                        'copy_vertical_align' => $paragraph->get('field_content_vertical_align')->value,
                        'title' => $paragraph->get('field_wwa_cb_title')->value,
                        'title_color' => $paragraph->get('field_wwa_cb_title_color')->color,
                        'subtitle' => $paragraph->get('field_subtitle')->value,
                        'subtitle_color' => $paragraph->get('field_subtitle_color')->color,
                        'description' => $paragraph->get('field_content')->value,
                        'image_desktop' => $this->getMediaDetails($paragraph->get('field_image')->target_id, $cacheable_dependencies),
                        'image_mobile' => $this->getMediaDetails($paragraph->get('field_img')->target_id, $cacheable_dependencies),
                        'show_video' => $paragraph->get('field_wwa_cb_is_video')->value,
                        'video' => $this->getBrightcoveVideo($paragraph->get('field_wwa_cb_video')->target_id),
                        'cta_links' => $paragraph->get('field_article_highlights_title')->value,
                        'cta_button_url' => end($cta_button_uri),
                        'cta_button_text' => $paragraph->get('field_block_content_cta_button')->title,
                        'cta_button_style' => '',
                        'cta_brightcove_video' => $this->getBrightcoveVideo($paragraph->get('field_cta_brightcove_video')->target_id)
                    ];
                    if ($block_data['field_hero_content_block'][$key]['video']) {
                        $cacheable_dependencies[] = $block_data['field_hero_content_block'][$key]['video'];
                        $block_data['field_hero_content_block'][$key]['video'] = $block_data['field_hero_content_block'][$key]['video']->toArray();
                    }
                    if ($block_data['field_hero_content_block'][$key]['cta_brightcove_video']) {
                        $cacheable_dependencies[] = $block_data['field_hero_content_block'][$key]['cta_brightcove_video'];
                        $block_data['field_hero_content_block'][$key]['cta_brightcove_video'] = $block_data['field_hero_content_block'][$key]['cta_brightcove_video']->toArray();
                    }
                    $cta_button_style = $this->getTermDetails($paragraph->get('field_block_content_cta_button_s')->target_id, $cacheable_dependencies);
                    if ($cta_button_style) {
                        $block_data['field_hero_content_block'][$key]['cta_button_style'] = [
                            'cta_behaviour' => $cta_button_style->get('field_cta_behaviour')->value,
                            'cta_color' => $cta_button_style->get('field_cta_color')->value,
                            'cta_size' => $cta_button_style->get('field_cta_size')->value,
                        ];
                    }
                }
                $block_data['analytics_component'] = '1';
                return $block_data;
                break;
            case 'pricing_card_grid':
                $block_data = $this->blockObjectToArray($block);
                $cta_details = $this->getTermDetails($block->get('field_pcgcta_style')->target_id, $cacheable_dependencies);
                $block_data['cta']['cta_style'] = $cta_details;
                $block_data['cta']['cta_link'] = str_replace('internal:', '', $block_data['field_c']);
                $block_data['cta']['cta_text'] = $block->get('field_cta_text')->value;
                if($block->get('field_cards')){
                    foreach ($block->get('field_cards')  as $key => $card) {
                        $paragraph = $this->loadParagraph($card->target_id);
                        $cacheable_dependencies[] =  $paragraph;
                        $block_data['field_cards'][$key]['field_title'] = $paragraph->get('field_title')->value;
                        $block_data['field_cards'][$key]['field_title_color'] = $paragraph->get('field_title_color')->color;
                        $block_data['field_cards'][$key]['field_subtitle'] = $paragraph->get('field_subtitle')->value;
                        $block_data['field_cards'][$key]['field_subtitle_color'] = $paragraph->get('field_subtitle_color')->color;
                        $block_data['field_cards'][$key]['field_right_column_copy'] = $paragraph->get('field_right_column_copy')->value;
                        $block_data['field_cards'][$key]['field_center_column_copy']  = $paragraph->get('field_center_column_copy')->value;
                        $block_data['field_cards'][$key]['field_image'] = $paragraph->get('field_pcgimage')->target_id ? $this->getMediaDetails($paragraph->get('field_pcgimage')->target_id, $cacheable_dependencies) : null;
                    }
                }
                $v_tid = $block->get('field_hb_bvideo')->target_id;
                if($v_tid){
                    $ventity = $this->getBrightcoveVideo($v_tid);
                    if (!empty($ventity)) {
                        $cacheable_dependencies[] = $ventity;
                        $video_details = $ventity->toArray();
                    }
                }
                $block_data['field_hb_bvideo'] = $video_details;
                
                $block_data['analytics_component'] = '1';
                $unwanted_indexes = [
                    'field_c',
                    'field_cta_text',
                    'field_pcgcta_style',
                    'status',
                    'info',
                    'metatag'
                ];

                foreach($unwanted_indexes as $index) {
                    unset($block_data[$index]);
                }
                return $block_data;
                break;
            default:
                return $this->blockObjectToArray($block);
                break;
        }

    }

    /**
     * Method to block or component type.
     * @codeCoverageIgnore
     */
    protected function getType($block) {
        return $block->get('type')->getValue()[0]['target_id'];
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getTermDetails($tid = false, &$cacheable_dependencies){
        if($tid){
            $term = Term::load($tid);
            $cacheable_dependencies[] = $term;
            return $term;
        }
        else{
            return null;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getImageDetails($fid, &$cacheable_dependencies){
        $file_storage = \Drupal::entityTypeManager()->getStorage('file');
        if(!$fid || empty($fid)){
            return null;
        }
        $file = $file_storage->load($fid);
        $file_data = [];
        if($file){
            $cacheable_dependencies[] = $file;
            $file_data['rel_url'] = file_url_transform_relative(file_create_url($file->getFileUri()));
        }
        return $file_data;
    }

    /**
     * @codeCoverageIgnore
     */
    function getMediaDetails($media_targetid, &$cacheable_dependencies){
        $data = Media::load($media_targetid);
        if(empty($data)){
            return;
        }
        $cacheable_dependencies[] = $data;
        $media_field = $data->get('image')->first()->getValue();
        $file_data = [];
        $file = file_load($media_field['target_id']);
        if ($file) {
            $file_data['rel_url'] = file_url_transform_relative(file_create_url($file->getFileUri()));
            $file_data['options'] =  $media_field;
            $cacheable_dependencies[] = $file;
        }
        return $file_data;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getTermsDetails($block_data, $fieldname, &$cacheable_dependencies = []){
        $termdata = array();
        if(!empty($block_data) && !empty($fieldname)){
            $tids = $block_data->get($fieldname)->getString();
            $tids = str_replace(' ', '', $tids);
            $tids = explode(',', $tids);
            $termdata = array();
            if($tids){
                foreach($tids as $tid){
                    if(is_numeric($tid)) {
                        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
                        $newterm = ($term) ? $term->toArray() : '';
                        if ($fieldname == 'field_services' || $fieldname == 'field_services_ribbon_items') {
                          $newterm['field_service_link'][0]['uri'] = str_replace('internal:', '', $newterm['field_service_link'][0]['uri']);

                        }
                        if ($fieldname === 'field_priority_link' || $fieldname === 'field_priorities') {
                          $newterm['field_priority_link'][0]['uri'] = str_replace('internal:', '', $newterm['field_priority_link'][0]['uri']);

                        }
                        $termdata[] = $newterm;
                        $cacheable_dependencies[] = $term;
                    }
                }
            }
        }

        return $termdata;
    }

    /**
     * Process data for icon bar module
     *
     * @codeCoverageIgnore
     */
    protected function iconBarModule(array $ribbon_data, &$cacheable_dependencies){

        foreach( $ribbon_data['field_priorities_ribbon_items'] as $key => $value){
            $term_data = $this->getTermDetails($value['target_id'], $cacheable_dependencies);
            if(empty($term_data)){
                continue;
            }
            $file_info =$term_data->get('field_priority_icon')->getValue();
            $file_data = $this->getImageDetails($file_info[0]['target_id'], $cacheable_dependencies);
            $term_data_array = $term_data->toArray();
            $term_data_array['field_priority_link'][0]['uri'] = str_replace('internal:', '', $term_data_array['field_priority_link'][0]['uri']);
            $term_data_array['field_priority_icon'][0] = $file_data;
            $ribbon_data['field_priorities_ribbon_items'][$key]= $term_data_array;
        }
        foreach( $ribbon_data['field_services_ribbon_items'] as $key => $value){
            $term_data = $this->getTermDetails($value['target_id'], $cacheable_dependencies);
            if(empty($term_data)){
                continue;
            }
            $file_info = $term_data->get('field_service_icon')->getValue();
            $file_data = $this->getMediaDetails($file_info[0]['target_id'], $cacheable_dependencies);
            $term_data_array = $term_data->toArray();
            $term_data_array['field_service_link'][0]['uri'] = str_replace('internal:', '', $term_data_array['field_service_link'][0]['uri']);
            $term_data_array['field_service_icon'][0] = $file_data;
            $ribbon_data['field_services_ribbon_items'][$key] = $term_data_array;
        }
        return $ribbon_data;
    }

    /**
     * Method to convert block object to array data & remove unwanted keys.
     */
    public function blockObjectToArray($block) {
      if (!empty($block)) {
        $block_data = $block->toArray();
        $unwanted_indexes = [
            'id',
            'uuid',
            'revision_id',
            'langcode',
            'revision_created',
            'revision_user',
            'revision_log',
            'changed',
            'default_langcode',
            'revision_default',
            'revision_translation_affected',
            'moderation_state',
            'scheduled_publication',
            'scheduled_moderation_state',
            'scheduled_transition_date',
            'scheduled_transition_state',
            'reusable'
        ];

        foreach($unwanted_indexes as $index) {
            unset($block_data[$index]);
        }

        return $block_data;
      }
      return [];
    }

    /**
     * Method to get brightcove video entity.
     *
     * @codeCoverageIgnore
     */
    public function getBrightcoveVideo($vid) {
        return \Drupal::entityTypeManager()->getStorage('brightcove_video')->load($vid);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getConfig($parameter = NULL) {
        $ret = \Drupal::config($parameter);
        return $ret;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadParagraph($pid) {
        return Paragraph::load($pid);
    }
}
