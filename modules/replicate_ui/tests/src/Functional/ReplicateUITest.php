<?php

/**
 * @file
 * Contains \Drupal\Tests\replicate_ui\Functional\ReplicateUITest.
 */

namespace Drupal\Tests\replicate_ui\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\AssertContentTrait;
use Drupal\simpletest\BlockCreationTrait;
use Drupal\simpletest\BrowserTestBase;

/**
 * Tests the UI functionality
 *
 * @group replicate
 */
class ReplicateUITest extends BrowserTestBase {

  use BlockCreationTrait;

  use AssertContentTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['replicate', 'replicate_ui', 'node', 'block'];

  /**
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();


    $this->user = $this->drupalCreateUser(['bypass node access', 'administer nodes', 'replicate entities']);
    $node_type = NodeType::create([
      'type' => 'page',
    ]);
    $node_type->save();
    $this->node = Node::create([
      'title' => 'test title',
      'type' => 'page',
    ]);
    $this->node->save();

    $this->placeBlock('local_tasks_block');
    $this->placeBlock('system_messages_block');
    \Drupal::configFactory()->getEditable('replicate_ui.settings')->set('entity_types', ['node'])->save();
    \Drupal::service('router.builder')->rebuild();
    Cache::invalidateTags(['entity_types']);
  }

  public function testFunctionality() {
    $result = $this->drupalGet($this->node->toUrl()->toString(TRUE)->getGeneratedUrl());
    $this->assertFalse(strpos($result, 'Replicate'));

    $this->drupalLogin($this->user);
    $result = $this->drupalGet($this->node->toUrl()->toString(TRUE)->getGeneratedUrl());
    $this->assertTrue(strpos($result, 'Replicate') !== FALSE);
    $this->assertEquals(200, $this->getSession()->getDriver()->getStatusCode());

    $this->getSession()->getPage()->clickLink('Replicate');
    $this->assertEquals(200, $this->getSession()->getDriver()->getStatusCode());

    $this->getSession()->getPage()->pressButton('Replicate');
    $result = $this->getSession()->getPage()->getContent();
    $this->assertTrue(strpos($result, '<em class="placeholder">node</em> (<em class="placeholder">1</em>) has been replicated to id <em class="placeholder">2</em>!') !== FALSE);
  }

}
