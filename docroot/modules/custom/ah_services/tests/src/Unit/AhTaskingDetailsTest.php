<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ah_services\Plugin\rest\resource\AhTaskingDetails;


/**
* Tests the REST export view plugin.
*
* @group AHD
*/
class AhTaskingDetailsTest extends UnitTestCase {

  /**
  * {@inheritdoc}
  */
  protected function setUp() {
    $this->ahcomApi = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhTaskingDetails')
    ->disableOriginalConstructor()
    ->setMethods([
      'getTidByName',
    ])
    ->getMock();
  }

  /**
   * Test response while empty form id.
   */
  public function testEmptyFormId() {
    $response = $this->ahcomApi->get();
    $this->assertEquals('No Data', $response);
  }

  /**
   * Test response while no term match.
   */
  public function testNoMatch() {
    $response = $this->ahcomApi->get('NoMatch');
    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $response);
    $this->assertEquals($response->getStatusCode(), 200);
    $this->assertEquals(['form' => []], $response->getResponseData());
  }

  /**
   * Test response while term matches.
   */
  public function testMatch() {
    $this->ahcomApi->expects($this->once())
        ->method('getTidByName')
        ->willReturn([
            'form_id' => (object) ['value' => 'Form Id'],
            'form_name' => (object) ['value' => 'Form Name'],
            'field_ah_forms_term_const' => 'Term Const',
        ]);
    $expected = [
        'form' => [
            'form_id' => 'Form Id',
            'form_name' => 'Form Name',
            'field_ah_forms_term_const' => 'Term Const',
        ]
    ];
    $response = $this->ahcomApi->get('Match');
    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $response);
    $this->assertEquals($response->getStatusCode(), 200);
    $this->assertEquals($expected, $response->getResponseData());
  }

}
