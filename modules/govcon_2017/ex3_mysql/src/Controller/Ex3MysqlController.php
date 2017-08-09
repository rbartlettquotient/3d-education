<?php

namespace Drupal\ex3_mysql\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseException;

class Ex3MysqlController extends ControllerBase {

  /**
   * Queries sample database and renders as Drupal markup.
   *
   * @return array Renderable Drupal array
   */
  public function content() {
    $output = '';

    // Use the external database
    try {
      Database::setActiveConnection('govcon');
      $connection = Database::getConnection();
      $sth = $connection->select('users', 'u')
        ->fields('u', [
          'id',
          'fname',
          'lname',
          'email',
        ]);
      $data = $sth->execute();
      $results = $data->fetchAll(\PDO::FETCH_OBJ);

      foreach ($results as $row) {
        $output .= "<p>Hi, my name is <strong>" . $row->fname . " " . $row->lname . "</strong>!<br>";
        $output .= "My Person ID is <strong>" . $row->id . "</strong> and I can be contacted at: ";
        $output .= "<strong><a href=\"mailto:" . $row->email . "\">" . $row->email . "</a></strong></p>";
      }
    }
    catch (\Exception $e) {
      $output = 'A general exception occurred:<br>' . $e->getMessage();
    }
    catch (\PDOException $e) {
      $output = 'A PDOException occurred:<br>' . $e->getMessage();
    }
    finally {
      // Switch back to the internal database
      Database::setActiveConnection();
    }

    // Return the render array.
    return [
      '#type' => 'markup',
      '#markup' => $this->t($output),
    ];
  }

}
