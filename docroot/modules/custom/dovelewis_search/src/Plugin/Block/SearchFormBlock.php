<?php
/**
 * @file
 * Contains \Drupal\dovelewis_search\Plugin\Block\SearchFormBlock.
 */

namespace Drupal\dovelewis_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormState;
use Drupal\views\Views;

/**
 * Provides a block to display the Search view exposed filters form.
 *
 * @Block(
 *   id = "dovelewis_search_block",
 *   admin_label = @Translation("Search view exposed filters form"),
 *   category = "Form"
 * )
 */

class SearchFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = [];
    $view_id = 'search';
    $display_id = 'block_search';
    $view = Views::getView($view_id);
    if ($view) {
      $view->setDisplay($display_id);
      $view->initHandlers();
      $form_state = (new FormState())
        ->setStorage([
          'view' => $view,
          'display' => &$view->display_handler->display,
          'rerender' => TRUE,
        ])
        ->setMethod('get')
        ->setAlwaysProcess()
        ->disableRedirect();
      $form_state->set('rerender', NULL);
      $form = \Drupal::formBuilder()->buildForm('\Drupal\views\Form\ViewsExposedForm', $form_state);
      $form['#action'] = '/search';
    }


    return $form;
  }
}
