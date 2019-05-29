<?php

namespace Drupal\Tests\ah_services\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests the REST export view plugin.
 *
 * @group AHD
 */
class BlockObjectToArrayTest extends UnitTestCase {

/**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->referenceReplace = $this->getMockBuilder('Drupal\ah_services\Services\ReferenceReplace')
    ->disableOriginalConstructor()
    ->setMethods()
    ->getMock();
  }

  public function testRemoveUnwantedIndex() {
    $block = $this->getMockBuilder('Drupal\block_content\Entity\BlockContent')
    ->disableOriginalConstructor()
    ->getMock();
    $block->expects($this->once())
    ->method('toArray')
    ->willReturn([
        'id' => 'id',
        'uuid' => 'uuid',
        'revision_id' => 'revision_id',
        'langcode' => 'langcode',
        'revision_created' => 'revision_created',
        'revision_user' => 'revision_user',
        'revision_log' => 'revision_log',
        'changed' => 'changed',
        'default_langcode' => 'default_langcode',
        'revision_default' => 'revision_default',
        'revision_translation_affected' => 'revision_translation_affected',
        'moderation_state' => 'moderation_state',
        'scheduled_publication' => 'scheduled_publication',
        'scheduled_moderation_state' => 'scheduled_moderation_state',
        'wanted_index' => 'wanted_index',
    ]);
    $block_data = $this->referenceReplace->blockObjectToArray($block);
    $this->assertEquals(['wanted_index' => 'wanted_index'], $block_data);
  }

  public function testEmptyBlock() {
    $block_data = $this->referenceReplace->blockObjectToArray(NULL);
    $this->assertEquals([], $block_data);
  }
}
