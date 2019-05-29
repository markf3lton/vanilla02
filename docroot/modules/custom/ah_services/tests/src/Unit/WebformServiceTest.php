<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ah_services\Services\WebformService;


/**
 * Tests the REST export view plugin.
 *
 * @group AHD
 */
class WebformServiceTest extends UnitTestCase { 
  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->formService = $this->getMockBuilder('Drupal\ah_services\Services\WebformService')
    ->disableOriginalConstructor()
    ->setMethods([
      't',
      'getElements',
      'getFormGlobalConfig',
      'getTermDetails',
      'getFieldAhFormTitle',
      'getFieldAhFormSubheader',
      'getFieldAhFormPostText',
      'getFieldAhFormButtonLabels',
      'getFieldAhConfirmationHeader',
      'getFieldFormsConfirmationText',
      'getFieldAhFormsErrorTitle',
      'getFieldAhFormsTaskDtls',
      'getWebformServiceUrl',
      'getBlockContent',
      'getValueByKey',

    ])
    ->getMock();

    $this->formService->blockContent = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods([
      'getBlockContent',
      'getValueByKey',
      'get',
      'first',
    ])
    ->getMock();

    $this->formService->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods([
      'addCacheDependencies',
    ])
    ->getMock();
  }
  /**
   * Test if the form id is not present.
   */
  function testGetFormArrayEmptyFormId () {
    $actual = $this->formService->getFormArray(false, $this->formService->cacheResponse );
    $expected = [];
    $this->assertEquals($expected,$actual);
  }
  /**
   * Test with integer which not an form entity ID. 
   */
  function testNonEntityId () {
    $this->formService->expects($this->once())
    ->method('getBlockContent')
    ->with(123)
    ->willReturn(null); 
    $actual = $this->formService->getFormArray(123, $this->formService->cacheResponse );
    $expected = [];
    $this->assertEquals($expected,$actual);

  }
  /**
   * Test form with a valid enity ID.
   */
  function testGetFormArray (){

    $block_data = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
      ->disableOriginalConstructor()
      ->getMock();
    $this->formService->expects($this->once())
    ->method('getBlockContent')
    ->with(716)
    ->willReturn($block_data);

    // $this->formService->blockContent->blockGet = $this->formService->blockContent->expects($this->once())
    // ->method('get')
    // ->with('field_webform_template');
    // $this->formService->blockContent->blockGet->expects($this->once())
    // ->method('first')
    // ->with('target_id')
    // ->willReturn(1);

    $this->formService->expects($this->once())
    ->method('getElements')
    ->willReturn(1);

    $this->formService->expects($this->once())
      ->method('getValueByKey')
      ->willReturn((object) ['target_id' => 'form_id']);
    $cacheable_dependencies = [];
    $actual = $this->formService->getFormArray(716, $cacheable_dependencies);
    // $expected = [];
    $this->assertNotEmpty($actual);

  }


}