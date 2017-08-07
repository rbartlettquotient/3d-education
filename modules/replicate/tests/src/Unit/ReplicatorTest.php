<?php

/**
 * @file
 * Contains \Drupal\Tests\replicate\Unit\ReplicatorTest.
 */

namespace Drupal\Tests\replicate\Unit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\replicate\Events\AfterSaveEvent;
use Drupal\replicate\Events\ReplicateAlterEvent;
use Drupal\replicate\Events\ReplicateEntityEvent;
use Drupal\replicate\Events\ReplicateEntityFieldEvent;
use Drupal\replicate\Events\ReplicatorEvents;
use Drupal\replicate\Replicator;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\replicate\Replicator
 * @group replicate
 */
class ReplicatorTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the cloneEntity method.
   *
   * @covers ::cloneEntity
   */
  public function testCloneForNonFieldableEntity() {
    $entity = $this->prophesize(EntityInterface::class);
    $entity->getEntityTypeId()->willReturn('entity_test');
    $clone = $this->prophesize(EntityInterface::class);
    $clone->getEntityTypeId()->willReturn('entity_test');
    $clone = $clone->reveal();

    $entity->createDuplicate()->willReturn($clone);

    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $event_dispatcher->dispatch('replicate__entity__entity_test', Argument::type(ReplicateEntityEvent::class))
      ->shouldBeCalled();
    $event_dispatcher->dispatch(ReplicatorEvents::REPLICATE_ALTER, Argument::type(ReplicateAlterEvent::class))
      ->shouldBeCalled();
    $event_dispatcher->dispatch(ReplicatorEvents::AFTER_SAVE, Argument::type(AfterSaveEvent::class))
      ->shouldNotBeCalled();

    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $replicator = new Replicator($entity_type_manager->reveal(), $event_dispatcher->reveal());

    $result = $replicator->cloneEntity($entity->reveal());
    $this->assertSame($clone, $result);
  }

  /**
   * Tests the cloneEntity and cloneEntityFields methods.
   *
   * @covers ::cloneEntity
   * @covers ::cloneEntityFields
   */
  public function testCloneForFieldableEntity() {
    $entity = $this->prophesize(FieldableEntityInterface::class);
    $entity->getEntityTypeId()->willReturn('entity_test');
    $clone = $this->prophesize(FieldableEntityInterface::class);
    $clone->getEntityTypeId()->willReturn('entity_test');

    $entity_ref_field_item_list = $this->prophesize(FieldItemListInterface::class);
    $clone->get('entity_ref')->willReturn($entity_ref_field_item_list->reveal());
    $textfield_field_item_list = $this->prophesize(FieldItemListInterface::class);
    $clone->get('field_textfield')->willReturn($textfield_field_item_list->reveal());

    $field_definitions = [];
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $field_definition->getType()->willReturn('entity_reference');
    $field_definitions['entity_ref'] = $field_definition->reveal();
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $field_definition->getType()->willReturn('textfield');
    $field_definitions['field_textfield'] = $field_definition->reveal();

    $clone->getFieldDefinitions()->willReturn($field_definitions)->shouldBeCalled();
    $clone = $clone->reveal();

    $entity->createDuplicate()->willReturn($clone);

    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $event_dispatcher->dispatch('replicate__entity__entity_test', Argument::type(ReplicateEntityEvent::class))
      ->shouldBeCalled();
    $event_dispatcher->dispatch('replicate__entity_field__entity_reference', Argument::that(function ($event) {
      if (!$event instanceof ReplicateEntityFieldEvent) {
        return FALSE;
      }
      if (!$event->getFieldItemList() instanceof FieldItemListInterface) {
        return FALSE;
      }
      return TRUE;
    }))
      ->shouldBeCalled();
    $event_dispatcher->dispatch('replicate__entity_field__textfield', Argument::that(function ($event) {
      if (!$event instanceof ReplicateEntityFieldEvent) {
        return FALSE;
      }
      if (!$event->getFieldItemList() instanceof FieldItemListInterface) {
        return FALSE;
      }
      return TRUE;
    }))
      ->shouldBeCalled();
    $event_dispatcher->dispatch(ReplicatorEvents::REPLICATE_ALTER, Argument::type(ReplicateAlterEvent::class))
      ->shouldBeCalled();
    $event_dispatcher->dispatch(ReplicatorEvents::AFTER_SAVE, Argument::type(AfterSaveEvent::class))
      ->shouldNotBeCalled();

    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $replicator = new Replicator($entity_type_manager->reveal(), $event_dispatcher->reveal());

    $result = $replicator->cloneEntity($entity->reveal());
    $this->assertSame($clone, $result);
  }

}
