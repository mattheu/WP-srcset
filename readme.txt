=== WP Srcset ===

Contributors: mattheu
Tags: srcset, retina, images, high-res
Requires at least: 4.0
Tested up to: 4.0
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Very simple plugin to get retina images on your WordPress site. Automatically load high resoloution images using the new srcset attribute.

== Description ==

The new srcset attribute has recently dropped in Chrome and Firefox. It is the simplest way to get retina ready images on your site.

This plugin will automatically add the srcset attribute to your images if a suitable retina sized image can be found.

It also includes a JS polyfill - [Picturefill](https://github.com/scottjehl/picturefill) - to ensure that this works on all browsers - not just those that support srcset. This is about 6.5kb, and can easily be dequeued if you only wish to support browsers that have implemented this functionality.

This plugin does not handle generation of the alternative images, and this should either be done manually or using another plugin. See installation instructions for more information.

**This  plugin requires PHP 5.3**

== Installation ==

There are 2 ways to use the plugin:

1. You will either need to manually register image sizes for 2x versions of your images in your theme.
2. Use an on the fly image generation solution such as [WPThumb](https://github.com/humanmade/WPThumb).

Example of how to register high resoloution versions of an image.
`add_image_size( 'small', '100', '100' );
add_image_size( 'small-2x', '200', '200' );`

 == Changelog ==

1.0 - Initial release. High res images for WordPress using srcset attribute and srcset-polyfill