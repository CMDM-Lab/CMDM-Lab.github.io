<?php
/**
 *                  _   _             _   
 *  __      ___ __ | | | | ___   ___ | |_ 
 *  \ \ /\ / / '_ \| |_| |/ _ \ / _ \| __|
 *   \ V  V /| |_) |  _  | (_) | (_) | |_ 
 *    \_/\_/ | .__/|_| |_|\___/ \___/ \__|
 *           |_|                          
 * ------------------------------------------
 * ---- WP THEME BUILT ON HOOT FRAMEWORK ----
 * ------------ (a Hybrid fork) -------------
 * ------------------------------------------
 *
 * :: Theme's main functions file :::::::::::::::::::::::::::::::::::::::::::::
 * :: Initialize and setup the theme framework, helper functions and objects ::
 *
 * To modify this theme, its a good idea to create a child theme. This way you can easily update
 * the main theme without loosing your changes. To know more about how to create child themes 
 * @see http://codex.wordpress.org/Theme_Development
 * @see http://codex.wordpress.org/Child_Themes
 *
 * Hooks, Actions and Filters are used throughout this theme. You should be able to do most of your
 * customizations without touching the main code. For more information on hooks, actions, and filters
 * @see http://codex.wordpress.org/Plugin_API
 *
 * @credit This theme is derived from 
 * * Underscores WordPress Theme, Copyright 2012 Automattic http://underscores.me/
 * * Hybrid Base WordPress Theme, Copyright 2013 - 2014, Justin Tadlock  http://themehybrid.com/
 * both of which, like WordPress, are distributed under the terms of the GNU GPL
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/* Load minified versions of CSS and JS throughout the framework. You can set this to true for loading
   unminified files (usefulfor development/debugging), or set it to false for loading minified files (for
   production i.e. live site). */
define( 'HOOT_DEBUG', true );

/* Get the template directory and make sure it has a trailing slash. */
$hoot_base_dir = trailingslashit( get_template_directory() );

/* Load the Core framework */
require_once( $hoot_base_dir . 'hoot/hoot.php' );

/* Load the Theme files */
require_once( $hoot_base_dir . 'hoot-theme/hoot-theme.php' );

/* Launch the Core framework. */
$hoot_class = new Hoot();

/* Launch the Theme */
$hoot_theme_class = new Hoot_Theme();