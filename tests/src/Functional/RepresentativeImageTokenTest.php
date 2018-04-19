<?php

namespace Drupal\Tests\representative_image\Functional;


/**
 * Tests token integration.
 *
 * @group representative_image
 */
class RepresentativeImageTokenTest extends RepresentativeImageBaseTest {
  public function setUp() {
    parent::setUp();
  }

//  public static function getInfo() {
//    return array(
//      'name' => 'Representative Image Token Integration',
//      'description' => 'Test the token integration of representative images.',
//      'group' => 'Representative Image',
//    );
//  }

  /**
   * Tests custom tokens.
   *
   * Test that the custom tokens defined by representative image return the
   * expected values.
   */
  public function testTokenIntegration() {
    // Setup articles to have some image fields.
    $new_field_label_1 = $this->createImageField('admin/structure/types/manage/article/fields');
    $new_field_label_2 = $this->createImageField('admin/structure/types/manage/article/fields');

    $image1 = $this->randomFile('image');
    $image2 = $this->randomFile('image');

    // Create an article nodes for testing.
    $edit = array(
      'title' => $this->randomName(),
      'files[field_' . $new_field_label_1 . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image1->uri),
      'files[field_' . $new_field_label_2 . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image2->uri),
    );
    $this->drupalPost('node/add/article', $edit, t('Save'));
    $node = node_load($this->getIdFromPath('node'));

    // Confirm that the correct image is being replaced properly.
    $this->setRepresentativeImageField('node', 'article', 'field_' . $new_field_label_1);
    $replacement = token_replace("foo [node:representative_image] bar", array('node' => $node));
    $this->assertImage($image1, $replacement);
    $this->assertNoImage($image2, $replacement);

    // Switch the representative image and confirm the representative image is
    // being replaced properly.
    $this->setRepresentativeImageField('node', 'article', 'field_' . $new_field_label_2);
    $replacement = token_replace("foo [node:representative_image] bar", array('node' => $node));
    $this->assertImage($image2, $replacement);
    $this->assertNoImage($image1, $replacement);
  }

}
