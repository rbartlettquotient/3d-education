<?php
/**
 * @file
 * Contains \Drupal\ex7_embed\Plugin\field\field_type\ExampleUserItem.
 */

namespace Drupal\ex7_embed\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'exampleuser' field type.
 *
 * @FieldType(
 *   id = "exampleuser",
 *   label = @Translation("Example User"),
 *   description = @Translation("Stores a user ID for users provided by an external datastore."),
 *   category = @Translation("Custom"),
 *   default_widget = "exampleuser_default",
 *   default_formatter = "exampleuser_default"
 * )
 */
class ExampleUserItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Example User'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'char',
          'length' => 255,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'value' => array('value'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();
    $constraints[] = $constraint_manager->create('ComplexData', array(
      'value' => array(
        'Length' => array(
          'max' => 255,
          'maxMessage' => t('%name: the example user name may not be longer than @max characters.', array('%name' => $this->getFieldDefinition()->getLabel(), '@max' => 255)),
        )
      ),
    ));
    return $constraints;
  }
}