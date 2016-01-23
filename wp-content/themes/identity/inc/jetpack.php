<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Identity
 */

/**
 * Add theme support for Infinite Scroll.
 * See: http://jetpack.me/support/infinite-scroll/
 */
function identity_jetpack_setup() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'main',
		'footer'    => 'page',
	) );
}
add_action( 'after_setup_theme', 'identity_jetpack_setup' );

/**
 * Return early if Site Logo is not available.
 */
function identity_the_site_logo() {
	if ( ! function_exists( 'jetpack_the_site_logo' ) ) {
		return;
	} else {
		jetpack_the_site_logo();
	}
}
