<?php

namespace Drupal\ah_forms_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "double_field"
 * )
 */
class DoubleField extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['delimiter'])) {
      throw new MigrateException('delimiter is empty');
    }

    $sources = $this->configuration['source'];
    if (!is_array($sources) && count($sources) != 2) {
      throw new MigrateException('Exactly two sources needed.');
    }
    $sources_value = [];
    $delimiter = $this->configuration['delimiter'];
    foreach ($sources as $index => $property) {
      $sources_value[] = explode($this->configuration['delimiter'], $row->getSourceProperty($property));
    }
    $return = [];
    foreach ($sources_value[0] as $index => $value) {
      $return[] = [
        'first' => $sources_value[0][$index],
        'second' => $sources_value[1][$index],
      ];
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
