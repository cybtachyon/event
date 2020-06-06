<?php

namespace Drupal\event\Tests;

use Drupal\Tests\BrowserTestBase;

/**
 * Example test that passes to confirm that automated build can run unit tests.
 *
 * @group event
 */
class EventExampleTest extends BrowserTestBase {

  /**
   * A meaningless test to ensure that automated build is running simpletest.
   */
  public function testAutomatedBuild() {
    $this->assertTrue(TRUE);
  }

}
