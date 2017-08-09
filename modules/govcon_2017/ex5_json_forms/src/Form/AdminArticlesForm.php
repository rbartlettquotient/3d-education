<?php
/**
 * @file
 * Contains \Drupal\ex5_json_forms\Form\AdminArticlesForm.
 */

namespace Drupal\ex5_json_forms\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\JsonFormsArticle;

class AdminArticlesForm extends FormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'ex5_json_forms.admin_articles';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $model_id = NULL) {

    //@todo- modify tableselect to render properly
    // see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Tableselect.php/class/Tableselect/8.2.x

    $jf = new \Drupal\ex5_json_forms\Ex5JsonFormsArticle();
    $record_values = $jf->adminGetArticles(); // get the articles

    if(isset($record_values->error)) {
      drupal_set_message(t("An exception occurred when loading Article records %err.", array('%err' => $record_values['error'])),
      'error');
      return $form;
    }

    // Build table rows
    if (!empty($record_values)) {
      foreach ($record_values as $k => $row) {
        $record_id = isset($row->id) ? $row->id : NULL;

        if(NULL == $record_id) {
          continue;
        }

        $u = Url::fromRoute('ex5_json_forms.view_article', array('article_id' => $record_id));
        $title_link = new Link($row->title, $u);
        //$title_link = $row->title;

        $links = array();
        $links['edit'] = array(
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('ex5_json_forms.admin_edit_article', array('article_id' => $record_id)),
        );
        $links['delete'] = array(
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('ex5_json_forms.admin_delete_article', array('article_id' => $record_id)),
        );

        $options[$record_id] = array(
          'title' => $title_link,
         // 'schema_type' => $schema_type,
          'operations' => array('data' => array('#type' => 'operations', '#links' => $links)),
        );

      }
    }

    // Table header
    $header = array(
      'title' => array('data' => $this->t('Title'), 'field' => 'title'),
      //'schema_type' => array('data' => $this->t('Record Type'), 'field' => 'schema_type'),
      'operations' => array('data' => ''),
    );

    // could add operations here
    // see example in comment admin:
    // https://api.drupal.org/api/drupal/core%21modules%21comment%21src%21Form%21CommentAdminOverview.php/function/CommentAdminOverview%3A%3AbuildForm/8.2.x

    $form['add_link'] = array(
      '#type' => 'markup',
      '#markup' => '<a href="/admin/content/json_article/add">Add Article</a>',
    );

    // Add table
    $form['schema_records'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No content available.'),
      '#attributes' => array(),
    );

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  //array &$form, Drupal\Core\Form\FormStateInterface $form_state
  public function validateForm(array &$form, FormStateInterface $form_state) {

    //@todo validate

  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    //@todo perform action

//    parent::submitForm($form, $form_state);

    drupal_set_message($this->t('Done doing whatever!'));
  }

} // class AdminArticles

