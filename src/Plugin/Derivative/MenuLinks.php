<?php

namespace Drupal\event\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default implementation for menu link plugins.
 */
class MenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  const MAX_BUNDLE_NUMBER = 10;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, RouteProviderInterface $route_provider, ThemeHandlerInterface $theme_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->routeProvider = $route_provider;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('router.route_provider'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];

    // If module event enabled.
    if ($this->moduleHandler->moduleExists('admin_toolbar')) {
      $links['event.type_add'] = [
          'title' => $this->t('Add event type'),
          'route_name' => 'entity.event_type.add_form',
          'parent' => 'entity.event_type.collection',
          'weight' => -2,
        ] + $base_plugin_definition;
      // Displays event link in toolbar.
      $links['event_page'] = [
          'title' => $this->t('Events'),
          'route_name' => 'entity.event.collection',
          'parent' => 'system.admin_content',
        ] + $base_plugin_definition;
      $links['add_event'] = [
          'title' => $this->t('Add event'),
          'route_name' => 'entity.event.add_page',
          'parent' => $base_plugin_definition['id'] . ':event_page',
        ] + $base_plugin_definition;
      // Adds links for each event type.
      foreach ($this->entityTypeManager->getStorage('event_type')->loadMultiple() as $type) {
        $links['event.add.' . $type->id()] = [
            'title' => $type->label(),
            'route_name' => 'entity.event.add_form',
            'parent' => $base_plugin_definition['id'] . ':add_event',
            'route_parameters' => ['event_type' => $type->id()],
          ] + $base_plugin_definition;
      }
    }

    return $links;
  }
}
