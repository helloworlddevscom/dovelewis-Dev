<?php

namespace Drupal\media_library_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\Entity\Media;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Language\LanguageManagerInterface;


use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class ImportForm extends FormBase {

  /**
   * Drupal\Core\File\FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Language\LanguageManagerInterface definition.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a ImportForm form.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager interface.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager interface.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user.
   */
  public function __construct(FileSystemInterface $file_system,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $languageManager,
                              AccountProxy $current_user) {
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $languageManager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_library_importer_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $media_config = $this->config('media_library_importer.settings');
    $import_folder = $media_config->get('import_folder');

    $folders = $this->getMediaFolders($import_folder);
    $options = $this->getMediaFoldersCheckboxOptions($folders);

    $form['media_folders'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media folders'),
      '#options' => $options,
      '#default_value' => array_keys($options),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $real_path = $this->fileSystem->realpath($this->config('system.file')->get('default_scheme') . "://");
    $media_folders = $form_state->getValue('media_folders');
    $operations = [];

    foreach ($media_folders as $media_folder) {
      if ($media_folder) {

        $image_files = glob("$media_folder/*.{jpg,png,gif}", GLOB_BRACE);

        foreach ($image_files as $file_url) {
          $uri = str_replace($real_path, 'public:/', $file_url);
          $file = $this->loadFile($uri);

          if ($file) {
            if ($this->mediaEntityExists($file)) {
              $this->messenger()->addMessage(
                $this->t('Media already exists. Not creating a new entity for "@file_name"', ['@file_name' => $file->getFilename()]), MessengerInterface::TYPE_WARNING);
            }
            else {
              $extra_fields = array();

              $module_handler = \Drupal::moduleHandler();
              $module_handler->invokeAll('alter_media_library_importer_media_extra_fields',
                [$file, $file_url, $uri, &$extra_fields]);
              // $this->createMediaEntity($file, $extra_fields);
              $operations[] = [
                [$this, 'createMediaEntity'],[$file, $extra_fields]
              ];
            }
          }
        }
      }
    }

    $num_operations = count($operations);
    if ($num_operations>0) {
      $batch = [
        'title' => $this->t('Creating media entities for @num files', ['@num' => $num_operations]),
        'operations' => $operations,
        'init_message' => $this->t('Batch operation is starting.'),
        'progress_message' => $this->t('Processed @current out of @total.'),
        'error_message' => $this->t('Batch operation has encountered an error.'),
        'finished' => [$this, 'batchMediaCreationFinished']
      ];
      batch_set($batch);
    }
  }

  public static function batchMediaCreationFinished($success, $results, $operations) {
    if ($success) {
      $message = t('Batch media creation completed successfully. @count new media entities created',['@count' => count($results)]);
      $type = 'status';
    }
    else {
      $message = t('Encountered an error during bulk media creation.');
      $type = 'error';
    }
    drupal_set_message($message, $type);
  }


  /**
   * {@inheritdoc}
   */
  protected function loadFile($uri) {
    $file = NULL;
    $files = $this->entityTypeManager->getStorage('file')->loadByProperties(['uri' => $uri]);
    if (empty($files)) {
      $file = $this->createFileEntity($uri);
    }
    else {
      $file = reset($files);
    }
    return $file;
  }

  /**
   * {@inheritdoc}
   */
  protected function mediaEntityExists($file) {
    $mediaQuery = $this->entityTypeManager->getStorage('media')->getQuery();
    $mediaQuery->condition('bundle', 'image');
    $mediaQuery->condition('field_media_image.target_id', $file->id());
    $result = $mediaQuery->execute();
    return ($result && count($result) > 0);
  }

  /**
   * {@inheritdoc}
   */
  protected function createFileEntity($uri) {
    $file = NULL;
    $media_config = $this->config('media_library_importer.settings');

    $unmanagedFilesImportAllowed = is_null($media_config->get('import_unmanaged_files')) ? TRUE : $media_config->get('import_unmanaged_files');
    if ($unmanagedFilesImportAllowed) {
      if (file_exists($uri)) {
        $filename = mb_substr($uri, mb_strrpos($uri, '/') + 1);
        $file = File::create([
          'uid' => $this->currentUser->id(),
          'filename' => $filename,
          'uri' => $uri,
          'status' => 1,
        ]);

        // $this->messenger()->addMessage(
        //   $this->t('Creating and importing unmanaged file "@file_name"', ['@file_name' => $file->getFilename()]), MessengerInterface::TYPE_STATUS);

        $file->save();
      }
      else {
        $this->messenger()->addMessage(
          $this->t('File "@file_name" does not exist!', ['@file_name' => $file->getFilename()]), MessengerInterface::TYPE_ERROR);
      }

    }
    else {
      $this->messenger()->addMessage(
        $this->t('Unmanaged file "@file_name" will not be imported.', ['@file_name' => $uri]), MessengerInterface::TYPE_WARNING);
    }

    return($file);

  }

  /**
   * {@inheritdoc}
   */
  public function createMediaEntity($file, $extra_fields = [], &$context) {
    if ($file) {
      $media_data = [
        'bundle' => 'image',
        'uid' => $this->currentUser->id(),
        'langcode' => $this->languageManager->getDefaultLanguage()->getId(),
        'name' => $file->getFilename(),
        'status' => 1,
        'field_media_image' => $file,
      ];
      if (!empty($extra_fields)) {
        $media_data = array_merge($media_data,$extra_fields);
      }
      $media = Media::create($media_data);
      // $media->setName($file->getFilename())->setPublished(TRUE)->save();

      $media->save();
      $context['results'][] = 'Created ' . $file->getFilename();
      // $this->messenger()->addMessage(
      //   $this->t('New media saved "@file_name"', ['@file_name' => $file->getFilename()]), MessengerInterface::TYPE_STATUS);
      return('done');
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getMediaFolders($root = NULL) {
    $media_config = $this->config('media_library_importer.settings');
    $tree = [];
    $real_path = $this->fileSystem->realpath($this->config('system.file')->get('default_scheme') . "://");

    $stylesShouldBeExcluded = is_null($media_config->get('exclude_styles')) ? TRUE : $media_config->get('exclude_styles');

    if ($root) {

      if (mb_substr($root, -1) == '/') {
        // Remove slash charater at the end.
        $root = mb_substr($root, 0, -1);
      }

      $name = mb_substr($root, mb_strrpos($root, '/') + 1);
      $image_files = glob("$root/*.{jpg,png,gif}", GLOB_BRACE);

      $tree = [
        $name =>
          [
            'name' => $name,
            'path' => $root,
            'uri' => str_replace($real_path, 'public:/', $root),
            'media_count' => count($image_files),
          ],
      ];

      $directories = glob($root . '/*', GLOB_ONLYDIR);
      foreach ($directories as $folder) {
        if ($name == 'styles' && $stylesShouldBeExcluded) {
          continue;
        }
        $tree[$name]['subdir'][] = $this->getMediaFolders($folder);
      }
      return $tree;
    }
    else {
      $root = $real_path;
      return $this->getMediaFolders($root);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getMediaFoldersMarkup($folders, $level = NULL) {
    if ($level) {
      $markup = '';
      foreach ($folders as $folder) {
        $current = key($folder);
        $markup .= '<li>';
        $markup .= key($folder);
        $markup .= ' (' . $folder[$current]['path'] . ')';
        $markup .= ' (' . $folder[$current]['media_count'] . ')';

        if ($sub_folder = $folder[$current]['subdir']) {
          $markup .= $this->getMediaFoldersMarkup($sub_folder);
        }
        $markup .= '</li>';
      }
    }
    else {
      $markup  = "<ul>";
      $markup .= $this->getMediaFoldersMarkup($folders, 1);
      $markup .= "</ul>";
    }
    return $markup;
  }

  /**
   * {@inheritdoc}
   */
  protected function getMediaFoldersCheckboxOptions($folders, $level = 0) {

    $options = [];

    $indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    $level++;

    foreach ($folders as $folder) {
      $media_count = $folder['media_count'];
      $hasSubdir = array_key_exists('subdir', $folder);

      $k = $folder['path'];
      if ($media_count > 0) {
        $v = $indentation . $folder['name'] . ' (' . $media_count . ')';
      }
      else {
        $v = $indentation . $folder['name'];
      }

      if ($media_count > 0 || $hasSubdir) {
        $options[$k] = $v;
      }

      if ($hasSubdir) {
        $sub_folders = $folder['subdir'];
        foreach ($sub_folders as $sub_folder) {
          $options = array_merge($options, $this->getMediaFoldersCheckboxOptions($sub_folder, $level));
        }
      }
    }

    return $options;
  }

}
