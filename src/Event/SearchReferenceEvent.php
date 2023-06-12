<?php

namespace Drupal\impex\Event;

use Symfony\Component\EventDispatcher\Event;

class SearchReferenceEvent extends Event {

  /**
   * The decode data.
   *
   * @var mixed
   */
  protected $data;

  /**
   * The serializer format.
   *
   * @var string
   */
  protected $format;

  /**
   * The referenced files list.
   *
   * @var array
   */
  protected $referenceFiles = [];

  /**
   * Constructor.
   *
   * @param mixed $data
   *   The decode data.
   * @param string $format
   *   The serializer format.
   */
  public function __construct($data, string $format) {
    $this->data = $data;
    $this->format = $format;
  }

  /**
   * Returns decode data.
   *
   * @return mixed
   *   The decode data.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Returns serializer format.
   *
   * @return mixed
   *   The serializer format.
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * Returns referenced files.
   *
   * @return array
   *   The referenced files list.
   */
  public function getReferenceFiles(): array {
    return $this->referenceFiles;
  }

  /**
   * Add reference file.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   * @param mixed $file
   *   The file content.
   */
  public function addReferenceFile(string $entityTypeId, $file) {
    $this->referenceFiles[$entityTypeId][] = $file;
  }

}
