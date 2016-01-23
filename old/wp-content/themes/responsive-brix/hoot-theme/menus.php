<?php
/**
 * Register custom theme menus
 * This file is loaded via the 'after_setup_theme' hook at priority '10'
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/* Register custom menus. */
add_action( 'init', 'hoot_base_register_menus', 5 );

/**
 * Registers nav menu locations.
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_base_register_menus() {
	register_nav_menu( 'primary', _x( 'Primary Navigation', 'nav menu location', 'responsive-brix' ) );
}