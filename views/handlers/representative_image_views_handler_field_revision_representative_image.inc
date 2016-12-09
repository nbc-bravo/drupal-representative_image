<?php

/**
 * @file
 * Name: representative_image_views_handler_field_revision_representative_image.
 *
 * Definition of
 * representative_image_views_handler_field_revision_representative_image.
 */

require_once 'representative_image_views_handler_field.inc';

/**
 * A handler to provide proper displays for the representative image.
 *
 * @ingroup views_field_handlers
 * @codingStandardsIgnoreStart
 */
class representative_image_views_handler_field_revision_representative_image extends representative_image_views_handler_field {

  /**
   * Render.
   *
   * @codingStandardsIgnoreEnd
   */
  public function render($values, $entity = NULL, $type = '') {
    if (isset($values->vid)) {
      $node = node_load(NULL, $values->vid);
      $output = parent::render($values, $node, 'node');
      return $output;
    }
    else {
      drupal_set_message(t('The representative image field will not work properly in a node revision view unless you include the `vid` field.'), 'warning', FALSE);
      return '';
    }
  }

}
