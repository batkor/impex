<?php

namespace Drupal\impex\Property;

/**
 * Implements base property object.
 */
abstract class ImperPropertyBase implements ImpexPropertyInterface {

  /**
   * The entity ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The format for export/import.
   *
   * @var string
   */
  protected $format;

  /**
   * The path to directory contains entities data.
   *
   * @var string
   */
  protected $folder;

  /**
   * {@inheritdoc}
   */
  public function setEntityTypeId(string $entityTypeId): ImpexPropertyInterface {
    $this->entityTypeId = $entityTypeId;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId(): ?string {
    return $this->entityTypeId;
  }

  /**
   * {@inheritdoc}
   */
  public function setFormat(string $format): ImpexPropertyInterface {
    $this->format = $format;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat(): string {
    return $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function setFolder(string $folder): ImpexPropertyInterface {
    $this->folder = $folder;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFolder(): ?string {
    return $this->folder;
  }

}
