<?php
/**
 * @file
 * Contains \Drupal\ex7_embed\Controller\Ex7EmbedController.
 *
 * Controller for example fields and formatters.
 *
 * Props to
 * https://www.drupal.org/docs/8/api/entity-api/upgrading-code-snippets-module-to-drupal-8-creating-a-custom-field
 * https://capgemini.github.io/drupal/writing-custom-fields-in-drupal-8/
 */

namespace Drupal\ex7_embed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Utility\Unicode;
use Drupal;

class Ex7EmbedController extends ControllerBase {


  /**
   * Returns response for the exampleuser name autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for example users.
   */
  public function user_autocomplete(Request $request) {
    $string = $request->query->get('q');
    if ($string) {
      $exampleusers = getExampleUsersList();
      foreach ($exampleusers as $email => $name) {
        $all_data = $email . ' ' . $name;
        if (strpos(Unicode::strtolower($all_data), Unicode::strtolower($string)) !== FALSE) {
          $matches[] = array('value' => $email, 'label' => $name);
        }
      }
    }

    return new JsonResponse($matches);
  }


}
