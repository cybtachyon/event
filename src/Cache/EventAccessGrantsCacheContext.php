<?php

namespace Drupal\event\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Cache\Context\UserCacheContextBase;

/**
 * Defines the event access grants cache context service.
 *
 * Cache context ID: 'user.event_grants' (to vary by all operations' grants).
 * Calculated cache context ID: 'user.event_grants:%operation', e.g.
 * 'user.event_grants:view' (to vary by the view operation's grants).
 *
 * The event entity type is not grants-based: its access is decided by
 * permissions and group membership, which the 'user' and group cache contexts
 * already cover. This context therefore resolves to a constant suffix, which
 * keeps views that reference 'user.event_grants:view' (saved when the event
 * module was grants-based) renderable without an unregistered-context
 * assertion.
 *
 * @see \Drupal\node\Cache\NodeAccessGrantsCacheContext
 * @ingroup event
 */
class EventAccessGrantsCacheContext extends UserCacheContextBase implements CalculatedCacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Event access view grants');
  }

  /**
   * {@inheritdoc}
   *
   * The event entity type carries no grants, so every operation resolves to
   * the same constant suffix.
   *
   * @param string|null $operation
   *   The operation the grants are requested for, e.g. 'view'.
   *
   * @return string
   *   The constant cache context suffix.
   */
  public function getContext($operation = NULL) {
    return 'all';
  }

  /**
   * {@inheritdoc}
   *
   * The context is a constant, so it adds no cache tags or max-age.
   *
   * @param string|null $operation
   *   The operation the grants are requested for, e.g. 'view'.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The empty cacheable metadata.
   */
  public function getCacheableMetadata($operation = NULL) {
    return new CacheableMetadata();
  }

}
