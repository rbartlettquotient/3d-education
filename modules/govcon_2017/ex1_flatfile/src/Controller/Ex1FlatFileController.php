<?php

namespace Drupal\ex1_flatfile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Ex1FlatFileController extends ControllerBase {

  /**
   * Retrieve contents of example_1.txt file and render as Drupal markup.
   *
   * @param string $path The txt file to read
   *
   * @return array Renderable Drupal array
   *
   * @throws FileNotFoundException If the given path is not a file
   */
  public function content($path) {
    if (!is_file($path)) {
      throw new FileNotFoundException($path);
    }
    else {
      $file_contents = file_get_contents($path);
      return [
        '#type' => 'markup',
        '#markup' => $this->t($file_contents),
      ];
    }
  }

}
