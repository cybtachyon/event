<?php

namespace Drupal\event_group\Controller;

use Drupal\group\Entity\Controller\GroupTypeController;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Plugin\GroupContentEnablerInterface;

/**
 * Overrides the Group Content page to show that events are always installed.
 * Yes, this code is all so that an Asterisk (*) shows up for event group types.
 */
class EventGroupTypeController extends GroupTypeController {

  /**
   * Builds an admin interface to manage the group type's group content plugins.
   *
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The group type to build an interface for.
   *
   * @return array
   *   The render array for the page.
   */
  public function content(GroupTypeInterface $group_type) {
    $page = parent::content($group_type);
    $plugin_id = 'event_group';

    $installed = $this->pluginManager->getInstalledIds($group_type);
    $plugin = $this->pluginManager->getDefinition($plugin_id);
    // If the plugin is installed on the group type, use that one instead of
    // an 'empty' version so that we may use methods on it which expect to
    // have a group type configured.
    if (in_array($plugin_id, $installed)) {
      $plugin = $this->groupType->getContentPlugin($plugin['id']);
    }
    $page['content'][$plugin_id] = $this->buildEventRow($plugin);

    return $page;
  }

  /**
   * Builds a row for a content enabler plugin.
   *
   * @param \Drupal\group\Plugin\GroupContentEnablerInterface $plugin
   *   The content enabler plugin to build operation links for.
   *
   * @return array
   *   A render array to use as a table row.
   */
  public function buildEventRow(GroupContentEnablerInterface $plugin) {
    // Events plugin is always installed.
    $status = $this->t('Installed*');

    $row = [
      'info' => [
        '#type' => 'inline_template',
        '#template' => '<div class="description"><span class="label">{{ label }}</span>{% if description %}<br/>{{ description }}{% endif %}</div>',
        '#context' => [
          'label' => $plugin->getLabel(),
        ],
      ],
      'provider' => [
        '#markup' => $this->moduleHandler->getName($plugin->getProvider())
      ],
      'entity_type_id' => [
        '#markup' => $this->entityTypeManager->getDefinition($plugin->getEntityTypeId())->getLabel()
      ],
      'status' => ['#markup' => $status],
      'operations' => $this->buildOperations($plugin),
    ];

    // Show the content enabler description if toggled on.
    if (!system_admin_compact_mode()) {
      $row['info']['#context']['description'] = $plugin->getDescription();
    }

    return $row;
  }
}
