<?php
/**
 * The core functions file for the framework. Functions defined here are generally used across the 
 * entire framework to make various tasks faster. This file should be loaded prior to any other files 
 * because its functions are needed to run the framework.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/**
 * Allows theme developers to set a definite prefix for their theme.  If this isn't set, the framework
 * will assume the prefix is the value of `get_template()`.  This should be called early, such as in 
 * the theme setup function.
 *
 * @since 1.0.0
 * @access public
 * @global object $hoot
 * @param string $prefix
 * @return void
 */
function hoot_set_prefix( $prefix ) {
	global $hoot;

	$hoot->prefix = sanitize_key( apply_filters( 'hoot_prefix', $prefix ) );
}

/**
 * Defines the theme prefix. This allows developers to infinitely change the theme. In theory,
 * one could use the Framework core to create their own theme or filter 'hoot_prefix' with a 
 * plugin to make it easier to use hooks across multiple themes without having to figure out
 * each theme's hooks (assuming other themes used the same system).
 *
 * @since 1.0.0
 * @access public
 * @global object $hoot
 * @return string $hoot->prefix The prefix of the theme.
 */
function hoot_get_prefix() {
	global $hoot;

	/* If the global prefix isn't set, define it. Plugin/theme authors may also define a custom prefix. */
	if ( empty( $hoot->prefix ) )
		$hoot->prefix = sanitize_key( apply_filters( 'hoot_prefix', get_template() ) );

	return $hoot->prefix;
}

/**
 * Adds contextual action hooks to the theme.  This allows users to easily add context-based content 
 * without having to know how to use WordPress conditional tags.  The theme handles the logic.
 *
 * An example of a basic hook would be 'hoot_header'.  The do_atomic() function extends that to 
 * give extra hooks such as 'hoot_singular_header', 'hoot_singular-post_header', and 
 * 'hoot_singular-post-ID_header'.
 *
 * @credit Justin Tadlock <justin@justintadlock.com>, Ptah Dunbar <pt@ptahd.com>
 *         http://ptahdunbar.com/wordpress/smarter-hooks-context-sensitive-hooks
 *
 * @since 1.0.0
 * @access public
 * @param string $tag     Usually the location of the hook but defines what the base hook is.
 * @param mixed  $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
 */
function hoot_do_atomic( $tag = '', $arg = '' ) {

	if ( empty( $tag ) )
		return false;

	/* Get the args passed into the function and remove $tag. */
	$args = func_get_args();
	array_splice( $args, 0, 1 );

	/* Do actions on the basic hook. */
	do_action_ref_array( $tag, $args );

	/* Loop through context array and fire actions on a contextual scale. */
	foreach ( (array) hoot_get_context() as $context )
		do_action_ref_array( "{$context}_{$tag}", $args );
}

/**
 * Adds contextual filter hooks to the theme.  This allows users to easily filter context-based content 
 * without having to know how to use WordPress conditional tags.  The theme handles the logic.
 *
 * An example of a basic hook would be 'hoot_entry_meta'.  The apply_atomic() function extends 
 * that to give extra hooks such as 'hoot_singular_entry_meta', 'hoot_singular-post_entry_meta', 
 * and 'hoot_singular-post-ID_entry_meta'.
 *
 * @since 1.0.0
 * @access public
 * @param string $tag     Usually the location of the hook but defines what the base hook is.
 * @param mixed  $value   The value on which the filters hooked to $tag are applied on.
 * @param mixed  $var,... Additional variables passed to the functions hooked to $tag.
 * @return mixed  $value  The value after it has been filtered.
 */
function hoot_apply_atomic( $tag = '', $value = '' ) {

	if ( empty( $tag ) )
		return false;

	/* Get the args passed into the function and remove $tag. */
	$args = func_get_args();
	array_splice( $args, 0, 1 );

	/* Apply filters on the basic hook. */
	$value = $args[0] = apply_filters_ref_array( $tag, $args );

	/* Loop through context array and apply filters on a contextual scale. */
	foreach ( (array) hoot_get_context() as $context )
		$value = $args[0] = apply_filters_ref_array( "{$context}_{$tag}", $args );

	/* Return the final value once all filters have been applied. */
	return $value;
}

/**
 * Wraps the output of hoot_apply_atomic() in a call to do_shortcode(). This allows developers to use 
 * context-aware functionality alongside shortcodes. Rather than adding a lot of code to the 
 * function itself, developers can create individual functions to handle shortcodes.
 *
 * @since 1.0.0
 * @access public
 * @param string $tag   Usually the location of the hook but defines what the base hook is.
 * @param mixed  $value The value to be filtered.
 * @return mixed  $value The value after it has been filtered.
 */
function hoot_apply_atomic_shortcode( $tag = '', $value = '' ) {
	return do_shortcode( hoot_apply_atomic( $tag, $value ) );
}

/**
 * Function for formatting a hook name if needed. It automatically adds the theme's prefix to 
 * the hook, and it will add a context (or any variable) if it's given.
 *
 * @since 1.0.0
 * @access public
 * @param string $tag     The basic name of the hook (e.g., 'before_header').
 * @param string $context A specific context/value to be added to the hook.
 */
function hoot_format_hook( $tag, $context = '' ) {
	return hoot_get_prefix() . ( ( !empty( $context ) ) ? "_{$context}" : "" ). "_{$tag}";
}

/**
 * Function for setting the content width of a theme.  This does not check if a content width has been set; it 
 * simply overwrites whatever the content width is.
 *
 * @since 1.0.0
 * @access public
 * @global int   $content_width The width for the theme's content area.
 * @param int    $width         Numeric value of the width to set.
 */
function hoot_set_content_width( $width = '' ) {
	global $content_width;

	$content_width = absint( $width );
}

/**
 * Function for getting the theme's content width.
 *
 * @since 1.0.0
 * @access public
 * @global int    $content_width The width for the theme's content area.
 * @return int    $content_width
 */
function hoot_get_content_width() {
	global $content_width;

	return $content_width;
}
