<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests the REST export view plugin.
 *
 * @group AHD
 */
class ReferenceReplaceTest extends UnitTestCase {

/**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->referenceReplace = $this->getMockBuilder('Drupal\ah_services\Services\ReferenceReplace')
    ->disableOriginalConstructor()
    ->setMethods([
      'getTermDetails',
      'getImageDetails',
      'getMediaDetails',
      'getTermsDetails',
      'iconBarModule',
      'blockObjectToArray',
      'getType',
      'getBrightcoveVideo',
      'getConfig',
      'loadParagraph'
    ])
    ->getMock();
  }

  public function testDefaultBlocks() {
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('default');
    $expected = [
      'type' => [
        'target_id' => 'default'
      ]
    ];
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn($expected);
    $cacheable_dependencies = [];
    $actual = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
  }

  public function testIconBars() {
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('icon_bars_module');
    $expected = [
      'type' => [
        'target_id' => 'icon_bars_module'
      ]
    ];
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn($expected);
    $expected['analytics_component'] = 1;
    $this->referenceReplace->expects($this->once())
    ->method('iconBarModule')
    ->willReturn($expected);
    $cacheable_dependencies = [];
    $actual = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
  }

  public function testPrioritiesListPoints() {
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block->expects($this->exactly(5))
    ->method('get')
    ->willReturn((object) ['target_id' => 1]);
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('priorities_list_points');
    $this->referenceReplace->expects($this->once())
    ->method('getMediaDetails')
    ->willReturn(['image' => 'img.png']);
    $this->referenceReplace->expects($this->once())
    ->method('getTermDetails')
    ->willReturn(['term' => 'term']);
    $video = $this->getMockBuilder('Drupal\brightcove\Entity\BrightcoveVideo')
    ->disableOriginalConstructor()
    ->setMethods(['toArray'])
    ->getMock();
    $video->expects($this->once())
    ->method('toArray')
    ->willReturn(['video' => 'video.mp4']);
    $this->referenceReplace->expects($this->once())
    ->method('getBrightcoveVideo')
    ->willReturn($video);
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn([
      'type' => [
        'target_id' => 'priorities_list_points'
      ]
    ]);
    $expected = [
      'type' => [
        'target_id' => 'priorities_list_points'
      ],
      'pl_image_details' => ['image' => 'img.png'],
      'pl_cta_details' => ['term' => 'term'],
      'pl_video_details' => ['video' => 'video.mp4'],
      'analytics_component' => 1
    ];
    $cacheable_dependencies = [];
    $actual = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $actual);
  }

  public function testGridModule() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('grid_module');
    $this->referenceReplace->expects($this->exactly(2))
    ->method('getTermsDetails')
    ->will($this->onConsecutiveCalls(
      [
        ['field_service_icon' => [['target_id' => 1]]],
        ['field_service_icon' => [['target_id' => 2]]]
      ],
      [
        ['field_priority_icon' => [['target_id' => 1]]],
        ['field_priority_icon' => [['target_id' => 2]]]
      ]
    ));
    $this->referenceReplace->expects($this->exactly(2))
    ->method('getMediaDetails')
    ->willReturn(['media' => 'img.png']);
    $this->referenceReplace->expects($this->exactly(2))
    ->method('getImageDetails')
    ->willReturn(['image' => 'img.png']);
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn([
      'type' => [
        'target_id' => 'grid_module'
      ]
    ]);
    $expected = [
      'type' => [
        'target_id' => 'grid_module'
      ],
      'grid_priorities_details' => [
        0 => [
          'field_priority_icon' => [
            0 => ['target_id' => 1],
            'image_details' => [
              ['image' => 'img.png']
            ]
          ]
        ],
        1 => [
          'field_priority_icon' => [
            0 => ['target_id' => 2],
            'image_details' => [
              ['image' => 'img.png']
            ]
          ]
        ]
      ],
      'grid_services_details' => [
        0 => [
          'field_service_icon' => [
            0 => ['target_id' => 1],
            'image_details' => [
              ['media' => 'img.png']
            ]
          ]
        ],
        1 => [
          'field_service_icon' => [
            0 => ['target_id' => 2],
            'image_details' => [
              ['media' => 'img.png']
            ]
          ]
        ]
      ],
      'analytics_component' => '1'
    ];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $cacheable_dependencies = [];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $block_data);
  }

  public function testHeroBanner() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('hero_banner');
    $this->referenceReplace->expects($this->exactly(2))
    ->method('blockObjectToArray')
    ->will($this->onConsecutiveCalls(
      [
        'type' => [
          'target_id' => 'hero_banner'
        ],
        'field_cta_link' => [['uri' => 'internal:uri_link']]
      ],
      ['video' => 'video.mp4']
    ));
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block->expects($this->exactly(8))
    ->method('get')
    ->will($this->onConsecutiveCalls(
      (object) ['target_id' => 1],
      (object) ['target_id' => 1],
      (object) ['target_id' => 1],
      (object) ['target_id' => 1],
      (object) ['target_id' => 1],
      (object) ['target_id' => 1],
      (object) ['target_id' => 1],
      (object) ['value' => 'cmp_id']
    ));
    $this->referenceReplace->expects($this->exactly(2))
    ->method('getMediaDetails')
    ->willReturn(['media' => 'img.png']);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getTermDetails')
    ->willReturn(['term' => 'term']);

    $this->referenceReplace->expects($this->once())
    ->method('getBrightcoveVideo')
    ->willReturn(['video' => 'video.mp4']);
    $cacheable_dependencies = [];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => [
        'target_id' => 'hero_banner'
      ],
      'field_cta_link' => [
        0 => [
          'uri' => 'uri_link'
        ]
        ],
      'field_background_media' => [
        'media' => 'img.png'
      ],
      'field_background_media_mobile' => [
        'media' => 'img.png'
      ],
      'field_cta_style' => ['term' => 'term'],
      'field_hb_bvideo' => [
        'video' => 'video.mp4'
      ],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testHorizontalModule() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('horizontal_module');
    $this->referenceReplace->expects($this->exactly(2))
    ->method('blockObjectToArray')
    ->will($this->onConsecutiveCalls(
      [
        'type' => [
          'target_id' => 'hero_banner'
        ],
        'field_ctalink' => [['uri' => 'internal:uri_link']]
      ],
      ['video' => 'video.mp4']
    ));
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getTermDetails')
    ->willReturn(['term' => 'term']);
    $this->referenceReplace->expects($this->once())
    ->method('getBrightcoveVideo')
    ->willReturn(['video' => 'video.mp4']);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block->expects($this->exactly(3))
    ->method('get')
    ->willReturn((object) ['target_id' => 1]);
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => [
        'target_id' => 'hero_banner'
      ],
      'field_ctalink' => [['uri' => 'uri_link']],
      'field_ctastyle' => ['term' => 'term'],
      'field_hb_bvideo' => ['video' => 'video.mp4'],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testFsModule() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('fs_module');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn(['type' => 'fs_module']);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $check_list_items = new class {
      public function getString() {
        return '1 ,3,4';
      }
    };
    $block->expects($this->once())
    ->method('get')
    ->willReturn($check_list_items);
    $this->referenceReplace->expects($this->exactly(3))
    ->method('getTermDetails')
    ->willReturn(['term' => 'term']);
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => 'fs_module',
      'field_checklistitems' => [
        ['term' => 'term'],
        ['term' => 'term'],
        ['term' => 'term']
      ],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testFAQ() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('faq');
    $this->referenceReplace->expects($this->exactly(2))
    ->method('blockObjectToArray')
    ->will($this->onConsecutiveCalls(
      [
        'type' => [
          'target_id' => 'faq'
        ],
      ],
      ['video' => 'video.mp4']
    ));
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block->expects($this->exactly(3))
    ->method('get')
    ->willReturn((object) ['target_id' => 1]);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getTermDetails')
    ->willReturn(['term' => 'term']);
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => ['target_id' => 'faq'],
      'field_faqcta_style' => ['term' => 'term'],
      'field_faq_video' => ['video' => 'video.mp4'],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testServicesDemo() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('services_demo');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn(['type' => 'services_demo']);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block->expects($this->exactly(5))
    ->method('get')
    ->willReturn((object) ['target_id' => 1]);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getTermDetails')
    ->willReturn(['term' => 'term']);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getMediaDetails')
    ->willReturn(['media' => 'img.png']);
    $video = $this->getMockBuilder('Drupal\brightcove\Entity\BrightcoveVideo')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $video->expects($this->once())
    ->method('get')
    ->willReturn((object) ['value' => 'video.mp4']);
    $this->referenceReplace->expects($this->once())
    ->method('getBrightcoveVideo')
    ->willReturn($video);
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => 'services_demo',
      'field_media' => ['media' => 'img.png'],
      'field_sd_cta' => ['term' => 'term'],
      'field_cta_brightcove_video' => [['value' => 'video.mp4']],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testAhAiBridge() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('ah_ai_bridge');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn(['type' => 'ah_ai_bridge']);
    $configMock = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $configMock->expects($this->exactly(3))
    ->method('get')
    ->will($this->onConsecutiveCalls(
      'base_api',
      'base_url',
      'files_url'
    ));
    $this->referenceReplace->expects($this->once())
    ->method('getConfig')
    ->willReturn($configMock);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => 'ah_ai_bridge',
      'field_insight_feed_url' => [
        'base_api' => 'base_api',
        'base_url' => 'base_url',
        'files_url' => 'files_url',
      ],
      'analytics_component' => '1',
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testPrioritiesDetails() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('priorities_details_module');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn([
      'type' => 'priorities_details_module',
      'field_stat_cta' => [['target_id' => 1]],
      'field_stat_image' => [['target_id' => 1]],
    ]);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
    ->disableOriginalConstructor()
    ->setMethods(['toArray'])
    ->getMock();
    $term->expects($this->once())
    ->method('toArray')
    ->willReturn(['term' => 'term']);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getTermDetails')
    ->willReturn($term);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getMediaDetails')
    ->willReturn(['media' => 'img.png']);
    $expected = [
      'type' => 'priorities_details_module',
      'field_stat_cta' => ['term' => 'term'],
      'field_stat_image' => ['media' => 'img.png'],
      'analytics_component' => '1',
    ];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $block_data);
  }

  public function testAhWebform() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('ah_webform');
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['id'])
    ->getMock();
    $block->expects($this->once())
    ->method('id')
    ->willReturn(1);
    $cacheable_dependencies = [];
    $expected = ['form_id' => 1];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $block_data);
  }

  public function testCaseStudyStats() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('case_study_statistics');
    $paragraph = $this->getMockBuilder('Drupal\paragraphs\Entity\Paragraph')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $paragraph->expects($this->exactly(2))
    ->method('get')
    ->will($this->onConsecutiveCalls(
      (object) ['value' => 'ico'],
      (object) ['target_id' => 1]
    ));
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $block->expects($this->exactly(2))
    ->method('get')
    ->willReturn([(object) ['target_id' => 1]]);
    $this->referenceReplace->expects($this->once())
    ->method('loadParagraph')
    ->willReturn($paragraph);
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn(['type' => 'case_study_statistics']);
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getMediaDetails')
    ->willReturn(['media' => 'img.png']);
    $cacheable_dependencies = [];
    $expected = [
      'type' => 'case_study_statistics',
      'field_stat_icon' => [[
        'icon_description' => 'ico',
        'icon_image' => ['media' => 'img.png']
        ]],
        'analytics_component' => '1'
    ];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $block_data);
  }

  public function testArticleHighlights() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('article_highlights');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn(['type' => 'article_highlights']);
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $block->expects($this->exactly(2))
    ->method('get')
    ->willReturn([(object) ['target_id' => 1]]);
    $paragraph = $this->getMockBuilder('Drupal\paragraphs\Entity\Paragraph')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $paragraph->expects($this->exactly(2))
    ->method('get')
    ->will($this->onConsecutiveCalls(
      (object) ['value' => 'title'],
      (object) ['value' => 'bullet']
    ));
    $this->referenceReplace->expects($this->once())
    ->method('loadParagraph')
    ->willReturn($paragraph);
    $cacheable_dependencies = [];
    $expected = [
      'type' => 'article_highlights',
      'field_article_highlights' => [[
        'article_highlight_title' => 'title',
        'article_highlights_bullets' => 'bullet'
        ]],
        'analytics_component' => '1'
    ];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $this->assertEquals($expected, $block_data);
  }

  public function testOnePager() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('onepager_content');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn(['type' => 'onepager_content']);
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $block->expects($this->exactly(2))
    ->method('get')
    ->willReturn([(object) ['target_id' => 1]]);
    $paragraph = $this->getMockBuilder('Drupal\paragraphs\Entity\Paragraph')
    ->disableOriginalConstructor()
    ->setMethods(['get'])
    ->getMock();
    $paragraph->expects($this->exactly(2))
    ->method('get')
    ->will($this->onConsecutiveCalls(
      (object) ['value' => 'title'],
      (object) ['target_id' => 1]
    ));
    $this->referenceReplace->expects($this->exactly(1))
    ->method('getMediaDetails')
    ->willReturn(['media' => 'img.png']);
    $this->referenceReplace->expects($this->once())
    ->method('loadParagraph')
    ->willReturn($paragraph);
    $cacheable_dependencies = [];
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => 'onepager_content',
      'field_article_content' => [
        0 => [
          'text' => 'title',
          'image' => ['media' => 'img.png'],
        ],
      ],
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testWwaBannerWithColorBg() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('wwa_banner');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn([
      'type' => 'onepager_content',
      'field_banner_background' => [
        0 => ['value' => 'color'],
      ],
      'field_bg_image' => [],
      'field_mobile_background_image' => [],
      'field_backgroundcolor' => [],
    ]);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => 'onepager_content',
      'field_banner_background' => [
        0 => ['value' => 'color'],
      ],
      'field_backgroundcolor' => [],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

  public function testWwaBannerWithImageBg() {
    $this->referenceReplace->expects($this->once())
    ->method('getType')
    ->willReturn('wwa_banner');
    $this->referenceReplace->expects($this->once())
    ->method('blockObjectToArray')
    ->willReturn([
      'type' => 'onepager_content',
      'field_banner_background' => [
        0 => ['value' => 'image'],
      ],
      'field_bg_image' => [
        0 => ['target_id' => 1]
      ],
      'field_mobile_background_image' => [
        0 => ['target_id' => 1]
      ],
      'field_backgroundcolor' => [],
    ]);
    $this->referenceReplace->expects($this->exactly(2))
    ->method('getImageDetails')
    ->willReturn(['image' => 'img.png']);
    $cacheable_dependencies = [];
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block_data = $this->referenceReplace->processBlocks($block, $cacheable_dependencies);
    $expected = [
      'type' => 'onepager_content',
      'field_banner_background' => [
        0 => ['value' => 'image'],
      ],
      'field_background_image' => ['image' => 'img.png'],
      'field_mobile_background_image' => ['image' => 'img.png'],
      'analytics_component' => '1'
    ];
    $this->assertEquals($expected, $block_data);
  }

}
