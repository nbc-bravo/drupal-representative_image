<?php

namespace Drupal\Tests\representative_image\Functional;

use Drupal\comment\Tests\CommentTestTrait;

/**
 * Test that entities can have associated representative image fields.
 *
 * @group representative_image
 */
class RepresentativeImageEntitiesTest extends RepresentativeImageBaseTest {

  use CommentTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['comment', 'field_ui', 'file', 'image', 'node', 'representative_image'];

  /**
   * Confirm that the defaults are sensible out of the box.
   */
  public function testDefaults() {
    // Create a content type and add an image field to it.
    $field_name = strtolower($this->randomMachineName());
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->createImageField($field_name, 'article');

    // Create a test node with some images.
    $image0 = $this->randomFile('image');
    $image1 = $this->randomFile('image');
    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'files[' . $field_name . '_0]' => $this->fileSystem->realpath($image0->uri),
    );
    $this->drupalPostForm('node/add/article', $edit, 'Save');
    $this->drupalPostForm(NULL, [$field_name . '[0][alt]' => $this->randomMachineName()], t('Save'));
    $nid_with_image = $this->getIdFromPath('node');
    $node_with_image = $this->nodeStorage->load($nid_with_image);

    // Create a test node without any images.
    $edit = array(
      'title[0][value]' => ($this->randomString()),
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    $nid_without_image = $this->getIdFromPath('node');
    $node_without_image = $this->nodeStorage->load($nid_without_image);

    // Set default to "logo" and check that it works.
    $this->setDefaultMethod('logo');
    $this->assertEquals($this->representativeImagePicker->getLogoUrl(), $this->representativeImagePicker->from($node_with_image), 'The global default image out of the box is the logo.');

    // Set default to "find" and check that it works.
    $this->setDefaultMethod('first');
    $this->assertImage($image0, $this->representativeImagePicker->from($node_with_image), 'The global default image out of the box is the first image from the first image field.');

    // Set default to "first_or_logo" and check that it works. Then edit the
    // node to give it an image and check again.
    $this->setDefaultMethod('first_or_logo');
    $this->assertEquals($this->representativeImagePicker->getLogoUrl(), $this->representativeImagePicker->from($node_without_image), 'The global default image out of the box is the logo.');
    $edit = array(
      'files[' . $field_name . '_0]' => $this->fileSystem->realpath($image1->uri),
    );
    $this->drupalPostForm('node/' . $nid_without_image . '/edit', $edit, t('Save'));
    $this->drupalPostForm(NULL, [$field_name . '[0][alt]' => $this->randomMachineName()], t('Save'));
    $this->nodeStorage->resetCache([$nid_without_image]);
    $node_without_image = $this->nodeStorage->load($nid_without_image);
    $this->assertImage($image1, $this->representativeImagePicker->from($node_without_image), 'The global default image out of the box is the first image from the first image field.');
  }

  /**
   * Confirm that node entities can have representative images.
   */
  public function testNodeTest() {
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Add field_image to the Page and Article content types.
    $field_name1 = 'field_page_image';
    $field_name2 = 'field_page_image2';
    $field_name3 = 'field_article_image';
    $this->createImageField($field_name1, 'page');
    $this->createImageField($field_name3, 'article');

    // Add a new field for Page and set the representative images for the article
    // and page content types.
    $field_name2 = 'image2';
    $this->createImageField($field_name2, 'page');
    $this->setRepresentativeImageField('node', 'page', $field_name2);
    $this->setRepresentativeImageField('node', 'article', $field_name3);

    // Grab some dummy images.
    $images = [];
    for ($i = 0; $i < 3; $i++) {
      $images[$i] = $this->randomFile('image');
    }
    // Now create a new Article with a given image.
    $edit = [
      'title[0][value]' => $this->randomString(),
      'files[' . $field_name3 . '_0]' => $this->fileSystem->realpath($images[0]->uri),
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    $this->drupalPostForm(NULL, [$field_name3 . '[0][alt]' => $this->randomMachineName()], t('Save'));

    // Now create a new Page with two images, one per field (this will allow us
    // to test if the correct image is returned as "representative").
    $edit = array(
      'title[0][value]' => $this->randomString(),
      'files[' . $field_name1 . '_0]' => $this->fileSystem->realpath($images[1]->uri),
      'files[' . $field_name2 . '_0]' => $this->fileSystem->realpath($images[2]->uri),
    );
    $this->drupalPostForm('node/add/page', $edit, 'Save');
    $this->drupalPostForm(NULL, [
      $field_name1 . '[0][alt]' => $this->randomMachineName(),
      $field_name2 . '[0][alt]' => $this->randomMachineName(),
    ], 'Save');

    $this->nodeStorage->resetCache([1, 2]);
    $this->assertImage($images[0], $this->representativeImagePicker->from($this->nodeStorage->load(1)));
    $this->assertImage($images[2], $this->representativeImagePicker->from($this->nodeStorage->load(2)));
  }

  /**
   * Confirm that user entities can have representative images.
   */
  public function testUserTest() {
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    // Add an image field to the default user bundle and set it as the
    // representative image. By running this process twice we test that changing
    // the representative image field on users works properly.
    for ($i = 1; $i <= 2; $i++) {
      $image = $this->randomFile('image');
      $field_name = 'field_image' . $i;
      $this->createImageFieldFor('user', 'user', $field_name);

      // Add an image to the admin user.
      $edit = array(
        'files[' . $field_name . '_0]' => $this->fileSystem->realpath($image->uri),
      );
      $this->drupalPostForm('user/' . $this->adminUser->id(). '/edit', $edit, 'Save');
      $this->drupalPostForm(NULL, [$field_name . '[0][alt]' => $this->randomMachineName()], t('Save'));

      // Test its representative image.
      $user_storage->resetCache([$this->adminUser->id()]);
      $this->adminUser = $user_storage->load($this->adminUser->id());
      $this->setDefaultMethod('');
      $this->assertEmpty($this->representativeImagePicker->from($this->adminUser), 'By default no representative image is set for users.');
      $this->setDefaultMethod('first_or_logo');
      $this->assertImage($image, $this->representativeImagePicker->from($this->adminUser), 'User has the right representative image.');
    }
  }

  /**
   * Confirm that comment entities can have representative images.
   */
  public function testCommentTest() {
    // Adjust the user permissions.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'administer nodes',
      'administer content types',
      'administer node fields',
      'bypass node access',
      'administer users',
      'administer user fields',
      'access comments',
      'post comments',
    ]);
    $this->drupalLogin($this->adminUser);

    // Create the article content type and enable commenting.
    $comment_storage = \Drupal::entityTypeManager()->getStorage('comment');
    $content_type = 'article';
    $this->drupalCreateContentType(['type' => $content_type, 'name' => 'Article']);
    $this->addDefaultCommentField('node', $content_type);

    // Add an image field to the article comment bundle and set it as the
    // representative image.
    $image = $this->randomFile('image');
    $field_name = 'field_image';
    $this->createImageFieldFor('comment', 'comment', $field_name);
    $this->setRepresentativeImageField('comment', 'comment', $field_name);

    // Add an image to a comment on a node.
    $node = $this->drupalCreateNode(['type' => $content_type]);
    $edit = [
      'comment_body[0][value]' => $this->randomString(),
      'files[' . $field_name . '_0]' => $this->fileSystem->realpath($image->uri),
    ];
    $this->drupalPostForm('node/' . $node->id(), $edit, t('Save'));
    $this->drupalPostForm(NULL, [$field_name . '[0][alt]' => $this->randomMachineName()], t('Save'));
    $comment_id = $this->getIdFromPath('comment');

    if (!empty($comment_id)) {
      $comment_storage->resetCache([$comment_id]);
      $comment = $comment_storage->load($comment_id);
      $this->assertImage($image, $this->representativeImagePicker->from($comment), 'Comment has the right representative image.');
    }
    else {
      $this->fail('Could not post comment');
    }
  }

  // @TODO There should be a test here for media entity.
}
