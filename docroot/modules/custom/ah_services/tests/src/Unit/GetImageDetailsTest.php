<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests sub menu tree generation.
 *
 * @group AHD
 */
class GetImageDetailsTest extends UnitTestCase {

/**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->ahcomApiResource = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhcomApiResource')
      ->disableOriginalConstructor()
      ->setMethods(['getMedia', 'getFile', 'createFileUri', 'fileCreateUrl', 'getValueByKey'])
      ->getMock();
  }

  /**
   * Test scenario with empty media id.
   */
  public function testEmptyMediaId(){
    $cacheDummy = [];
    $actual = $this->ahcomApiResource->getImageData('', $cacheDummy);
    $this->assertEmpty($actual);
  }

  /**
   * Test scenario with empty media.
   */
  public function testEmptyMediaEntity(){
    $cacheDummy = [];
    $this->ahcomApiResource->expects($this->once())->method('getMedia')->willReturn(null);
    $actual = $this->ahcomApiResource->getImageData(32, $cacheDummy);
    $this->assertEmpty($actual);
  }

  /**
   * Test scenario with empty media file.
   */
  public function testEmptyMediaFile(){
    $cacheDummy = [];
    $this->ahcomApiResource->expects($this->once())->method('getMedia')->willReturn(['media' => 32]);
    $this->ahcomApiResource->expects($this->once())->method('getValueByKey')->willReturn(['target_id' => 32]);
    $this->ahcomApiResource->expects($this->once())->method('getFile')->willReturn(null);
    $actual = $this->ahcomApiResource->getImageData(32, $cacheDummy);
    $this->assertEquals(['img_data' => ['target_id'=>32]], $actual);
  }

  /**
   * Test scenario with media and file.
   */
  public function testMediaIdWithFid(){
    $cacheDummy = [];
    $this->ahcomApiResource->expects($this->once())->method('getMedia')->willReturn(['media' => 32]);
    $this->ahcomApiResource->expects($this->once())->method('getValueByKey')->willReturn(['target_id' => 32]);
    $this->ahcomApiResource->expects($this->once())->method('getFile')->willReturn('getfileDetails');
    $this->ahcomApiResource->expects($this->once())->method('createFileUri')->willReturn('file_url_obj');
    $this->ahcomApiResource->expects($this->once())->method('fileCreateUrl')->willReturn('http//www.dummy.com/src/image/ex_img.jpg');
    $actual = $this->ahcomApiResource->getImageData(32, $cacheDummy);
    $expected = ['url'=>'http//www.dummy.com/src/image/ex_img.jpg', 'img_data'=>['target_id'=>32]];
    $this->assertEquals($expected, $actual);
  }

}
