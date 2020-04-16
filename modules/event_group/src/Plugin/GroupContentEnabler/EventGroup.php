<?php

namespace Drupal\event_group\Plugin\GroupContentEnabler;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\event\Entity\EventType;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a content enabler for events.
 *
 * @GroupContentEnabler(
 *   id = "event_group",
 *   label = @Translation("Event Group"),
 *   description = @Translation("Enables a Group from an Event Entity."),
 *   entity_type_id = "event",
 *   entity_access = TRUE,
 *   reference_label = @Translation("Title"),
 *   reference_description = @Translation("The title of the event that manages the group"),
 * )
 */
class EventGroup extends GroupContentEnablerBase {
  
  /**
   * {@inheritdoc}
   */
  public function getGroupOperations(GroupInterface $group) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['entity_cardinality'] = 1;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Disable the entity cardinality field as the functionality of this module
    // relies on a cardinality of 1. We don't just hide it, though, to keep a UI
    // that's consistent with other content enabler plugins.
    $info = $this->t("This field has been disabled by the plugin to guarantee the functionality that's expected of it.");
    $form['entity_cardinality']['#disabled'] = TRUE;
    $form['entity_cardinality']['#description'] .= '<br /><em>' . $info . '</em>';

    return $form;
  }
}
