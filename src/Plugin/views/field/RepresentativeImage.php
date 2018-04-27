<?php

namespace Drupal\representative_image\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides a default implementation for field handlers.
 *
 * @ViewsField("representative_image")
 */
class RepresentativeImage extends FieldPluginBase {

  /**
   * {@inheritDoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['image_style'] = ['default' => ''];
    $options['image_link'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $image_styles = image_style_options(FALSE);
    $form['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#empty_option' => $this->t('None (original image)'),
      '#default_value' => isset($this->options['image_style']) ? $this->options['image_style'] : NULL,
      '#options' => $image_styles,
    ];

    $link_types = [
      'content' => $this->t('Content'),
      'file' => $this->t('File'),
    ];
    $form['image_link'] = [
      '#title' => $this->t('Link image to'),
      '#type' => 'select',
      '#empty_option' => $this->t('Nothing'),
      '#default_value' => isset($this->options['image_link']) ? $this->options['image_link'] : NULL,
      '#options' => $link_types,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\representative_image\RepresentativeImagePicker $representative_image_picker */
    $representative_image_picker = \Drupal::service('representative_image.picker');
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getEntity($values);
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = \Drupal::config('representative_image.settings');

    $representative_field_name = NULL;

    $field_name = $representative_image_picker->getFieldFrom($entity);
    if (!empty($field_name) && !$entity->get($field_name)->isEmpty()) {
      $representative_field_name = $field_name;
    }
    else {
      $default_behavior = $config->get('default_behavior');
      if ($default_behavior != 'first') {
        return '';
      }
      $field_definitions = \Drupal::service('entity_field.manager')
        ->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
      foreach ($field_definitions as $field_id => $field_definition) {
        if (($field_definition->getType() == 'image') && (!$entity->get($field_id)
            ->isEmpty())) {
          $representative_field_name = $field_id;
          break;
        }
      }
      if (empty($representative_field_name)) {
        return '';
      }
    }

    $output = [
      '#theme' => 'image_formatter',
      '#image_style' => $this->options['image_style'],
      '#item' => $entity->get($representative_field_name),
      '#cache' => [
        'tags' => $config->getCacheTags(),
      ],
    ];

    $link = $this->options['image_link'];
    if ($link == 'content') {
      $output['#url'] = $entity->toUrl()->toString();
    }
    elseif ($link == 'file') {
      $output['#url'] = $entity->get($representative_field_name)->entity->url('canonical');
    }

    return $output;
  }

}
