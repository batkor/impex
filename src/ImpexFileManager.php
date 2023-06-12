<?php

namespace Drupal\impex;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;

/**
 * The proxy file system.
 */
class ImpexFileManager {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The import data collections.
   *
   * @var array
   */
  protected $importData = [];

  /**
   * Constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   */
  public function __construct(
    FileSystemInterface $fileSystem
  ) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * Write data to file.
   *
   * @param string $data
   *   The data to write.
   * @param string $uri
   *   The file URI.
   * @param string|null $directory
   *   The path to destination directory.
   */
  public function writeData(string $data, string $uri, string $directory = NULL) {
    if (empty($directory)) {
      $directory = $this->getDirectory();
    }

    $destination = rtrim($directory, '/') . $uri;

    if ($this->prepareDirectory($destination)) {
      $this->fileSystem->saveData($data, $destination, FileSystemInterface::EXISTS_REPLACE);
    }
  }

  /**
   * Prepare path to export data file.
   *
   * @param string $destination
   *   The export data file destination.
   *
   * @return bool
   *   The prepare result.
   */
  public function prepareDirectory(string $destination): bool {
    $dir = dirname($destination);

    return $this
      ->fileSystem
      ->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
  }

  /**
   * Returns a directory contains impex data.
   *
   * @return string
   *   The path to directory.
   */
  public function getDirectory() {
    $settings = Settings::get('impex');

    if (!empty($settings['directory'])) {
      return rtrim($settings['directory'], '/');
    }

    return rtrim($this->fileSystem->getTempDirectory(), '/') . '/impex';
  }

  /**
   * Scan import directory and collects files.
   *
   * @param string|null $mask
   *   The file name masc.
   * @param string|null $directory
   *   Path for scan.
   *
   * @return $this
   */
  public function scanImportDirectory(string $mask = NULL, string $directory = NULL): self {
    if (empty($directory)) {
      $directory = $this->getDirectory();
    }

    $result = $this->fileSystem
      ->scanDirectory($directory, '/.*/', ['recurse' => FALSE]);

    if (empty($mask)) {
      $mask = '/.*/';
    }

    foreach ($result as $path) {
      $this->importData[$path->name] = $this
        ->fileSystem
        ->scanDirectory($path->uri, $mask);
    }

    return $this;
  }

  /**
   * Returns files for import by entity type ID, else all files.
   *
   * @param string|null $entityTypeId
   *   The entity ID.
   *
   * @return array
   *   The import files.
   */
  public function getImportFiles(string $entityTypeId = NULL): array {
    if (empty($entityTypeId)) {
      return $this->importData;
    }

    if (array_key_exists($entityTypeId, $this->importData)) {
      return [$entityTypeId => $this->importData[$entityTypeId]];
    }

    return [];
  }

  /**
   * Returns contains data on file.
   *
   * @param string $uri
   *   The URI top file.
   *
   * @return false|string
   *   The file content.
   */
  public function readData(string $uri) {
    return file_get_contents($uri);
  }

  public function getFileByUuid(string $uuid) {
    foreach ($this->importData as $entityTypeId => $files) {
      foreach ($files as $file) {
        if ($file->name === $uuid) {
          return [$entityTypeId => $file] ;
        }
      }
    }

    return FALSE;
  }

}
