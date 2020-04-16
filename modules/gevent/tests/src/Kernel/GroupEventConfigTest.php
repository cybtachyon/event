<?php

namespace Drupal\Tests\gevent\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests that all config provided by this module passes validation.
 *
 * @group gevent
 */
class GroupEventConfigTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['group', 'event', 'gevent', 'views'];

  /**
   * Tests that the module's config installs properly.
   */
  public function testConfig() {
    $this->installConfig(['gevent']);
  }

}
