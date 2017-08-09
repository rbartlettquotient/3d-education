<?php

namespace Drupal\ex2_jsonfile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

class Ex2JsonFileController extends ControllerBase {

  /**
   * Retrieve contents of example_2.json file and render as Drupal markup.
   *
   * @param string $path The json file to read
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
      $output = "";
      $file_contents = file_get_contents($path);
      $json = json_decode($file_contents);
      foreach ($json->data as $people => $person) {
        $output .= "<p>Hi, my name is <strong>" . $person->fname . " " . $person->lname . "</strong>!<br>";
        $output .= "My Person ID is <strong>" . $person->id . "</strong> and I can be contacted at: ";
        $output .= "<strong><a href=\"mailto:" . $person->email . "\">" . $person->email . "</a></strong></p>";
      }
      return [
        '#type' => 'markup',
        '#markup' => $this->t($output),
      ];
    }
  }

  /**
   * Retrieve contents of example_2.json file and render as raw JSON.
   *
   * @param string $path The json file to read
   *
   * @return array String containing JSON
   *
   * @throws FileNotFoundException If the given path is not a file
   */
  public function jsonContent($path) {
    if (!is_file($path)) {
      throw new FileNotFoundException($path);
    }
    else {

      $file_contents = str_replace("\n", '', file_get_contents($path));
      $response = new JsonResponse();
      $response->setContent($file_contents);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }
  }

}
