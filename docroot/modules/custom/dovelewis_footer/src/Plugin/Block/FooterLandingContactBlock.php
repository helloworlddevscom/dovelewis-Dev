<?php
/**
 * @file
 * Contains \Drupal\dovelewis_footer\Plugin\Block\FooterLandingContactBlock.
 */

namespace Drupal\dovelewis_footer\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display contact information.
 *
 * @Block(
 *   id = "footer_landing_contact",
 *   admin_label = @Translation("Footer Landing - Contact"),
 *   category = "Block"
 * )
 */

class FooterLandingContactBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'block__footer_landing_contact',
    ];
  }
}
