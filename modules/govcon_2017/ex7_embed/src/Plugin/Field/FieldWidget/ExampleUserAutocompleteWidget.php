<?php

namespace Drupal\ex7_embed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'exampleuser_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "exampleuser_autocomplete",
 *   label = @Translation("Example user autocomplete widget"),
 *   field_types = {
 *     "exampleuser"
 *   }
 * )
 */

class ExampleUserAutocompleteWidget extends WidgetBase {
  public static function defaultSettings() {
    return array(
        'size' => '60',
        'autocomplete_route_name' => 'exampleuser.autocomplete',
        'placeholder' => '',
      ) + parent::defaultSettings();
  }

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $exampleusers = getExampleUsersList();
    $element['value'] = $element + array(
        '#type' => 'textfield',
        '#default_value' =>  (isset($items[$delta]->value) && isset($exampleusers[$items[$delta]->value])) ? $exampleusers[$items[$delta]->value] : '',
        '#autocomplete_route_name' => $this->getSetting('autocomplete_route_name'),
        '#autocomplete_route_parameters' => array(),
        '#size' => $this->getSetting('size'),
        '#placeholder' => $this->getSetting('placeholder'),
        '#maxlength' => 255,
        //'#element_validate' => array('exampleuser_autocomplete_validate'),
      );
    return $element;
  }

}