<?php

namespace Drupal\media_library_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class ConfigurationForm extends ConfigFormBase {

  /**
   * Drupal\Core\File\FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs configuration form.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(FileSystemInterface $file_system) {
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('file_system'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_library_importer_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $real_path = $this->fileSystem->realpath($this->config('system.file')->get('default_scheme') . "://");
    $config = $this->config('media_library_importer.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Settings'),
    ];

    $form['settings']['exclude_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude styles folder'),
      '#default_value' => is_null($config->get('exclude_styles')) ? TRUE : $config->get('exclude_styles'),
    ];

    $form['settings']['import_unmanaged_files'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Import unmanaged files'),
      '#default_value' => is_null($config->get('import_unmanaged_files')) ? TRUE : $config->get('import_unmanaged_files'),
    ];

    $form['settings']['media_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Media bundle image field'),
      '#default_value' => $config->get('media_field') ? $config->get('media_field') : 'field_media_image',
    ];

    $form['settings']['import_folder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Import from folder'),
      '#default_value' => $config->get('import_folder') ? $config->get('import_folder') : $real_path,
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
    $config = $this->config('media_library_importer.settings');
    $config->set('exclude_styles', $form_state->getValue('exclude_styles'));
    $config->set('import_unmanaged_files', $form_state->getValue('import_unmanaged_files'));
    $config->set('media_field', $form_state->getValue('media_field'));
    $config->set('import_folder', $form_state->getValue('import_folder'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_library_importer.settings',
    ];
  }

}
