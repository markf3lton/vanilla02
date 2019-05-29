<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests sub menu tree generation.
 *
 * @group AHD
 */
class GenerateSubMenuTreeTest extends UnitTestCase {

/**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->ahcomApiResource = $this->getMockBuilder('Drupal\ah_services\Plugin\rest\resource\AhcomApiResource')
      ->disableOriginalConstructor()
      ->setMethods(['getMenuFields'])
      ->getMock();
  }

  /**
   * Test empty menu behaviour.
   */
  public function testEmptyMenuInput() {
    $output = $input = [];
    $this->ahcomApiResource->generateSubMenuTree($output, $input, FALSE);
    $this->assertEmpty($output);
  }

  /**
   * Test one disabled menu behaviour.
   */
  public function testOneDisabledMenu() {
    $output = [];
    $input[0] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
      ->disableOriginalConstructor()
      ->setMethods()
      ->getMock();

    $input[0]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
      ->disableOriginalConstructor()
      ->setMethods(['isEnabled'])
      ->getMock();

    $input[0]->link->expects($this->once())
      ->method('isEnabled')
      ->willReturn(false);

    $this->ahcomApiResource->generateSubMenuTree($output, $input, FALSE);
    $this->assertEmpty($output);
  }

  /**
   * Test one menu without any childs.
   */
  public function testOneEnabledMenuWithoutChild() {
    $output = [];
    $input[0] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
      ->disableOriginalConstructor()
      ->setMethods()
      ->getMock();

    $input[0]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
      ->disableOriginalConstructor()
      ->setMethods([
        'isEnabled',
        'getTitle',
        'getDescription',
        'getUrlObject',
        'getMetadata'
      ])
      ->getMock();

    $input[0]->link->expects($this->once())
      ->method('isEnabled')
      ->willReturn(true);

    $input[0]->link->expects($this->once())
      ->method('getTitle')
      ->willReturn('Why choose us');

    $generated_url = $this->getMockBuilder('Drupal\Core\GeneratedUrl')
      ->disableOriginalConstructor()
      ->setMethods(['getGeneratedUrl'])
      ->getMock();

    $generated_url->expects($this->once())
      ->method('getGeneratedUrl')
      ->willReturn('/intmvp-why-choose-us');

    $url = $this->getMockBuilder('Drupal\Core\Url')
      ->disableOriginalConstructor()
      ->setMethods(['toString'])
      ->getMock();

    $url->expects($this->once())
      ->method('toString')
      ->willReturn($generated_url);

    $input[0]->link->expects($this->once())
      ->method('getUrlObject')
      ->willReturn($url);

    $input[0]->link->expects($this->once())
      ->method('getMetadata')
      ->willReturn(['entity_id' => 1]);

    $this->ahcomApiResource->expects($this->once())
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'menu_unique_name',
        'is_default' => 1,
        'unique_name_footer' => 'menu_unique_footer_name'
      ]);

    $this->ahcomApiResource->generateSubMenuTree($output, $input, FALSE);
    $this->assertNotEmpty($output);
    $expected_output = [
      'menu-0' => [
        'name' => 'Why choose us',
        'tid' => 'menu-0',
        'url_str' => '/intmvp-why-choose-us',
        'description' => '',
        'unique_name' => 'menu_unique_name',
        'unique_name_footer' => 'menu_unique_footer_name',
        'isdefault' => '1',
      ]
    ];
    $this->assertEquals($expected_output, $output);
  }

  /**
   * Test 3 menu without any childs and with first menu disabled.
   */
  public function testThreeMixedMenuWithoutChild() {
    $output = [];
    for($i=0; $i<3; $i++) {
      $input[$i] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
        ->disableOriginalConstructor()
        ->setMethods()
        ->getMock();

      $input[$i]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
        ->disableOriginalConstructor()
        ->setMethods([
          'isEnabled',
          'getTitle',
          'getDescription',
          'getUrlObject',
          'getMetadata'
        ])
        ->getMock();

      $input[$i]->link->expects($this->once())
        ->method('isEnabled')
        ->willReturn($i % 3);

      if ($i % 3) {
        $input[$i]->link->expects($this->once())
          ->method('getTitle')
          ->willReturn('Menu title ' . $i);

        $generated_url = $this->getMockBuilder('Drupal\Core\GeneratedUrl')
          ->disableOriginalConstructor()
          ->setMethods(['getGeneratedUrl'])
          ->getMock();

        $generated_url->expects($this->once())
          ->method('getGeneratedUrl')
          ->willReturn('/generated-url-' . $i);

        $url = $this->getMockBuilder('Drupal\Core\Url')
          ->disableOriginalConstructor()
          ->setMethods(['toString'])
          ->getMock();

        $url->expects($this->once())
          ->method('toString')
          ->willReturn($generated_url);

        $input[$i]->link->expects($this->once())
          ->method('getUrlObject')
          ->willReturn($url);

        $input[$i]->link->expects($this->once())
          ->method('getMetadata')
          ->willReturn(['entity_id' => $i]);
      }
    }

    $this->ahcomApiResource->expects($this->at(0))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'menu_unique_name_1',
        'is_default' => '1',
        'unique_name_footer' => 'menu_unique_footer_name_1',
      ]);

    $this->ahcomApiResource->expects($this->at(1))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'menu_unique_name_2',
        'is_default' => '0',
        'unique_name_footer' => 'menu_unique_footer_name_2',
      ]);

    $this->ahcomApiResource->generateSubMenuTree($output, $input, FALSE);
    $this->assertNotEmpty($output);
    $expected_output = [
      'menu-1' => [
        'name' => 'Menu title 1',
        'tid' => 'menu-1',
        'url_str' => '/generated-url-1',
        'description' => null,
        'unique_name' => 'menu_unique_name_1',
        'unique_name_footer' => 'menu_unique_footer_name_1',
        'isdefault' => '1',
      ],
      'menu-2' => [
        'name' => 'Menu title 2',
        'tid' => 'menu-2',
        'url_str' => '/generated-url-2',
        'description' => null,
        'unique_name' => 'menu_unique_name_2',
        'unique_name_footer' => 'menu_unique_footer_name_2',
        'isdefault' => '0',
      ]
    ];
    $this->assertEquals($expected_output, $output);
  }

  /**
   * Test one menu with one child.
   */
  public function testOneMenuWithOneChild() {
    $output = [];
    $input[0] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
      ->disableOriginalConstructor()
      ->setMethods()
      ->getMock();

    $subtree[0] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
      ->disableOriginalConstructor()
      ->setMethods()
      ->getMock();

    $subtree[0]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
      ->disableOriginalConstructor()
      ->setMethods([
        'isEnabled',
        'getTitle',
        'getDescription',
        'getUrlObject',
        'getMetadata'
      ])
      ->getMock();

    $subtree[0]->link->expects($this->once())
      ->method('isEnabled')
      ->willReturn(true);

    $subtree[0]->link->expects($this->once())
      ->method('getTitle')
      ->willReturn('Sub menu title');

    $generated_url = $this->getMockBuilder('Drupal\Core\GeneratedUrl')
      ->disableOriginalConstructor()
      ->setMethods(['getGeneratedUrl'])
      ->getMock();

    $generated_url->expects($this->once())
      ->method('getGeneratedUrl')
      ->willReturn('/sub-menu-url');

    $url = $this->getMockBuilder('Drupal\Core\Url')
      ->disableOriginalConstructor()
      ->setMethods(['toString'])
      ->getMock();

    $url->expects($this->once())
      ->method('toString')
      ->willReturn($generated_url);

    $subtree[0]->link->expects($this->once())
      ->method('getUrlObject')
      ->willReturn($url);

    $subtree[0]->link->expects($this->once())
      ->method('getMetadata')
      ->willReturn(['entity_id' => 1]);

    $input[0]->hasChildren = TRUE;
    $input[0]->depth = 1;
    $input[0]->subtree = $subtree;

    $input[0]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
      ->disableOriginalConstructor()
      ->setMethods([
        'isEnabled',
        'getTitle',
        'getDescription',
        'getUrlObject',
        'getMetadata'
      ])
      ->getMock();

    $input[0]->link->expects($this->once())
      ->method('isEnabled')
      ->willReturn(true);

    $input[0]->link->expects($this->once())
      ->method('getTitle')
      ->willReturn('Menu title');

    $generated_url = $this->getMockBuilder('Drupal\Core\GeneratedUrl')
      ->disableOriginalConstructor()
      ->setMethods(['getGeneratedUrl'])
      ->getMock();

    $generated_url->expects($this->once())
      ->method('getGeneratedUrl')
      ->willReturn('/menu-url');

    $url = $this->getMockBuilder('Drupal\Core\Url')
      ->disableOriginalConstructor()
      ->setMethods(['toString'])
      ->getMock();

    $url->expects($this->once())
      ->method('toString')
      ->willReturn($generated_url);

    $input[0]->link->expects($this->once())
      ->method('getUrlObject')
      ->willReturn($url);

    $input[0]->link->expects($this->once())
      ->method('getMetadata')
      ->willReturn(['entity_id' => 1]);

    $this->ahcomApiResource->expects($this->at(0))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'menu_unique_name',
        'is_default' => 1,
        'unique_name_footer' => 'menu_unique_footer_name'
      ]);

    $this->ahcomApiResource->expects($this->at(1))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'sub_menu_unique_name',
        'is_default' => 0,
        'unique_name_footer' => 'sub_menu_unique_footer_name'
      ]);

    $this->ahcomApiResource->generateSubMenuTree($output, $input, FALSE);
    $this->assertNotEmpty($output);
    $expected_output = [
      'menu-0' => [
        'name' => 'Menu title',
        'tid' => 'menu-0',
        'url_str' => '/menu-url',
        'description' => null,
        'unique_name' => 'menu_unique_name',
        'unique_name_footer' => 'menu_unique_footer_name',
        'isdefault' => 1,
        'child' => [
          'menu-0' => [
            'name' => 'Sub menu title',
            'tid' => 'menu-0',
            'url_str' => '/sub-menu-url',
            'description' => null,
            'unique_name' => 'sub_menu_unique_name',
            'unique_name_footer' => 'sub_menu_unique_footer_name',
            'isdefault' => 0,
          ]
        ],
      ]
    ];
    $this->assertEquals($expected_output, $output);
  }

  /**
   * Test 3 menu with childs and with first menu disabled.
   */
  public function testThreeMixedMenuWithChilds() {
    $output = [];
    for($i=0; $i<3; $i++) {
      $input[$i] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
        ->disableOriginalConstructor()
        ->setMethods()
        ->getMock();

      $input[$i]->hasChildren = TRUE;
      $input[$i]->depth = 1;

      $input[$i]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
        ->disableOriginalConstructor()
        ->setMethods([
          'isEnabled',
          'getTitle',
          'getDescription',
          'getUrlObject',
          'getMetadata'
        ])
        ->getMock();

      $input[$i]->link->expects($this->once())
        ->method('isEnabled')
        ->willReturn($i % 3);

      if ($i % 3) {
        $input[$i]->link->expects($this->once())
          ->method('getTitle')
          ->willReturn('Menu title ' . $i);

        $generated_url = $this->getMockBuilder('Drupal\Core\GeneratedUrl')
          ->disableOriginalConstructor()
          ->setMethods(['getGeneratedUrl'])
          ->getMock();

        $generated_url->expects($this->once())
          ->method('getGeneratedUrl')
          ->willReturn('/menu-url-' . $i);

        $url = $this->getMockBuilder('Drupal\Core\Url')
          ->disableOriginalConstructor()
          ->setMethods(['toString'])
          ->getMock();

        $url->expects($this->once())
          ->method('toString')
          ->willReturn($generated_url);

        $input[$i]->link->expects($this->once())
          ->method('getUrlObject')
          ->willReturn($url);

        $input[$i]->link->expects($this->once())
          ->method('getMetadata')
          ->willReturn(['entity_id' => $i]);

        $subtree = [];
        for($j=0; $j<3; $j++) {
          $subtree[$j] = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkTreeElement')
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

          $subtree[$j]->link = $this->getMockBuilder('Drupal\Core\Menu\MenuLinkContent')
            ->disableOriginalConstructor()
            ->setMethods([
              'isEnabled',
              'getTitle',
              'getDescription',
              'getUrlObject',
              'getMetadata'
            ])
            ->getMock();

          $subtree[$j]->link->expects($this->once())
            ->method('isEnabled')
            ->willReturn($j % 3);

          if ($j % 3) {
            $subtree[$j]->link->expects($this->once())
              ->method('getTitle')
              ->willReturn('Sub menu title ' . $j);

            $generated_url = $this->getMockBuilder('Drupal\Core\GeneratedUrl')
              ->disableOriginalConstructor()
              ->setMethods(['getGeneratedUrl'])
              ->getMock();

            $generated_url->expects($this->once())
              ->method('getGeneratedUrl')
              ->willReturn('/sub-menu-url-' . $j);

            $url = $this->getMockBuilder('Drupal\Core\Url')
              ->disableOriginalConstructor()
              ->setMethods(['toString'])
              ->getMock();

            $url->expects($this->once())
              ->method('toString')
              ->willReturn($generated_url);

            $subtree[$j]->link->expects($this->once())
              ->method('getUrlObject')
              ->willReturn($url);

            $subtree[$j]->link->expects($this->once())
              ->method('getMetadata')
              ->willReturn(['entity_id' => $j]);
          }

          $input[$i]->subtree = $subtree;
        }
      }
    }

    $this->ahcomApiResource->expects($this->at(0))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'menu_unique_name_1',
        'is_default' => '1',
        'unique_name_footer' => 'menu_unique_footer_name_1',
      ]);

    $this->ahcomApiResource->expects($this->at(1))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'sub_menu_unique_name_11',
        'is_default' => '0',
        'unique_name_footer' => 'sub_menu_unique_footer_name_11',
      ]);

    $this->ahcomApiResource->expects($this->at(2))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'sub_menu_unique_name_12',
        'is_default' => '0',
        'unique_name_footer' => 'sub_menu_unique_footer_name_12',
      ]);

    $this->ahcomApiResource->expects($this->at(3))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'menu_unique_name_2',
        'is_default' => '0',
        'unique_name_footer' => 'menu_unique_footer_name_2',
      ]);

    $this->ahcomApiResource->expects($this->at(4))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'sub_menu_unique_name_21',
        'is_default' => '0',
        'unique_name_footer' => 'sub_menu_unique_footer_name_21',
      ]);

    $this->ahcomApiResource->expects($this->at(5))
      ->method('getMenuFields')
      ->willReturn((object) [
        'unique_name' => 'sub_menu_unique_name_22',
        'is_default' => '0',
        'unique_name_footer' => 'sub_menu_unique_footer_name_22',
      ]);

    $this->ahcomApiResource->generateSubMenuTree($output, $input, FALSE);
    $this->assertNotEmpty($output);
    $expected_output = [
      'menu-1' => [
        'name' => 'Menu title 1',
        'tid' => 'menu-1',
        'url_str' => '/menu-url-1',
        'description' => null,
        'unique_name' => 'menu_unique_name_1',
        'unique_name_footer' => 'menu_unique_footer_name_1',
        'isdefault' => '1',
        'child' => [
          'menu-1' => [
            'name' => 'Sub menu title 1',
            'tid' => 'menu-1',
            'url_str' => '/sub-menu-url-1',
            'description' => null,
            'unique_name' => 'sub_menu_unique_name_11',
            'unique_name_footer' => 'sub_menu_unique_footer_name_11',
            'isdefault' => '0',
          ],
          'menu-2' => [
            'name' => 'Sub menu title 2',
            'tid' => 'menu-2',
            'url_str' => '/sub-menu-url-2',
            'description' => null,
            'unique_name' => 'sub_menu_unique_name_12',
            'unique_name_footer' => 'sub_menu_unique_footer_name_12',
            'isdefault' => '0',
          ]
        ],
      ],
      'menu-2' => [
        'name' => 'Menu title 2',
        'tid' => 'menu-2',
        'url_str' => '/menu-url-2',
        'description' => null,
        'unique_name' => 'menu_unique_name_2',
        'unique_name_footer' => 'menu_unique_footer_name_2',
        'isdefault' => '0',
        'child' => [
          'menu-1' => [
            'name' => 'Sub menu title 1',
            'tid' => 'menu-1',
            'url_str' => '/sub-menu-url-1',
            'description' => null,
            'unique_name' => 'sub_menu_unique_name_21',
            'unique_name_footer' => 'sub_menu_unique_footer_name_21',
            'isdefault' => '0',
          ],
          'menu-2' => [
            'name' => 'Sub menu title 2',
            'tid' => 'menu-2',
            'url_str' => '/sub-menu-url-2',
            'description' => null,
            'unique_name' => 'sub_menu_unique_name_22',
            'unique_name_footer' => 'sub_menu_unique_footer_name_22',
            'isdefault' => '0',
          ]
        ],
      ]
    ];
    $this->assertEquals($expected_output, $output);
  }
}
