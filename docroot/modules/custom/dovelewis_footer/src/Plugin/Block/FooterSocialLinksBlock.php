<?php
/**
 * @file
 * Contains \Drupal\dovelewis_footer\Plugin\Block\FooterSocialLinksBlock.
 */

namespace Drupal\dovelewis_footer\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display DoveLewis social media links.
 *
 * @Block(
 *   id = "footer_social_links",
 *   admin_label = @Translation("Footer - Social Links"),
 *   category = "Block"
 * )
 */

class FooterSocialLinksBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'block__footer_social_links',
    ];
  }
}
