<?php

/**
 * @file
 * Definition of Drupal\exampleuser\Plugin\field\formatter\ExampleUserFancyFormatter.
 */

namespace Drupal\ex7_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal;

/**
 * Plugin implementation of the 'exampleuser' formatter.
 *
 * @FieldFormatter(
 *   id = "exampleuser_fancy",
 *   module = "ex7_embed",
 *   label = @Translation("Example User Fancy"),
 *   field_types = {
 *     "exampleuser"
 *   }
 * )
 */
class ExampleUserFancyFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $exampleusers = getExampleUsersList();
    foreach ($items as $delta => $item) {
      if (isset($exampleusers[$item->value])) {
        //@todo theme differently
        $elements[$delta] = array('#markup' => '<h2>Fancy</h2>' . $exampleusers[$item->value]);
      }
    }
    return $elements;
  }

}