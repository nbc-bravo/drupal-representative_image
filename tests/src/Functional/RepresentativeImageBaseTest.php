<?php

namespace Drupal\Tests\representative_image\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Contains common logic for functional tests.
 *
 * @group representative_image
 */
class RepresentativeImageBaseTest extends JavascriptTestBase {

  use ImageFieldCreationTrait;
  use TestFileCreationTrait;

  /**
   * The file system service
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The node storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The representative image picker service.
   *
   * @var \Drupal\representative_image\RepresentativeImagePicker
   */
  protected $representativeImagePicker;

  /**
   * The array of the default image file being used by representative_image
   *
   * @var array
   */
  protected $defaultImageFile;

  /**
   * {@inheritdoc}
   */
  protected $minkDefaultDriverClass = DrupalSelenium2Driver::class;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'field_ui', 'file', 'image', 'node', 'representative_image', 'system', 'user', 'views_ui'];

  /**
   * The admin user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->fileSystem = \Drupal::service('file_system');
    $this->nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $this->representativeImagePicker = \Drupal::service('representative_image.picker');

    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'access administration pages',
      'administer site configuration',
      'administer image styles',
      'administer nodes',
      'administer node display',
      'administer content types',
      'administer node fields',
      'bypass node access',
      'administer users',
      'administer user fields',
      'administer views',
    ]);
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('local_tasks_block');
    $this->setUpArticleContentType();
  }

  /**
   * Configures the article content type for testing representative image.
   *
   * Adds two image fields and a representative image field. Then hides
   * the image fields from the default display and shows the
   * representative image field.
   */
  protected function setUpArticleContentType() {
    // Create article content type with two image fields.
    $this->defaultImageFile = $this->randomFile('image');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->createImageField('field_image1', 'article');
    $this->createImageField('field_image2', 'article');

    // Add representative image field with default image. Set it to field1.
    $edit = [
      'new_storage_type' => 'representative_image',
    ];
    $this->drupalPostForm('admin/structure/types/manage/article/fields/add-field', $edit, 'Save and continue');
    $edit = [
      'label' => 'Representative image',
      'field_name' => 'representative_image',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save and continue');
    $edit = [
      'files[settings_default_image_uuid]' => $this->fileSystem->realpath($this->defaultImageFile->uri),
    ];
    $this->drupalPostForm(NULL, $edit, 'Save field settings');
    $this->drupalPostForm(NULL, [], 'Save settings');

    // Adjust display settings so only Representative Image is shown.
    // Toggle row weight selects as visible.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->getSession()->getPage()->findButton('Show row weights')->click();
    $page = $this->getSession()->getPage();
    $page->findField('fields[field_representative_image][region]')
      ->setValue('content');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->findField('fields[field_image1][region]')
      ->setValue('hidden');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->findField('fields[field_image2][region]')
      ->setValue('hidden');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // @TODO Need to do this or RepresentativeImagePicker::getRepresentativeImageField() will return ''.
    drupal_flush_all_caches();
  }

  /**
   * Correct image assertion for given entity.
   *
   * Asserts that, given an entity, the expected image is considered the
   * representative image.
   *
   * @param object $entity
   *   The entity to test.
   * @param object $image
   *   An image object as returned by getTestFiles(image).
   * @param string $message
   *   (optional) The message to display with the test results.
   */
  function assertRepresentativeImage($entity, $entity_type, $image, $message = '') {
    $message = empty($message) ? 'The correct representative image was returned' : $message;
    $this->assertTrue(strpos(representative_image($entity, $entity_type), $image->name), $message);
  }

  /**
   * Correct field assertion for given bundle.
   *
   * Asserts that the correct field is set as the representative image field for
   * the given bundle.
   *
   * @param string $entity_type
   *   The type of entity being checked.
   * @param string $bundle_name
   *   The bundle being checked.
   * @param string $field
   *   The name of the field expected to be identified as the representative
   *   image field.
   * @param string $message
   *   (optional) The message to display with the test results.
   */
  public function assertRepresentativeImageField($entity_type, $bundle_name, $field, $message = '') {
    $message = empty($message) ? $entity_type . '\'s representative image is correctly set to ' . $field : $message;
    $this->assertTrue(representative_image_get_field($entity_type, $bundle_name) == $field, $message);
  }

  /**
   * Correct field assertion for generated image.
   *
   * Asserts that an image generated by getTestFiles(image) was displayed
   * correctly.
   *
   * @param  object $image
   *   An image object as returned by getTestFiles(image).
   * @param  string $haystack
   *   (optional) A string to search for the image path. If provided pregmatch
   *   will be used otherwise assertPattern will be called.
   * @param string $message
   *   (optional) The message to display with the test results.
   */
  public function assertImage($image, $haystack = '', $message = '') {
    $message = empty($message) ? $image->name . ' was correctly displayed' : $message;
    list($filename, $extension) = explode('.', $image->filename);
    $pattern = '/' . $filename . '(_?[0-9]?\.{1})' . $extension . '/';

    if (!empty($haystack)) {
      $this->assertTrue((bool) preg_match($pattern, $haystack), $message);
    }
    else {
      $this->assertSession()->responseMatches($pattern);
    }
  }

  /**
   * Negative field assertion for generated image.
   *
   * Asserts that an image generated by getTestFiles(image) was correctly
   * not displayed.
   *
   * @param  object $image
   *   An image object as returned by getTestFiles(image).
   * @param  string $haystack
   *   (optional) A string to search for the image path. If provided pregmatch
   *   will be used otherwise assertNoPattern will be called.
   * @param string $message
   *   (optional) The message to display with the test results.
   */
  public function assertNoImage($image, $haystack = '', $message = '') {
    $message = empty($message) ? $image->name . ' was correctly not displayed' : $message;
    list($filename, $extension) = explode('.', $image->filename);
    $pattern = '/' . $filename . '(_?[0-9]?)\.' . $extension . '/';

    if (!empty($haystack)) {
      $this->assert(!preg_match($pattern, $haystack), $message);
    }
    else {
      $this->assertNoPattern($pattern, $message);
    }
  }

  /**
   * Provides a random image object.
   *
   * @param string $type
   *   The type of file to be created for testing purposes.
   *
   * @return
   *   A file object which has the following attributes:
   *   - $file->url (for example, public://image-2.jpg)
   *   - $file->filename (for example, image-2.jpg)
   *   - $file->name (for example, image-2)
   */
  public function randomFile($type = 'image') {
    // Get all test images in the form of an array.
    $files = $this->getTestFiles($type);
    // Get the next one on the list, wrapping around if necessary.
    static $i = 0;
    return $files[($i++)%count($files)];
  }

  /**
   * Attach a field to a bundle.
   *
   * @param string $entity_type
   *   The type of entity to which this field is being added.
   * @param string $bundle_name
   *   The bundle to which this field is being added.
   *
   * @param string $field
   *   The name of the field being attached.
   */
  public function setRepresentativeImageField($entity_type, $bundle_name, $field) {
    $edit = array(
      'representative_image[' . $entity_type . '][' . $bundle_name . ']' => $field,
    );

    $this->drupalPostForm('admin/config/media/representative-image', $edit, 'Save configuration');
  }

  /**
   * Default method for representative image fallback.
   *
   * Sets the method that should be used to find a representative image when
   * none is found.
   *
   * @param string $method (optional)
   *   'logo', 'first', 'all'
   */
  public function setDefaultMethod($method = '') {
    $edit = array(
      'default_behavior' => $method,
    );
    $this->drupalPostForm('admin/config/media/representative-image', $edit, t('Save configuration'));
  }

  /**
   * Get the id for the given type of entity.
   *
   * @param  $type
   *   Entity type.
   *
   * @return string
   */
  public function getIdFromPath($type) {
    $match = array();

    switch ($type) {
      case 'node':
        $pattern = '/node\/([0-9]+)/';
        break;
      case 'file':
        $pattern = '|file/([0-9]+)|';
        break;
      case 'comment':
        $pattern = '/#comment-([0-9]+)/';
        break;
      default:
        $pattern = '';
    }

    preg_match($pattern, $this->getURL(), $match);
    $id = isset($match[1]) ? $match[1] : '';
    $this->assertTrue(!empty($id), $type . ' id found.');

    return $id;
  }

  /**
   * Create a new image field for an entity and bundle.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param string $field_name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   */
  protected function createImageFieldFor($entity_type, $bundle, $field_name) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'image',
      'settings' => [],
      'cardinality' => 1,
    ])->save();

    $field_config = FieldConfig::create([
      'field_name' => $field_name,
      'label' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'required' => FALSE,
      'settings' => [],
      'description' => '',
    ]);
    $field_config->save();

    entity_get_form_display($entity_type, $bundle, 'default')
      ->setComponent($field_name, [
        'type' => 'image_image',
        'settings' => [],
      ])
      ->save();

    entity_get_display($entity_type, $bundle, 'default')
      ->setComponent($field_name, [
        'type' => 'image',
        'settings' => [],
      ])
      ->save();
  }

  /**
   * Create a representative image field for an entity and bundle.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   */
  protected function createRepresentativeImageFieldFor($entity_type, $bundle) {
    FieldStorageConfig::create([
      'field_name' => 'representative_image',
      'entity_type' => $entity_type,
      'type' => 'representative_image',
      'settings' => [],
      'cardinality' => 1,
    ])->save();

    $field_config = FieldConfig::create([
      'field_name' => 'representative_image',
      'label' => 'Representative image',
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'required' => FALSE,
      'settings' => [],
      'description' => '',
    ]);
    $field_config->save();

    entity_get_form_display($entity_type, $bundle, 'default')
      ->setComponent('representative_image', [
        'type' => 'image_image',
        'settings' => [],
      ])
      ->save();

    entity_get_display($entity_type, $bundle, 'default')
      ->setComponent('representative_image', [
        'type' => 'image',
        'settings' => [],
      ])
      ->save();
  }

}
