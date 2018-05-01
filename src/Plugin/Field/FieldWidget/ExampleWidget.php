<?php

namespace Drupal\representative_image\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'representative_image_example' field widget.
 *
 * @FieldWidget(
 *   id = "representative_image_example",
 *   label = @Translation("Example"),
 *   field_types = {"string"},
 * )
 */
class ExampleWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
      'prefix' => '',
      'suffix' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $element['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of textfield'),
      '#default_value' => $settings['size'],
      '#required' => TRUE,
      '#min' => 1,
    ];
    $element['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $settings['placeholder'],
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];
    $element['prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prefix'),
      '#default_value' => $settings['prefix'],
    ];
    $element['suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Suffix'),
      '#default_value' => $settings['suffix'],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary[] = $this->t('Textfield size: @size', ['@size' => $settings['size']]);

    if ($settings['placeholder']) {
      $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $settings['placeholder']]);
    }
    if ($settings['prefix']) {
      $summary[] = $this->t('Prefix: @prefix', ['@prefix' => $settings['prefix']]);
    }
    if ($settings['suffix']) {
      $summary[] = $this->t('Suffix: @suffix', ['@suffix' => $settings['suffix']]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $settings = $this->getSettings();
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $settings['size'],
      '#placeholder' => $settings['placeholder'],
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#field_prefix' => $settings['prefix'],
      '#field_suffix' => $settings['suffix'],
    ];

    return $element;
  }

}
