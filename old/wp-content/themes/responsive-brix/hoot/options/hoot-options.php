<?php
/**
 * Hoot Options framework is an extended version of the
 * Options Framework, Copyright 2010 - 2014, WP Theming http://wptheming.com
 * and is licensed under GPLv2
 *
 * This file is loaded at 'after_setup_theme' hook with 4 priority.
 *
 * @package hoot
 * @subpackage options-framework
 * @since hoot 1.0.0
 */

/* Include Helper functions */
require_once( trailingslashit( HOOTOPTIONS_DIR ) . 'includes/helpers.php' );

/**
 * Inititalize Hoot Options
 *
 * @since 1.0.0
 * @param bool $loadonly only load Options files, do not initiate
 */
if ( is_admin() && ! function_exists( 'hootoptions_init' ) ) :

	function hootoptions_init( $loadonly = false ) {

		//  If user can't edit theme options, and if its not a 'loadonly', exit
		if ( ! current_user_can( 'edit_theme_options' ) && ! $loadonly ) {
			return;
		}

		// Loads the required Hoot Options Framework classes.
		require_once trailingslashit( HOOTOPTIONS_DIR ) . 'includes/class-hoot-options.php';
		require_once trailingslashit( HOOTOPTIONS_DIR ) . 'includes/class-hoot-options-admin.php';
		require_once trailingslashit( HOOTOPTIONS_DIR ) . 'includes/class-hoot-options-interface.php';
		require_once trailingslashit( HOOTOPTIONS_DIR ) . 'includes/class-hoot-options-media-uploader.php';
		require_once trailingslashit( HOOTOPTIONS_DIR ) . 'includes/sanitization.php';

		//  If user can't edit theme options, exit
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// Instantiate the options page.
		$hoot_options_admin = new Hoot_Options_Admin;
		$hoot_options_admin->init();

		// Instantiate the media uploader class
		$hoot_options_media_uploader = new Hoot_Options_Media_Uploader;
		$hoot_options_media_uploader->init();

	}

	add_action( 'init', 'hootoptions_init', 20 );

endif;