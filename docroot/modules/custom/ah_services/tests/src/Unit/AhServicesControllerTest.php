<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;


/**
* Tests the REST export view plugin.
*
* @group AHD
*/
class AhServicesControllerTest extends UnitTestCase {

  /**
  * {@inheritdoc}
  */
  protected function setUp() {
    $this->ahService = $this->getMockBuilder('Drupal\ah_services\Controller\AhServicesController')
    ->disableOriginalConstructor()
    ->setMethods([
        'getAliasByPath'
    ])
    ->getMock();
  }

  public function testNonAcquiaEnvNodePreview() {
    $this->setOutputCallback(function() {});
    $response = $this->ahService->nodePreview(1);
    $target_url = $response->getTargetUrl();
    $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    $this->assertEquals('http://athenahealthnodejsstg.prod.acquia-sites.com?cache=clear&preview=latest', $target_url);
  }

  public function testAcquiaDevEnvNodePreview() {
    $this->setOutputCallback(function() {});
    $_ENV['AH_SITE_ENVIRONMENT'] = 'dev';
    $response = $this->ahService->nodePreview(1);
    $target_url = $response->getTargetUrl();
    $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    $this->assertEquals('http://athenahealthnodejsdev.prod.acquia-sites.com?cache=clear&preview=latest', $target_url);
  }

  public function testAcquiaStgEnvNodePreview() {
    $this->setOutputCallback(function() {});
    $_ENV['AH_SITE_ENVIRONMENT'] = 'test';
    $response = $this->ahService->nodePreview(1);
    $target_url = $response->getTargetUrl();
    $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    $this->assertEquals('http://athenahealthnodejsstg.prod.acquia-sites.com?cache=clear&preview=latest', $target_url);
  }

  public function testAcquiaProdEnvNodePreview() {
    $this->setOutputCallback(function() {});
    $_ENV['AH_SITE_ENVIRONMENT'] = 'prod';
    $response = $this->ahService->nodePreview(1);
    $target_url = $response->getTargetUrl();
    $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    $this->assertEquals('http://www.athenahealth.com?cache=clear&preview=latest', $target_url);
  }

}
