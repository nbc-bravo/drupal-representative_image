This module allows you to define representative image or media fields for entities like nodes, taxonomy terms
and the like. These can then be used in Open Graph meta tags (via tokens); or as fields in views.
The media module is also supported. A default image can be defined for those entities without images.

== EXAMPLE 1 ==
Imagine you want to setup opengraph metadata to display an image with your
content, but different content types have different image fields. With this you
can set all your content types to simply use the representative image field.

== EXAMPLE 2 ==
If you want to create an administrative view of all your content similar
to the table found at /admin/content you can easily show thumbnail with your
content by including the representative image field in views.

Features
========

* A setting in admin/structure/types/manage/my_content_type allowing you to select a representative image field. For example, for articles the representative field might be image, but for some other content type it might be logo, etc.</li>
* A token [node:representative_image], which you can use anywhere you use tokens, which gives you a full URL to that Image. If you don't need a token, you can also use the representative_image() function. Tokens can the be used with <a href="http://drupal.org/project/metatag">Metatag Open Graph</a> module to display, in the og:image meta tag, a representative image for your node, which might be different per node type.</li>
* Support for image fields, even when using the <a href="/project/media">media module (>= 2.x)</a>.</li>
* Views support. For example, lets say you want a view of all content, including a thumbnail but each content type has a unique image field. Instead of using some views hackery, just add the "Representative Image" field in your view and Voila!</li>

Sample usage of token integration for OpenGraph meta
====================================================

(1) Download representative image, <a href="http://drupal.org/project/metatag">Metatag</a> and <a href="http://drupal.org/project/token">Token</a></li>
(2) Create an image field for Page, and another for Article</li>
(3) Make sure you set the image fields you created in step (2) as the representative images in admin/structure/types/manage/page and in admin/structure/types/manage/article</li>
(4) Enable metatag and metatag_opengraph (this module allows you to specify information when sharing node pages on Facebook, for example)</li>
(5) Go to admin/config/search/metatags/config/node</li>
(6) In the OpenGraph section, in the Open Graph Image fiend, enter [node:representative_image]</li>
(7) In admin/config/search/metatags/config/global, in the OpenGraph section, in the Open Graph Image fiend, enter sites/all/themes/my_theme/path/to/my/logo.png</li>
(8) Create an article with an image (say, node/1)</li>
(9) Create a page with an image (say, node/2)</li>
(10) Create an article with no image (say, node/3)</li>

See how these pages behave when you share them on Facebook: the expected image appears as representative of these nodes.

Limitations
===========

* If you have installed a version prior to version 7.x-1.0-beta1, the upgrade path is not tested and might not work.</li>
* While there is views support for representative image, there is currently no views support for entities that are loaded using a views relationship.</li>

Deploying / exporting
=====================

Most of the settings are variables, so you can either use <a href="http://drupal.org/project/strongarm">strongarm</a>, or use a hook_update_n() in your module, something like:
<?php
/**
 * Implements hook_update_n(). Set up representative images
 */
function mymodule_update_7005() {
  variable_set('representative_image_default', 'sites/default/files/path/to/default.png');
  // set other representative_image variables here. You can examine them by installing devel and visiting devel/variable
}
?>

