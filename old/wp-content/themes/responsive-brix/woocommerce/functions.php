<?php
/**
 * Woocommerce functions
 * This file is loaded at 'after_setup_theme' action @priority 10
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.8
 */

/** Woocommerce Templates **/

// Remove default woocommerce opening divs for the content
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

// Remove woocommerce breadcrumbs
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// Remove default woocommerce closing divs for the content
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/**
 * Registers woocommerce sidebars.
 *
 * @since 1.8
 * @access public
 * @return void
 */
function hoot_woo_register_sidebars() {
	hoot_register_sidebar(
		array(
			'id'          => 'woocommerce-sidebar',
			'name'        => _x( 'Woocommerce Sidebar', 'sidebar', 'responsive-brix' ),
			'description' => __( 'The main sidebar for woocommerce pages', 'responsive-brix' )
		)
	);
}
add_action( 'widgets_init', 'hoot_woo_register_sidebars' );

/**
 * Add woocommerce sidebar class.
 *
 * @since 1.8
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
function hoot_theme_woo_attr_sidebar( $attr, $context ) {
	if ( !empty( $context ) && $context == 'primary' ) {
		if ( isset( $attr['class'] ) )
			$attr['class'] .= ' woocommerce-sidebar';
		else
			$attr['class'] = 'woocommerce-sidebar';
	}

	return $attr;
}
add_filter( 'hoot_attr_sidebar', 'hoot_theme_woo_attr_sidebar', 11, 2 );

/**
 * Apply sidebar layout for woocommerce pages
 *
 * @since 1.8
 * @access public
 * @param string $sidebar
 * @return array
 */
function hoot_woo_main_layout( $sidebar ) {

	// Check for pages which use WooCommerce templates (cart and checkout are standard pages with shortcodes and thus are not included)
	if ( is_woocommerce() ){
		if ( is_product() ) { // single product page. Wrapper for is_singular
			$sidebar = hoot_get_option( 'sidebar_wooproduct' );
		} else { // shop, category, tag archives etc
			$sidebar = hoot_get_option( 'sidebar_wooshop' );
		}
	}

	if ( is_cart() || is_checkout() ) {
		$sidebar = 'none';
	}

	return $sidebar;
}
add_filter( 'hoot_main_layout', 'hoot_woo_main_layout' );


/**
 * Change product # displayed on shop page
 *
 * @since 1.8
 * @access public
 * @param int $value
 * @return int
 */
if ( !function_exists('hoot_woo_loop_per_page') ) {
function hoot_woo_loop_per_page( $value ) {
	return hoot_get_option( 'wooshop_products', 12 );
}
}
add_filter( 'loop_shop_per_page', 'hoot_woo_loop_per_page', 20 );

/**
 * Override theme default specification for product # per row
 *
 * @since 1.8
 * @access public
 * @param int $value
 * @return int
 */
if ( !function_exists('hoot_woo_loop_columns') ) {
function hoot_woo_loop_columns( $value ) {
	return hoot_get_option( 'wooshop_product_columns', 4 );
}
}
add_filter( 'loop_shop_columns', 'hoot_woo_loop_columns', 999 );

/**
 * Add inline style if product # per row is different
 *
 * @since 1.8
 * @access public
 * @return void
 */
if ( !function_exists('hoot_woo_custom_loop_columns_css') ) {
function hoot_woo_custom_loop_columns_css() {
	$columns = hoot_get_option( 'wooshop_product_columns', 4 );

	if ( $columns == 4 )
		return;

	switch ( $columns ) {
		case '2':
			$css = '.woocommerce ul.products li.product, .woocommerce-page ul.products li.product { width: 48.1%; }';
			break;
		case '3':
			$css = '.woocommerce ul.products li.product, .woocommerce-page ul.products li.product { width: 30.8%; }';
			break;
		case '5':
			$css = '.woocommerce ul.products li.product, .woocommerce-page ul.products li.product { width: 16.96%; }';
			break;
	}

	if ( !empty( $css ) )
		wp_add_inline_style( 'style', $css );
}
}
add_action( 'wp_enqueue_scripts', 'hoot_woo_custom_loop_columns_css', 99 );

/**
 * Bug fix for Woocommerce in some installations (on posts/products singular)
 * WC_Query hooks into pre_get_posts (@priority 10) and checks for `isset( $q->queried_object->ID )`
 *   in woocommerce\includes\class=wc-query.php at line#215. This gives suppressed error
 *   "Notice: Undefined property: WP_Query::$queried_object in \wp-includes\query.php on line 3960"
 * Note that class WP_Query (\includes\query.php) does unset($this->queried_object); in WP_Query::init()
 * 
 * The proper way to chck queried object is `get_queried_object()` and not $q->queried_object
 * So we set $q->queried_object by running get_queried_object() at pre_get_posts @priority 9
 *
 * @since 1.5
 * @access public
 * @return void
 */
function hoot_woo_set_queried_object( $q ){
	if ( $q->is_main_query() )
		$r = get_queried_object();
}
add_action( 'pre_get_posts', 'hoot_woo_set_queried_object', 9 );