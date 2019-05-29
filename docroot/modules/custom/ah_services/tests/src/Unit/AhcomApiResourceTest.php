<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ah_services\Plugin\rest\resource\AhcomApiResource;


/**
* Tests the REST export view plugin.
*
* @group AHD
*/
class AhcommApiResourceTest extends UnitTestCase {

  /**
  * {@inheritdoc}
  */
  protected function setUp() {
    $this->ahcomApi = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhcomApiResource')
    ->disableOriginalConstructor()
    ->setMethods([
      't',
      'getNode',
      'getValue',
      'replaceToken',
      'getPanelizer',
      'getEntityByUuid',
      'getConfig',
      'getStorageWrapper',
      'termLoad',
      'fileLoad',
      'getModalID',
      'getBlockData',
      'getTermDetails',
      'getTids',
      'getVanityUrl',
      'getRedirectUrl',
      'isPreview',
      'getNodeLatestRevision',
      'getDtmScript'
    ])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('t')
    ->will($this->returnArgument(0));

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods([
      'addCacheDependencies',
      'clearCache'
    ])
    ->getMock();
  }

  /**
  * Test empty url, which should throw HttpException.
  *
  * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
  */
  public function testEmptyRestUrl() {

    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));
    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);
    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $json_response = $this->ahcomApi->get();
    //$this->expectException(\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException::class);
  }

  /**
  * Test non valid landing page.
  */
  public function testEmptyNode() {
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));
    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath', 'getConfig'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn(null);

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn([]);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');
    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);
    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    $json_response = $this->ahcomApi->get('/non-valid-node');
    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
  }

  /**
  * Test non valid node type.
  */
  public function testNonValidNodeType() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath', 'getConfig'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);

    $json_response = $this->ahcomApi->get('/non-valid-node');
    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
  }

  /**
  * Test landing page with no panelizers.
  */
  public function testNodeWithEmptyPanelizer() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->once())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->once())
    ->method('getType')
    ->willReturn('landing_page');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->once())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn(NULL);

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->willReturn([
      [
        'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
      ]
    ]);

    $this->ahcomApi->expects($this->any())
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));



    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****



    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    //print_r($response_data) ;exit;
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertEmpty($response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer with no blocks.
  */
  public function testNodePanelizerWithNoBlocks() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath', 'getConfig'])
    ->getMock();
    //$cache_response
    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->once())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->once())
    ->method('getType')
    ->willReturn('landing_page');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      [
        'panels_display' => [],
      ]
    ]);

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->willReturn([
      [
        'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
      ]
    ]);

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertEmpty($response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer with one blocks.
  */
  public function testNodePanelizerWithOneBlocks() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->once())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->once())
    ->method('getType')
    ->willReturn('landing_page');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      'panels_display' => [
        'blocks' => [
          'uuid' => [
            'id' => 'block_content:block_uuid',
            'label' => 'block_label',
            'provider' => 'block_content',
            'status' => TRUE,
            'uuid' => 'uuid',
            'weight' => 0,
          ],
        ],
      ]
    ]);

    $this->ahcomApi->expects($this->once())
    ->method('getEntityByUuid')
    ->willReturn($this->getMockBuilder('Drupal\Core\Entity\Entity')
    ->disableOriginalConstructor());

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->will($this->onConsecutiveCalls(
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        'value' => 'landing page'
      ],
      [
        ['target_id' => 'panel_machine_name'],
      ]
    ));

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    $ah_services_mock = $this->getMockBuilder('Drupal\ah_services\Services\ReferenceReplace')
    ->disableOriginalConstructor()
    ->setMethods(['processBlocks'])
    ->getMock();

    $ah_services_mock->expects($this->once())
    ->method('processBlocks')
    ->willReturn(['entity_reference_content_array']);

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****

    $this->ahcomApi->ahService = $ah_services_mock;

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $expected_custom_blocks = [
      1 => [
        'data' => ['entity_reference_content_array'],
        'panel_info' => [
          'machine_name' => 'panel_machine_name',
        ],
      ],
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertArrayEquals($expected_custom_blocks, $response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer with multiple blocks.
  */
  public function testNodePanelizerWithMultipleBlocks() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->once())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->once())
    ->method('getType')
    ->willReturn('landing_page');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      'panels_display' => [
        'blocks' => [
          'uuid_1' => [
            'id' => 'block_content:block_uuid_1',
            'label' => 'block_label_1',
            'provider' => 'block_content',
            'status' => TRUE,
            'uuid' => 'uuid_1',
            'weight' => 0,
          ],
          'uuid_2' => [
            'id' => 'block_content:block_uuid_2',
            'label' => 'block_label_2',
            'provider' => 'block_content',
            'status' => TRUE,
            'uuid' => 'uuid_2',
            'weight' => 2,
          ],
          'uuid_3' => [
            'id' => 'block_content:block_uuid_3',
            'label' => 'block_label_3',
            'provider' => 'block_content',
            'status' => TRUE,
            'uuid' => 'uuid_3',
            'weight' => 1,
          ],
        ],
      ]
    ]);

    $this->ahcomApi->expects($this->exactly(3))
    ->method('getEntityByUuid')
    ->willReturn($this->getMockBuilder('Drupal\Core\Entity\Entity')
    ->disableOriginalConstructor());

    $this->ahcomApi->expects($this->exactly(6))
    ->method('getValue')
    ->will($this->onConsecutiveCalls(
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        ['value' => 'landing page']
      ],
      [
        ['target_id' => 'panel_machine_name_1'],
      ],
      [
        ['target_id' => 'panel_machine_name_3'],
      ],
      [
        ['target_id' => 'panel_machine_name_2'],
      ]
    ));

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    $ah_services_mock = $this->getMockBuilder('Drupal\ah_services\Services\ReferenceReplace')
    ->disableOriginalConstructor()
    ->setMethods(['processBlocks'])
    ->getMock();

    $ah_services_mock->expects($this->exactly(3))
    ->method('processBlocks')
    ->willReturn(['entity_reference_content_array']);

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****


    $this->ahcomApi->ahService = $ah_services_mock;

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $expected_custom_blocks = [
      1 => [
        'data' => ['entity_reference_content_array'],
        'panel_info' => [
          'machine_name' => 'panel_machine_name_1',
        ],
      ],
      2 => [
        'data' => ['entity_reference_content_array'],
        'panel_info' => [
          'machine_name' => 'panel_machine_name_3',
        ],
      ],
      3 => [
        'data' => ['entity_reference_content_array'],
        'panel_info' => [
          'machine_name' => 'panel_machine_name_2',
        ],
      ],
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertArrayEquals($expected_custom_blocks, $response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer with one field.
  */
  public function testNodePanelizerWithOneField() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->any())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->once())
    ->method('getType')
    ->willReturn('landing_page');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      'panels_display' => [
        'blocks' => [
          'uuid' => [
            'id' => 'entity_field:node:field_name',
            'label' => 'Field Name',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid',
            'weight' => 0,
          ],
        ],
      ]
    ]);

    $this->ahcomApi->nodeField = $this->getMockBuilder('Drupal\ah_services\Services\NodeField')
    ->disableOriginalConstructor()
    ->setMethods(['getFieldValue'])
    ->getMock();

    $this->ahcomApi->nodeField->expects($this->once())
    ->method('getFieldValue')
    ->willReturn([
      'key' => 'field_name',
      'value' => 'field_value',
      'ac' => 1
    ]);

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->will($this->onConsecutiveCalls(
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        'value' => 'landing page'
      ]
    ));

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $expected_custom_blocks = [
      1 => [
        'data' => [
          'field_name' => 'field_value',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertArrayEquals($expected_custom_blocks, $response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer with multiple fields.
  */
  public function testNodePanelizerWithMultipleFields() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->any())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->once())
    ->method('getType')
    ->willReturn('landing_page');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      'panels_display' => [
        'blocks' => [
          'uuid_1' => [
            'id' => 'entity_field:node:field_name_1',
            'label' => 'Field Name 1',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_1',
            'weight' => 0,
          ],
          'uuid_2' => [
            'id' => 'entity_field:node:field_name_2',
            'label' => 'Field Name 2',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_2',
            'weight' => 0,
          ],
          'uuid_3' => [
            'id' => 'entity_field:node:field_name_3',
            'label' => 'Field Name 3',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_3',
            'weight' => 0,
          ],
        ],
      ]
    ]);

    $this->ahcomApi->nodeField = $this->getMockBuilder('Drupal\ah_services\Services\NodeField')
    ->disableOriginalConstructor()
    ->setMethods(['getFieldValue'])
    ->getMock();

    $this->ahcomApi->nodeField->expects($this->exactly(3))
    ->method('getFieldValue')
    ->will($this->onConsecutiveCalls(
      [
        'key' => 'field_name_1',
        'value' => 'field_value_1',
        'ac' => 1
      ],
      [
        'key' => 'field_name_2',
        'value' => 'field_value_2',
        'ac' => 1
      ],
      [
        'key' => 'field_name_3',
        'value' => 'field_value_3',
        'ac' => 1
      ]
    ));

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->will($this->onConsecutiveCalls(
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        'value' => 'landing page'
      ]
    ));

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $expected_custom_blocks = [
      1 => [
        'data' => [
          'field_name_1' => 'field_value_1',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
      2 => [
        'data' => [
          'field_name_2' => 'field_value_2',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
      3 => [
        'data' => [
          'field_name_3' => 'field_value_3',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertArrayEquals($expected_custom_blocks, $response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer exclude page image for case study landers.
  */
  public function testNodePanelizerExcludeImage() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->any())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->any())
    ->method('getType')
    ->willReturn('blog');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      'panels_display' => [
        'blocks' => [
          'uuid_1' => [
            'id' => 'entity_field:node:field_list_image',
            'label' => 'Field Name 1',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_1',
            'weight' => 0,
          ],
          'uuid_2' => [
            'id' => 'entity_field:node:field_name_2',
            'label' => 'Field Name 2',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_2',
            'weight' => 0,
          ],
          'uuid_3' => [
            'id' => 'entity_field:node:field_name_3',
            'label' => 'Field Name 3',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_3',
            'weight' => 0,
          ],
        ],
      ]
    ]);

    $this->ahcomApi->nodeField = $this->getMockBuilder('Drupal\ah_services\Services\NodeField')
    ->disableOriginalConstructor()
    ->setMethods(['getFieldValue'])
    ->getMock();

    $this->ahcomApi->nodeField->expects($this->exactly(2))
    ->method('getFieldValue')
    ->will($this->onConsecutiveCalls(
      [
        'key' => 'field_name_2',
        'value' => 'field_value_2',
        'ac' => 1
      ],
      [
        'key' => 'field_name_3',
        'value' => 'field_value_3',
        'ac' => 1
      ]
    ));

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->will($this->onConsecutiveCalls(
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'Case Study List'
        ]
      ]
    ));

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $expected_custom_blocks = [
      1 => [
        'data' => [
          'field_name_2' => 'field_value_2',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
      2 => [
        'data' => [
          'field_name_3' => 'field_value_3',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertArrayEquals($expected_custom_blocks, $response_data['pagedata']['custom_blocks']);
  }

  /**
  * Test landing page panelizer with multiple fields.
  */
  public function testNodePanelizerBothBlocksAndFields() {
    $this->ahcomApi->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods(['getPathByAlias', 'getAliasByPath'])
    ->getMock();

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getPathByAlias')
    ->willReturn('/node/1');

    $this->ahcomApi->aliasManager->expects($this->once())
    ->method('getAliasByPath')
    ->willReturn('/home');

    $this->ahcomApi->cacheResponse = $this->getMockBuilder('Drupal\ah_services\Services\CacheResponse')
    ->disableOriginalConstructor()
    ->setMethods(['clearCache', 'addCacheDependencies'])
    ->getMock();
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('clearCache')
    ->willReturn(null);
    $this->ahcomApi->cacheResponse->expects($this->once())
    ->method('addCacheDependencies')
    ->willReturn(null);

    $node_mock = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['getType', 'getTitle', 'id', 'hasField'])
    ->getMock();

    $node_mock->expects($this->any())
    ->method('hasField')
    ->willReturn(true);

    $node_mock->expects($this->any())
    ->method('getType')
    ->willReturn('blog');

    $node_mock->expects($this->once())
    ->method('getTitle')
    ->willReturn('Node Title');

    $node_mock->expects($this->any())
    ->method('id')
    ->willReturn(1);

    $this->ahcomApi->expects($this->once())
    ->method('getNode')
    ->willReturn($node_mock);

    $this->ahcomApi->expects($this->once())
    ->method('getPanelizer')
    ->willReturn([
      'panels_display' => [
        'blocks' => [
          'uuid_1' => [
            'id' => 'block_content:block_uuid_1',
            'label' => 'block_label_1',
            'provider' => 'block_content',
            'status' => TRUE,
            'uuid' => 'uuid_1',
            'weight' => 0,
          ],
          'uuid_2' => [
            'id' => 'entity_field:node:field_name_2',
            'label' => 'Field Name 2',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_2',
            'weight' => 1,
          ],
          'uuid_3' => [
            'id' => 'block_content:block_uuid_3',
            'label' => 'block_label_3',
            'provider' => 'block_content',
            'status' => TRUE,
            'uuid' => 'uuid_3',
            'weight' => 2,
          ],
          'uuid_4' => [
            'id' => 'entity_field:node:field_name_4',
            'label' => 'Field Name 4',
            'provider' => 'ctools_block',
            'status' => TRUE,
            'uuid' => 'uuid_4',
            'weight' => 3,
          ],
        ],
      ]
    ]);

    $ah_services_mock = $this->getMockBuilder('Drupal\ah_services\Services\ReferenceReplace')
    ->disableOriginalConstructor()
    ->setMethods(['processBlocks'])
    ->getMock();

    $ah_services_mock->expects($this->exactly(2))
    ->method('processBlocks')
    ->willReturn(['entity_reference_content_array']);

    $this->ahcomApi->ahService = $ah_services_mock;

    $this->ahcomApi->nodeField = $this->getMockBuilder('Drupal\ah_services\Services\NodeField')
    ->disableOriginalConstructor()
    ->setMethods(['getFieldValue'])
    ->getMock();

    $this->ahcomApi->nodeField->expects($this->exactly(2))
    ->method('getFieldValue')
    ->will($this->onConsecutiveCalls(
      [
        'key' => 'field_name_2',
        'value' => 'field_value_2',
        'ac' => 1
      ],
      [
        'key' => 'field_name_4',
        'value' => 'field_value_4',
        'ac' => 1
      ]
    ));

    $this->ahcomApi->expects($this->exactly(2))
    ->method('getEntityByUuid')
    ->willReturn($this->getMockBuilder('Drupal\Core\Entity\Entity')
    ->disableOriginalConstructor());

    $this->ahcomApi->expects($this->any())
    ->method('getValue')
    ->will($this->onConsecutiveCalls(
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
        ],
      ],
      [
        [
          'value' => 'Case Study List'
        ]
        ],
      [
        ['target_id' => 'panel_machine_name_1'],
      ],
      [
        ['target_id' => 'panel_machine_name_3']
      ]
    ));

    $this->ahcomApi->expects($this->exactly(4))
    ->method('replaceToken')
    ->will($this->onConsecutiveCalls(
      'This is a landing page',
      'Landing page meta description',
      'About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>',
      'test test'
    ));

    // Global Mocks  ****start****
    $this->ahcomApi->menuTree = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTree')
    ->disableOriginalConstructor()
    ->setMethods(['load', 'transform'])
    ->getMock();
    $termMock = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['load'])
    ->getMock();
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('transform')
    ->willReturn([]);
    $this->ahcomApi->menuTree->expects($this->any())
    ->method('load')
    ->willReturn([]);
    $entityStorageMock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityNullStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadByProperties'])
    ->getMock();

    $entityStorageMock->expects($this->any())
    ->method('loadByProperties')
    ->willReturn([]);
    $termStorageMock = $this->getMockBuilder('Drupal\taxonomy\TermStorage')
    ->disableOriginalConstructor()
    ->setMethods(['loadTree'])
    ->getMock();
    $termStorageMock->expects($this->any())
    ->method('loadTree')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getStorageWrapper')
    ->will($this->onConsecutiveCalls(
      $entityStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
      ,
      $termStorageMock
    ));

    $this->ahcomApi->expects($this->any())
    ->method('termLoad')
    ->willReturn([]);


    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      // $args = func_get_args();
      if ($args === 'simple.logo_image') {
        $ret = [1];
      }
      else if('footer_sm_cta_type') {
        $ret = 1;
      }

      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getConfig')
    ->willReturn($configMock);

    $fileMock = $this->getMockBuilder('Drupal\file\Entity\File')
    ->disableOriginalConstructor()
    ->setMethods(['getFileUri'])
    ->getMock();

    $this->ahcomApi->expects($this->any())
    ->method('fileLoad')
    ->willReturn($fileMock);

    $fileMock->expects($this->any())
    ->method('getFileUri')
    ->willReturn('/logo.png');

    $this->ahcomApi->expects($this->any())
    ->method('getModalID')
    ->willReturn([
      '6981' => 71
    ]);
    $blockMock = $this->getMockBuilder('Drupal\block_content\Entity\Block')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $blockMock->expects($this->any())
    ->method('get')
    ->will($this->returnCallback(function ($args) {
      $ret = (object)array();
      if ($args === 'field_backgroundcolor') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      else if('field_main_question') {
        $ret->color = '#fffff';
        $ret->value = 'hi';
      }
      return $ret;
    }));
    $this->ahcomApi->expects($this->any())
    ->method('getTermDetails')
    ->willReturn([]);
    $this->ahcomApi->expects($this->any())
    ->method('getBlockData')
    ->willReturn($blockMock);

    $this->ahcomApi->expects($this->any())
    ->method('getTids')
    ->willReturn([]);
    // Global Mocks   ****end****

    $json_response = $this->ahcomApi->get('/home');

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $json_response);
    $this->assertEquals($json_response->getStatusCode(), 200);
    $response_data = $json_response->getResponseData();
    $this->assertEquals('Node Title', $response_data['pagedata']['title']);
    $expected_meta_info = [
      "title" => "This is a landing page",
      "description" => "Landing page meta description",
      "abstract" => "About | athenahealth |<p><strong>Lorem Ipsum<\/strong> is simply dummy text of the printing and typesetting industry.<\/p>",
      "keywords" => "test test",
      "canonical" => "/home",
      "service_crawl" => 'a:4:{s:5:"title";s:22:"This is a landing page";s:11:"description";s:29:"Landing page meta description";s:8:"abstract";s:43:"[node:title] | [site:name]  |[node:summary]";s:8:"keywords";s:9:"test test";}'
    ];
    $expected_custom_blocks = [
      1 => [
        'data' => ['entity_reference_content_array'],
        'panel_info' => [
          'machine_name' => 'panel_machine_name_1',
        ],
      ],
      2 => [
        'data' => [
          'field_name_2' => 'field_value_2',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
      3 => [
        'data' => ['entity_reference_content_array'],
        'panel_info' => [
          'machine_name' => 'panel_machine_name_3',
        ],
      ],
      4 => [
        'data' => [
          'field_name_4' => 'field_value_4',
          'analytics_component' => 1
        ],
        'panel_info' => [
          'machine_name' => 'fields',
        ],
      ],
    ];
    $this->assertArrayEquals($expected_meta_info, $response_data['pagedata']['meta_info']);
    $this->assertArrayEquals($expected_custom_blocks, $response_data['pagedata']['custom_blocks']);
  }

}
