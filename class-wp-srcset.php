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

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	function __construct() {

		$this->plugin_url = plugin_dir_url( __FILE__ );

		/**
		 * Allow filtering of supported resolutions.
		 * @var [type]
		 */
		$this->resolutions = apply_filters( 'hm_wp_srcset_resolutions', $this->resolutions );
		$this->resolutions = array_map( 'absint', $this->resolutions );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'init', array( $this, 'register_2x_image_sizes' ), 1000 );
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

		$srcset = array();

		foreach ( $this->resolutions as $multiplier ) {
			if ( $src = $this->get_alt_img_src( $attachment->ID, $size, $multiplier ) ) {
				array_push( $srcset, sprintf( '%s %dx', $src, $multiplier ) );
			}
		}

		if ( ! empty( $srcset ) ) {
			$attr['srcset'] = implode( ', ', $srcset );
		}

		return $attr;

	}

	/**
	 *	Add retina image attr to content images when inserting
	 */
	function filter_image_send_to_editor( $html, $attachment_id, $caption, $title, $align, $url, $size, $alt = '' ) {

		$srcset = array();

		foreach ( $this->resolutions as $multiplier ) {
			if ( $src = $this->get_alt_img_src( $attachment->ID, $size, $multiplier ) ) {
				array_push( $srcset, sprintf( '%s %dx', $src, $multiplier ) );
			}
		}

		if ( ! empty( $srcset ) ) {
			$attr['srcset'] = implode( ', ', $srcset );
		}

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
	function get_alt_img_src( $attachment_id, $size, $multiplier ) {

		$requested_image = wp_get_attachment_image_src( $attachment_id, $size );

		if ( is_string( $size ) ) {

			$alt_img = wp_get_attachment_image_src( $attachment_id, $size . $multiplier . 'x' );

		} else {

			$size_args = array(
				'width' => $requested_image[1] * $multiplier,
				'height' => $requested_image[2] * $multiplier
			);

			if ( isset( $size[2] ) ) {
				$size_args['crop'] = $size[2];
			} elseif ( $size['crop'] ) {
				$size_args['crop'] = $size['crop'];
			}

			$alt_img = wp_get_attachment_image_src( $attachment_id, $size_args );

		}

		// Return if the alt image is not exactly the requested size.
		// TODO - handle rounding errors.
		if (
			$alt_img[1] != $requested_image[1] * $multiplier
			|| $alt_img[2] != $requested_image[2] * $multiplier
		) {
			return;
		}

		return $alt_img[0];

	}

	function register_2x_image_sizes() {

		global $_wp_additional_image_sizes;

		foreach ( get_intermediate_image_sizes() as $size ) {

			if ( false !== strpos( $size, '2x' ) ) {
				continue;
			}

			if ( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$args = array(
					'width'  => get_option( 'thumbnail_size_w' ),
					'height' => get_option( 'thumbnail_size_h' ),
					'crop'   => get_option( 'thumbnail_crop' )
				);
			} elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$args = $_wp_additional_image_sizes[ $size ];
			} else {
				continue;
			}

			foreach ( $this->resolutions as $multiplier ) {
				add_image_size(
					$size . $multiplier . 'x',
					$args['width'] * $multiplier,
					$args['height'] * $multiplier,
					$args['crop']
				);
			}

		}
	}
}
