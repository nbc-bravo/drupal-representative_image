<?php

namespace Drupal\Tests\representative_image\Functional;


/**
 * Test the token integration of representative images.
 *
 * @group representative_image
 */
class RepresentativeImageTokenTest extends RepresbentativeImageBaseTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['field_ui', 'file', 'image', 'node', 'representative_image'];

  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests custom tokens.
   *
   * Test that the custom tokens defined by representative image return the
   * expected values.
   */
  public function testTokenIntegration() {
    /** @var \Drupal\Core\Utility\Token $token */
    $token = \Drupal::token();

    // Setup articles to have some image fields.
    $field_image1 = 'field_image1';
    $field_image2 = 'field_image2';
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->createImageField($field_image1, 'article');
    $this->createImageField($field_image2, 'article');

    $image1 = $this->randomFile('image');
    $image2 = $this->randomFile('image');

    // Create an article node for testing.
    $edit = array(
      'title[0][value]' => $this->randomString(),
      'files[' . $field_image1 . '_0]' => $this->fileSystem->realpath($image1->uri),
      'files[' . $field_image2 . '_0]' => $this->fileSystem->realpath($image2->uri),
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    $this->drupalPostForm(NULL, [
      $field_image1 . '[0][alt]' => $this->randomMachineName(),
      $field_image2 . '[0][alt]' => $this->randomMachineName(),
    ], t('Save'));
    $node = $this->nodeStorage->load($this->getIdFromPath('node'));

    // Confirm that the correct image is being replaced properly.
    $this->setRepresentativeImageField('node', 'article', $field_image1);
    $replacement = $token->replace("foo [node:representative_image] bar", ['node' => $node]);
    $this->assertImage($image1, $replacement);
    $this->assertNoImage($image2, $replacement);

    // Switch the representative image and confirm the representative image is
    // being replaced properly.
    $this->setRepresentativeImageField('node', 'article', $field_image2);
    $replacement = $token->replace("foo [node:representative_image] bar", ['node' => $node]);
    $this->assertImage($image2, $replacement);
    $this->assertNoImage($image1, $replacement);
  }

}
