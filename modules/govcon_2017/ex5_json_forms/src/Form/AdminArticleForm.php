<?php
/**
 * @file
 * Contains \Drupal\ex5_json_forms\Form\AdminArticleForm.
 */

namespace Drupal\ex5_json_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\JsonFormsArticle;
use Drupal\JsonFormsData;

class AdminArticleForm extends FormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'ex5_json_forms.admin_article';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $article_id = NULL, $action = NULL) {
    // $first will be 'add', or the article Id
    // $second will be blank or an action 'edit' or 'delete'

    // such as:
    // /article/add
    // /article/dpt-2342423-234234234-/edit
    // /article/dpt-2342423-234234234-/delete

    $this_article_data = array();

    $jf = new \Drupal\ex5_json_forms\Ex5JsonFormsArticle();
    $jd = new \Drupal\ex5_json_forms\Ex5JsonFormsData();

    try {
      $articles_schema = $jf->adminGetArticleSchema();

    }
    catch(Exception $ex) {
      drupal_set_message(t("Unable to retrieve article schema: %err", array('%err' => $ex->getMessage())));
      return $form;
    }

    if($action == 'edit' && NULL !== $article_id) {
      try {
        $this_article_data = $jf->adminGetArticleById($article_id);
      }
      catch(Exception $ex) {
        drupal_set_message(t("Unable to retrieve article schema: %err", array('%err' => $ex->getMessage())));
        return $form;
      }
      $this_article_data = $jd->generateFlattenedFromArray($this_article_data);
    }
    $form = $jd->generateDrupalForm($articles_schema, $this_article_data);

    //@todo be nice- if $form is empty, tell the admin why the form can't be generated
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  //array &$form, Drupal\Core\Form\FormStateInterface $form_state
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $jf = new \Drupal\ex5_json_forms\Ex5JsonFormsArticle();
    $jd = new \Drupal\ex5_json_forms\Ex5JsonFormsData();

    $article_array = $jd->generateArrayFromFormValues($form, $form_state);
    $article_array['type'] = 'article';

    if(!$jf->validateArticleAgainstSchema($article_array)) {
      form_set_error("JSON does not validate.");
    }

  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // perform save- new or existing
    $jd = new \Drupal\ex5_json_forms\Ex5JsonFormsData();

    $article_array = $jd->generateArrayFromFormValues($form, $form_state);
    $article_array['type'] = 'article';

    $jf = new \Drupal\ex5_json_forms\Ex5JsonFormsArticle();
    $return = $jf->adminSaveArticle($article_array);

    if(isset($return['error'])) {
      drupal_set_message($this->t($return['error']), 'error');
    }
    else {
      drupal_set_message($this->t('Done saving!'));
      // redirect to the list of articles
      $form_state->setRedirect('ex5_json_forms.admin_get_all_articles');
      return;
    }

  }



} // class AdminArticle