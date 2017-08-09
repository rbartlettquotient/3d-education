<?php

/**
 * @file
 * Contains \Drupal\ex5_json_forms\Ex5JsonFormsBreadcrumbBuilder.
 */

namespace Drupal\ex5_json_forms;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class to define the breadcrumb builder.
 */
class Ex5JsonFormsBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * The storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs the JsonFormsBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
    public function __construct(EntityManagerInterface $entity_manager) {
      # $this->storage = $entity_manager->getStorage('ex5_json_forms_articles');
    }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumbs = new Breadcrumb();
    $breadcrumbs->addCacheContexts(['route']);
    $breadcrumbs->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumbs->addLink(Link::createFromRoute($this->t('Content'), '/admin/content'));

    return $breadcrumbs;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {

    #    return $route_match->getRouteName() == 'comment.reply' && $route_match->getParameter('entity');
#    if($route_match->getRouteName() == 'ex5_json_forms.admin_get_models') {
#      return TRUE;
#    }

    return FALSE;
  }


}
