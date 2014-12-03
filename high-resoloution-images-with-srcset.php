<?php

/*
Plugin Name: x WordPress Srcset
Description: Automatic high resolution retina images using srcset.
Version: 1.0.1
Author: Human Made Limited
Author URI: http://hmn.md/
*/

/*  Copyright 2011 Human Made Limited  (email : hello@humanmade.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class HM_WordPress_Srcset {

	private $plugin_url;
	private $multipliers;

	function __construct() {

		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->multipliers = apply_filters( 'hm_wp_srcset_multipliers', array( 2 ) );

		register_activation_hook( __FILE__ , array( $this, 'plugin_activation_check' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'image_attributes' ), 10, 3 );

	}

	/**
	 * Enqueue polyfill script.
	 *
	 * @return null
	 */
	function enqueue_scripts() {

		wp_enqueue_script( 'picturefill', $this->plugin_url . '/picturefill/dist/picturefill.min.js', false, false, true );

	}

	/**
	 * Plugin requires WP 4.1
	 * or die.
	 *
	 * @return null
	 */
	function plugin_activation_check() {

		if ( version_compare( get_bloginfo('version'), '4.1-beta', '<=' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die( "Plugin requires WordPress version 4.1." );
		}

	}

	/**
	 * Filter image attributes and add the srcset attr.
	 *
	 * @param  array $attr
	 * @param  WP_Post $attachment
	 * @return array attributes
	 */
	function image_attributes( $attr, $attachment, $size ) {

		$requested_image = wp_get_attachment_image_src( $attachment->ID, $size );
		$size_args       = array( 'width' => $requested_image[1], 'height' => $requested_image[2] );
		$srcset          = array();

		foreach ( $this->multipliers as $multiplier ) {
			if ( $src = $this->get_alt_img_src( $attachment->ID, $size_args, $multiplier ) ) {
				array_push( $srcset, sprintf( '%s %dx', $src, $multiplier ) );
			}
		}

		$attr['src']    = $requested_image[0];
		$attr['srcset'] = implode( ', ', $srcset );

		return $attr;

	}

	/**
	 *	Add retina image attr to content images when inserting
	 */
	function image_send_to_editor( $html, $attachment_id, $caption, $title, $align, $url, $size, $alt = '' ) {

		$requested_image = wp_get_attachment_image_src( $attachment_id, $size );
		$size_args       = array( 'width' => $requested_image[1], 'height' => $requested_image[2] );
		$srcset          = array();

		$attr['src'] = $requested_image[0];

		foreach ( $this->multipliers as $multiplier ) {
			if ( $src = $this->get_alt_img_src( $attachment_id,  $size_args, $multiplier ) ) {
				array_push( $srcset, sprintf( '%s %dx', $src, $multiplier ) );
			}
		}

		$html = preg_replace( '/src="\w*"/', 'src="' . $src . '"', $html );
		$html = str_replace( '/>', 'srcset="' . implode( ', ', $srcset ) . '" />', $html );

		return $html;

	}

	/**
	 * Get the src for an alternate sized version of an attachment.
	 *
	 * @param  string/int $attachment_id
	 * @param  Array  $size array of width, height args.
	 * @param  int $multiplier return src for image at x times the size.
	 * @return string src.
	 */
	function get_alt_img_src( $attachment_id, Array $size, $multiplier ) {

		$alt_size = array(
			$size['width']  * $multiplier,
			$size['height'] * $multiplier,
		);

		$alt_img = wp_get_attachment_image_src( $attachment_id, $alt_size );

		// Return if the alt image is not exactly the requested size.
		if ( $alt_img[1] != $alt_size[0] && $alt_img[2] != $alt_size[1] ) {
			return;
		}

		return $alt_img[0];

	}

}

$retina = new HM_WordPress_Srcset();
