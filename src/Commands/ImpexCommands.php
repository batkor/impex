<?php

namespace Drupal\impex\Commands;

use Drupal\Component\Utility\NestedArray;
use Drupal\impex\Property\ExportProperty;
use Drupal\impex\Property\ImportProperty;
use Drush\Commands\DrushCommands;

/**
 * A Drush commands file.
 */
class ImpexCommands extends DrushCommands {

  /**
   * Export entities.
   *
   * @param string $format
   *   The serialize type.
   * @param string $entityTypeId
   *   The entity type ID.
   * @param array $options
   *   The command options.
   *
   * @command impex:export
   *
   * @option bundle The entity bundle ID.
   * @option references The mark for export all referenced entities.
   * @option folder The path to directory for export.
   * @option id_list The entity IDs list for export. Use ',' for separate and '-' for range.
   * @option exclude_ids The entity IDs list for exclude from export. Use ',' for separate.
   *
   * @usage drush impex:export json node
   *   Use json serializer for export all nodes.
   */
  public function export(string $format, string $entityTypeId, array $options = [
    'bundle' => NULL,
    'references' => FALSE,
    'folder' => NULL,
    'id_list' => NULL,
    'exclude_ids' => NULL,
  ]) {
    $property = new ExportProperty();
    $property->setEntityTypeId($entityTypeId);
    $property->setFormat($format);
    $property->markReference($options['references']);

    if (!empty($options['folder'])) {
      $property->setFolder($options['folder']);
    }

    if (!empty($options['bundle'])) {
      $property->setCondition('bundle', $options['bundle']);
    }

    if (!empty($options['id_list'])) {
      $parts = explode(',', $options['id_list']);

      $ids = [];

      foreach ($parts as $part) {
        $subParts = explode('-', $part);

        if (count($subParts) < 2) {
          $ids = NestedArray::mergeDeep($ids, $subParts);

          continue;
        }

        $ids = NestedArray::mergeDeep($ids, range($subParts[0], $subParts[1]));
      }

      $property->setCondition('id', $ids);
    }

    if (!empty($options['exclude_ids'])) {
      $ids = explode(',', $options['exclude_ids']);
      $property->setExcludeIds($ids);
    }

    \Drupal::service('impex.export.manager')->exportByProperties($property);
  }

  /**
   * Import entities.
   *
   * @command impex:import
   */
  public function import($format, $options = [
    'override' => FALSE,
    'folder' => NULL,
    'entity_type_id' => NULL,
  ]) {
    $property = new ImportProperty();
    $property->setFormat($format);

    if ($options['override']) {
      $property->markOverride();
    }

    if (!empty($options['folder'])) {
      $property->setFolder($options['folder']);
    }

    if (!empty($options['entity_type_id'])) {
      $property->setEntityTypeId($options['entity_type_id']);
    }

    \Drupal::service('impex.import.manager')->import($property);
  }

}
