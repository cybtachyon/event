<?php

namespace Drupal\event_group\Plugin;

use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Plugin\GroupContentEnablerManager;

/**
 * Manages GroupContentEnabler plugin implementations.
 *
 * @see hook_group_content_info_alter()
 * @see \Drupal\group\Annotation\GroupContentEnabler
 * @see \Drupal\group\Plugin\GroupContentEnablerInterface
 * @see \Drupal\group\Plugin\GroupContentEnablerBase
 * @see plugin_api
 */
class EventGroupContentEnablerManager extends GroupContentEnablerManager {

  /**
   * Installs the Event Group plugins on Event groups.
   **
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function installEventPlugin() {
    // Only work on the event group.
    $group_type = $this->getGroupTypeStorage()->load('events');
    // If we're looking at a specific group, return if its not event.
    if (!isset($group_type)) {
      return;
    }
    
    $plugin_id = 'event_group';
    $installed = $this->getInstalledIds($group_type);
    if (!in_array($plugin_id, $installed)) {
      $this->getGroupContentTypeStorage()
        ->createFromPlugin($group_type, $plugin_id)
        ->save();
    }
  }
}
