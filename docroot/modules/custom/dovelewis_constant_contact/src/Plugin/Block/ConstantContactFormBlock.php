<?php
/**
 * @file
 * Contains \Drupal\dovelewis_constant_contact\Plugin\Block\ConstantContactFormBlock.
 */

namespace Drupal\dovelewis_constant_contact\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block to display the Constant Contact signup form.
 *
 * @Block(
 *   id = "constant_contact_signup_form",
 *   admin_label = @Translation("Constant Contact signup form"),
 *   category = "Block"
 * )
 */

class ConstantContactFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\dovelewis_constant_contact\Form\ConstantContactForm');

    return [
      'form' => $form,
      '#theme' => 'block__constant_contact_signup_form',
    ];
  }
}
