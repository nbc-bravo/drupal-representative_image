== This module provides: ==

(1) An field in admin/structure/types/manage/my_content_type allowing you to select a representative image field.

(2) A token [node:representative-image], which you can use anywhere you use tokens, which gives you a full URL to that Image. If you don't need a token, you
can also use the representative_image() function

== Sample usage with OpenGraph meta: ==

(1) Install representative image
(2) Create an image field for Page, and another for Article
(3) Make sure you set the field as the representative image in admin/structure/types/manage/page and in admin/structure/types/manage/article
(4) download the metatag module and enable metatag_opengraph (this module allows you to specify information when sharing node pages on Facebook, for example)
(5) Go to admin/config/search/metatags/config/node
(6) In the OpenGraph section, in the Open Graph Image fiend, enter [node:representative-image]
(7) In admin/config/search/metatags/config/global, in the OpenGraph section, in the Open Graph Image fiend, enter sites/all/themes/my_theme/path/to/my/logo.png
(8) Create an article with an image (say, node/1)
(9) Create a page with an image (say, node/1)
(10) Create an article with no image (say, node/1)

== Limitations ==

Only works with nodes

== Setting a default value ==

(1) install drush (drupal.org/project/drush) on your computer
(2) on a command line, type drush vset representative_image_default 'sites/all/path/to/your/default/image.png'

== Deploying / exporting ==

Use strongarm and features or deploy something like this in mymodule.install
To help you build this, see the _representative_image_deploy_helper()
function.

/**
 * Implements hook_update_n(). Set up representative images
 */
function mymodule_update_7005() {
  variable_set('representative_image_default', 'sites/default/files/path/to/default.png');
  variable_set('representative_image_fields', array(
    'article' => 'image',
    'my_content_type' => 'my_field',
  ));
}
