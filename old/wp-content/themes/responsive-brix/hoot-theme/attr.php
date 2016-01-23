<?php
/**
 * HTML attribute filters.
 * Most of these functions filter the generic values from the framework found in hoot/functions/attr.php
 * Attributes for non-generic structural elements (mostly theme specific) can be loaded in this file.
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/* Modify Original Filters from Framework */
add_filter( 'hoot_attr_content', 'hoot_theme_attr_content' );
add_filter( 'hoot_attr_sidebar', 'hoot_theme_attr_sidebar', 10, 2 );

/* New Theme Filters */
add_filter( 'hoot_attr_page_wrapper', 'hoot_theme_attr_page_wrapper' );
add_filter( 'hoot_attr_page_template_content', 'hoot_theme_page_template_content', 10, 2 );

/**
 * Modify Main content container of the page attributes.
 *
 * @since 1.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_theme_attr_content( $attr ) {
	$layout_class = hoot_main_layout_class( 'content' );
	if ( !empty( $layout_class ) ) {
		if ( isset( $attr['class'] ) )
			$attr['class'] .= ' ' . $layout_class;
		else
			$attr['class'] = $layout_class;
	}

	return $attr;
}

/**
 * Modify Sidebar attributes.
 *
 * @since 1.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
function hoot_theme_attr_sidebar( $attr, $context ) {
	if ( !empty( $context ) && $context == 'primary' ) {
		$layout_class = hoot_main_layout_class( 'primary-sidebar' );
		if ( !empty( $layout_class ) ) {
			if ( isset( $attr['class'] ) )
				$attr['class'] .= ' ' . $layout_class;
			else
				$attr['class'] = $layout_class;
		}
	}

	return $attr;
}

/**
 * Page wrapper attributes.
 *
 * @since 1.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_theme_attr_page_wrapper( $attr ) {
	$site_layout = hoot_get_option( 'site_layout' );

	$attr['class'] = ( $site_layout == 'boxed' ) ? 'grid site-boxed' : 'site-stretch';
	$attr['class'] .= ' page-wrapper';

	return $attr;
}

/**
 * Main content container of the page attributes when a page template is being displayed
 *
 * @since 1.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
function hoot_theme_page_template_content( $attr, $context ) {

	if ( is_page_template() ) {
		$attr['id']       = 'content';
		$attr['class']    = 'content';
		$attr['role']     = 'main';
		$attr['itemprop'] = 'mainContentOfPage';
		$template_slug = basename( get_page_template(), '.php' );
		$attr['class'] .= ' ' . sanitize_html_class( 'content-' . $template_slug );
	} elseif ( function_exists( 'hoot_attr_content' ) ) {
		// Get page attributes for main content container of a non-template regular page
		$attr = apply_filters( "hoot_attr_content", $attr, $context );
	}

	return $attr;
}