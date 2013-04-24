<?php

/**
 * Implements hook_views_data_alter().
 */
function representative_image_views_data_alter(&$data) {
  $data['node']['representative_image'] = array(
    'title' => t('Representative Image'),
    'help' => t('Displays an image that is representative of the given node. The image is pulled from the field specified in the content type edit form.'),
    'field' => array(
      'handler' => 'representative_image_views_handler_field_representative_image',
      'no group by' => TRUE,
    ),
  );
  $data['node_revision']['representative_image'] = array(
    'title' => t('Representative Image'),
    'help' => t('Displays an image that is representative of the given node. The image is pulled from the field specified in the content type edit form.'),
    'field' => array(
      'handler' => 'representative_image_views_handler_field_revision_representative_image',
      'no group by' => TRUE,
    ),
  );
}

/**
 * Implementation of hook_views_handlers().
 */
function representative_image_views_handlers() {
  return array(
    'info' => array(
      'path' => drupal_get_path('module', 'representative_image') .'/handlers',
    ),
    'handlers' => array(
      'representative_image_views_handler_field' => array(
        'parent' => 'views_handler_field',
      ),
      'representative_image_views_handler_field_representative_image' => array(
        'parent' => 'representative_image_views_handler_field',
      ),
      'representative_image_views_handler_field_revision_representative_image' => array(
        'parent' => 'representative_image_views_handler_field',
      ),
    ),
  );
}
