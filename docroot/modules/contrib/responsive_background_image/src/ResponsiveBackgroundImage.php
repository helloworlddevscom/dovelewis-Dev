<?php

namespace Drupal\responsive_background_image;

use Drupal\media\Entity\Media;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\file\Entity\File;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Drupal\Core\Render\Markup;

/**
 * Provides a method to generate a responsive background image using a Responsive Image Style.
 */
class ResponsiveBackgroundImage
{

  /**
   * Generates a Drupal style tag array containing CSS media queries to apply a responsive
   * background image to a specific DOM element/node.
   * The return value must be assigned correctly. See the return description below.
   *
   * @param string $css_selector
   *   A CSS selector that points to the HTML tag to which a background image will be applied. Do
   *   not include curly braces/brackets.
   *   This selector should be unique to avoid the same background image being accidentally applied
   *   to multiple elements. For example, if you have a Paragraph type of Hero, you should add a
   *   class containing the entity ID to the Paragraph template, and then pass in that class as
   *   part of the selector here. This way multiple instances of the Hero Paragraph can appear on
   *   the same page with different background images.
   *   For example: '.paragraph--id--3 .hero__image', where '3' is the entity ID retrieved from the
   *   entity using $paragraph_entity->id().
   * @param object $entity
   *    An instance of Drupal\Core\Entity\ContentEntityBase which contains the image field or an instance of Drupal\file\Entity\File.
   *    If passing instance of Drupal\Core\Entity\ContentEntityBase, preferably this method has been called from within a preprocess hook
   *    such as THEME_preprocess_paragraph(&$vars), in which case the entity can be retrieved using $vars['paragraph'].
   * @param string $field_machine_name
   *    The machine name of the image field. For example: 'field_hero_background_image'. If passing instance of Drupal\file\Entity\File
   *    for $entity, pass null for $field_machine_name.
   * @param string $responsive_image_style
   *    The machine name of the Responsive Image Style to be used.
   * @param string $media_entity_field_machine_name
   *    Optional. If the image field is a Media field, and the Image field on the Image Media Type is custom and not the default 'field_media_image',
   *    pass in the custom field machine name.
   * @return array
   *    A Drupal style tag array containing CSS media queries to apply responsive background images
   *    to a specific HTML tag.
   *    Assuming that this method has been called from inside a preprocess function such as
   *    THEME_preprocess_paragraph(&$vars), the return value should be assigned to
   *    $vars['#attached']['html_head'][], or else calling this method will have no effect.
   *    Returns false if media queries cannot be generated.
   */
  public static function generateMediaQueries(string $css_selector, $entity, string $field_machine_name = null, string $responsive_image_style_machine_name, string $media_entity_field_machine_name = 'field_media_image') {

    if (is_a($entity, 'Drupal\file\Entity\File')) {
      // If File, get uri.
      $uri = $entity->getFileUri();
      $file_entity = $entity;
    }
    elseif (is_a($entity, 'Drupal\Core\Entity\ContentEntityBase')) {
      // If ContentEntityBase, retrieve uri from image field.

      // Get field type.
      $field_type = $entity->get($field_machine_name)->getFieldDefinition()->getType();

      // @TODO Take into account if image field is hidden in display settings.

      // If field type is an entity_reference we'll assume this is a Media Image field.
      if ($field_type == 'entity_reference') {
        $moduleHandler = \Drupal::service('module_handler');
        if ($moduleHandler->moduleExists('media')) {
          $media_entity = Media::load($entity->get($field_machine_name)
            ->getValue()[0]['target_id']);

          $file_entity = File::load($media_entity->get($media_entity_field_machine_name)
            ->getValue()[0]['target_id']);
        } elseif ($moduleHandler->moduleExists('media_entity')) {
          $media_entity = \Drupal\media_entity\Entity\Media::load($entity->get($field_machine_name)
            ->getValue()[0]['target_id']);

          $file_entity = File::load($media_entity->get($media_entity_field_machine_name)
            ->getValue()[0]['target_id']);
        }
      } else if ($field_type == 'image') {
        $file_entity = File::load($entity->get($field_machine_name)->getValue()[0]['target_id']);
      } else {
        \Drupal::logger('responsive_background_image')->error('Responsive Background Image field must be of type Image or Media Image.');
        return false;
      }

      $uri = $file_entity->getFileUri();
    }
    else {
      \Drupal::logger('responsive_background_image')->error('$entity must be of type Drupal\Core\Entity\ContentEntityBase or Drupal\file\Entity\File.');
      return false;
    }

    // Load Responsive Image Style and mappings.
    $ResponsiveImageStyle = ResponsiveImageStyle::load($responsive_image_style_machine_name);
    $image_style_mappings = array_reverse($ResponsiveImageStyle->getImageStyleMappings());

    // Load theme breakpoints.
    $breakpoint_group = $ResponsiveImageStyle->getBreakpointGroup();
    $breakpoints = \Drupal::service('breakpoint.manager')
      ->getBreakpointsByGroup($breakpoint_group);

    $media_queries_1x = '';
    $media_queries_2x = '';

    // If a fallback image style is set, add a default background image to media query.
    $fallback_image_style = $ResponsiveImageStyle->getFallbackImageStyle();
    switch ($fallback_image_style) {
      case '_empty image_':
        break;
      case '_original image_':
        $media_queries_1x .= self::createFallbackMediaQuery($css_selector, file_url_transform_relative($file_entity->createFileUrl()));
        break;
      default:
        $media_queries_1x .= self::createFallbackMediaQuery($css_selector, file_url_transform_relative(ImageStyle::load($ResponsiveImageStyle->getFallbackImageStyle())
          ->buildUrl($uri)));
        break;
    }

    // Loop through image style mappings starting from smallest to largest and build media queries.
    foreach ($image_style_mappings as $key => $image_style_mapping) {
      // Load media query for breakpoint.
      $media_query = $breakpoints[$image_style_mapping['breakpoint_id']]->getMediaQuery();

      // Get path to image using image style.
      $image_style = $image_style_mapping['image_mapping'];
      switch ($image_style) {
        case '_empty image_':
          continue 2;
        case '_original image_':
          $image_path = file_url_transform_relative($file_entity->createFileUrl());
          break;
        default:
          $image_path = file_url_transform_relative(ImageStyle::load($image_style)->buildUrl($uri));
          break;
      }

      // If multiplier is 1x.
      if ($image_style_mapping['multiplier'] == '1x') {
        $media_queries_1x .= self::createSingleMediaQuery($media_query, $css_selector, $image_path, '1x');
      }
      // If multiplier is greater than 1x.
      // @TODO: Should we actually pass the exact multiplier to the media query?
      else {
        $media_queries_2x .= self::createSingleMediaQuery($media_query, $css_selector, $image_path, '2x');
      }
    }

    $all_media_queries = $media_queries_1x . $media_queries_2x;

    // Create unique id for use in style tag machine name.
    $entity_id = $entity->id();
    $uuid = \Drupal::service('uuid')->generate();

    // Return style tag array.
    return [
      [
        '#tag' => 'style',
        // We have to add this as insecure markup, otherwise auto-escaping escapes the & to &amp; and breaks image urls.
        // See https://www.drupal.org/project/responsive_background_image/issues/3067838#comment-13208830
        '#value' => Markup::create($all_media_queries),
        // Should be last element of <head>, currently impossible
        // due to core issue #2391025, so we'll at least set this as
        // high as possible behind the meta tags,
        // but it won't get behind the <title>.
        '#weight' => 99999,
      ],
      'responsive-background-image-' . $entity_id . '-' . $uuid
    ];
  }

  /**
   * @param string $css_selector
   *    CSS selector for element that will have background image.
   * @param string $fallback_image_path
   *    Path to the fallback image.
   * @return string
   *    A CSS property for a fallback background image.
   */
  private static function createFallbackMediaQuery(string $css_selector, string $fallback_image_path) {
    return '
    ' . $css_selector . ' {
      background-image: url(' . $fallback_image_path . ');
    }';
  }

  /**
   * @param string $media_query
   *    CSS media query from Breakpoint group config.
   * @param string $css_selector
   *    CSS selector for element that will have background image.
   * @param string $image_path
   *    Path to image.
   * @param string $multiplier
   *    Responsive image multiplier/pixel ratio.
   * @return string
   *    A single CSS media query for one one window width and one multiplier/pixel ratio.
   */
  private static function createSingleMediaQuery(string $media_query, string $css_selector, string $image_path, string $multiplier) {
    switch ($multiplier) {
      case '1x':
        $min_pixel_ratio = '';
        break;

      default:
        $min_pixel_ratio = 'and (min-device-pixel-ratio: 1.5)';
        break;
    }

    return '
    @media ' . $media_query . $min_pixel_ratio . ' {
      ' . $css_selector . ' {
        background-image: url(' . $image_path . ');
      }
    }';
  }
}
