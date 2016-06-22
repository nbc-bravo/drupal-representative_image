<?php

/**
 * @file
 * Token integration for the Representative Image module.
 */

/**
 * Implements hook_token_info_alter().
 */
function representative_image_token_info_alter(array &$info) {
  $entities = entity_get_info();
  foreach ($entities as $entity_type => $entity_info) {
    $token_type = !empty($entity_info['token type']) ? $entity_info['token type'] : $entity_type;
    // Only add this token if the entity type has tokens.
    if (isset($info['tokens'][$token_type])) {
      $info['tokens'][$token_type]['representative_image'] = array(
        'name' => t('Representative image'),
        'description' => t('The representative image for the @label.', array('@label' => $entity_info['label'])),
      );
    }
  }
}

/**
 * Implements hook_tokens().
 */
function representative_image_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $replacements = array();

  $entities = entity_get_info();
  foreach ($entities as $entity_type => $entity_info) {
    $token_type = !empty($entity_info['token type']) ? $entity_info['token type'] : $entity_type;
    if ($type == $token_type && !empty($data[$token_type])) {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'representative_image':
            if ($image = representative_image($data[$token_type], $entity_type)) {
              $replacements[$original] = !empty($options['sanitize']) ? check_url($image) : $image;
            }
            break;
        }
      }
    }
  }

  return $replacements;
}
