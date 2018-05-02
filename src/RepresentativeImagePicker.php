<?php

namespace Drupal\representative_image;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Helper service to find representative image fields.
 */
class RepresentativeImagePicker {

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a RepresentativeImagePicker object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface
   *   The entity field manager service.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Finds suported image fields to use as representative field.
   *
   * @param string $entity_type
   *   The entity type name.
   * @param string $bundle
   *   The bundle name.
   *
   * @return array
   *   An associative array with field id as keys and field labels as values.
   */
  public function getSupportedFields($entity_type, $bundle) {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    $options = [];
    foreach ($field_definitions as $field_id => $field_definition) {
      if ($field_definition->getType() == 'image') {
        $options[$field_id] = $field_definition->getConfig($bundle)->label() . ' (' . $field_id . ')';
      }
    }

    return $options;
  }

  /**
   * Finds the representative image field in an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity instance.
   *
   * @return string
   *   The field identifier or an empty string if not found.
   */
  public function getRepresentativeImageField(EntityInterface $entity) {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
    $representative_image_field = NULL;
    foreach ($field_definitions as $field_id => $field_definition) {
      if ($field_definition->getType() == 'representative_image') {
        return $field_id;
      }
    }
    return '';
  }

}
