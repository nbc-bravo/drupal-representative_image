<?php

namespace Drupal\Tests\representative_image\Functional;

/**
 * Tests views integration.
 *
 * @group representative_image
 */
class RepresentativeImageViewsTest extends RepresentativeImageBaseTest {
  public function setUp() {
    parent::setUp(array('representative_image_test', 'views'));

    views_invalidate_cache();
  }

//  public static function getInfo() {
//    return array(
//      'name' => 'Representative Image Views Integration',
//      'description' => 'Test the views integration of representative images.',
//      'group' => 'Representative Image',
//    );
//  }

  public function testViewsIntegration() {
    // Setup articles to have some image fields and set one to be the
    // representative image.
    $new_field_label_1 = $this->createImageField('admin/structure/types/manage/article/fields');
    $new_field_label_2 = $this->createImageField('admin/structure/types/manage/article/fields');
    $this->setRepresentativeImageField('node', 'article', 'field_' . $new_field_label_1);

    $image_array_1 = array();
    $image_array_2 = array();
    $count = 3;
    // Create image files to use for testing.
    for ($i = 0; $i < $count; $i++) {
      $image_array_1[] = $this->randomFile('image');
      $image_array_2[] = $this->randomFile('image');
    }

    // Create article nodes for testing.
    for ($i = 0; $i < $count; $i++) {
      $edit = array(
        'title' => $this->randomName(),
        'files[field_' . $new_field_label_1 . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image_array_1[$i]->uri),
        'files[field_' . $new_field_label_2 . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image_array_2[$i]->uri),
      );
      $this->drupalPost('node/add/article', $edit, t('Save'));
    }

    // Test that the correct images are being displayed in the test view.
    views_invalidate_cache();
    $this->drupalGet('representative-image-views-test');
    for ($i = 0; $i < $count; $i++) {
      $this->assertImage($image_array_1[$i]);
    }

    // Switch the representative image and check that view changes
    // appropriately.
    $this->setRepresentativeImageField('node', 'article', 'field_' . $new_field_label_2);
    views_invalidate_cache();
    $this->drupalGet('representative-image-views-test');
    for ($i = 0; $i < $count; $i++) {
      $this->assertImage($image_array_2[$i]);
    }

  }

}

