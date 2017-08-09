<?php

/**
 * @file JsonFormsArticle.php
 *
 * Get and set article data.
 *
 * Used by Ex5JsonFormsController and various forms, for handling article data.
 *
 */

  namespace Drupal\ex5_json_forms {

    use Drupal\ex5_json_forms\Ex5JsonFormsData;

    class Ex5JsonFormsArticle {

      public function __construct() {

      }

      public function adminGetArticles() {

        $jf = new Ex5JsonFormsData();
        $record_data = $jf->readData('article');
        return $record_data;

      } //adminGetArticles

      /** Get the article using article ID */
      public function adminGetArticleById($article_id) {

        $jf = new Ex5JsonFormsData();
        $record_data = $jf->getRecord('article', $article_id);
        return $record_data;

      } // adminGetArticleById

      /** Get schema for article */
      public function adminGetArticleSchema() {
        $jf = new Ex5JsonFormsData();
        $article_schema = $jf->getSchema('article');
        return $article_schema;
      }

      public function getArticleShell() {

        $json_form = new Ex5JsonFormsData();
        $article_structure = $json_form->getRecordShell('article');

        return $article_structure;
      }

      public function validateArticleAgainstSchema($article_data) {

        $json_form = new Ex5JsonFormsData();
        $is_valid = $json_form->validateAgainstSchema('article', $article_data);

        return $is_valid;
      }

      /** Save article */
      public function adminSaveArticle($values_array) {

        $jf = new Ex5JsonFormsData();

        $response = $jf->writeRecord('article', $values_array);
        if(!array_key_exists('id', $response) || !isset($response['id']) || strlen(trim($response['id'])) < 1) {
          return array(
            'error' => 'An error occurred when attempting to save the article. Your changes may not have been saved.',
            'response' => $response
          );
        }

        return $response;

      }

      public function adminDeleteArticle($article_id) {
        $jf = new Ex5JsonFormsData();
        $response = $jf->deleteRecord('article', $article_id);
        return $response;
      }

    } // JsonFormsArticle
  } // namespace

?>