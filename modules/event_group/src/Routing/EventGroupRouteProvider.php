<?php

namespace Drupal\event_group\Routing;

use Drupal\event\Entity\EventType;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for event_group group content.
 */
class EventGroupRouteProvider {

  /**
   * Provides the shared collection route for group event plugins.
   */
  public function getRoutes() {
    $routes = $plugin_ids = $permissions_add = $permissions_create = [];

    foreach (EventType::loadMultiple() as $name => $event_type) {
      $plugin_id = "event_group:$name";

      $plugin_ids[] = $plugin_id;
      $permissions_add[] = "create $plugin_id content";
      $permissions_create[] = "create $plugin_id entity";
    }

    // If there are no event types yet, we cannot have any plugin IDs and should
    // therefore exit early because we cannot have any routes for them either.
    if (empty($plugin_ids)) {
      return $routes;
    }

    $routes['entity.group_content.event_group_relate_page'] = new Route('group/{group}/event/add');
    $routes['entity.group_content.event_group_relate_page']
      ->setDefaults([
        '_title' => 'Add existing content',
        '_controller' => '\Drupal\event_group\Controller\EventGroupController::addPage',
      ])
      ->setRequirement('_group_permission', implode('+', $permissions_add))
      ->setRequirement('_group_installed_content', implode('+', $plugin_ids))
      ->setOption('_group_operation_route', TRUE);

    $routes['entity.group_content.event_group_add_page'] = new Route('group/{group}/event/create');
    $routes['entity.group_content.event_group_add_page']
      ->setDefaults([
        '_title' => 'Add new content',
        '_controller' => '\Drupal\event_group\Controller\EventGroupController::addPage',
        'create_mode' => TRUE,
      ])
      ->setRequirement('_group_permission', implode('+', $permissions_create))
      ->setRequirement('_group_installed_content', implode('+', $plugin_ids))
      ->setOption('_group_operation_route', TRUE);

    return $routes;
  }

}
