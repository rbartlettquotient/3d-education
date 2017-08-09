<?php
/**
 * @file
 * Contains \Drupal\ex5_json_forms\Form\AdminArticleForm.
 */

namespace Drupal\ex5_json_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\JsonFormsArticle;

class AdminArticleDeleteForm extends FormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'ex5_json_forms.admin_article_delete';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $article_id = NULL, $action = NULL) {
    // like:
    // /article/dpt-2342423-234234234-/delete

    $form['msg'] = array(
      '#type' => 'markup',
      '#markup' => $this->t('Are you sure you want to delete the Article with id %article_id?',
          array('%article_id' => $article_id)),
    );

    $form['id'] = array(
      '#type' => 'hidden',
      '#value' => $article_id,
    );

    $form['save'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
    );

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $article_id = $form_state->getValue('id');

    $article = new \Drupal\ex5_json_forms\Ex5JsonFormsArticle();
    $return = $article->adminDeleteArticle($article_id);

    if(isset($return['error'])) {
      drupal_set_message($this->t($return['error']), 'error');
    }
    else {
      drupal_set_message($this->t('Done deleting!'));
      // redirect to the list of articles
      $form_state->setRedirect('ex5_json_forms.admin_get_all_articles');
      return;

    }

  }

} // class AdminArticle