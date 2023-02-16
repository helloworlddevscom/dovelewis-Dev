/**
 * Global JS file.
 */

(function($, Drupal) {
  'use strict';


  /**
   * Example of a Drupal behavior.
   * Copy this as a start.
   */
  var sampleBehavior = false;
  Drupal.behaviors.sampleBehavior = {
    attach: function(context, settings) {
      // Attempt to resolve behavior being called multiple times per page load.
      if (context !== document) {
        return;
      }
      if (!sampleBehavior) {
        sampleBehavior = true;
      }
      else {
        return;
      }

      // Check if component exists.
      if ($('.example-component').length) {
        // Code here.
      }
    }
  };


  /**
   * Attempt to prevent CSS animations until after DOM load.
   */
  Drupal.behaviors.preventAnimations = {
    attach: function(context, settings) {
      $(window).ready(function() {
        $('body').removeClass('preload');
      });
    }
  };


  /**
   * Desktop main menu open and close.
   */
  Drupal.behaviors.desktopMenuToggles = {
    attach: function(context, settings) {
      var $main_menu = $('.region--header-bottom .menu--main--top-level');
      var $main_menu_dropdown = $('.region--header-dropdown');
      var $menu_items = $main_menu.find('.menu-item--dropdown-toggle');
      var open_class = 'desktop-menu-open';
      var open_item_class = 'menu-item--open';
      var $search_toggle = $('.search-toggle');
      var search_open_class = 'desktop-search-open';
      var $open_menu_item = false;
      var $open_dropdown_item = false;
      var open_item_number_class;
      var open_method;
      var open = false;
      var search_open = false;
      // var $search_form = $('.header__top [data-module="search"][data-delta="form"]');


      // Main menu dropdown toggles.
      $menu_items.each(function() {

        // Prevent click event on top level menu item links.
        $(this).find('a').on('click tap touch', function(e) {
          e.preventDefault();
        });

        // Click event.
        $(this).off().on('click.desktopMenuToggle tap.desktopMenuToggle touch.desktopMenuToggle', function(e) {
          e.stopPropagation();

          var $menu_item = $(this);

          // Clicked menu item is not already open.
          if ($menu_item[0] != $open_menu_item[0]) {
            if (open) {
              // Close open menu item.
              closeMenu();
            }

            // Open this menu item.
            open_method = 'click';
            openMenu($(this), $menu_item);
          }
          // Clicked menu item is already open.
          else {
            if (open_method == 'click') {
              // Close this open menu item.
              closeMenu();
            }
            else {
              open_method = 'click';
              $main_menu_dropdown.off();
            }
          }
        });

        // Hover event.
        // $(this).on('mouseenter.desktopMenuToggle', function() {
        //   var $menu_item = $(this).closest('.menu-item');
        //
        //   if (open_method != 'click' && !$menu_item.hasClass(open_item_class)) {
        //     // Close open menu item.
        //     if (open) {
        //       closeMenu();
        //     }
        //
        //     // Open this menu item.
        //     open_method = 'hover';
        //     openMenu($(this), $menu_item);
        //   }
        // });
      });

      function openMenu($toggle, $menu_item) {
        // Close search if open.
        if (search_open) {
          closeSearch();
        }

        // Get menu item number.
        var menu_item_classes = $menu_item.attr('class').split(' ');
        $.each(menu_item_classes, function (index, item_class) {
          var class_split = item_class.split('--');

          if (class_split[1]) {
            var integer = parseInt(class_split[1]);
            if (Number.isInteger(integer)) {
              open_item_number_class = item_class;
            }
          }
        });

        // Add open classes.
        $('body').addClass(open_class);
        $open_menu_item = $menu_item;
        $open_menu_item.addClass(open_item_class);
        $open_dropdown_item = $main_menu_dropdown.find('.menu-level-0 > .' + open_item_number_class);
        $open_dropdown_item.addClass(open_item_class);

        // Setup close events.
        $main_menu_dropdown.off();
        $(document).off('click.desktopMenuClose tap.desktopMenuClose touch.desktopMenuClose', handleOpenMenuClick);
        //
        // if (open_method == 'hover') {
        //   $main_menu_dropdown.on('mouseleave.desktopMenuClose', function () {
        //     if (open) {
        //       closeMenu();
        //     }
        //   });
        // }

        $(document).on('click.desktopMenuClose tap.desktopMenuClose touch.desktopMenuClose', handleOpenMenuClick);

        open = true;
      }

      function closeMenu() {
        $main_menu_dropdown.off();
        $('body').removeClass(open_class);
        $open_menu_item.removeClass(open_item_class);
        $open_dropdown_item.removeClass(open_item_class);
        $(document).off('click.desktopMenuClose tap.desktopMenuClose touch.desktopMenuClose');

        open = false;
        open_method = null;
        $open_menu_item = false;
        $open_dropdown_item = false;
      }

      function handleOpenMenuClick(e) {
        e.stopPropagation();

        // Check if the clicked area is outside menu.
        if (!$(e.target).closest($main_menu_dropdown).length && !$(e.target).closest('.menu-item--dropdown-toggle').length) {
          if (open) {
            closeMenu();
          }
          e.preventDefault();
        }
      }

      // Search.
      $search_toggle.on('click.searchMenuToggle tap.searchMenuToggle touch.searchMenuToggle', function() {
        // Close.
        if (search_open) {
          closeSearch();
        }
        // Open.
        else {
          if (open) {
            closeMenu();
          }

          // $('body').addClass(open_class);
          $('body').addClass(search_open_class);
          search_open = true;

          $main_menu_dropdown.mouseleave(function() {
            $(document).off('click.searchClose tap.searchClose touch.searchClose', handleSearchClick);
            $(document).on('click.searchClose tap.searchClose touch.searchClose', handleSearchClick);
          });
        }
      });

      function handleSearchClick(e) {
        e.stopPropagation();

        // Check if the clicked area is outside menu.
        if (!$(e.target).closest($main_menu_dropdown).length) {
          $main_menu_dropdown.off();
          closeSearch();
        }
      }

      function closeSearch() {
        // $('body').removeClass(open_class);
        $('body').removeClass(search_open_class);
        $(document).off('click.searchClose tap.searchClose touch.searchClose');
        search_open = false;
      }
    }
  };


  /**
   * Sets position of mobile menu toggle button.
   */
  Drupal.behaviors.mobileMenuTogglePos =  {
    attach: function(context, settings) {
      var $menu_toggle = $('.mobile-menu-toggle');
      var $header = $('.region--header-bottom__inner.grid-container');
      var breakpoint = 1280;
      var window_width = $(window).width();
      var orig_pos_top = $menu_toggle.offset().top;

      if (window_width < breakpoint) {
        setPosRight();
      }

      $(window).scroll(function() {
        if (window_width < breakpoint) {
          var pos = $(window).scrollTop();

          if (pos >= orig_pos_top - 20) {
            $menu_toggle.addClass('fixed');
          }
          else {
            $menu_toggle.removeClass('fixed');
          }
        }
      });

      $(window).resize(function() {
        window_width = $(window).width();

        if (window_width < breakpoint) {
          setPosRight();
        }
      });

      function setPosRight() {
        var header_pos_left = $header.offset().left;
        var header_width = $header.width();
        var header_right_pad = parseFloat($header.css('padding-right').replace('px', ''));
        var toggle_pos_right = window_width - (header_pos_left + header_width) - header_right_pad;
        $menu_toggle.css('right', toggle_pos_right);
      }
    }
  };


  /**
   * Duplicates parent of mobile menu items and inserts them as a child of itself.
   *
   * @TODO: There is a backend attempt at this, but it only works when render cache is turned off.
   * See: dovelewis_menu.module dovelewis_menu_preprocess_menu__extras__main_menu().
   */
  Drupal.behaviors.mobileMenuDuplicate = {
    attach: function (context, settings) {
      var $menu = $('.menu--main--mobile');

      $menu.once('mobileMenuDuplicate').find('.dropdown-toggle').each(function() {
        var $this_context = $(this);
        var $short_title = $this_context.children('.title--short').clone();
        var $long_title = $this_context.children('.title--long').clone();
        var $menu_level_1 = $this_context.closest('.menu-item__inner').children('.menu-level-1');
        var $new_menu_item = $('<div class="menu-item menu-item--parent-duplicate"><div class="menu-item__inner"><div class="menu-item__inner__parent-wrapper"></div></div></div>');
        $new_menu_item.find('.menu-item__inner__parent-wrapper').prepend($short_title);
        $new_menu_item.find('.menu-item__inner__parent-wrapper').prepend($long_title);
        $new_menu_item.prependTo($menu_level_1);
      });
    }
  };


  /**
   * Toggles mobile menu open/close.
   */
  Drupal.behaviors.mobileMenuToggle =  {
    attach: function(context, settings) {
      var $menu_toggle = $('.mobile-menu-toggle');
      var $menu = $('.mobile-menu');
      var open_class = 'open';
      var body_open_class = 'mobile-menu-open';
      var open = false;

      $menu_toggle.on('click.mobileMenuToggle tap.mobileMenuToggle touch.mobileMenuToggle', function() {
        if (open) {
          $('body').removeClass(body_open_class).removeClass('js-stop-scroll');
          $menu.removeClass(open_class);

          if (/ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase()) ) {
            $('body').css('position', 'initial');
          }

          open = false;
        }
        else {
          $('body').addClass(body_open_class).addClass('js-stop-scroll');
          $menu.addClass(open_class);

          if (/ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase()) ) {
            $('body').css('position', 'fixed');
          }

          open = true;
        }
      });
    }
  };


  /**
   * Toggles mobile menu item open/close.
   */
  Drupal.behaviors.mobileMenuItemToggle =  {
    attach: function(context, settings) {
      var $menu = $('.menu--main--mobile');
      var $menu_item_toggle = $menu.find('.menu-level-0 > .menu-item > .menu-item__inner > .dropdown-toggle');
      var open_class = 'open';

      // Prevent click event on top level menu item links.
      $menu_item_toggle.find('> a').on('click tap touch', function(e) {
        e.preventDefault();
      });

      $menu_item_toggle.off().on('click.mobileMenuItemToggle tap.mobileMenuItemToggle touch.mobileMenuItemToggle', function() {
        var $menu_item = $(this).closest('.menu-item');

        if ($menu_item.hasClass(open_class)) {
          $menu_item.removeClass(open_class);
        }
        else {
          $menu_item.addClass(open_class);
        }
      });
    }
  };


  /**
   * Attempt to prevent body from scrolling when jQuery UI modal dialog opens.
   */
  Drupal.behaviors.modalStopBodyScroll = {
    attach: function(context, settings) {
      $(window).once('dialogOpenEvent').on("dialogopen", function(event, ui) {
        $('body').addClass('js-stop-scroll');

        // Scroll modal to top because it likes to open halfway scrolled down in IOS.
        $('#drupal-modal').scrollTop(0);
        // Getting really desperate here...
        setTimeout(function() {
          $('#drupal-modal').scrollTop(0);
        }, 20);

        // Add extra position fixed in IOS because IOS makes it extremely
        // difficult to stop the body from scrolling.
        if (/ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase()) ) {
          $('body').css('position', 'fixed');
        }
      });

      $(window).once('dialogCloseEvent').on("dialogclose", function(event, ui) {
        $('body').removeClass('js-stop-scroll');

        if (/ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase()) ) {
          $('body').css('position', 'initial');
        }
      });
    }
  };


  /**
   * Loads a random image in the site footer.
   */
  Drupal.behaviors.randomFooterImage = {
    attach: function(context, settings) {
      if ($('.footer-image img').length > 0) {
        $('.footer-image img').attr('src', '/themes/dovelewis/images/footer-images/random-images/' + getRandomImage());
      }

      /*
      return random int between min and max (inclusive)
      easier updating array, works as long as the images are
      just 'integer.svg' then we adjust max to account for more images
      */
      function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

      function getRandomImage() {
        return getRandomInt(1, 8) + '.svg';
      }
    }
  };


  /**
   * Rearranges footer menu block at breakpoint.
   */
  Drupal.behaviors.footerReorder = {
    attach: function(context, settings) {
      var breakpoint = 767;
      var $footer_menu_block = $('#block-dovelewis-footer');
      var moved = false;

     if ($(window).width() <= breakpoint) {
       moveToTop();
     }

      $(window).resize(function() {
        if ($(window).width() <= breakpoint) {
          if (!moved) {
           moveToTop();
          }
        }
        else {
          if (moved) {
            moveToOrig();
          }
        }
      });

     function moveToTop() {
       $footer_menu_block.prependTo('.region--footer-right-top').addClass('js-moved');
       moved = true;
     }

     function moveToOrig() {
       $footer_menu_block.prependTo('.region--footer-left').removeClass('js-moved');
       moved = false;
     }
    }
  };


  /**
   * Initializes Foundation Accordion on .accordion elements.
   * Note that you will also need to include foundation-accordion library in twig template.
   * See: views-view-unformatted--faqs-landing.html.twig.
   * @TODO: Move this to seperate file. See Overlake.
   */
  Drupal.behaviors.foundationAccordionInit = {
    attach: function (context, settings) {
      if ($('.view-id--faqs_landing .accordion').length > 0) {
        var options = {
          multiExpand: true,
          allowAllClosed: true,
          deepLink: true,
          // deepLinkSmudge: true,
          // deepLinkSmudgeDelay: 100,
          // updateHistory: true,
        };
        var accordion = new Foundation.Accordion($('.accordion'), options);
      }
      else if ($('.paragraph--type--faqs.paragraph--view-mode--faq-accordion .accordion').length > 0) {
        var options = {
          multiExpand: true,
          allowAllClosed: true,
        };
        var accordion = new Foundation.Accordion($('.accordion'), options);
      }
    }
  };


  /**
   * Initializes Foundation Orbit slider on .orbit elements.
   * Note that you will also need to include foundation-orbit library in twig template.
   * See: field--field-header-carousel.html.twig.
   * @TODO: Move this to seperate file. See Overlake.
   */
  Drupal.behaviors.foundationOrbitInit = {
    attach: function (context, settings) {
      if ($('.orbit').length > 0) {
        var options = {
          autoPlay: false,
          swipe: true,
          accessible: true,
        };

        var orbitSlider = new Foundation.Orbit($('.orbit'), options);
      }
    }
  };


  /**
   * Loads a random image in the "Donate Today" block.
   */
  Drupal.behaviors.randomDonateTodayBlockImage = {
    attach: function(context, settings) {
      if ($('.block-id--donatetoday').length > 0) {
        var img = new Image();
        img.src = '/themes/dovelewis/images/donate-today-block-images/' + getRandomImage();

        img.onload = function() {
          $('.block-id--donatetoday .animal-image img').once('randomimage').attr('src', img.src);
          $('.block-id--donatetoday .animal-image img').addClass('show');
        }
      }

      function getRandomImage() {
        var images = ['1.png', '2.png', '3.png'];
        return images[Math.floor(Math.random() * images.length)];
      }
    }
  };


  /**
   * Polyfill for isInteger() method for IE 11.
   */
  Number.isInteger = Number.isInteger || function(value) {
    return typeof value === "number" &&
      isFinite(value) &&
      Math.floor(value) === value;
  };

})(jQuery, Drupal);
