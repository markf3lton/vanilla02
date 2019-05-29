<?php
/**
 * @file
 * Contains \Drupal\ah_services\Controller\AhServicesController.
 */
namespace Drupal\ah_services\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Entity\Query\QueryFactory;

class AhServicesController extends ControllerBase {

  public function nodePreview($node) {
    $alias = $this->getAliasByPath('/node/' . $node);
    if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
      switch ($_ENV['AH_SITE_ENVIRONMENT']) {
        case 'dev':
          $node_base_url = 'http://athenahealthnodejsdev.prod.acquia-sites.com';
          break;
        case 'test':
          $node_base_url = 'http://athenahealthnodejsstg.prod.acquia-sites.com';
          break;
        case 'prod':
          $node_base_url = 'http://www.athenahealth.com';
          break;
        }
    }
    else {
       // do something for a non-Acquia-hosted application (like a local dev install).
       $node_base_url = 'http://athenahealthnodejsstg.prod.acquia-sites.com';
    }
    $path = $node_base_url . $alias . '?cache=clear&preview=latest';
    $response = new TrustedRedirectResponse($path);
    return $response->send();
  }

  /**
   * Method to get url alias of page.
   *
   * @codeCoverageIgnore
   */
  protected function getAliasByPath($path) {
    return \Drupal::service('path.alias_manager')->getAliasByPath($path);
  }
}
