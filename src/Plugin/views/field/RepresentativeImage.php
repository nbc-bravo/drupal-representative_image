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
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getEntity($values);
    /** @var \Drupal\representative_image\RepresentativeImagePicker $representative_image_picker */
    $representative_image_picker = \Drupal::service('representative_image.picker');

    $representative_image_field = $representative_image_picker->getRepresentativeImageField($entity);
    if (empty($representative_image_field)) {
      return '';
    }

    // Extract the image URL from the field formatter.
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
    $output = $view_builder->viewField($entity->{$representative_image_field}, 'default');

    if (empty($output['#item'])) {
      return '';
    }

    $image_style = $this->options['image_style'];
    if (!empty($image_style)) {
      $output['#image_style'] = $image_style;
    }

    $link = $this->options['image_link'];
    if ($link == 'content') {
      $output['#url'] = $entity->toUrl()->toString();
    }
    elseif ($link == 'file') {
      $output['#url'] = $entity->get($representative_image_field)->entity->url('canonical');
    }

    return $output;
  }

}
