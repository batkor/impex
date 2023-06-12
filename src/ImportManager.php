<?php

namespace Drupal\impex;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\impex\Event\PreImportEntity;
use Drupal\impex\Event\SearchReferenceEvent;
use Drupal\impex\Property\ImportProperty;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The import manager.
 */
class ImportManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The files list contains entities data to import.
   *
   * @var array
   */
  protected $files = [];

  /**
   * The path list to entity types.
   *
   * @var array
   */
  protected $entityTypePaths = [];

  /**
   * The file manager.
   *
   * @var \Drupal\impex\ImpexFileManager
   */
  protected $fileManager;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  protected $importEntitiesMap = [];

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\impex\ImpexFileManager $fileManager
   *   The file manager.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    ImpexFileManager $fileManager,
    SerializerInterface $serializer,
    EventDispatcherInterface $eventDispatcher
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileManager = $fileManager;
    $this->serializer = $serializer;
    $this->eventDispatcher = $eventDispatcher;
  }

  public function import(ImportProperty $property) {
    $fileMask = '/.*\.' . $property->getFormat() . '/';
    $importFiles = $this
      ->fileManager
      ->scanImportDirectory($fileMask)
      ->getImportFiles($property->getEntityTypeId());

    foreach ($importFiles as $entityTypeId => $files) {
      foreach ($files as $file) {
        $this->convertDataToEntity($file, $entityTypeId, $property->getFormat());
      }
    }
  }

  /**
   * Convert import data to entities.
   *
   * @param object $file
   *   Object contains next properties.
   *   - uri: The file URI.
   *   - name: The filename without extension.
   *   - filename: The filename.
   * @param string $entityTypeId
   *   The entity type ID.
   * @param string $format
   *   The import format.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function convertDataToEntity(object $file, string $entityTypeId, string $format) {
    $entityDefinition = $this
      ->entityTypeManager
      ->getDefinition($entityTypeId);
    $decode = $this->serializer->decode($this->fileManager->readData($file->uri), $format);

    $event = new SearchReferenceEvent($decode, $format);
    $this
      ->eventDispatcher
      ->dispatch(SearchReferenceEvent::class, $event);

    foreach ($event->getReferenceFiles() as $entityTypeId => $files) {
      foreach ($files as $file) {
        $this->convertDataToEntity($file, $entityTypeId, $format);
      }
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this
      ->serializer
      ->denormalize($decode, $entityDefinition->getClass(), $format);
    $this->importEntitiesMap[$file->name] = $entity->uuid();
  }

}
