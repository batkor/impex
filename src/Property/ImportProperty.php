<?php

namespace Drupal\impex\Property;

/**
 * Implements properties object for import.
 */
class ImportProperty extends ImperPropertyBase {

  /**
   * The mark a need overriding entities.
   *
   * @var bool
   */
  protected $override;

  /**
   * Set status override entities.
   */
  public function markOverride(): self {
    $this->override = TRUE;

    return $this;
  }

  /**
   * Returns status override entities.
   */
  public function isOverride(): bool {
    return $this->override;
  }

}
