<?php

// Test Setup
// Do setup for all tests.
// This has to be done here because WP_UnitTestCase setUp method is called after init.

add_action( 'init', function() {
	add_image_size( 'category-thumb', 300 ); // 300 pixels wide (and unlimited height)
	add_image_size( 'test_small_cropped', 50, 50, true );
	add_image_size( 'test_small_uncropped', 50, 50 );
}, 1 );
