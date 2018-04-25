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
        'logo' => $this->t('Use the site logo'),
        'first_or_logo' => $this->t('Use the first image on the given entity. If no image is found, use the site logo.'),
      ],
    ];

    $form['header'] = [
      '#markup' => $this->t('Below is the list of all entity types and their respective bundles. To override the default behavior, select a field from a specific bundle.'),
    ];

    $entity_definitions = \Drupal::entityTypeManager()->getDefinitions();
    foreach ($entity_definitions as $entity_type) {
      $this->addEntityFieldsToForm($form, $config, $entity_type);
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Adds fields per entity to the form.
   *
   * @param $form
   * @param $config
   * @param $entity_type
   * @param $specific_bundles
   */
  protected function addEntityFieldsToForm(&$form, $config, $entity_type, $specific_bundles = array()) {
    $bundles = \Drupal::service('entity_type.bundle.info')
      ->getBundleInfo($entity_type->id());
    // If necessary, filter out the bundles.
    $bundles = empty($specific_bundles) ? $bundles : array_intersect_key_key($bundles, array_flip($specific_bundles));

    if (!$entity_type->entityClassImplements(FieldableEntityInterface::class)) {
      return ;
    }

    $form[$entity_type->id()] = array(
      '#type' => 'fieldset',
      '#title' => $entity_type->getBundleLabel(),
      '#collapsed' => TRUE,
      '#collapsible' => TRUE,
      '#group' => 'entities',
      '#attributes' => array('id' => 'representative_image_' . $entity_type->id()),
    );
    $form[$entity_type->id()]['header'] = array(
      '#markup' => $this->t('Select an image field to use for each bundle.'),
    );
    $options = [];
    foreach ($bundles as $bundle_id => $bundle_config) {
      $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type->id(), $bundle_id);
      foreach ($field_definitions as $field_id => $field_definition) {
        if ($field_definition->getType() == 'image') {
          $options[$field_id] = $field_definition->getConfig($bundle_id)->label() . ' (' . $field_id . ')';
        }
      }
      if (empty($options)) {
        $form[$entity_type->id()][$bundle_id] = array(
          '#type' => 'item',
          '#title' => $bundle_config['label'],
          '#description' => $this->t('No image fields available.'),
          '#parents' => array('representative_image', $entity_type->id(), $bundle_id),
        );
      }
      else {
        $form[$entity_type->id()][$bundle_id] = array(
          '#type' => 'select',
          '#title' => $bundle_config['label'],
          '#options' => $options,
          '#empty_option' => $this->t('Default behavior'),
          '#empty_value' => '',
          '#parents' => array('representative_image', $entity_type->id(), $bundle_id),
          '#default_value' => $config->get('entity_defaults.' . $entity_type->id() . '.' . $bundle_id),
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('representative_image.settings')
      ->set('default_behavior', $values['default_behavior'])
      ->set('entity_defaults', $values['representative_image'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
