<?php

namespace Drupal\impex\Property;

/**
 * Defines a common interface for property objects.
 */
interface ImpexPropertyInterface {

  /**
   * Set entity type ID.
   */
  public function setEntityTypeId(string $entityTypeId): self;

  /**
   * Returns entity type ID.
   */
  public function getEntityTypeId(): ?string;

  /**
   * Set format export/import entities.
   */
  public function setFormat(string $format): ImpexPropertyInterface;

  /**
   * Return format export/import entities.
   */
  public function getFormat(): string;

  /**
   * Set path to directory contains entities data.
   *
   * @param string $folder
   *   The path to folder.
   */
  public function setFolder(string $folder): ImpexPropertyInterface;

  /**
   * Returns path to directory contains entities data.
   */
  public function getFolder(): ?string;

}
