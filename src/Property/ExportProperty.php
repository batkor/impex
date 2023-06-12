<?php

namespace Drupal\impex\Property;

/**
 * Implements properties object for export.
 */
class ExportProperty extends ImperPropertyBase {

  /**
   * The exclude entity IDs.
   *
   * @var array
   */
  protected $excludeIds = [];

  /**
   * The properties list for query condition.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * The mark for export reference entities.
   *
   * @var bool
   */
  protected $reference = FALSE;

  /**
   * Set query conditions.
   *
   * @param string $field
   *   The property name.
   * @param mixed $value
   *   The property value.
   */
  public function setCondition(string $field, $value): ExportProperty {
    $this->conditions[$field] = $value;

    return $this;
  }

  /**
   * Returns properties list for query condition.
   *
   * @return array
   *   The properties list.
   */
  public function getConditions(): array {
    return $this->conditions;
  }

  /**
   * Set exclude entity IDs list.
   */
  public function setExcludeIds(array $ids): self {
    $this->excludeIds = $ids;

    return $this;
  }

  /**
   * Returns exclude entity IDs list.
   */
  public function getExcludeIds(): array {
    return $this->excludeIds;
  }

  /**
   * Set mark status for export reference entities.
   *
   * @param bool $status
   *   The status.
   */
  public function markReference(bool $status): self {
    $this->reference = $status;

    return $this;
  }

  /**
   * Returns status export reference entities.
   */
  public function isReference(): bool {
    return $this->reference;
  }

}
