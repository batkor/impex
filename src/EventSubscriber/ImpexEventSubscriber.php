<?php

namespace Drupal\impex\EventSubscriber;

use Drupal\Component\Utility\NestedArray;
use Drupal\impex\Event\SearchReferenceEvent;
use Drupal\impex\ImpexFileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImpexEventSubscriber implements EventSubscriberInterface {

  /**
   * The file manager.
   *
   * @var \Drupal\impex\ImpexFileManager
   */
  protected $fileManager;

  /**
   * The search reference files event.
   *
   * @var \Drupal\impex\Event\SearchReferenceEvent
   */
  protected $searchReferenceEvent;

  /**
   * Constructor.
   *
   * @param \Drupal\impex\ImpexFileManager $fileManager
   *   The file manager.
   */
  public function __construct(ImpexFileManager $fileManager) {
    $this->fileManager = $fileManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SearchReferenceEvent::class => 'searchReferenced',
    ];
  }

  /**
   * Search reference entities on import file collections.
   *
   * @param \Drupal\impex\Event\SearchReferenceEvent $event
   *   The search reference files event.
   */
  public function searchReferenced(SearchReferenceEvent $event) {
    $this->searchReferenceEvent = $event;

    if (method_exists($this, $event->getFormat())) {
      $this->{$event->getFormat()}($event->getData());
    }
  }

  /**
   * Parse JSON file data and add to reference files storage.
   *
   * @param mixed $data
   *   The source file data.
   */
  public function json($data) {
    foreach ($data as $key => $datum) {
      if (is_array($datum)) {
        $this->json($datum);
      }

      if ($key === 'target_uuid') {
        foreach ($this->fileManager->getFileByUuid($datum) as $entityTypeId => $file) {
          $this
            ->searchReferenceEvent
            ->addReferenceFile($entityTypeId, $file);
        }
      }
    }
  }

}
