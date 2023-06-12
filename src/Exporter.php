<?php

namespace Drupal\impex;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\impex\Property\ExportProperty;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The entity exporter service.
 */
class Exporter {

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The file manager.
   *
   * @var \Drupal\impex\ImpexFileManager
   */
  protected $fileManager;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer.
   * @param \Drupal\impex\ImpexFileManager $fileManager
   *   The file manager.
   */
  public function __construct(
    SerializerInterface $serializer,
    ImpexFileManager $fileManager
  ) {
    $this->serializer = $serializer;
    $this->fileManager = $fileManager;
  }

  /**
   * Serialize entity and write data to file.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for export.
   * @param \Drupal\impex\Property\ExportProperty $property
   *   The property object.
   * @param array $options
   *   The serialize options.
   */
  public function export(ContentEntityInterface $entity, ExportProperty $property, array $options = []) {
    $options += [
      'json_encode_options' => JSON_PRETTY_PRINT,
      'xml_format_output' => 'formatOutput',
    ];

    $result = $this->serializer->serialize($entity, $property->getFormat(), $options);
    $uri = $this->generateExportPath($entity, $property->getFormat());
    $this
      ->fileManager
      ->writeData($result, $uri, $property->getFolder());
  }

  /**
   * Generate file URI to save data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for export.
   * @param string $format
   *   The serialize format.
   *
   * @return string
   *   The URI to save a data.
   */
  protected function generateExportPath(EntityInterface $entity, string $format): string {
    return "/{$entity->getEntityTypeId()}/{$entity->uuid()}.{$format}";
  }

}
