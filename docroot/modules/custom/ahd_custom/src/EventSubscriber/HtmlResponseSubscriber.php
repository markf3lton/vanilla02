<?php

namespace Drupal\ahd_custom\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Cache\CacheableResponse;

/**
 * Response subscriber to handle HTML responses.
 */
class HtmlResponseSubscriber implements EventSubscriberInterface {


  /**
   * Constructs a HtmlResponseSubscriber object.
   *
   * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $html_response_attachments_processor
   *   The HTML response attachments processor service.
   */
  public function __construct() {
  }

  /**
   * Processes attachments for HtmlResponse responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    $response = $event->getResponse();
    // Rest API reponse: replace line and paragraph separator.
    if ($response instanceof CacheableResponse
     && isset($response->headers)
     && $response->headers->get('content-type') == 'application/json') {
      $res_content = $response->getContent();
      $res_content = str_replace('\u2028', '\u003Cbr \/\u003E', $res_content);
      $res_content = str_replace('\u2029', '\u003Cbr \/\u003E', $res_content);
      // Replace data-color
      $res_content = str_replace('\u003Cspan data-color=\u0022', '<span data-color="', $res_content);
      $res_content = str_replace('\u003Cspan data-background-color=\u0022', '<span data-background-color="', $res_content);
      $res_content = preg_replace('/<span data-((background-)?color)="(#[0-9a-fA-F]{6})/', '\u003Cspan style=\u0022$1:$3;', $res_content);
      $response->setContent($res_content);
      $event->setResponse($response);
    }
    // Normal html response: remove duplicate meta tags.
    else if ($response instanceof HtmlResponse) {
      $attachments = $response->getAttachments();
      $is_canonical_set_in_html_head = FALSE;
      $is_meta_description_set_in_html_head = FALSE;
      if ($attachments) {
        if (isset($attachments['html_head'])) {
          foreach($attachments['html_head'] as $link) {
            // Check canonical rel link is set in html head of attachments.
            if (isset($link[0]['#attributes']['rel'])
            && $link[0]['#attributes']['rel'] == 'canonical') {
              $is_canonical_set_in_html_head = TRUE;
            }
            // Check meta description tag is set in html head of attachments.
            else if (isset($link[0]['#attributes']['name'])
            && $link[0]['#attributes']['name'] == 'description') {
              $is_meta_description_set_in_html_head = TRUE;
            }
          }
        }
        if (isset($attachments['html_head_link']) && $is_canonical_set_in_html_head) {
          foreach ($attachments['html_head_link'] as $key => $html_head_item) {
            // Remove duplicate entity canonical url as we are using metatag module.
            if (isset($html_head_item[0]['rel'])
            && $html_head_item[0]['rel'] == 'canonical') {
              unset($attachments['html_head_link'][$key]);
            }
            // Check meta description tag is set in html head link of attachments.
            else if (isset($html_head_item[0]['name'])
            && $html_head_item[0]['name'] == 'description') {
              $is_meta_description_set_in_html_head = TRUE;
            }
          }
        }
        // Add meta description tag to html head if it is not set.
        if (!$is_meta_description_set_in_html_head) {
          $attachments['html_head'][] = [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => '',
              ],
            ],
            'description',
          ];
        }
      }
      $response = $response->setAttachments($attachments);
      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond', 100];
    return $events;
  }

}
