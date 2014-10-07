<?php

class FieldTestCase extends WP_UnitTestCase {

	private $attachment_id;

	function setUp() {

		parent::setUp();

		add_image_size( 'test', '100', '100', true );
		add_image_size( 'test2x', '200', '200', true );

		$filepath            = __DIR__ . '/resources/image.jpg';
		$this->attachment_id = $this->create_attachment_from_local_file( $filepath );

		if ( ! is_numeric( $this->attachment_id ) ) {
			wp_die( 'Creating test image failed' );
		}

	}

	function tearDown() {
		wp_delete_attachment( $this->attachment_id, true );
		unset( $this->attachment_id );
		parent::tearDown();
	}

	function test2x() {

		$image  = wp_get_attachment_image( $this->attachment_id );
		$result = preg_match( "/srcset=\"(.+?)\"/u", $image, $matches );

		print_r( $image );
		if ( ! $result ) {
			$this->assertFalse( 'srcset attribute not created.' );
		}

		// Convert the 'srcset' attribute to array of single srcsets and multiplier
		$srcset = array_map( function( $single_srcset ) {

			$multiplier = preg_match( '/ (\d+?)x/', $single_srcset, $multiplier_matches );

			if ( ! $multiplier ) {
				$this->assertFalse( 'srcset multiplier not set.' );
			}

			return array(
				'multiplier' => $multiplier_matches[1],
				'src'        => trim( str_replace( $multiplier_matches[0] , '', $single_srcset ) ),
			);

		}, explode( ',', $matches[1] ) );


		print_r( $srcset );
	}

	function create_attachment_from_local_file( $filepath ) {

		$uploads  = wp_upload_dir();
		$filetype = wp_check_filetype( basename( $filepath ), null );

		copy( $filepath, trailingslashit( $uploads['path'] ) . basename( $filepath ) );
		$filepath = trailingslashit( $uploads['path'] ) . basename( $filepath );

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => trailingslashit( $wp_upload_dir['url'] ) . basename( $filepath ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filepath ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attachment_id = wp_insert_attachment( $attachment, $filepath );

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Generate the metadata for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filepath );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		return $attachment_id;

	}

}