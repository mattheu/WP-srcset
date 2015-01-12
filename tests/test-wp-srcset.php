<?php

class TestWPSrcset extends WP_UnitTestCase {

	private $plugin_instance;
	private $test_image_id;

	function setUp() {

		parent::setUp();

		$this->test_image_id = $this->setupUploadAttachment( __DIR__ . '/assets/dog.JPG' );

	}

	function tearDown() {
		wp_delete_attachment( $this->test_image_id, true );
		unset( $this->test_image_id );
		parent::tearDown();
	}

	function setupUploadAttachment( $source_file ) {

		// Get upload dir & work out file destination path.
		$wp_upload_dir = wp_upload_dir();
		$file          = trailingslashit( $wp_upload_dir['path'] ) . basename( $source_file );
		$filetype      = wp_check_filetype( basename( $source_file ), null );

		// Copy test file to uploads directory.
		copy( $source_file, $file );

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $file ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Insert the attachment.
		$attachment_id = wp_insert_attachment( $attachment, $file );

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $file );

		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;

	}

	function getPathFromUploadUrl( $img ) {

		if ( ! $img ) {
			return '';
		}

		$wp_upload_dir = wp_upload_dir();
		return str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $img );
	}

	function testImageSizeStringCropped() {

		$size = 'test_small_cropped';

		$img = WP_Srcset::get_instance()->get_alt_img_src( $this->test_image_id, $size, 2 );

		$this->assertNotEmpty( $img );

		$image_size = getimagesize( $this->getPathFromUploadUrl( $img ) );

		// Check that the alt image at 2x resolution is 100x100.
		$this->assertEquals( $image_size[0], 100 );
		$this->assertEquals( $image_size[1], 100 );

	}

	function testImageSizeStringUncropped() {

		$size = 'test_small_uncropped';

		$img = WP_Srcset::get_instance()->get_alt_img_src( $this->test_image_id, $size, 2 );

		$this->assertNotEmpty( $img );

		$image_size = getimagesize( $this->getPathFromUploadUrl( $img ) );

		$this->assertEquals( $image_size[0], 100 );
		$this->assertEquals( $image_size[1], 67 );

	}
}

