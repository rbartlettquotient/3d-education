<?php

/**
 * @file
 * Definition of Drupal\exampleuser\Plugin\field\formatter\ExampleUserDefaultFormatter.
 */

namespace Drupal\ex7_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal;

/**
 * Plugin implementation of the 'exampleuser' formatter.
 *
 * @FieldFormatter(
 *   id = "exampleuser_default",
 *   module = "ex7_embed",
 *   label = @Translation("Example User"),
 *   field_types = {
 *     "exampleuser"
 *   }
 * )
 */
class ExampleUserDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $exampleusers = getExampleUsersList();
    foreach ($items as $delta => $item) {
      if (isset($exampleusers[$item->value])) {
        $elements[$delta] = array('#markup' => $exampleusers[$item->value]);
      }
    }
    return $elements;
  }

}