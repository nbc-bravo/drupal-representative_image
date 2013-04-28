<?php

/**
 * @file
 * Definition of representative_image_views_handler_field_representative_image.
 */

require_once 'representative_image_views_handler_field.inc';

/**
 * A handler to provide proper displays for the representative image.
 *
 * @ingroup views_field_handlers
 */
class representative_image_views_handler_field_representative_image extends representative_image_views_handler_field {

  function render($values) {
    $output = '';

    // Get the entity type from the views data.
    $data = views_fetch_data($this->view->base_table);
    $type = $data['representative_image']['entity_type'];

    // Load the entity and render the field.
    $entities = entity_load($type, array($values->{$this->view->base_field}));
    $entity = array_pop($entities);

    $output = is_object($entity) ? parent::render($values, $entity, $type) : '';

    return $output;
  }

}
