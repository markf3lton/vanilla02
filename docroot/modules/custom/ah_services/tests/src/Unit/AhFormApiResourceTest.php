<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ah_services\Plugin\rest\resource\AhFormApiResource;

/**
 * Tests the REST export view plugin.
 *
 * @group AHD
 */
class AhFormApiResourceTest extends UnitTestCase {

/**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->ahFormApi = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhFormApiResource')
    ->disableOriginalConstructor()
    ->setMethods([
      't',
      'getNode',
      'getValue',
      'replaceToken',
      'getPanelizer',
      'getEntityByUuid',
    ])
    ->getMock();
    $this->ahFormApi->ahService = $this->getMockBuilder('Drupal\ah_services\Services\WebformService') 
    ->disableOriginalConstructor()
    ->setMethods(['getFormArray'
      ])
    ->getMock();

    $this->ahFormApi->expects($this->any())
      ->method('t')
      ->will($this->returnArgument(0));

    $this->ahFormApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods([
      'addCacheDependencies',
      'clearCache',
    ])
    ->getMock();
  }
  /**
   * Test empty form ID
   */
  public function testEmptyFormId(){
    $json_response = $this->ahFormApi->get();
    $this->assertEquals('No Data',$json_response);
  }

  /**
   * Test empty form ID
   */
  public function testGetMethod(){
    $cache = [];
    $this->ahFormApi->ahService->expects($this->once())
    ->method('getFormArray')
    ->with(123,$cache )
    ->willReturn(['123']);
    $this->ahFormApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->with(['block_content:123'])
    ->willReturn(null);
    $json_response = $this->ahFormApi->get(123);
    $response_data = $json_response->getResponseData();
    $this->assertEquals(['123'], $response_data['form']  );
  }

}
