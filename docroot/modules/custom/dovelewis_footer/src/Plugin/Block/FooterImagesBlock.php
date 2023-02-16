<?php
/**
 * @file
 * Contains \Drupal\dovelewis_footer\Plugin\Block\FooterImagesBlock.
 */

namespace Drupal\dovelewis_footer\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display random animal images.
 *
 * @Block(
 *   id = "footer_images",
 *   admin_label = @Translation("Footer - Images"),
 *   category = "Block"
 * )
 */

class FooterImagesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'block__footer_images',
    ];
  }
}
