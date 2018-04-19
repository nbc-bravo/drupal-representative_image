<?php

namespace Drupal\Tests\representative_image\Functional;

/**
 * Tests altering representative images.
 *
 * @group representative_image
 */

/**
 * Test that third-party modules can alter the representative images.
 */
class RepresentativeImageAlterTest extends RepresentativeImageBaseTest {

  public function setUp() {
    parent::setUp(array('representative_image_test'));
  }

//  public static function getInfo() {
//    return array(
//      'name' => 'Representative Image Alter functionality',
//      'description' => 'Test that third-party modules can alter representative images.',
//      'group' => 'Representative Image',
//    );
//  }

  /**
   * Confirm that node entities can have representative images.
   */
  public function testAlterTest() {
    global $base_url;
    $this->assertTrue(representative_image($this->drupalCreateNode(), 'node') == $base_url . '/page.png', 'It is possible for third party modules to alter the representative image for page.');
    $this->assertTrue(representative_image($this->drupalCreateNode(array('type' => 'article')), 'node') == $base_url . '/article.png', 'It is possible for third party modules to alter the representative image for article.');
  }

}
