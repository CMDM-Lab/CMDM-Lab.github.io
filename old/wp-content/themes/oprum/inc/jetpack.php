<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Oprum
 */

/**
 * Add theme support for infinity scroll
 */
function oprum_infinite_scroll_init() {
	add_theme_support( 'infinite-scroll', array(
				'container' => 'main',
				'render'	=> 'oprum_infinite_scroll_render',
				'footer'	=> false,
				'posts_per_page' => false,
				'type'	=> 'click'
			) );
}
add_action( 'after_setup_theme', 'oprum_infinite_scroll_init' );

/**
 * Set the code to be rendered on for calling posts for infinity scroll
 */
function oprum_infinite_scroll_render() {
	the_post();   //get_template_part( 'content', get_post_format() );
}

/**
 * Remove sharedaddy from excerpt.
 */
function oprum_sidebar_sharedaddy() {
	remove_filter( 'the_content', 'sharing_display', 19 );
}
add_action( 'dynamic_sidebar', 'oprum_sidebar_sharedaddy' );

function oprum_excerpt_sharedaddy() {
    remove_filter( 'the_excerpt', 'sharing_display', 19 );
}
add_action( 'loop_start', 'oprum_excerpt_sharedaddy' );