<?php

namespace Drupal\event\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Event type entity.
 *
 * @ConfigEntityType(
 *   id = "event_type",
 *   label = @Translation("Event type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event\EventTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\event\Form\EventTypeForm",
 *       "edit" = "Drupal\event\Form\EventTypeForm",
 *       "delete" = "Drupal\event\Form\EventTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     }
 *   },
 *   config_prefix = "type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "event",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/event/add",
 *     "edit-form" = "/admin/structure/event/manage/{event_type}/edit",
 *     "delete-form" = "/admin/structure/event/manage/{event_type}/delete",
 *     "collection" = "/admin/structure/event"
 *   }
 * )
 */
class EventType extends ConfigEntityBundleBase implements EventTypeInterface {

  /**
   * The Event type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Event type label.
   *
   * @var string
   */
  protected $label;


  /**
   * The Event type timezone.
   *
   * @var bool
   */
  protected $timezone;

  /**
   * {@inheritdoc}
   */
  public function useTimezones() {
    return $this->timezone;
  }

}
