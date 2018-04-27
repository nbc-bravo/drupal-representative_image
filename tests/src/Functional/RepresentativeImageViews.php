<?php

namespace Drupal\Tests\representative_image\Functional;

/**
 * Test the views integration of representative images
 *
 * @group representative_image
 */
class RepresentativeImageViewsTest extends RepresentativeImageBaseTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['field_ui', 'file', 'image', 'node', 'representative_image', 'representative_image_test'];

  public function testViewsIntegration() {
    // Setup articles to have some image fields and set one to be the
    // representative image.
    $field_name1 = 'field_image1';
    $field_name2 = 'field_image2';
    $this->createImageField($field_name1, 'article');
    $this->createImageField($field_name2, 'article');
    $this->setRepresentativeImageField('node', 'article', $field_name1);

    $image_array_1 = [];
    $image_array_2 = [];
    $count = 3;
    // Create image files to use for testing.
    for ($i = 0; $i < $count; $i++) {
      $image_array_1[] = $this->randomFile('image');
      $image_array_2[] = $this->randomFile('image');
    }

    // Create article nodes for testing.
    for ($i = 0; $i < $count; $i++) {
      $edit = array(
        'title[0][value]' => $this->randomString(),
        'files[' . $field_name1 . '_0]' => $this->fileSystem->realpath($image_array_1[$i]->uri),
        'files[' . $field_name2 . '_0]' => $this->fileSystem->realpath($image_array_2[$i]->uri),
      );
      $this->drupalPostForm('node/add/article', $edit, 'Save');
      $this->drupalPostForm(NULL, [
        $field_name1 . '[0][alt]' => $this->randomMachineName(),
        $field_name2 . '[0][alt]' => $this->randomMachineName(),
      ], 'Save');
    }

    $this->drupalGet('representative-image-views-test');
    for ($i = 0; $i < $count; $i++) {
      $this->assertImage($image_array_1[$i]);
    }

    // Switch the representative image and check that view changes
    // appropriately.
    $this->setRepresentativeImageField('node', 'article', $field_name2);
    $this->drupalGet('representative-image-views-test');
    for ($i = 0; $i < $count; $i++) {
      $this->assertImage($image_array_2[$i]);
    }
  }

}

