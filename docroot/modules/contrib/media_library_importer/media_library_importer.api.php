<?php

/**
 * Add addditional fields to media being added
 *
 * This hooks allows modules to add addditional fields to media being added
 *
 * @param \Drupal\file\Entity\File $file
 *   The file being imported.
 *
 * @param int $file_url
 *   The url of the file being imported.
 *
 * @param int $uri
 *   The uri of the file being imported.
 *
 * @param int $extra_fields
 *   Extra fields for the media to be created. Here, any other fields belonging
 *   to media bundle can be added
 *
 */
function hook_alter_media_library_importer_media_extra_fields($file, $file_url, $uri, &$extra_fields) {
}
