<?php

namespace Drupal\Tests\event\Kernel;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\event\Entity\Event;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies the published and owner entity keys on the event type.
 *
 * Core and the group module resolve the "published" and "owner" keys from
 * the type definition when they rewrite entity queries. A definition
 * without those keys produces SQL with an empty column name
 * (SQLSTATE 42S22, error 1054) for users with group-based access, so the
 * keys must stay mapped to real fields.
 *
 * @group event
 */
#[RunTestsInSeparateProcesses]
class EventEntityKeysTest extends KernelTestBase {

  /**
   * Asserts the published and owner keys resolve to real fields.
   */
  public function testEntityKeys() {
    $definition = $this->entityTypeManager()->getDefinition('event');

    $this->assertTrue($definition->entityClassImplements(EntityPublishedInterface::class));
    $this->assertSame('status', $definition->getKey('published'));
    $this->assertSame('status', $definition->getKey('status'));
    $this->assertSame('user_id', $definition->getKey('owner'));
    $this->assertSame('user_id', $definition->getKey('uid'));
  }

  /**
   * Asserts the published API reads and writes through the status field.
   */
  public function testPublishedApi() {
    /** @var Event $event */
    $event = $this->createUnpublishedEvent();
    $this->assertFalse($event->isPublished());

    $event->setPublished();
    $this->assertTrue($event->isPublished());

    $event->setUnpublished();
    $this->assertFalse($event->isPublished());

    $event->setPublished(FALSE);
    $this->assertFalse($event->isPublished());
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'datetime_range',
    'event',
    'field',
    'language',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('event');
  }

  /**
   * Creates an unsaved, unpublished default event.
   */
  protected function createUnpublishedEvent(): Event {
    $this->entityTypeManager()->getStorage('event_type')->create([
      'id' => 'default',
      'label' => 'Default',
    ])->save();

    // The storage interface is not typed per entity in this core, so the
    // concrete class is asserted before returning.
    /** @var Event $event */
    $event = $this->entityTypeManager()->getStorage('event')->create([
      'machine_name' => 'test_event',
      'name' => 'Test event',
      'status' => FALSE,
      'type' => 'default',
    ]);
    return $event;
  }

  /**
   * Gets the entity type manager service.
   */
  protected function entityTypeManager(): EntityTypeManagerInterface {
    return $this->container->get('entity_type.manager');
  }

}
