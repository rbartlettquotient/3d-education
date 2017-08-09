<?php
/**
 * @file
 * Contains \Drupal\ex5_json_forms\Controller\Ex5JsonFormsController.
 *
 * Controller for admin paths to CRUD Articles
 */

namespace Drupal\ex5_json_forms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\ex5_json_forms\Ex5JsonFormsData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Drupal\JsonFormsModel;

class Ex5JsonFormsController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Constructs an AdminController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * @return string
   */
  public function getTitle() {
    //@todo dynamic title based on the title
    return 'View article content';
  }

  /**
   * Article functions.
   */

  /**
   * Presents an administrative article listing.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   * @param string $type
   *   The type of the overview form ('approval' or 'new') default to 'new'.
   *
   * @return array
   *   The article multiple delete confirmation form or the articles overview
   *   administration form.
   */
  public function viewAllArticlesPage(Request $request) {
    return $this->formBuilder->getForm('\Drupal\ex5_json_forms\Form\AdminArticlesForm', $request);
  }

  public function newArticlePage(Request $request) {
    return $this->formBuilder->getForm('\Drupal\ex5_json_forms\Form\AdminArticleForm', 'add');
  }

  public function editArticlePage(Request $request, $article_id, $action = 'edit') {
    return $this->formBuilder->getForm('\Drupal\ex5_json_forms\Form\AdminArticleForm', $article_id, $action);
  }

  public function deleteArticlePage(Request $request, $article_id, $action = 'delete') {
    return $this->formBuilder->getForm('\Drupal\ex5_json_forms\Form\AdminArticleDeleteForm', $article_id, $action);
  }

  public function viewArticlePage(Request $request, $article_id = NULL) {

    $js = new Ex5JsonFormsData();
    $data = (array)($js->getRecord('article', $article_id));

    $content['#theme'] = 'ex5_json_forms_article_template';
    $content['#article_id'] = $article_id;
    $content['#title'] = $data['title'];
    $content['#data'] = $data;

    return $content;
  }

  public function autocompleteArticleType($vocabulary_name = '', $tags_typed = '') { // Request $request, $article_id = NULL) {

    // If the request has a '/' in the search text, then the menu system will have
    // split it into multiple arguments, recover the intended $tags_typed.
    $args = func_get_args();
    // Shift off the $field_name argument.
    array_shift($args);
    $tags_typed = implode('/', $args);

    // get the vid for this $vocabulary_name
    $vocab_object = _get_vocabulary_by_machinename($vocabulary_name);

    if (NULL == $vocab_object) {
      // Error string. The JavaScript handler will realize this is not JSON and
      // will display it as debugging information.
      print t('Taxonomy vocabulary @vocab_name not found.', array('@vocab_name' => $vocabulary_name));
      exit;
    }

    $vid = $vocab_object->vid;

    // The user enters a comma-separated list of tags. We only autocomplete the last tag.
    $tags_typed = drupal_explode_tags($tags_typed);
    $tag_last = drupal_strtolower(array_pop($tags_typed));

    $term_matches = array();
    if ($tag_last != '') {

      $query = db_select('taxonomy_term_data', 't');
      $query->addTag('translatable');
      $query->addTag('term_access');

      // Do not select already entered terms.
      if (!empty($tags_typed)) {
        $query->condition('t.name', $tags_typed, 'NOT IN');
      }
      // Select rows that match by term name.
      $tags_return = $query
        ->fields('t', array('tid', 'name'))
        ->condition('t.vid', $vid)
        ->condition('t.name', '%' . db_like($tag_last) . '%', 'LIKE')
        ->range(0, 10)
        ->execute()
        ->fetchAllKeyed();

      $prefix = count($tags_typed) ? drupal_implode_tags($tags_typed) . ', ' : '';

      foreach ($tags_return as $tid => $name) {
        $n = $name;
        // Term names containing commas or quotes must be wrapped in quotes.
        if (strpos($name, ',') !== FALSE || strpos($name, '"') !== FALSE) {
          $n = '"' . str_replace('"', '""', $name) . '"';
        }
        $term_matches [$prefix . $n] = check_plain($name);
      }
    }

    drupal_json_output($term_matches);
  }

}