<?php

namespace Drupal\representative_image;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * RepresentativeImagePicker service.
 */
class RepresentativeImagePicker {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a RepresentativeImagePicker object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $moduleHandler) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Given an entity, return its representative image or an empty string.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity instance.
   *
   * @return string
   *   A string with a representative image as a fully formed absolute URL or an
   *   empty string if nothing could be found.
   */
  public function from(EntityInterface $entity) {
    $image = '';

    $field_name = $this->getFieldFrom($entity);
    // @TODO looks like EntityInterface is not the right parameter type to use in this method.
    if (!empty($field_name) && !$entity->get($field_name)->isEmpty()) {
      $image = file_create_url($entity->get($field_name)->entity->getFileUri());
    }

    // Allow other modules to swap in their own representative image. We call a
    // separate alter hook per entity type to help improve performance. Most
    // modules that use this hook will want to treat different types differently.
    $this->moduleHandler->alter('representative_image_' . $entity->getEntityTypeId(). '_image', $image, $entity);

    // If all else fails use a sensible default.
    if (empty($image)) {
      $image = $this->getDefaultFrom($entity);
    }

    return $image;
  }

  /**
   * Given an entity, extract it's representative image field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity instance.
   *
   * @return string
   *   The field identifier.
   */
  public function getFieldFrom(EntityInterface $entity) {
    $config = $this->configFactory->get('representative_image.settings');
    return $config->get('entity_defaults.' . $entity->getEntityTypeId() . '.' . $entity->bundle());
  }

  /**
   * Given an entity, extract it's default representative image.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity instance.
   *
   * @return string
   *   A string with a representative image as a fully formed absolute URL or an
   *   empty string if nothing could be found.
   */
  protected function getDefaultFrom(EntityInterface $entity) {
    $config = $this->configFactory->get('representative_image.settings');
    $method = $config->get('default_behavior');
    switch ($method) {
      case 'first':
      case 'first_or_logo':
        $available_fields = [];
        // @TODO Inject this service.
        $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
        foreach ($field_definitions as $field_id => $field_definition) {
          if ($field_definition->getType() == 'image') {
            $available_fields[] = $field_id;
          }
        }
        if (!empty($available_fields) && !$entity->get($available_fields[0])->isEmpty()) {
          $default = file_create_url($entity->get($available_fields[0])->entity->getFileUri());
          break;
        }
      // Conditional "break" above.
      case 'logo':
      case 'first_or_logo':
        $default = $this->getLogoUrl();
        break;

      default:
        $default = '';
    }

    return $default;
  }

  /**
   * Returns the site logo's full URL.
   *
   * @return string
   *   The logo's full URL.
   */
  public function getLogoUrl() {
    global $base_url;
    $logo = theme_get_setting('logo')['url'];
    return $base_url . '/' . preg_replace('/^' . str_replace('/', '\/', $base_url) . '\//', '', $logo);
  }

}
