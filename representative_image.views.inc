<?php

/**
 * @file
 * Provide views data for representative_image.module.
 */

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Implements hook_views_data_alter().
 */
function representative_image_views_data_alter(&$data) {
  $entity_definitions = \Drupal::entityTypeManager()->getDefinitions();
  foreach ($entity_definitions as $entity_type_id => $entity_type_definition) {
    $data[$entity_type_definition->getBaseTable()]['representative_image'] = [
      'title' => t('Representative Image'),
      'help' => t('Displays an image that is representative of the given entity. The image is pulled from the field specified in the content type edit form.'),
      'field' => [
        'id' => 'representative_image',
        'no group by' => TRUE,
      ],
      'entity_type' => $entity_type_id,
      'entity_id' => $entity_type_definition->getKey('id'),
    ];
  }

  // Handle node revisions separately.
  // @TODO Not sure if this is correct.
  $data['node_revision']['representative_image'] = [
    'title' => t('Representative Image'),
    'help' => t('Displays an image that is representative of the given node revision. The image is pulled from the field specified in the content type edit form.'),
    'field' => [
      'id' => 'representative_image',
      'no group by' => TRUE,
    ],
  ];
}

