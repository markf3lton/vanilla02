<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ah_services\Plugin\rest\resource\AhcomApiResource;


/**
* Tests the REST export view plugin.
*
* @group AHD
*/
class DtmScriptTest extends UnitTestCase {

  /**
  * {@inheritdoc}
  */
  protected function setUp() {
    $this->ahcomApi = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhcomApiResource')
    ->disableOriginalConstructor()
    ->setMethods()
    ->getMock();
    $this->ahcomApi->pathMatcher = $this->getMockBuilder('Drupal\Core\Path\PathMatcher')
    ->disableOriginalConstructor()
    ->setMethods(['getFrontPagePath'])
    ->getMock();
    $this->ahcomApi->pathMatcher->expects($this->any())
    ->method('getFrontPagePath')
    ->willReturn('<front>');
  }

  public function testDtmScriptEmptyPattern() {
    $conf = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $conf->expects($this->exactly(2))
    ->method('get')
    ->will($this->returnCallback(function ($arg) {
        if ($arg == 'dtm_dev_url_pages') {
            return '';
        }
        else if ($arg == 'dtm_prod_url') {
            return '//dtm-prod-script.js';
        }
    }));
    $actual = $this->ahcomApi->getDtmScript('/node-one', '/node/1', $conf);
    $this->assertEquals('//dtm-prod-script.js', $actual);
  }

  public function testDtmScriptNonMatchingPattern() {
    $conf = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $conf->expects($this->exactly(2))
    ->method('get')
    ->will($this->returnCallback(function ($arg) {
        if ($arg == 'dtm_dev_url_pages') {
            return '/xyz' . PHP_EOL . '/zyx';
        }
        else if ($arg == 'dtm_prod_url') {
            return '//dtm-prod-script.js';
        }
    }));
    $actual = $this->ahcomApi->getDtmScript('/node-one', '/node/1', $conf);
    $this->assertEquals('//dtm-prod-script.js', $actual);
  }

  public function testDtmScriptMatchingPattern() {
    $conf = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $conf->expects($this->exactly(2))
    ->method('get')
    ->will($this->returnCallback(function ($arg) {
        if ($arg == 'dtm_dev_url_pages') {
            return '/node'. PHP_EOL . '/node-*' . PHP_EOL . '/zyx';
        }
        else if ($arg == 'dtm_dev_url') {
            return '//dtm-dev-script.js';
        }
    }));
    $actual = $this->ahcomApi->getDtmScript('/node-one', '/node/1', $conf);
    $this->assertEquals('//dtm-dev-script.js', $actual);
  }
}
