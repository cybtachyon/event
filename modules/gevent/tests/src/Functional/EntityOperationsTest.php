<?php

namespace Drupal\Tests\gevent\Functional;

use Drupal\Tests\group\Functional\EntityOperationsTest as GroupEntityOperationsTest;

/**
 * Tests that entity operations (do not) show up on the group overview.
 *
 * @see gevent_entity_operation()
 *
 * @group gevent
 */
class EntityOperationsTest extends GroupEntityOperationsTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['gevent'];

  /**
   * {@inheritdoc}
   */
  public function provideEntityOperationScenarios() {
    $scenarios['withoutAccess'] = [
      [],
      ['group/1/events' => 'Events'],
    ];

    $scenarios['withAccess'] = [
      [],
      ['group/1/events' => 'Events'],
      ['access group_event overview'],
    ];

    $scenarios['withAccessAndViews'] = [
      ['group/1/events' => 'Events'],
      [],
      ['access group_event overview'],
      ['views'],
    ];

    return $scenarios;
  }

}
