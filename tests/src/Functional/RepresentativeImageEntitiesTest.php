<?php

namespace Drupal\Tests\representative_image\Functional;

/**
 * Tests representative images in entities.
 *
 * @group representative_image
 */
class RepresentativeImageEntitiesCase extends RepresentativeImageBaseTest {

  public function setUp() {
    parent::setUp();
  }

  public static function getInfo() {
    return array(
      'name' => 'Representative Image functionality',
      'description' => 'Test that entities can have associated representative image fields.',
      'group' => 'Representative Image',
    );
  }

  /**
   * Confirm that the defaults are sensible out of the box.
   */
  public function testDefaults() {
    // Add some image fields to the article content type.
    $new_field_label1 = $this->createImageField('admin/structure/types/manage/article/fields');

    // Create a test node with some images.
    $image0 = $this->randomFile('image');
    $image1 = $this->randomFile('image');
    $edit = array(
      'title' => ($this->randomName()),
      'files[field_image_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image0->uri),
      'files[field_' . $new_field_label1 . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image1->uri),
    );
    $this->drupalPost('node/add/article', $edit, t('Save'));
    $nid_with_image = $this->getIdFromPath('node');

    // Create a test node without any images.
    $edit = array(
      'title' => ($this->randomName()),
    );
    $this->drupalPost('node/add/article', $edit, t('Save'));
    $nid_without_image = $this->getIdFromPath('node');

    // Set default to "logo" and check that it works.
    $this->setDefaultMethod('logo');
    $this->assertTrue(representative_image(node_load($nid_with_image, TRUE), 'node') == theme_get_setting('logo'), 'The global default image out of the box is the logo.');

    // Set default to "find" and check that it works.
    $this->setDefaultMethod('first');
    $this->assertImage($image0, representative_image(node_load($nid_with_image, TRUE), 'node'), 'The global default image out of the box is the first image from the first image field.');

    // Set default to "first_or_logo" and check that it works. Then edit the
    // node to give it an image and check again.
    $this->setDefaultMethod('first_or_logo');
    $this->assertTrue(representative_image(node_load($nid_without_image, TRUE), 'node') == theme_get_setting('logo'), 'The global default image out of the box is the logo.');
    $edit = array(
      'files[field_' . $new_field_label1 . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image1->uri),
    );
    $this->drupalPost('node/' . $nid_without_image . '/edit', $edit, t('Save'));
    $this->assertImage($image1, representative_image(node_load($nid_without_image), 'node'), 'The global default image out of the box is the first image from the first image field.');

  }

  /**
   * Confirm that node entities can have representative images.
   */
  public function testNodeTest() {
    // Add the existing image field (field_image) for Page.
    $edit = array(
      'fields[_add_existing_field][field_name]' => 'field_image',
      'fields[_add_existing_field][widget_type]' => 'image_image',
      'fields[_add_existing_field][label]' => $this->randomName(),
    );
    $this->drupalPost('admin/structure/types/manage/page/fields', $edit, 'Save');
    $this->drupalPost(NULL, array(), 'Save settings');

    // Add a new field for Page and set the representative images for the article
    // and page content types.
    $new_field_label = $this->createImageField('admin/structure/types/manage/page/fields');
    $this->setRepresentativeImageField('node', 'page', 'field_' . $new_field_label);
    $this->setRepresentativeImageField('node', 'article', 'field_image');

    // Grab some dummy images.
    $images = array();
    for ($i = 0; $i < 3; $i++) {
      $images[$i] = $this->randomFile('image');
    }
    // Now create a new Article with a given image.
    $edit = array(
      'title' => $this->randomName(),
      'files[field_image_' . LANGUAGE_NONE . '_0]' => drupal_realpath($images[0]->uri),
    );
    $this->drupalPost('node/add/article', $edit, t('Save'));

    // Now create a new Page with two images, one per field (this will allow us
    // to test if the correct image is returned as "representative").
    $edit = array(
      'title' => ($article_image = $this->randomName()),
      'files[field_image_' . LANGUAGE_NONE . '_0]' => drupal_realpath($images[1]->uri),
      'files[field_' . $new_field_label . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($images[2]->uri),
    );
    $this->drupalPost('node/add/page', $edit, t('Save'));

    $this->assertRepresentativeImageField('node', 'page', 'field_' . $new_field_label, 'Page\'s representative image is a new image field (even though field_image does exist.');
    $this->assertRepresentativeImageField('node', 'article', 'field_image');
    $this->assertRepresentativeImage(node_load(1), 'node', $images[0]);
    $this->assertRepresentativeImage(node_load(2), 'node', $images[2]);
  }

  /**
   * Confirm that user entities can have representative images.
   */
  public function testUserTest() {
    // Add an image field to the default user bundle and set it as the
    // representative image. By running this process twice we test that changing
    // the representative image field on users works properly.
    for ($i = 1; $i <= 2; $i++) {
      $this->resetStaticVariables();

      $image = $this->randomFile('image');

      $new_field_label = $this->createImageField('admin/config/people/accounts/fields');
      $this->setRepresentativeImageField('user', 'user', 'field_' . $new_field_label);

      // Add an image to the admin user.
      $edit = array(
        'files[field_' . $new_field_label . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image->uri),
      );

      $this->drupalPost('user/' . $this->admin->uid . '/edit', $edit, t('Save'));
      $this->assertRepresentativeImageField('user', 'user', 'field_' . $new_field_label);
      $this->assertRepresentativeImage(user_load($this->admin->uid, TRUE), 'user', $image);
    }
  }

  /**
   * Confirm that comment entities can have representative images.
   */
  public function testCommentTest() {
    $content_type = 'article';

    // Add an image field to the article comment bundle and set it as the
    // representative image. By running this process twice we test that changing
    // the representative image field on comments works properly.
    for ($i = 1; $i <= 2; $i++) {
      $this->resetStaticVariables();

      $image = $this->randomFile('image');

      $new_field_label = $this->createImageField('admin/structure/types/manage/' . $content_type . '/comment/fields');
      $this->setRepresentativeImageField('comment', 'comment_node_' . $content_type, 'field_' . $new_field_label);

      // Add an image to a comment on a node.
      $node = $this->drupalCreateNode(array('type' => $content_type));
      $edit = array(
        'comment_body[' . LANGUAGE_NONE . '][0][value]' => $this->randomString(),
        'files[field_' . $new_field_label . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image->uri),
      );
      $this->drupalPost('comment/reply/' . $node->nid, $edit, t('Save'));
      $cid = $this->getIdFromPath('comment');

      if (!empty($cid)) {
        $this->assertRepresentativeImageField('comment', 'comment_node_' . $content_type, 'field_' . $new_field_label);
        $this->assertRepresentativeImage(comment_load($cid, TRUE), 'comment', $image);
      }

    }
  }

  public function testFileTest() {
    // =========================================================================
    // Imprtant note: This test assumes file_entity-7.x-2.x-unstable7 or higher.
    // =========================================================================
    $file_type = 'document';

    // Add an image field to the file image bundle and set it as the
    // representative image. The language cache needs to be reset, otherwise the
    // fields are not returned properly.
    drupal_static_reset('field_language');
    $file = $this->randomFile($file_type == 'document' ? 'text' : $file_type);
    $image = $this->randomFile('image');

    $new_field_label = $this->createImageField('admin/structure/file-types/manage/' .$file_type  . '/fields');
    $this->setRepresentativeImageField('file', $file_type, 'field_' . $new_field_label);

    // First create the file entity. Do not confuse this with $file.
    $edit = array(
      'files[upload]' => drupal_realpath($file->uri),
    );
    $this->drupalPost('file/add', $edit, t('Next'));
    $this->drupalPost(NULL, array('scheme' => 'public'), t('Next'));
    $this->drupalPost(NULL, array(), t('Save'));
    $this->clickLink($file->filename);

    $fid = $this->getIdFromPath('file');

    // Now upload an image to our custom field.
    $edit = array(
      'files[field_' . $new_field_label . '_' . LANGUAGE_NONE . '_0]' => drupal_realpath($image->uri),
    );
    $this->drupalPost('file/' . $fid . '/edit', $edit, t('Save'));

    // Get the full file entity.
    $files = entity_load('file', array($fid), array(), TRUE);
    $file = array_pop($files);

    $this->assertRepresentativeImageField('file', $file_type, 'field_' . $new_field_label);
    $this->assertRepresentativeImage($file, 'file', $image);
  }

}
