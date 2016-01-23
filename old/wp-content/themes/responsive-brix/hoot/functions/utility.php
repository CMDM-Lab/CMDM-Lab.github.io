<?php
/**
 * Additional helper functions that the framework or themes may use.  The functions in this file are functions
 * that don't really have a home within any other parts of the framework.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/* Add extra support for post types. */
add_action( 'init', 'hoot_add_post_type_support' );

/**
 * This function is for adding extra support for features not default to the core post types.
 * Excerpts are added to the 'page' post type.  Comments and trackbacks are added for the
 * 'attachment' post type.  Technically, these are already used for attachments in core, but 
 * they're not registered.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_add_post_type_support() {

	/* Add support for excerpts to the 'page' post type. */
	add_post_type_support( 'page', array( 'excerpt' ) );

	/* Add thumbnail support for audio and video attachments. */
	add_post_type_support( 'attachment:audio', 'thumbnail' );
	add_post_type_support( 'attachment:video', 'thumbnail' );
}

/* Filters the title for untitled posts. */
add_filter( 'the_title', 'hoot_untitled_post' );

/**
 * The WordPress standards requires that a link be provided to the single post page for untitled 
 * posts.  This is a filter on 'the_title' so that an '(Untitled)' title appears in that scenario, allowing 
 * for the normal method to work.
 *
 * @since 1.0.0
 * @access public
 * @param string $title
 * @return string
 */
function hoot_untitled_post( $title ) {

	if ( empty( $title ) && in_the_loop() && !is_admin() ) { // && !is_singular()

		/* Translators: Used as a placeholder for untitled posts on non-singular views. */
		$title = __( '(Untitled)', 'responsive-brix' );
	}

	return $title;
}

/**
 * Retrieves the file with the highest priority that exists.  The function searches both the stylesheet 
 * and template directories.  This function is similar to the locate_template() function in WordPress 
 * but returns the file name with the URI path instead of the directory path.
 *
 * @since 1.0.0
 * @access public
 * @link http://core.trac.wordpress.org/ticket/18302
 * @param array $file_names The files to search for.
 * @return string
 */
function hoot_locate_theme_file( $file_names ) {

	$located = '';

	/* Loops through each of the given file names. */
	foreach ( (array) $file_names as $file ) {

		/* If the file exists in the stylesheet (child theme) directory. */
		if ( is_child_theme() && file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$located = trailingslashit( get_stylesheet_directory_uri() ) . $file;
			break;
		}

		/* If the file exists in the template (parent theme) directory. */
		elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$located = trailingslashit( get_template_directory_uri() ) . $file;
			break;
		}
	}

	return $located;
}

/**
 * Converts a hex color to RGB.  Returns the RGB values as an array.
 *
 * @since 1.0.0
 * @access public
 * @param string $hex
 * @return array
 */
function hoot_hex_to_rgb( $hex ) {

	/* Remove "#" if it was added. */
	$color = trim( $hex, '#' );

	/* If the color is three characters, convert it to six. */
	if ( 3 === strlen( $color ) )
		$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];

	/* Get the red, green, and blue values. */
	$red   = hexdec( $color[0] . $color[1] );
	$green = hexdec( $color[2] . $color[3] );
	$blue  = hexdec( $color[4] . $color[5] );

	/* Return the RGB colors as an array. */
	return array( 'r' => $red, 'g' => $green, 'b' => $blue );
}

/**
 * Trim a string like PHP trim function
 * It additionally trims the <br> tags and breaking and non breaking spaces as well
 *
 * @since 1.0.0
 * @access public
 * @param string $content
 * @return array
 */
function hoot_trim( $content ) {
	$content = trim( $content, " \t\n\r\0\x0B\xC2\xA0" ); // trim non breaking spaces as well
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$content = preg_replace('/(?:<br\s*\/?>\s*)+$/', '', $content);
	$content = trim( $content, " \t\n\r\0\x0B\xC2\xA0" ); // trim non breaking spaces as well
	return $content;
}

/**
 * Insert into associative array at a specific location
 *
 * @since 1.1.1
 * @access public
 * @param array $insert
 * @param array $target
 * @param int|string $location 0 based position, or key in $target
 * @param string $order 'before' or 'after'
 * @return array
 */
function hoot_array_insert( $insert, $target, $location, $order = 'before' ) {

	if ( !is_array( $insert ) || !is_array( $target ) )
		return $target;

	if ( is_int( $location ) ) {

		if ( $order == 'after' )
			$location++;
		$target = array_slice( $target, 0, $location, true ) +
					$insert +
					array_slice( $target, $location, count( $target ) - 1, true );
		return $target;

	} elseif ( is_string( $location ) ) {

		$count = ( $order == 'after' ) ? 1 : 0;
		foreach ( $target as $key => $value ) {
			if ( $key === $location ) {
				$target = array_slice( $target, 0, $count, true ) +
							$insert +
							array_slice( $target, $count, count( $target ) - 1, true );
				return $target;
			}
			$count++;
		}
		// $location not found. So lets just return a simple array merge
		return array_merge( $target, $insert );

	}

	// Just for brevity
	return $target;
}

/**
 * Function for grabbing a WP nav menu theme location name.
 *
 * @since 1.0.0
 * @access public
 * @param string $location
 * @return string
 */
function hoot_get_menu_location_name( $location ) {

	$locations = get_registered_nav_menus();

	return $locations[ $location ];
}

/**
 * Function for grabbing a dynamic sidebar name.
 *
 * @since 1.0.0
 * @access public
 * @param string $sidebar_id
 * @return string
 */
function hoot_get_sidebar_name( $sidebar_id ) {
	global $wp_registered_sidebars;

	if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) )
		return $wp_registered_sidebars[ $sidebar_id ]['name'];
}

/**
 * Helper function for getting the script/style `.min` suffix for minified files.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function hoot_get_min_suffix() {
	return defined( 'HOOT_DEBUG' ) && HOOT_DEBUG ? '' : '.min';
}

/**
 * Function for checking if 404 page is being displayed.
 * This is an extension to the WordPress 'is_404()' conditional tag, as it checks if the main query is
 * altered to display a custom page as 404 page by the Hoot framework.
 *
 * @since 1.0.0
 * @access public
 * @return bool
 */
function hoot_is_404() {
	global $hoot;
	if ( isset( $hoot->is_404 ) && is_bool( $hoot->is_404 ) )
		return $hoot->is_404;
	else
		return is_404();
}

/**
 * Check if this is the premium version of theme.
 * Ideally, HOOT_PREMIUM should be set to (bool)true at the start of '/hoot-theme/hoot-theme.php' file.
 *
 * @since 1.1.0
 * @access public
 * @return bool
 */
function hoot_is_premium() {
	$return = ( defined( 'HOOT_PREMIUM' ) && HOOT_PREMIUM === true );
	return $return;
}