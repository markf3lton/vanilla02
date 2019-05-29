<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests sub menu tree generation.
 *
 * @group AHD
 */
class GetTermDetailsTest extends UnitTestCase {

/**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->ahcomApiResource = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhcomApiResource')
      ->disableOriginalConstructor()
      ->setMethods(['getValue', 'getTerm'])
      ->getMock();
  }

  /**
   * Test scenario of empty block.
   */
  public function testEmptyBlockData() {
    $cacheable_dependencies = [];
    $actual = $this->ahcomApiResource->getTermDetails(null, 'field_test', 'vocabulary', $cacheable_dependencies);
    $this->assertEmpty($actual);
    $this->assertEmpty($cacheable_dependencies);
  }

  /**
   * Test scenatio of empty block filed.
   */
  public function testEmptyBlockFieldData() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn(null);
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_test', 'vocabulary', $cacheable_dependencies);
    $this->assertEmpty($actual);
    $this->assertEmpty($cacheable_dependencies);
  }

  /**
   * Test scenario of empty term.
   */
  public function testEmptyTerm() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_test', 'vocabulary', $cacheable_dependencies);
    $this->assertEmpty($actual);
    $this->assertEmpty($cacheable_dependencies);
  }

  /**
   * Test scenario of non empty term.
   */
  public function testNonEmptyTerm() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName'])
      ->getMock();
    $term->expects($this->once())
      ->method('getName')
      ->willReturn('Test term');
    $this->ahcomApiResource->expects($this->once())
    ->method('getTerm')
    ->willReturn($term);
    $expected = [['name' => 'Test term']];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_test', 'vocabulary', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
  }

  /**
   * Test scenario of cta term.
   */
  public function testCtaTerm() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term->expects($this->once())
      ->method('getName')
      ->willReturn('Test term');
    $term->expects($this->exactly(4))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name];
      }));
    $this->ahcomApiResource->expects($this->once())
    ->method('getTerm')
    ->willReturn($term);
    $expected = [[
      'name' => 'Test term',
      'field_cta_behaviour' => 'field_cta_behaviour',
      'field_cta_color' => 'field_cta_color',
      'field_cta_size' => 'field_cta_size',
      'field_cta_type' => 'field_cta_type',
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_test', 'cta', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
  }

  /**
   * Test scenario of multiple cta term.
   */
  public function testMultipleCtaTerm() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn([['target_id' => 1], ['target_id' => 2]]);
    $term_one = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term_one->expects($this->once())
      ->method('getName')
      ->willReturn('Test term one');
    $term_one->expects($this->exactly(4))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name . ' one'];
      }));
    $term_two = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term_two->expects($this->once())
      ->method('getName')
      ->willReturn('Test term two');
    $term_two->expects($this->exactly(4))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name . ' two'];
      }));
    $this->ahcomApiResource->expects($this->at(1))
    ->method('getTerm')
    ->willReturn($term_one);
    $this->ahcomApiResource->expects($this->at(2))
    ->method('getTerm')
    ->willReturn($term_two);
    $expected = [[
      'name' => 'Test term one',
      'field_cta_behaviour' => 'field_cta_behaviour one',
      'field_cta_color' => 'field_cta_color one',
      'field_cta_size' => 'field_cta_size one',
      'field_cta_type' => 'field_cta_type one',
    ],
    [
      'name' => 'Test term two',
      'field_cta_behaviour' => 'field_cta_behaviour two',
      'field_cta_color' => 'field_cta_color two',
      'field_cta_size' => 'field_cta_size two',
      'field_cta_type' => 'field_cta_type two',
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_test', 'cta', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[1]);
  }

  /**
   * Test scenario of segment term.
   */
  public function testSegmentTerm() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term->expects($this->once())
      ->method('getName')
      ->willReturn('Test term');
    $term->expects($this->exactly(2))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name];
      }));
    $this->ahcomApiResource->expects($this->once())
    ->method('getTerm')
    ->willReturn($term);
    $expected = [[
      'name' => 'Test term',
      'field_unique_name' => 'field_unique_name',
      'field_isdefault' => 'field_isdefault',
      'service_url' => [],
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_segment', 'vocabulary', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
  }

  /**
   * Test scenario of segment term with empty modal.
   */
  public function testSegmentTermWithEmptyModal() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->at(0))
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term->expects($this->once())
      ->method('getName')
      ->willReturn('Test term');
    $term->expects($this->exactly(2))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name];
      }));
    $this->ahcomApiResource->expects($this->at(1))
      ->method('getValue')
      ->willReturn([]);
    $this->ahcomApiResource->expects($this->once())
      ->method('getTerm')
      ->willReturn($term);
    $expected = [[
      'name' => 'Test term',
      'field_unique_name' => 'field_unique_name',
      'field_isdefault' => 'field_isdefault',
      'service_url' => [],
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_segment', 'modal_popup', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
  }

  /**
   * Test scenario of segment term with modal.
   */
  public function testSegmentTermWithModal() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->at(0))
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term->expects($this->once())
      ->method('getName')
      ->willReturn('Test term');
    $term->expects($this->exactly(4))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name];
      }));
    $this->ahcomApiResource->expects($this->at(2))
      ->method('getValue')
      ->willReturn([
        [
          'target_id' => 100,
          'value' => '/url/one',
        ]
      ]);
    $this->ahcomApiResource->expects($this->any())
      ->method('getTerm')
      ->willReturn($term);
    $expected = [[
      'name' => 'Test term',
      'field_unique_name' => 'field_unique_name',
      'field_isdefault' => 'field_isdefault',
      'service_url' => [
        'field_uniquename_serv' => '/url/one',
      ],
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_segment', 'modal_popup', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
  }

  /**
   * Test scenario of segment term with modal empty service term.
   */
  public function testSegmentTermWithModalEmptySer() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->at(0))
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term->expects($this->exactly(2))
      ->method('getName')
      ->willReturn('Test term');
    $term->expects($this->exactly(3))
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        if ($field_name == 'field_uniquename_serv') {
            return (object) ['value' => null];
        }
        return (object) ['value' => $field_name];
      }));
    $this->ahcomApiResource->expects($this->at(2))
      ->method('getValue')
      ->willReturn([
        [
          'target_id' => 100,
          'value' => '/url/one',
        ]
      ]);
    $this->ahcomApiResource->expects($this->any())
      ->method('getTerm')
      ->willReturn($term);
    $expected = [[
      'name' => 'Test term',
      'field_unique_name' => 'field_unique_name',
      'field_isdefault' => 'field_isdefault',
      'service_url' => [
        'Test term' => '/url/one',
      ],
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_segment', 'modal_popup', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[1]);
  }

  /**
   * Test scenario of cta term.
   */
  public function testServiceTerm() {
    $cacheable_dependencies = [];
    $block_data = (object) ['block_id' => 1];
    $this->ahcomApiResource->expects($this->once())
      ->method('getValue')
      ->willReturn([['target_id' => 1]]);
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->setMethods(['getName', 'get'])
      ->getMock();
    $term->expects($this->once())
      ->method('getName')
      ->willReturn('Test term');
    $term->expects($this->once())
      ->method('get')
      ->will($this->returnCallback(function ($field_name) {
        return (object) ['value' => $field_name];
      }));
    $this->ahcomApiResource->expects($this->once())
    ->method('getTerm')
    ->willReturn($term);
    $expected = [[
      'name' => 'Test term',
      'field_uniquename_serv' => 'field_uniquename_serv',
    ]];
    $actual = $this->ahcomApiResource->getTermDetails($block_data, 'field_service', 'vocabulary', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
    $this->assertInstanceOf('Drupal\taxonomy\Entity\Term', $cacheable_dependencies[0]);
  }
}
