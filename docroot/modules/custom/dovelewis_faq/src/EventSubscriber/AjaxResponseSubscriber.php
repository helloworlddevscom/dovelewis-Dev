<?php

namespace Drupal\dovelewis_faq\EventSubscriber;

use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle AJAX responses.
 *
 * Prevents the faqs_landing view from scrolling to top on Ajax response.
 */
class AjaxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();

    if (!($response instanceof ViewAjaxResponse)) {
      return;
    }

    $view = $response->getView();

    // Only alter commands if view is FAQ Landing.
    if ($view->storage->id() != 'faqs_landing') {
      return;
    }

    $commands = &$response->getCommands();
    $this->alterCommands($commands);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

  /**
   * Alter the views AJAX response commands.
  ﻿ * @param array $commands
   */
  protected function alterCommands(&$commands) {
    foreach ($commands as $delta => &$command) {
      // Remove the viewsScrollTop command.
      if (isset($command['command']) && $command['command'] === 'viewsScrollTop') {
        unset($commands[$delta]);
      }
    }
  }
}
