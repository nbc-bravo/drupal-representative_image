<?php

/**
 * @file
 * Definition of views_handler_field_representative_image.
 */

/**
 * A handler to provide proper displays for the representative image.
 *
 * @ingroup views_field_handlers
 */
class representative_image_views_handler_field_representative_image extends views_handler_field {
  function query() {
    // Do nothing -- to override the parent query.
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['image_style'] = array('default' => '');
    $options['image_link'] = array('default' => '');

    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $image_styles = image_style_options(FALSE);
    $form['image_style'] = array(
      '#title' => t('Image style'),
      '#type' => 'select',
      '#empty_option' => t('None (original image)'),
      '#default_value' => isset($this->options['image_style']) ? $this->options['image_style'] : NULL,
      '#options' => $image_styles,
    );

    $link_types = array(
      'content' => t('Content'),
      'file' => t('File'),
    );
    $form['image_link'] = array(
      '#title' => t('Link image to'),
      '#type' => 'select',
      '#empty_option' => t('Nothing'),
      '#default_value' => isset($this->options['image_link']) ? $this->options['image_link'] : NULL,
      '#options' => $link_types,
    );
  }

  function render($values) {
    // @todo: add handling for other entity types.
    $node = node_load($values->nid);

    if ($field = representative_image_get_field($this->options['table'], $node->type)) {
      $image = field_view_field($this->options['table'], $node, $field);
      if (isset($image[0]['#file'])) {
        $image_file = $image[0]['#file'];
        $settings = $image = array(
          'item' => array(
            'uri' => $image_file->uri,
            'alt' => $image_file->field_file_image_alt_text,
            'title' => $image_file->field_file_image_title_text,
          ),
          'image_style' => $this->options['image_style'],
        );

        switch ($this->options['image_link']) {
          case 'content':
            $settings['path'] = array(
              'path' => 'node/' . $node->nid,
            );
            break;
          case 'file':
            $settings['path'] = array(
              'path' => $image_file->uri,
            );
            break;
        }

        return theme('image_formatter', $settings);
      }
    }

    // @todo: should we check if there is a default or are we getting rid of
    // default?
    return '';
  }

}
