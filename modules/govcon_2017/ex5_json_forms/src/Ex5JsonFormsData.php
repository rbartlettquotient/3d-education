<?php

/**
 * @file JsonFormsData.php
 *
 * Used to auto-generate Drupal forms from a schema, and validate data against a schema.
 *
 */

namespace Drupal\ex5_json_forms {

  class Ex5JsonFormsData {

    public function __construct() {

    }

    /** Get schema for the supplied content type. */
    public function getSchema($content_type) {

      $schema_json = NULL;

      $json_path = drupal_get_path('module', 'ex5_json_forms') . '/ex5-json-forms-' . $content_type . '.schema.json';
      if (!file_exists($json_path)) {
        throw new \ErrorException('Missing JSON file for schema ' . $content_type . '.');
      }
      else {
        $schema_json_string = file_get_contents ($json_path);
        try {
          $schema_json = json_decode($schema_json_string);
        }
        catch(\ErrorException $ex2) {
          throw new \ErrorException('Error reading JSON from file for schema ' . $content_type . ': ' . $ex2->getMessage());
        }
      }

      return $schema_json;
    }

    /** Given a content type, generate an empty shell structure for data for this content type. */
    public function getRecordShell($content_type) {

      $data_structure = array();
      //@todo- return the minimum viable structure for the specified $content_type

      return $data_structure;
    }

    public function getRecord($content_type, $id) {

      $json_data = $this->readData($content_type);

      $json_record = NULL;
      foreach($json_data as $j) {
        if(isset($j->id) && $j->id == $id) {
          $json_record = $j;
          break;
        }
      }
      return $json_record;
    }

    public function writeRecord($content_type, $json_record) {
      $json_data = $this->readData($content_type);

      $new_json_data = array();
      $found_record = FALSE;

      foreach($json_data as $j) {
        if(array_key_exists('id', $json_record) && isset($json_record['id']) && $j->id == $json_record['id']) {
          $new_json_data[] = $json_record;
          $found_record = TRUE;
        }
        else {
          $new_json_data[] = $j;
        }
      }

      if(FALSE == $found_record) {
        if(!isset($json_record['id'])) {
          $json_record['id'] = 'article-' . rand(11111, 9999999) . time();
        }
        $new_json_data[] = $json_record;
      }

      $this->writeData($content_type, $new_json_data);

      return $json_record;
    }

    public function deleteRecord($content_type, $content_id) {

      $json_data = $this->readData($content_type);
      $new_json_data = array();
      foreach($json_data as $j) {
        if(isset($j->id) && $j->id == $content_id) {
          // skip this to delete it
        }
        else {
          $new_json_data[] = $j;
        }

      }

      $this->writeData($content_type, $new_json_data);

    }

    public function readData($content_type) {

      $json_data = NULL;

      $json_path = drupal_get_path('module', 'ex5_json_forms') . '/ex5-json-forms-' . $content_type . '.json';
      if (!file_exists($json_path)) {
        throw new \ErrorException('Missing JSON file for ' . $content_type . ' data.');
      }
      else {
        $json_string = file_get_contents($json_path);
        try {
          $json_data = json_decode($json_string);
        }
        catch(\ErrorException $ex2) {
          throw new \ErrorException('Error reading JSON from file for ' . $content_type . ' content: ' . $ex2->getMessage());
        }
      }

      return $json_data;
    }

    public function writeData($content_type, $json_data) {

      //@todo test: is this really JSON data?

      $json_path = drupal_get_path('module', 'ex5_json_forms') . '/ex5-json-forms-' . $content_type . '.json';
      try {
        $json_string = json_encode($json_data);
        file_put_contents($json_path, $json_string);
      }
      catch(\ErrorException $ex2) {
        throw new \ErrorException('Error writing JSON to file for ' . $content_type . ' content: ' . $ex2->getMessage());
      }

    }

    /**
     * Validate $content_data against the schema indicated by $content_type.
     * @param $content_type
     * @param $content_data
     * @return bool
     */
    public function validateAgainstSchema($content_type, $content_data) {

      $is_valid = TRUE;

      $schema = $this->getSchema($content_type);

      //@todo- is $content_data valid given our schema?

      return $is_valid;
    }

    public function generateArrayFromFormValues(&$form, $form_state) {

      $field_values = array();

      $jf = new \Drupal\ex5_json_forms\Ex5JsonFormsArticle();

      try {
        $articles_schema = $jf->adminGetArticleSchema();

        if(isset($articles_schema->schema->properties)) {
          $article_properties_temp = $articles_schema->schema->properties;

          foreach($article_properties_temp as $temp_name => $temp_data) {
            if(!isset($temp_data->properties) && !empty($temp_data)) {
              if(strlen(trim($form_state->getValue($temp_name))) > 0) {
                $field_values[$temp_name] = $form_state->getValue($temp_name);
              }
              if(isset($temp_data->fields)) {

                foreach($temp_data->fields as $temp_child_name => $temp_child_data) {
                  if(!isset($temp_child_data->properties) && !empty($temp_child_data)) {

                    $fv = $form_state->getValue($temp_name . '__' . $temp_child_name);
                    if(isset($fv)) {
                      if(!is_array($fv) && strlen($fv) > 0) {
                        $field_values[$temp_name][$temp_child_name] = $fv;
                      }
                      else {
                        $field_values[$temp_name][$temp_child_name] = $fv['value'];
                      }
                    }

                  }
                }
              }
            }

          }
        } // if we got some properties of the schema
      }
      catch(Exception $ex) {
        drupal_set_message(t("Unable to retrieve article schema: %err", array('%err' => $ex->getMessage())));
        return $field_values;
      }

      return $field_values;
    }

    public function generateFlattenedFromArray($field_values) {

      $flattened = array();

      /**
       * Flatten the array to just name-value pairs
       * We have an array like:
       * content => (
       *  'title' => 'blah',
       *  'legacy_data' => array(
       *    ...
       *   ),
       *  ...
       * ),
       *
       */

      if(isset($field_values) && count($field_values) > 0) {
        $flattened = $this->_flatten_values($field_values);
      } // if we got some properties of the schema

      return $flattened;
    }

    private function _flatten_values($var, $parent_var = NULL) {

      $flat = array();

      // loop through
      if(is_object($var)) {
        foreach($var as $k => $v) {
          if( !is_object($v)) {
            if(NULL == $parent_var) {
              $flat[$k] = $v;
            }
            else {
              $flat[$parent_var][$k] = $v;
            }
          }
          else {
            $flat += $this->_flatten_values($v, $k);
          }
        }
      }

      return $flat;
    }

    private function _generateFormField($field_data) {

      $f = array();
      $field_name = isset($field_data->parent_field)
        ? $field_data->parent_field . '__' . $field_data->field_name
        : $field_data->field_name;

      if(is_object($field_data) && isset($field_data->ux) && is_object($field_data->ux)) {
        switch($field_data->ux->field_type) {
          case 'hidden':
            $f[$field_name] = array(
              '#type' => 'hidden'
            );
            break;

          case 'fieldset':
            $f[$field_name] = array(
              '#type' => 'fieldset',
              '#title' => isset($field_data->label) ? $field_data->label : $field_name
            );
            break;

          case 'textarea':
            $f[$field_name] = array(
              '#type' => 'textarea',
              '#title' => isset($field_data->label) ? $field_data->label : $field_name
            );
            break;

          case 'text_format':
            $f[$field_name] = array(
              '#type' => 'text_format',
              '#format' => 'full_html',
              '#title' => isset($field_data->label) ? $field_data->label : $field_name
            );

            break;

          case 'text':
            //@todo - no, use multiple textareas; for demo we separate by linebreak
            if(isset($field_data->multiple) && TRUE == $field_data->multiple) {
              $f[$field_name] = array(
                '#type' => 'textarea',
                '#title' => isset($field_data->label) ? $field_data->label : $field_name
              );
            }
            else {
              $f[$field_name] = array(
                '#type' => 'textfield',
                '#title' => isset($field_data->label) ? $field_data->label : $field_name
              );
            }
            break;

          case 'radios':
            // look for enum
            $values = array(1 => "true", 0 => "false");
            if(isset($field_data->enum)) {
              $values = array();
              // really Drupal??
              $vals = (array)$field_data->enum;
              foreach($vals as $k => $v) {
                $newk = (string)$k;
                $values[$newk] = $v;
              }

            }
            $f[$field_name] = array(
              '#type' => 'radios',
              '#title' => isset($field_data->label) ? $field_data->label : $field_name,
              '#options' => $values
            );

            break;
          default:
            break;
        }
      }

      if(isset($field_data->default_value)) {
        $f[$field_name]['#default_value'] = $field_data->default_value;
      }

      if(isset($field_data->help_text)) {
        $f[$field_name]['#description'] = $field_data->help_text;
      }

      return $f;

    }

    /** Generate a Drupal form based on the structure defined by $schema_json, and the data in $json_data.
     * @param $schema_json
     * @param $json_data
     */
    public function generateDrupalForm($schema_json, $json_data) {

      // loop through the schema, generating the form
      $form = array();
      if(isset($schema_json->schema->properties) && isset($schema_json->authoringForm)) {

        $author_form = $schema_json->authoringForm;

        $i = 0;
        foreach ($author_form as $field_name => $field_or_fieldset) {

          $fieldset = NULL;
          if (isset($field_or_fieldset->fields)) {
            // create the fieldset
            $field = $field_or_fieldset;
            $field->field_name = $field_name;
            if (isset($json_data[$field_name])) {
              $field->default_value = $json_data[$field_name];
            }
            $f = $this->_generateFormField($field);
            if (!empty($f)) {
              $form += $f;
            }

            // create the child fields
            $parent_field = $field_name;
            foreach ($field_or_fieldset->fields as $child_field_name => $child_field) {
              $field = $child_field;
              $field->field_name = $child_field_name;
              if (isset($json_data[$parent_field][$child_field_name])) {
                if (is_array($json_data[$parent_field][$child_field_name])) {
                  $field->default_value = implode('\r\n', $json_data[$parent_field][$child_field_name]);
                } else {
                  $field->default_value = $json_data[$parent_field][$child_field_name];
                }
              } elseif (isset($json_data[$parent_field][0][$child_field_name])) {
                $field->default_value = $json_data[$parent_field][0][$child_field_name];
              }

              $field->parent_field = $parent_field;

              $f = $this->_generateFormField($field);
              if (!empty($f)) {
                $form[$field_name][] = $f;
              }

            }
          } elseif (isset($field_or_fieldset->ux->field_type)) {
            // add item to $form array
            $field = $field_or_fieldset;
            $field->field_name = $field_name;
            if (isset($json_data[$field_name])) {
              $field->default_value = $json_data[$field_name];
            } elseif (isset($json_data['content'][$field_name])) {
              $field->default_value = isset($json_data['content'][$field_name]) ? $json_data['content'][$field_name] : '';
            }
            $f = $this->_generateFormField($field);
            if (!empty($f)) {
              $form += $f;
            }

          }

          $i++;
        }

        $form['save'] = array(
          '#type' => 'submit',
          '#value' => t('Save'),
        );
      }

      return $form;
    }

  } // JsonFormsData
} // namespace

?>