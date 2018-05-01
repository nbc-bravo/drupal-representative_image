<?php

namespace Drupal\representative_image\Form;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Representative Image settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'representative_image_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['representative_image.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('representative_image.settings');

    $form['default_behavior'] = [
      '#type' => 'select',
      '#title' => $this->t('Default behavior when no representative image has been selected'),
      '#default_value' => $config->get('default_behavior'),
      '#empty_option' => $this->t('Don\'t render an image'),
      '#options' => [
        'first' => $this->t('Use the first image found on the given entity'),
        'default' => $this->t('Use the field\'s default image.'),
        'first_or_default' => $this->t('Use the first image on the given entity. If no image is found, use the field\'s default image.'),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('representative_image.settings')
      ->set('default_behavior', $values['default_behavior'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
