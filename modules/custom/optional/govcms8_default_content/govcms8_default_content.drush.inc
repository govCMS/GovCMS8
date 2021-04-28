<?php

/**
 * @file
 * Drush commands used for importing and rolling back content.
 */

use Drupal\govcms8_default_content\InstallHelper;
use Drush\Drush;

/**
 * Implements hook_drush_command().
 */
function govcms8_default_content_drush_command() {
  $commands['govcms8-default-content-import'] = [
    'description' => 'Import default content for GovCMS8',
    'aliases' => ['govcms-import'],
  ];

  $commands['govcms8-default-content-rollback'] = [
    'description' => 'Rollback default content for GovCMS8',
    'aliases' => ['govcms-rollback'],
  ];

  return $commands;
}

/**
 * Content import drush command.
 */
function drush_govcms8_default_content_import() {
  \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->importContent();
  Drush::service('logger')->info(dt('Imported GovCMS8 default content.'));
}

/**
 * Content rollback drush command.
 */
function drush_govcms8_default_content_rollback() {
  \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->deleteImportedContent();
  Drush::service('logger')->info(dt('Rolled back GovCMS8 default content.'));
}
