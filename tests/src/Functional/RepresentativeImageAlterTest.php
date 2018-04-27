<?php

namespace Drupal\Tests\representative_image\Functional;

/**
 * Tests altering representative images.
 *
 * @group representative_image
 */
class RepresentativeImageAlterTest extends RepresentativeImageBaseTest {

  public static $modules = ['field_ui', 'file', 'image', 'node', 'representative_image', 'representative_image_alter_test'];

  /**
   * Confirm that node entities can have representative images.
   */
  public function testAlterTest() {
    global $base_url;
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Page']);

    $node = $this->drupalCreateNode(['type' => 'article']);
    $this->assertEquals($base_url . '/article.png', $this->representativeImagePicker->from($node), 'It is possible for third party modules to alter the representative image for page.');

    $node = $this->drupalCreateNode(['type' => 'page']);
    $this->assertEquals($base_url . '/page.png', $this->representativeImagePicker->from($node), 'It is possible for third party modules to alter the representative image for article.');
  }

}
