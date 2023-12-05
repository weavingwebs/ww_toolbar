<?php

namespace Drupal\ww_toolbar;

use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;

/**
 * WW Toolbar menu builder.
 */
class WwToolbarMenuBuilder {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $activeTrail;

  /**
   * Constructs a new WwToolbarMenuBuilder object.
   */
  public function __construct(
    MenuLinkTreeInterface $menu_link_tree,
    MenuActiveTrailInterface $menu_active_trail
  ) {
    $this->menuTree = $menu_link_tree;
    $this->activeTrail = $menu_active_trail;
  }

  /**
   * Build a menu.
   *
   * @see \Drupal\system\Plugin\Block\SystemMenuBlock::build().
   *
   * @param string $menu_name
   *   The id of the menu to build.
   *
   * @return array
   *   A drupal_render() array.
   */
  public function build($menu_name) {
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    // Adjust the menu tree parameters based on the block's configuration.
    $level = 1;
    $depth = 1;
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    // For menu blocks with start level greater than 1, only show menu items
    // from the current active trail. Adjust the root according to the current
    // position in the menu in order to determine if we can show the subtree.
    if ($level > 1) {
      if (count($parameters->activeTrail) >= $level) {
        // Active trail array is child-first. Reverse it, and pull the new menu
        // root based on the parent of the configured start level.
        $menu_trail_ids = array_reverse(array_values($parameters->activeTrail));
        $menu_root = $menu_trail_ids[$level - 1];
        $parameters->setRoot($menu_root)->setMinDepth(1);
        if ($depth > 0) {
          $parameters->setMaxDepth(min($level - 1 + $depth - 1, $this->menuTree->maxDepth()));
        }
      }
      else {
        return [];
      }
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    return $this->menuTree->build($tree);
  }

}
