<?php

namespace Drupal\impex;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\impex\Property\ExportProperty;

/**
 * The manager for export entities.
 */
class ExportManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity exporter.
   *
   * @var \Drupal\impex\Exporter
   */
  protected $exporter;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\impex\Exporter $exporter
   *   The entity exporter.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    Exporter $exporter
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->exporter = $exporter;
  }

  /**
   * Export entities by properties.
   *
   * @param \Drupal\impex\Property\ExportProperty $property
   *   The property object.
   */
  public function exportByProperties(ExportProperty $property) {
    $entityDef = $this->entityTypeManager->getDefinition($property->getEntityTypeId());
    $storage = $this->entityTypeManager->getStorage($property->getEntityTypeId());
    $query = $storage->getQuery();
    $entityTypeKeys = $entityDef->getKeys();

    foreach ($property->getConditions() as $field => $value) {
      if (empty($value)) {
        continue;
      }

      if (array_key_exists($field, $entityTypeKeys)) {
        $field = $entityTypeKeys[$field];
      }

      $query->condition($field, $value, is_array($value) ? 'IN' : '=');
    }

    if (!empty($property->getExcludeIds())) {
      $field = $entityTypeKeys['id'] ?? 'id';
      $query->condition($field, $property->getExcludeIds(), 'NOT IN');
    }

    $ids = $query->execute();

    if (empty($ids)) {
      return;
    }

    $size = Settings::get('entity_update_batch_size', 50);

    /** @var \Drupal\Core\Entity\ContentEntityInterface[] $entities */
    foreach (array_chunk($storage->loadMultiple($ids), $size) as $entities) {
      foreach ($entities as $entity) {
        $this->exporter->export($entity, $property);

        if (!$property->isReference()) {
          continue;
        }

        foreach ($entity->referencedEntities() as $referencedEntity) {
          if ($referencedEntity instanceof ContentEntityInterface) {
            $this->exporter->export($referencedEntity, $property);
          }
        }
      }
    }
  }

}
