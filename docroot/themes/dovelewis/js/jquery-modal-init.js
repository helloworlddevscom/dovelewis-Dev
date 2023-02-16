/**
 * Initializes jQuery Modal plugin.
 * Loaded as part of jquery-modal theme library.
 */


(function($, Drupal) {
  'use strict';

  var modalInit = false;
  Drupal.behaviors.modalInit = {
    attach: function(context, settings) {
      // Attempt to resolve behavior being called multiple times per page load.
      if (context !== document) {
        return;
      }
      if (!modalInit) {
        modalInit = true;
      }
      else {
        return;
      }

      // Check if component exists.
      var $modal = $('.jquery-modal');
      if ($modal.length) {

        // Check if cookie is already set, meaning user
        // has already seen modal and should not see it again.
        if (!Cookies.get('modal-closed')) {
          $modal.modal();

          // After modal closes.
          $modal.on($.modal.AFTER_CLOSE, function(event, modal) {
            // Set cookie to ensure user does not see modal again.
            Cookies.set('modal-closed', true);
          });
        }
      }
    }
  };

})(jQuery, Drupal);
