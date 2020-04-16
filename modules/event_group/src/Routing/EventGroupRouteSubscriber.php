<?php

namespace Drupal\event_group\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class EventGroupRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Replace "some.route.name" below with the actual route you want to override.
    if ($route = $collection->get('entity.group_type.content_plugins')) {
      $route->setDefaults(array(
        '_controller' => '\Drupal\event_group\Controller\EventGroupTypeController::content',
      ));
    }
  }
}
