<?php
/**
 * @file
 * Contains \Drupal\dovelewis_mailchimp\Plugin\Block\MailchimpFormBlock.
 */

namespace Drupal\dovelewis_mailchimp\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display the Mailchimp signup form.
 *
 * @Block(
 *   id = "mailchimp_signup_form",
 *   admin_label = @Translation("Mailchimp signup form"),
 *   category = "Block"
 * )
 */

class MailchimpFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'block__mailchimp_signup_form',
    ];
  }
}
