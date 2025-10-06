<?php

namespace Drupal\event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\event\Entity\EventInterface;
use Drupal\event\EventStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EventController.
 *
 *  Returns responses for Event routes.
 */
class EventController extends ControllerBase implements ContainerInjectionInterface {

  protected RendererInterface $renderer;

  protected DateFormatterInterface $dateFormatter;

  protected EventStorageInterface $storage;

  protected EntityViewBuilderInterface $viewBuilder;

  public function __construct(
    RendererInterface $renderer,
    DateFormatterInterface $date_formatter,
    EventStorageInterface $storage,
    EntityViewBuilderInterface $view_builder
  ) {
    $this->renderer = $renderer;
    $this->dateFormatter = $date_formatter;
    $this->storage = $storage;
    $this->viewBuilder = $view_builder;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * This is a factory method that returns a new instance of this class. The
   * factory should pass any needed dependencies into the constructor of this
   * class, but not the container itself. Every call to this method must return
   * a new instance of this class; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   */
  public static function create(ContainerInterface $container) {
    /** @var RendererInterface $renderer */
    $renderer = $container->get('renderer');
    /** @var DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    /** @var EventStorageInterface $event_storage */
    $event_storage = $entity_type_manager->getStorage('event');
    $event_view_builder = $entity_type_manager->getViewBuilder('event');

    return new static(
      $renderer,
      $date_formatter,
      $event_storage,
      $event_view_builder
    );
  }

  /**
   * Displays a Event  revision.
   *
   * @param int $event_revision
   *   The Event  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($event_revision) {
    $event = $this->storage->loadRevision($event_revision);
    return $this->viewBuilder->view($event);
  }

  /**
   * Page title callback for a Event  revision.
   *
   * @param int $event_revision
   *   The Event  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($event_revision) {
    /** @var EventInterface $event */
    $event = $this->storage->loadRevision($event_revision);
    return $this->t('Revision of %title from %date', ['%title' => $event->label(), '%date' => $this->dateFormatter->format($event->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Event .
   *
   * @param \Drupal\event\Entity\EventInterface $event
   *   A Event  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EventInterface $event) {
    $account = $this->currentUser();
    $langcode = $event->language()->getId();
    $langname = $event->language()->getName();
    $languages = $event->getTranslationLanguages();
    $has_translations = (count($languages) > 1);

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $event->label()]) : $this->t('Revisions for %title', ['%title' => $event->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all event revisions") || $account->hasPermission('administer event entities')));
    $delete_permission = (($account->hasPermission("delete all event revisions") || $account->hasPermission('administer event entities')));

    $rows = [];

    $vids = $this->storage->revisionIds($event);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\event\Entity\EventInterface $revision */
      $revision = $this->storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $event->getRevisionId()) {
          $link = $revision->toLink($date, 'revision');
        }
        else {
          $link = $event->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderInIsolation($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.event.translation_revert',
                [
                  'event' => $event->id(),
                  'event_revision' => $vid,
                  'langcode' => $langcode,
                ]
              ) :
              Url::fromRoute('entity.event.revision_revert',
                [
                  'event' => $event->id(),
                  'event_revision' => $vid,
                ]
              ),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.event.revision_delete',
                [
                  'event' => $event->id(),
                  'event_revision' => $vid,
                ]
              ),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['event_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
