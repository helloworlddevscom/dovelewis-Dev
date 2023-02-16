/**
 * Initializes Slick Carousels.
 * Loaded as part of slick-carousel theme library.
 */


(function($, Drupal) {
  'use strict';


/**
 * Initializes Slick Carousels.
 *
 * Note that you will also need to include slick-carousel library in twig template.
 *
 * We initialize each Slick Carousel separately so we can pass in different options.
 */
var foundationSlickCarouselInit = false;
Drupal.behaviors.foundationSlickCarouselInit = {
  attach: function (context, settings) {
    // Attempt to resolve behavior being called multiple times per page load.
    if (context !== document) {
      return;
    }
    if (!foundationSlickCarouselInit) {
      foundationSlickCarouselInit = true;
    }
    else {
      return;
    }


    if ($('.paragraph--type--homepage-marquee').length > 0) {
      var $carousel = $('.paragraph--type--homepage-marquee .slick-carousel');
      $carousel.slick(
        {
          slidesToShow: 1,
          slidesToScroll: 1,
          infinite: true,
          dots: true,
          centerMode: false,
          mobileFirst: true,
          autoplay: true,
          autoplaySpeed: 9000,
          responsive: [
            {
              breakpoint: 500,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
              }
            },
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
              }
            },
            {
              breakpoint: 1280,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
              }
            }
          ]
        }
      );
    }
  }
};


})(jQuery, Drupal);
