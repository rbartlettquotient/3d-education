<?php

/**
 * @file
 * Contains \Drupal\Tests\replicate_ui\Functional\ReplicateUISettingsTest.
 */

namespace Drupal\Tests\replicate_ui\Functional;

use Drupal\simpletest\BrowserTestBase;

/**
 * Tests the replicate settings UI.
 *
 * @group replicate_ui
 */
class ReplicateUISettingsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['replicate', 'replicate_ui', 'node'];

  public function testSettings() {
    $this->drupalGet('/admin/config/content/replicate');
    $this->assertEquals(403, $this->getSession()->getDriver()->getStatusCode());

    $account = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/content/replicate');
    $this->assertEquals(200, $this->getSession()->getDriver()->getStatusCode());

    $this->submitForm(['entity_types[node]' => 'node'], 'Save configuration');
    $this->assertEquals(['node'], \Drupal::configFactory()->get('replicate_ui.settings')->get('entity_types'));
  }

}
