<?php

namespace Drupal\ex6_jsonfile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Ex6JsonFileController extends ControllerBase {

  /**
   * Retrieve JSON from an API endpoint.
   *
   * @return array Renderable Drupal array
   *
   * @throws FileNotFoundException If the given path is not a file
   */
  public function content() {
    $host = \Drupal::request()->getHost();
    $port = \Drupal::request()->getPort();

    $json_in = $this->sendRequest('http://' . $host . ':' . $port . '/ex2_json');

    $json = json_decode($json_in);
    $output = '<p>This data is coming from http://' . $host . ':' . $port . '/ex2_json';
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

  public function sendRequest($endpoint, $POST = FALSE) {

    $ch = curl_init();

    if ($POST === TRUE) {
      curl_setopt($ch, CURLOPT_URL, $endpoint);
      curl_setopt($ch, CURLOPT_POST, 1);
    }
    else {
      curl_setopt($ch, CURLOPT_URL, $endpoint);
    }

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);

    if ($info['http_code'] == 200) {
      $this->valid_request = TRUE;
    }
    else {
      $this->valid_request = FALSE;
    }

    curl_close($ch);

    return $response;
  }

}
