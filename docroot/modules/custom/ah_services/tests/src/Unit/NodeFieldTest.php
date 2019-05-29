<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ah_services\Services\NodeField;


/**
* Tests the REST export view plugin.
*
* @group AHD
*/
class NodeFieldTest extends UnitTestCase {

  /**
  * {@inheritdoc}
  */
  protected function setUp() {
    $this->nodeField = $this->getMockBuilder('Drupal\ah_services\Services\NodeField')
    ->disableOriginalConstructor()
    ->setMethods(['getNodeFieldValue', 'loadEntity', 'getEntityId'])
    ->getMock();

    $this->nodeField->referenceReplace = $this->getMockBuilder('Drupal\ah_services\Services\ReferenceReplace')
    ->disableOriginalConstructor()
    ->setMethods([
      'getMediaDetails'
    ])
    ->getMock();

    $this->nodeField->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManager')
    ->disableOriginalConstructor()
    ->setMethods([
      'getAliasByPath'
    ])
    ->getMock();
  }

  /**
   * Test empty or invalid field name.
   */
  public function testEmptyFieldOrInvalidField() {
    $cacheable_dependencies = [];
    $actual_value = $this->nodeField->getFieldValue('node', '', $cacheable_dependencies);
    $this->assertEmpty($actual_value);
    $actual_value = $this->nodeField->getFieldValue('node', 'invalid', $cacheable_dependencies);
    $this->assertEmpty($actual_value);
  }

  /**
   * Test title and read more text field.
   */
  public function testTitleAndReadMoreField() {
    $cacheable_dependencies = [];
    $this->nodeField->expects($this->exactly(3))
    ->method('getNodeFieldValue')
    ->will($this->onConsecutiveCalls(
        'Title',
        [['value' => '1']],
        'Read more'
    ));
    $expected = [
        [
            'key' => 'field_title',
            'value' => 'Title',
            'ac' => 1
        ],
        [
            'key' => 'field_read_more_text',
            'value' => 'Read more'
        ]
    ];
    foreach(['field_title', 'field_read_more_text'] as $key => $field) {
        $actual_value = $this->nodeField->getFieldValue('node', $field, $cacheable_dependencies);
        $this->assertEquals($expected[$key], $actual_value);
    }
  }

  /**
   * Test subtitle field.
   */
  public function testSubtilteField() {
    $cacheable_dependencies = [];
    $this->nodeField->expects($this->exactly(2))
    ->method('getNodeFieldValue')
    ->will($this->onConsecutiveCalls(
        ['value' => 'Sub Title'],
        [['value' => 1]]
    ));
    $expected = [
        'key' => 'field_subtitle',
        'value' => [
            'value' => 'Sub Title',
            ['divider' => 1],
        ],
        'ac' => 1
    ];
    $actual_value = $this->nodeField->getFieldValue('node', 'field_subtitle', $cacheable_dependencies);
    $this->assertEquals($expected, $actual_value);
  }

  /**
   * Test list image field.
   */
  public function testListImage() {
    $cacheable_dependencies = [];
    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();

    $node->expects($this->exactly(2))
    ->method('get')
    ->willReturn((object)['target_id' => 1]);
    $this->nodeField->referenceReplace->expects($this->once())
    ->method('getMediaDetails')
    ->willReturn(['rel_url' => '/image.jpg']);
    $expected = [
        'key' => 'field_list_image',
        'value' => ['rel_url' => '/image.jpg']
    ];
    $actual_value = $this->nodeField->getFieldValue($node, 'field_list_image', $cacheable_dependencies);
    $this->assertEquals($expected, $actual_value);
  }

  /**
   * Test content list field
   */
  public function testContentListField() {
    $cacheable_dependencies = [];
    $content_mock_one = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $content_mock_one->expects($this->exactly(2))
    ->method('get')
    ->willReturn((object)['target_id' => 1]);

    $content_mock_two = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $content_mock_two->expects($this->exactly(2))
    ->method('get')
    ->willReturn((object)['target_id' => 2]);

    $this->nodeField->referenceReplace->expects($this->any())
    ->method('getMediaDetails')
    ->willReturn('/image.jpg');

    $this->nodeField->expects($this->exactly(12))
    ->method('getNodeFieldValue')
    ->will($this->onConsecutiveCalls(
        [['target_id' => 1], ['target_id' => 2]],
        [['summary' => 'Content 1 Summary']],
        ['value' => 'Title 1'],
        ['value' => 'Sub Title 1'],
        [['value' => 1]],
        [['summary' => '']],
        [['value' => 'Content 2 Body']],
        ['value' => 'Title 2'],
        ['value' => 'Sub Title 2'],
        [['value' => 1]],
        [['value' => 5]],
        [['value' => 5]]
    ));
    $this->nodeField->expects($this->exactly(2))
    ->method('loadEntity')
    ->will($this->onConsecutiveCalls(
        $content_mock_one,
        $content_mock_two
    ));
    $this->nodeField->aliasManager->expects($this->exactly(2))
    ->method('getAliasByPath')
    ->will($this->onConsecutiveCalls(
        '/content-one',
        '/content-two'
    ));
    $actual = $this->nodeField->getFieldValue('node', 'field_content_list', $cacheable_dependencies);
    $expected = [
        'key' => 'field_content_list',
        'value' => [
            [
                'list_image' => '/image.jpg',
                'title' => ['value' => 'Title 1'],
                'sub_title' => ['value' => 'Sub Title 1'],
                'body' => 'Content 1 Summary',
                'link' => '/content-one',
                'read_more' => 1
            ],
            [
                'list_image' => '/image.jpg',
                'title' => ['value' => 'Title 2'],
                'sub_title' => ['value' => 'Sub Title 2'],
                'body' => 'Content 2 Body',
                'link' => '/content-two',
                'read_more' => 1
            ],
            'contents_per_page' => 5,
        ],
        'ac' => '1'
    ];
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test statistics icon title field.
   */
  public function testStatIconTitleField() {$cacheable_dependencies = [];
    $this->nodeField->expects($this->once())
    ->method('getNodeFieldValue')
    ->willReturn('Stat Icon Title');
    $expected = [
        'key' => 'field_stat_icon_title',
        'value' => 'Stat Icon Title'
    ];
    $actual_value = $this->nodeField->getFieldValue('node', 'field_statistic_icons_title', $cacheable_dependencies);
    $this->assertEquals($expected, $actual_value);
  }

  /**
   * Test body field.
   */
  public function testBodyField() {
    $cacheable_dependencies = [];
    $this->nodeField->expects($this->once())
    ->method('getNodeFieldValue')
    ->willReturn([['value' => 'Body']]);
    $expected = [
        'key' => 'article_content',
        'value' => [
            ['value' => 'Body']
        ],
        'ac' => '1'
    ];
    $actual_value = $this->nodeField->getFieldValue('node', 'body', $cacheable_dependencies);
    $this->assertEquals($expected, $actual_value);
  }

  /**
   * Test statistics icon field.
   */
  public function testStatIconField() {
    $cacheable_dependencies = [];

    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $node->expects($this->exactly(2))
    ->method('get')
    ->willReturn([(object)['target_id' => 1], (object)['target_id' => 2]]);

    $paragraph = $this->getMockBuilder('Drupal\paragraphs\Entity\Paragraph')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $paragraph->expects($this->exactly(7))
    ->method('get')
    ->will($this->onConsecutiveCalls(
        (object)['value' => 'Image'],
        (object)['value' => 'Image Description'],
        (object)['target_id' => 11],
        (object)['value' => 'GIF'],
        (object)['value' => 'Gif Description'],
        (object)['target_id' => 12],
        (object)['target_id' => 13]
    ));

    $this->nodeField->expects($this->exactly(2))
    ->method('loadEntity')
    ->willReturn($paragraph);

    $this->nodeField->referenceReplace->expects($this->exactly(3))
    ->method('getMediaDetails')
    ->will($this->onConsecutiveCalls(
        ['rel_url' => '/image.jpg'],
        ['rel_url' => '/image.jpg'],
        ['rel_url' => '/image.gif']
    ));

    $expected = [
        'key' => 'field_statistic_icons',
        'value' => [
            [
                'icon_description' => 'Image Description',
                'icon_type' => 'Image',
                'icon_image' => ['rel_url' => '/image.jpg']
            ],
            [
                'icon_description' => 'Gif Description',
                'icon_type' => 'GIF',
                'icon_image' => ['rel_url' => '/image.jpg'],
                'icon_gif' => ['rel_url' => '/image.gif']
            ]
        ],
        'ac' => 1
    ];

    $actual = $this->nodeField->getFieldValue($node, 'field_statistic_icons', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test highlights field.
   */
  public function testHighlightField() {
    $cacheable_dependencies = [];

    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $node->expects($this->exactly(2))
    ->method('get')
    ->willReturn([(object)['target_id' => 1]]);

    $paragraph = $this->getMockBuilder('Drupal\paragraphs\Entity\Paragraph')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $paragraph->expects($this->exactly(2))
    ->method('get')
    ->will($this->onConsecutiveCalls(
        (object)['value' => 'Highlights Title'],
        (object)['value' => 'Highlights Content']
    ));

    $this->nodeField->expects($this->exactly(1))
    ->method('loadEntity')
    ->willReturn($paragraph);

    $expected = [
        'key' => 'field_highlights',
        'value' => [
            [
                'highlights_title' => 'Highlights Title',
                'highlights_content' => 'Highlights Content'
            ]
        ],
        'ac' => 1
    ];

    $actual = $this->nodeField->getFieldValue($node, 'field_highlights', $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
  }
}
