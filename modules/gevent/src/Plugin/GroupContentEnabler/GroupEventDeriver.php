<?php

namespace Drupal\gevent\Plugin\GroupContentEnabler;

use Drupal\event\Entity\EventType;
use Drupal\Component\Plugin\Derivative\DeriverBase;

class GroupEventDeriver extends DeriverBase {

  /**
   * {@inheritdoc}.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach (EventType::loadMultiple() as $name => $event_type) {
      $label = $event_type->label();

      $this->derivatives[$name] = [
        'entity_bundle' => $name,
        'label' => t('Group event (@type)', ['@type' => $label]),
        'description' => t('Adds %type content to groups both publicly and privately.', ['%type' => $label]),
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
