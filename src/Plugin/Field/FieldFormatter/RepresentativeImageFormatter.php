<?php

namespace Drupal\representative_image\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'Representative Image' formatter.
 *
 * @FieldFormatter(
 *   id = "representative_image",
 *   label = @Translation("Representative Image"),
 *   field_types = {
 *     "representative_image"
 *   }
 * )
 */
class RepresentativeImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $entity = $items->getEntity();
    $settings = $this->getSettings();
    $field_settings = $items->getFieldDefinition()->getSettings();

    $render_array = [];
    $field_item = NULL;

    $field_name = $field_settings['representative_image_field_name'];
    if (!empty($field_name) && !$entity->get($field_name)->isEmpty()) {
      $field_item = $entity->get($field_name);
    }
    else {
      $behavior = $field_settings['representative_image_behavior'];
      if ($behavior == 'nothing') {
        return $render_array;
      }
      else {
        if ($behavior == 'first') {
          $field_item = $this->getFirstAvailableImageField($entity);
        }
        else {
          $field_item = $this->getDefaultImage($items, $langcode);
        }
      }
    }
    if (empty($field_item)) {
      return $element;
    }

    $element[] = [
      '#theme' => 'image_formatter',
      '#image_style' => $settings['image_style'],
      '#item' => $field_item,
    ];

    if (!empty($settings['image_link'])) {
      if ($settings['image_link'] == 'content') {
        $element['#url'] = $entity->toUrl()->toString();
      }
      elseif ($settings['image_link'] == 'file') {
        $element['#url'] = $field_item->entity->url('canonical');
      }
    }

    return $element;
  }

  /**
   * Returns the default field image.
   *
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items
   *   The item list.
   * @param string $langcode
   *   The language code of the referenced entities to display.
   *
   * @return \Drupal\Core\Field\FieldItemInterface|null
   *   The field item or NULL if not found.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @TODO Had to overwrite this method because calling it would throw a parameter mismatch error.
   */
  public function getDefaultImage($items, $langcode) {
    $field_item = NULL;
    $default_image = $this->getFieldSetting('default_image');
    // If we are dealing with a configurable field, look in both
    // instance-level and field-level settings.
    if (empty($default_image['uuid']) && $this->fieldDefinition instanceof FieldConfigInterface) {
      $default_image = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('default_image');
    }
    if (!empty($default_image['uuid']) && $file = \Drupal::entityManager()->loadEntityByUuid('file', $default_image['uuid'])) {
      // Clone the FieldItemList into a runtime-only object for the formatter,
      // so that the fallback image can be rendered without affecting the
      // field values in the entity being rendered.
      $items = clone $items;
      $items->setValue([
        'target_id' => $file->id(),
        'alt' => $default_image['alt'],
        'title' => $default_image['title'],
        'width' => $default_image['width'],
        'height' => $default_image['height'],
        'entity' => $file,
        '_loaded' => TRUE,
        '_is_default' => TRUE,
      ]);
      $field_item = $items[0];
    }

    return $field_item;
  }

  /**
   * Returns the first image field containing an image in the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being viewed.
   *
   * @return \Drupal\Core\Field\FieldItemInterface|null
   *   The field item or NULL if not found.
   */
  protected function getFirstAvailableImageField(EntityInterface $entity) {
    $field_item = NULL;
    $representative_image_picker = \Drupal::service('representative_image.picker');
    foreach ($representative_image_picker->getSupportedFields($entity->getEntityTypeId(), $entity->bundle()) as $field_id => $field_label) {
      if (!$entity->get($field_id)->isEmpty()) {
        $field_item = $entity->get($field_id);
        break;
      }
    }
    return $field_item;
  }

}
