<?php
/**
 * Functions for outputting common site data in the `<head>` area of a site.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/* Adds common theme items to <head>. */
add_action( 'wp_head', 'hoot_meta_charset',  0 );
add_action( 'wp_head', 'hoot_meta_compatible',  0 );
add_action( 'wp_head', 'hoot_meta_responsive', 1 );
add_action( 'wp_head', 'hoot_meta_template', 1 );
add_action( 'wp_head', 'hoot_link_pingback', 3 );
add_action( 'wp_head', 'hoot_link_profile', 3 );
add_action( 'wp_head', 'hoot_link_favicon', 3 );

/* Filter the WordPress title. */
add_filter( 'wp_title', 'hoot_wp_title', 1, 3 );

/**
 * Adds the meta charset to the header.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_meta_charset() {
	printf( '<meta charset="%s" />' . "\n", get_bloginfo( 'charset' ) );
}

/**
 * Adds the meta http-equiv to the header.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_meta_compatible() {
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> <!-- Enable IE Highest available mode (compatibility mode); users with GCF will have page rendered using Google Chrome Frame -->' . "\n";
}

/**
 * Adds the meta for responsive theme.
 *
 * @since 1.0.0
 * @access public
 */
function hoot_meta_responsive() {
	echo '<meta name="HandheldFriendly" content="True">' . "\n";
	echo '<meta name="MobileOptimized" content="767">' . "\n";
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
}

/**
 * Generates the relevant template info.  Adds template meta with theme version.  Uses the theme 
 * name and version from style.css.
 * filter hook.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_meta_template() {
	$theme    = wp_get_theme( get_template() );
	$template = sprintf( '<meta name="template" content="%s %s" />' . "\n", esc_attr( $theme->get( 'Name' ) ), esc_attr( $theme->get( 'Version' ) ) );

	echo apply_filters( 'hoot_meta_template', $template );
}

/**
 * Adds the pingback link to the header.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_link_pingback() {
	if ( 'open' === get_option( 'default_ping_status' ) )
		printf( '<link rel="pingback" href="%s" />' . "\n", get_bloginfo( 'pingback_url' ) );
}

/**
 * Adds the favicon link to the header if the theme has defined it.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_link_profile() {
	echo '<link rel="profile" href="http://gmpg.org/xfn/11" />' . "\n";
}

/**
 * Adds the profile link to the header.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_link_favicon() {
	if ( function_exists( 'hoot_favicon' ) ) {
		hoot_favicon();
		echo "\n";
	}
}

/**
 * Filters the `wp_title` output early.
 *
 * @since 1.0.0
 * @access public
 * @param string  $title
 * @param string  $separator
 * @param string  $seplocation
 * @return string
 */
function hoot_wp_title( $doctitle, $separator, $seplocation ) {

	if ( is_front_page() )
		$doctitle = get_bloginfo( 'name' ) . $separator . ' ' . get_bloginfo( 'description' );

	elseif ( is_home() || is_singular() )
		$doctitle = single_post_title( '', false );

	elseif ( is_category() ) 
		$doctitle = single_cat_title( '', false );

	elseif ( is_tag() )
		$doctitle = single_tag_title( '', false );

	elseif ( is_tax() )
		$doctitle = single_term_title( '', false );

	elseif ( is_post_type_archive() )
		$doctitle = post_type_archive_title( '', false );

	elseif ( is_author() )
		$doctitle = hoot_single_author_title( '', false );

	elseif ( get_query_var( 'minute' ) && get_query_var( 'hour' ) )
		$doctitle = hoot_single_minute_hour_title( '', false );

	elseif ( get_query_var( 'minute' ) )
		$doctitle = hoot_single_minute_title( '', false );

	elseif ( get_query_var( 'hour' ) )
		$doctitle = hoot_single_hour_title( '', false );

	elseif ( is_day() )
		$doctitle = hoot_single_day_title( '', false );

	elseif ( get_query_var( 'w' ) )
		$doctitle = hoot_single_week_title( '', false );

	elseif ( is_month() )
		$doctitle = single_month_title( ' ', false );

	elseif ( is_year() )
		$doctitle = hoot_single_year_title( '', false );

	elseif ( is_archive() )
		$doctitle = hoot_single_archive_title( '', false );

	elseif ( is_search() )
		$doctitle = hoot_search_title( '', false );

	elseif ( hoot_is_404() )
		$doctitle = hoot_404_title( '', false );

	/* If the current page is a paged page. */
	if ( ( ( $page = get_query_var( 'paged' ) ) || ( $page = get_query_var( 'page' ) ) ) && $page > 1 )
		/* Translators: 1 is the page title. 2 is the page number. */
		$doctitle = sprintf( __( '%1$s Page %2$s', 'responsive-brix' ), $doctitle . $separator, number_format_i18n( absint( $page ) ) );

	/* Trim separator + space from beginning and end. */
	$doctitle = trim( strip_tags( $doctitle ), "{$separator} " );

	return $doctitle;
}
