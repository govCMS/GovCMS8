<?php

namespace Drupal\govcms_media\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\govcms_media\Element;
use Drupal\govcms_media\Exception\IndeterminateBundleException;
use Drupal\govcms_media\MediaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form for uploading multiple media assets at once.
 *
 * Code from \Drupal\lightning_media_bulk_upload\Form\BulkUploadForm
 * in lightning_media 8.x-2.3 submodule.
 */
class BulkUploadForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The media helper service.
   *
   * @var \Drupal\govcms_media\MediaHelper
   */
  protected $helper;

  /**
   * BulkUploadForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\govcms_media\MediaHelper $helper
   *   The media helper service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MediaHelper $helper, TranslationInterface $translator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->helper = $helper;
    $this->setStringTranslation($translator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('govcms_media.media_helper'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $extensions = $this->helper->getFileExtensions(TRUE);

    $form['dropzone'] = [
      '#type' => 'dropzonejs',
      '#dropzone_description' => $this->t('Drag files here to upload them'),
      '#extensions' => implode(' ', $extensions),
    ];
    $form['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
    ];

    $variables = [
      '@max_size' => static::bytesToString(Environment::getUploadMaxSize()),
      '@extensions' => Element::oxford($extensions),
    ];
    $form['dropzone']['#description'] = $this->t('You can upload as many files as you like. Each file can be up to @max_size in size. The following file extensions are accepted: @extensions', $variables);

    return $form;
  }

  /**
   * Converts a number of bytes into a human-readable string.
   *
   * @param int $bytes
   *   A number of bytes.
   *
   * @return string
   *   The human-readable measurement, like '2 MB' or '10 GB'.
   */
  public static function bytesToString($bytes) {
    $units = array_map('t', ['bytes', 'KB', 'MB', 'GB', 'TB']);

    while ($bytes > 1024) {
      $bytes /= 1024;
      array_shift($units);
    }
    return $bytes . ' ' . reset($units);
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $uploads = $form_state->getValue(['dropzone', 'uploaded_files']);
    $errors = [];
    
    foreach ($uploads as $upload) {
      // A new file uploaded by dropzone.
      $file = $this->entityTypeManager->getStorage('file')->create([
        'uri' => $upload['path'],
      ]);
      
      // Let other modules perform validation on the new file.
      $errors = array_merge($errors, \Drupal::moduleHandler()->invokeAll('file_validate', [$file]));
    }   
    
    if (!empty($errors)) {
      foreach ($errors as $error) {
        $form_state->setErrorByName('dropzone', $error);
      }
      // Remove the new file as error occured.
      $file->delete();
    }
    
    // Call parent's validation function.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bulk_create = [];

    $uploads = $form_state->getValue(['dropzone', 'uploaded_files']);

    foreach ($uploads as $upload) {
      // Create a file entity for the temporary file.
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->entityTypeManager->getStorage('file')->create([
        'uri' => $upload['path'],
      ]);
      $file->setTemporary();
      $file->save();

      try {
        $entity = $this->helper->createFromInput($file);
      }
      catch (IndeterminateBundleException $e) {
        if (isset($form['dropzone'])) {
          $form_state->setError($form['dropzone'], $e->__toString());
        }
        else {
          $this->messenger()->addError($e->__toString()); 
        }
        continue;
      }

      $file = MediaHelper::useFile($entity, $file);
      $file->setPermanent();
      $file->save();
      $entity->save();
      array_push($bulk_create, $bulk_create ? $entity->id() : $entity);
    }

    if ($bulk_create) {
      /** @var \Drupal\media\MediaInterface $entity */
      $redirect = array_shift($bulk_create)->toUrl('edit-form', [
        'query' => [
          'bulk_create' => $bulk_create,
        ],
      ]);
      $form_state->setRedirectUrl($redirect);
    }
  }

}
