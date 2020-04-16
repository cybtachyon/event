<?php

namespace Drupal\gevent\Plugin\GroupContentEnabler;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\event\Entity\EventType;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a content enabler for events.
 *
 * @GroupContentEnabler(
 *   id = "group_event",
 *   label = @Translation("Group event"),
 *   description = @Translation("Adds events to groups both publicly and privately."),
 *   entity_type_id = "event",
 *   entity_access = TRUE,
 *   reference_label = @Translation("Title"),
 *   reference_description = @Translation("The title of the event to add to the group"),
 *   deriver = "Drupal\gevent\Plugin\GroupContentEnabler\GroupEventDeriver"
 * )
 */
class GroupEvent extends GroupContentEnablerBase {

  /**
   * Retrieves the event type this plugin supports.
   *
   * @return \Drupal\event\EventTypeInterface
   *   The event type this plugin supports.
   */
  protected function getEventType() {
    return EventType::load($this->getEntityBundle());
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupOperations(GroupInterface $group) {
    $account = \Drupal::currentUser();
    $plugin_id = $this->getPluginId();
    $type = $this->getEntityBundle();
    $operations = [];

    if ($group->hasPermission("create $plugin_id entity", $account)) {
      $route_params = ['group' => $group->id(), 'plugin_id' => $plugin_id];
      $operations["gevent-create-$type"] = [
        'title' => $this->t('Add @type', ['@type' => $this->getEventType()->label()]),
        'url' => new Url('entity.group_content.create_form', $route_params),
        'weight' => 30,
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTargetEntityPermissions() {
    $permissions = parent::getTargetEntityPermissions();
    $plugin_id = $this->getPluginId();

    // Add a 'view unpublished' permission by re-using most of the 'view' one.
    $original = $permissions["view $plugin_id entity"];
    $permissions["view unpublished $plugin_id entity"] = [
      'title' => str_replace('View ', 'View unpublished ', $original['title']),
    ] + $original;

    return $permissions;
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

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $dependencies['config'][] = 'event.type.' . $this->getEntityBundle();
    return $dependencies;
  }

}
