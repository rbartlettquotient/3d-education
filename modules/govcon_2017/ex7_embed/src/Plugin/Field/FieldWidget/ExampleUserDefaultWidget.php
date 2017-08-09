<?php
/**
 * @file
 * Contains \Drupal\ex7_embed\Plugin\field\FieldWidget\ExampleUserDefaultWidget.
 */
namespace Drupal\ex7_embed\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'exampleuser_default' widget.
 *
 * @FieldWidget(
 *   id = "exampleuser_default",
 *   label = @Translation("Example user select"),
 *   field_types = {
 *     "exampleuser"
 *   }
 * )
 */
class ExampleUserDefaultWidget extends WidgetBase {
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $exampleusers = getExampleUsersList();
    $element['value'] = $element + array(
        '#type' => 'select',
        '#options' => $exampleusers,
        '#empty_value' => '',
        '#default_value' => (isset($items[$delta]->value) && isset($exampleusers[$items[$delta]->value])) ? $items[$delta]->value : NULL,
        '#description' => t('Select a user'),
      );
    return $element;
  }
}