<?php
/**
 * @file
 * Contains \Drupal\dovelewis_menu\Plugin\Block\MobileMenuLogoBlock.
 */

namespace Drupal\dovelewis_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display the DoveLewis logo for use in the mobile menu.
 *
 * @Block(
 *   id = "mobile_menu_logo",
 *   admin_label = @Translation("Mobile Menu Logo"),
 *   category = "Block"
 * )
 */

class MobileMenuLogoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'block__mobile_menu_logo',
    ];
  }
}
