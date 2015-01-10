<?php

class WP_Srcset {

	/**
	 * Plugin directory URL.
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Supported resolutions.
	 * The plugin will to provide images for screens at these resoloutions.
	 * eg 1.5, 3 etc.
	 * @var array - Array of integers.
	 */
	private $resolutions = array( 2 );

	function __construct() {

		$this->plugin_url  = plugin_dir_url( __FILE__ );

		/**
		 * Allow filtering of supported resolutions.
		 * @var [type]
		 */
		$this->resolutions = apply_filters( 'hm_wp_srcset_resolutions', $this->resolutions );
		$this->resolutions = array_map( 'absint', $this->resolutions );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_image_attributes' ), 10, 3 );
		add_filter( 'image_send_to_editor', array( $this, 'filter_image_send_to_editor' ), 10, 8 );

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
	 * Should be registered from the main
	 * plugin file using register_activation_hook
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
	function filter_image_attributes( $attr, $attachment, $size ) {

		$requested_image = wp_get_attachment_image_src( $attachment->ID, $size );
		$size_args       = array( 'width' => $requested_image[1], 'height' => $requested_image[2] );
		$srcset          = array();

		foreach ( $this->resolutions as $multiplier ) {
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
	function filter_image_send_to_editor( $html, $attachment_id, $caption, $title, $align, $url, $size, $alt = '' ) {

		$requested_image = wp_get_attachment_image_src( $attachment_id, $size );
		$size_args       = array( 'width' => $requested_image[1], 'height' => $requested_image[2] );
		$src             = $requested_image[0];
		$srcset          = array();

		foreach ( $this->resolutions as $multiplier ) {
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
		if ( $alt_img[1] != $alt_size[0] || $alt_img[2] != $alt_size[1] ) {
			return;
		}

		return $alt_img[0];

	}

}
