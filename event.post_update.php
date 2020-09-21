<?php

/**
 * @file
 * Post update functions for the Event module.
 */

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\views\Entity\View;

/**
 * Adding events_admin view
 */
function event_post_update_add_event_admin_view() {
  // Only create if the views module is enabled and the events_admin view doesn't
  // exist.
  if (\Drupal::moduleHandler()->moduleExists('views')) {
    if (!View::load('events_admin')) {
      // Save the events_admin view to config.
      $module_handler = \Drupal::moduleHandler();
      $optional_install_path = $module_handler->getModule('event')->getPath() . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY;
      $storage = new FileStorage($optional_install_path);

      \Drupal::entityTypeManager()
        ->getStorage('view')
        ->create($storage->read('views.view.events_admin'))
        ->save();

      return t('The events_admin view has been created.');
    }

    return t("The events_admin view already exists and was not replaced.");
  }
}
