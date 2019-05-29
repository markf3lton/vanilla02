<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\StringTranslation\TranslatableMarkup;


/**
* Tests the custom Filter IPE Blocks service.
*
* @group AHD
*/
class FilterIpeBlocksTest extends UnitTestCase {

  /**
  * {@inheritdoc}
  */
  protected function setUp() {
    $this->filterIpe = $this->getMockBuilder('Drupal\ah_custom_ipe\FilterIpeBlocks')
    ->disableOriginalConstructor()
    ->setMethods()
    ->getMock();

    $this->filterIpe->entityRepository = $this->getMockBuilder('Drupal\Core\Entity\EntityRepository')
    ->disableOriginalConstructor()
    ->setMethods(['loadEntityByUuid'])
    ->getMock();

    $this->panels_display = $this->getMockBuilder('Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant')
    ->disableOriginalConstructor()
    ->setMethods(['getContexts'])
    ->getMock();
  }

  /**
   * Test filter on empty list of blocks.
   */
  public function testEmptyBlocks() {
    $blocks = [];
    $this->filterIpe->filterReusable($blocks, $this->panels_display);
    $this->assertEmpty($blocks);
  }

  /**
   * Test filter on non-empty list of blocks.
   */
  public function testBlocksFilterWithoutComponentType() {
    $blocks = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid',
        'plugin_id' => 'plugin:block_content_uuid',
      ],
      [
        'provider' => 'ctools_block',
        'uuid' => 'ctools_block_uuid',
        'plugin_id' => 'plugin:ctools_block_uuid',
      ],
      [
        'provider' => 'xyz',
        'uuid' => 'xyz_uuid',
        'plugin_id' => 'plugin:xyz_uuid',
      ],
    ];
    $block_content = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['hasField'])
    ->getMock();
    $block_content->expects($this->once())
    ->method('hasField')
    ->willReturn(FALSE);
    $this->filterIpe->entityRepository->expects($this->once())
    ->method('loadEntityByUuid')
    ->willReturn($block_content);
    $blocks = $this->filterIpe->filterReusable($blocks, $this->panels_display);
    $expected = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid',
        'plugin_id' => 'plugin:block_content_uuid',
      ],
    ];
    $this->assertArrayEquals($expected, $blocks);
  }

  /**
   * Test filter on non-empty list of blocks.
   */
  public function testBlocksFilterWithComponentType() {
    $blocks = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_1',
        'plugin_id' => 'plugin:block_content_uuid_1',
      ],
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_2',
        'plugin_id' => 'plugin:block_content_uuid_2',
      ],
      [
        'provider' => 'ctools_block',
        'uuid' => 'ctools_block_uuid',
        'plugin_id' => 'plugin:ctools_block_uuid',
      ],
      [
        'provider' => 'xyz',
        'uuid' => 'xyz_uuid',
        'plugin_id' => 'plugin:xyz_uuid',
      ],
    ];
    $block_content = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['hasField', '__set', '__get'])
    ->getMock();
    $block_content->expects($this->exactly(2))
    ->method('hasField')
    ->willReturn(TRUE);
    $component_type_one = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
    ->disableOriginalConstructor()
    ->setMethods(['first'])
    ->getMock();
    $component_type_one->expects($this->any())
    ->method('first')
    ->willReturn((object) ['value' => '0']);
    $component_type_two = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
    ->disableOriginalConstructor()
    ->setMethods(['first'])
    ->getMock();
    $component_type_two->expects($this->any())
    ->method('first')
    ->willReturn((object) ['value' => '1']);
    $block_content->expects($this->any())
    ->method('__set')
    ->will($this->onConsecutiveCalls(
      $component_type_one,
      $component_type_two
    ));
    $block_content->expects($this->any())
    ->method('__get')
    ->will($this->onConsecutiveCalls(
      $component_type_one,
      $component_type_one,
      $component_type_two,
      $component_type_two
    ));
    $this->filterIpe->entityRepository->expects($this->exactly(2))
    ->method('loadEntityByUuid')
    ->willReturn($block_content);
    $blocks = $this->filterIpe->filterReusable($blocks, $this->panels_display);
    $expected = [
      1 => [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_2',
        'plugin_id' => 'plugin:block_content_uuid_2',
      ],
    ];
    $this->assertArrayEquals($expected, $blocks);
  }

  /**
   * Test filter on non-empty list of blocks with session current component.
   */
  public function testBlocksFilterWithComponentTypeSession() {
    $blocks = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_1',
        'plugin_id' => 'plugin:block_content_uuid_1',
      ],
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_2',
        'plugin_id' => 'plugin:block_content_uuid_2',
      ],
      [
        'provider' => 'ctools_block',
        'uuid' => 'ctools_block_uuid',
        'plugin_id' => 'plugin:ctools_block_uuid',
      ],
      [
        'provider' => 'xyz',
        'uuid' => 'xyz_uuid',
        'plugin_id' => 'plugin:xyz_uuid',
      ],
    ];
    $block_content = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['hasField', '__set', '__get'])
    ->getMock();
    $block_content->expects($this->exactly(2))
    ->method('hasField')
    ->willReturn(TRUE);
    $component_type_one = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
    ->disableOriginalConstructor()
    ->setMethods(['first'])
    ->getMock();
    $component_type_one->expects($this->any())
    ->method('first')
    ->willReturn((object) ['value' => '0']);
    $component_type_two = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
    ->disableOriginalConstructor()
    ->setMethods(['first'])
    ->getMock();
    $component_type_two->expects($this->any())
    ->method('first')
    ->willReturn((object) ['value' => '1']);
    $block_content->expects($this->any())
    ->method('__set')
    ->will($this->onConsecutiveCalls(
      $component_type_one,
      $component_type_two
    ));
    $block_content->expects($this->any())
    ->method('__get')
    ->will($this->onConsecutiveCalls(
      $component_type_one,
      $component_type_one,
      $component_type_two,
      $component_type_two
    ));
    $this->filterIpe->entityRepository->expects($this->exactly(2))
    ->method('loadEntityByUuid')
    ->willReturn($block_content);
    $_SESSION['current_component'] = [
      'uuid' => 'block_content_uuid_1'
    ];
    $blocks = $this->filterIpe->filterReusable($blocks, $this->panels_display);
    $expected = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_1',
        'plugin_id' => 'plugin:block_content_uuid_1',
        'category' => new TranslatableMarkup('Place New Component')
      ],
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_2',
        'plugin_id' => 'plugin:block_content_uuid_2',
      ],
    ];
    $this->assertArrayEquals($expected, $blocks);
    $this->assertFalse(isset($_SESSION['current_component']));
  }

  /**
   * Test filter on non-empty list of blocks with Fields for hybrid page.
   */
  public function testBlocksFilterWithComponentTypeAndFields() {
    $blocks = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_1',
        'plugin_id' => 'plugin:block_content_uuid_1',
      ],
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_2',
        'plugin_id' => 'plugin:block_content_uuid_2',
      ],
      [
        'provider' => 'ctools_block',
        'uuid' => 'ctools_block_uuid',
        'plugin_id' => 'plugin:ctools_block_uuid',
      ],
      [
        'provider' => 'xyz',
        'uuid' => 'xyz_uuid',
        'plugin_id' => 'plugin:xyz_uuid',
      ],
    ];
    $block_content = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['hasField', '__set', '__get'])
    ->getMock();
    $block_content->expects($this->exactly(2))
    ->method('hasField')
    ->willReturn(TRUE);
    $component_type_one = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
    ->disableOriginalConstructor()
    ->setMethods(['first'])
    ->getMock();
    $component_type_one->expects($this->any())
    ->method('first')
    ->willReturn((object) ['value' => '0']);
    $component_type_two = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
    ->disableOriginalConstructor()
    ->setMethods(['first'])
    ->getMock();
    $component_type_two->expects($this->any())
    ->method('first')
    ->willReturn((object) ['value' => '1']);
    $block_content->expects($this->any())
    ->method('__set')
    ->will($this->onConsecutiveCalls(
      $component_type_one,
      $component_type_two
    ));
    $block_content->expects($this->any())
    ->method('__get')
    ->will($this->onConsecutiveCalls(
      $component_type_one,
      $component_type_one,
      $component_type_two,
      $component_type_two
    ));
    $this->filterIpe->entityRepository->expects($this->exactly(2))
    ->method('loadEntityByUuid')
    ->willReturn($block_content);
    $_SESSION['current_component'] = [
      'uuid' => 'block_content_uuid_1'
    ];
    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['bundle'])
    ->getMock();
    $node->expects($this->once())
    ->method('bundle')
    ->willReturn('blog');
    $context_data = $this->getMockBuilder('Drupal\Core\TypedData\TypedData')
    ->disableOriginalConstructor()
    ->setMethods(['getValue'])
    ->getMock();
    $context_data->expects($this->exactly(2))
    ->method('getValue')
    ->willReturn($node);
    $contexts = $this->getMockBuilder('Drupal\ctools\Context\AutomaticContext')
    ->disableOriginalConstructor()
    ->setMethods(['getContextData'])
    ->getMock();
    $contexts->expects($this->exactly(2))
    ->method('getContextData')
    ->willReturn($context_data);
    $this->panels_display->expects($this->exactly(2))
    ->method('getContexts')
    ->willReturn([$contexts]);
    $blocks = $this->filterIpe->filterReusable($blocks, $this->panels_display);
    $expected = [
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_1',
        'plugin_id' => 'plugin:block_content_uuid_1',
        'category' => new TranslatableMarkup('Place New Component')
      ],
      [
        'provider' => 'block_content',
        'uuid' => 'block_content_uuid_2',
        'plugin_id' => 'plugin:block_content_uuid_2',
      ],
      [
        'provider' => 'ctools_block',
        'uuid' => 'ctools_block_uuid',
        'plugin_id' => 'plugin:ctools_block_uuid',
      ],
    ];
    $this->assertArrayEquals($expected, $blocks);
    $this->assertFalse(isset($_SESSION['current_component']));
  }

}
