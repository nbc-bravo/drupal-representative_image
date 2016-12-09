<?php

/**
 * @file
 * Name representative_image.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function representative_image_views_data_alter(&$data) {
  // Add a handler for entities that have a representative image field set.
  $entities = entity_get_info();

  foreach ($entities as $entity_name => $entity) {
    $data[$entity['base table']]['representative_image'] = array(
      'title' => t('Representative Image'),
      'help' => t('Displays an image that is representative of the given entity. The image is pulled from the field specified in the content type edit form.'),
      'field' => array(
        'handler' => 'representative_image_views_handler_field_representative_image',
        'no group by' => TRUE,
      ),
      'entity_type' => $entity_name,
      'entity_key' => $entity['entity keys']['id'],
    );
  }

  // Separately we handle node revisions.
  $data['node_revision']['representative_image'] = array(
    'title' => t('Representative Image'),
    'help' => t('Displays an image that is representative of the given node revision. The image is pulled from the field specified in the content type edit form.'),
    'field' => array(
      'handler' => 'representative_image_views_handler_field_revision_representative_image',
      'no group by' => TRUE,
    ),
  );
}
