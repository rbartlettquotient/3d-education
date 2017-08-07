<?php

/**
 * @file
 * Contains \Drupal\replicate\Replicator.
 */

namespace Drupal\replicate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\replicate\Events\AfterSaveEvent;
use Drupal\replicate\Events\ReplicateAlterEvent;
use Drupal\replicate\Events\ReplicateEntityEvent;
use Drupal\replicate\Events\ReplicateEntityFieldEvent;
use Drupal\replicate\Events\ReplicatorEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Replicator. Manages the replication of an entity.
 *
 * @package Drupal\replicate
 */
class Replicator {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Creates a new Replicator instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
  }


  /**
   * Replicate a entity by entity type ID and entity ID and save it.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The cloned entity.
   */
  public function replicateByEntityId($entity_type_id, $entity_id) {
    if ($entity = $this->entityTypeManager->getStorage($entity_type_id)->load($entity_id)) {
      return $this->replicateEntity($entity);
    }
  }

  /**
   * Replicate a entity and save it.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The cloned entity.
   */
  public function replicateEntity(EntityInterface $entity) {
    if ($clone = $this->cloneEntity($entity)) {
      $this->entityTypeManager->getStorage($entity->getEntityTypeId())->save($clone);
      $event = new AfterSaveEvent($clone);
      $this->eventDispatcher->dispatch(ReplicatorEvents::AFTER_SAVE, $event);
      return $clone;
    }
  }

  /**
   * Clone a entity by entity type ID and entity ID without saving.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The cloned entity.
   */
  public function cloneByEntityId($entity_type_id, $entity_id) {
    if ($entity = $this->entityTypeManager->getStorage($entity_type_id)->load($entity_id)) {
      return $this->cloneEntity($entity);
    }
  }

  /**
   * Clone a entity without saving.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The cloned entity.
   */
  public function cloneEntity(EntityInterface $entity) {
    if ($clone = $entity->createDuplicate()) {
      $event = new ReplicateEntityEvent($entity);
      $this->eventDispatcher->dispatch(ReplicatorEvents::replicateEntityEvent($entity->getEntityTypeId()), $event);

      if ($clone instanceof FieldableEntityInterface) {
        /** @var FieldableEntityInterface $clone */
        $this->dispatchEventCloneEntityFields($clone);
      }

      $event = new ReplicateAlterEvent($clone, $entity);
      $this->eventDispatcher->dispatch(ReplicatorEvents::REPLICATE_ALTER, $event);
      return $clone;
    }
  }

  /**
   * Clone an entity field to another.
   *
   * We can not create and return the target field here, because it needs to
   * know its parent entity, which can not be changed after creation.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field to clone.
   * @param \Drupal\Core\Field\FieldItemListInterface $target_field
   *   The field to clone into.
   */
  public function cloneEntityField(FieldItemListInterface $field, FieldItemListInterface $target_field) {
    $target_field->setValue($field->getValue());

    $this->postCloneEntityField($target_field);
  }

  /**
   * Postprocess a cloned entity field.
   *
   * A public API method so modules can e.g. clone a field partially.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $target_field
   *   The cloned field.
   */
  public function postCloneEntityField(FieldItemListInterface $target_field) {
    $entity = $target_field->getEntity();
    if (!$entity instanceof FieldableEntityInterface) {
      // @todo Can this ever happen? The interface only assures EntityInterface.
      throw new FieldException(t('Trying to clone into non fieldable Entity.'));
    }
    /** @var FieldableEntityInterface $entity */

    $violations = $target_field->validate();
    if ($violations->count()) {
      // This autocasts violations to string.
      $t_args = array('%violations' => implode(' & ', $violations));
      throw new FieldException(t('Trying to clone into incompatible field: %violations', $t_args));
    }

    $this->dispatchEventCloneEntityField($entity, $target_field->getName(), $target_field->getFieldDefinition());
  }

  /**
   * Fires events for each field of a fieldable entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $clone
   *   The cloned fieldable entity.
   */
  protected function dispatchEventCloneEntityFields(FieldableEntityInterface $clone) {
    foreach ($clone->getFieldDefinitions() as $field_name => $field_definition) {
      $this->dispatchEventCloneEntityField($clone, $field_name, $field_definition);
    }
  }

  /**
   * Fires events for a single field of a fieldable entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $clone
   *   The cloned fieldable entity.
   * @param $field_name
   *   The field name.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   */
  private function dispatchEventCloneEntityField(FieldableEntityInterface $clone, $field_name, FieldDefinitionInterface $field_definition) {
    $event = new ReplicateEntityFieldEvent($clone->get($field_name), $clone);
    $this->eventDispatcher->dispatch(ReplicatorEvents::replicateEntityField($field_definition->getType()), $event);
  }

}
