<?php
/**
 * Functions for handling theme main stylesheets in the frontend.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/* Register Main styles. */
add_action( 'wp_enqueue_scripts', 'hoot_register_styles', 0 );

/* Load Main styles. It's a good practice to load any other stylesheet before the main style. Hence users can enqueue custom stylesheets at default priority 10, so that the main style.css is always loaded at the end. */
add_action( 'wp_enqueue_scripts', 'hoot_enqueue_styles', 11 );

/* Load the development stylsheet (unminified) in script debug mode. */
add_filter( 'stylesheet_uri', 'hoot_min_stylesheet_uri', 5, 2 );

/* Filters the WP locale stylesheet. */
add_filter( 'locale_stylesheet_uri', 'hoot_locale_stylesheet_uri', 5 );

/**
 * Registers stylesheets for the framework. This function merely registers styles with WordPress using
 * the wp_register_style() function. It does not load any stylesheets on the site. If a theme wants to 
 * register its own custom styles, it should do so on the 'wp_enqueue_scripts' hook.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_register_styles() {

	/* Get styles. */
	$styles = hoot_get_styles();

	/* Get the minified suffix */
	$suffix = hoot_get_min_suffix();

	/* Loop through each style and register it. */
	foreach ( $styles as $style => $args ) {

		$defaults = array( 
			'handle'  => $style, 
			'src'     => trailingslashit( HOOT_CSS ) . "{$style}{$suffix}.css",
			'deps'    => null,
			'version' => false,
			'media'   => 'all'
		);

		$args = wp_parse_args( $args, $defaults );

		wp_register_style(
			sanitize_key( $args['handle'] ), 
			esc_url( $args['src'] ), 
			is_array( $args['deps'] ) ? $args['deps'] : null, 
			preg_replace( '/[^a-z0-9_\-.]/', '', strtolower( $args['version'] ) ), 
			esc_attr( $args['media'] )
		);
	}
}

/**
 * Tells WordPress to load the styles using the wp_enqueue_style() function.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_enqueue_styles() {

	/* Get styles. */
	$styles = hoot_get_styles();

	/* Loop through each style and enqueue it. */
	foreach ( $styles as $style => $args )
		wp_enqueue_style( $style );
}

/**
 * Returns an array of the available styles for use in themes.
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function hoot_get_styles() {

	/* Get the minified suffix */
	$suffix = hoot_get_min_suffix();

	/* Initialize */
	$styles = array();

	/* If a child theme is active, add the parent theme's style. */
	if ( is_child_theme() ) {
		$parent = wp_get_theme( get_template() );

		/* Get the parent theme stylesheet. */
		$src = trailingslashit( THEME_URI ) . "style.css";

		/* If a '.min' version of the parent theme stylesheet exists, use it. */
		if ( !empty( $suffix ) && file_exists( trailingslashit( THEME_DIR ) . "style{$suffix}.css" ) )
			$src = trailingslashit( THEME_URI ) . "style{$suffix}.css";

		$styles['parent'] = array( 'src' => $src, 'version' => $parent->get( 'Version' ) );
	}

	/* Add the active theme style. */
	$styles['style'] = array( 'src' => get_stylesheet_uri(), 'version' => wp_get_theme()->get( 'Version' ) );

	/* Return the array of styles. */
	return apply_filters( 'hoot_styles', $styles );
}

/**
 * Filters the 'stylesheet_uri' returned by get_stylesheet_uri() to allow theme developers to offer a
 * minimized version of their main 'style.css' file. It will detect if a 'style.min.css' file is available
 * and use it if HOOT_DEBUG is disabled.
 *
 * @since 1.0.0
 * @access public
 * @param string  $stylesheet_uri      The URI of the active theme's stylesheet.
 * @param string  $stylesheet_dir_uri  The directory URI of the active theme's stylesheet.
 * @return string $stylesheet_uri
 */
function hoot_min_stylesheet_uri( $stylesheet_uri, $stylesheet_dir_uri ) {

	/* Get the minified suffix */
	$suffix = hoot_get_min_suffix();

	/* Use the .min stylesheet if available. */
	if ( !empty( $suffix ) ) {

		/* Remove the stylesheet directory URI from the file name. */
		$stylesheet = str_replace( trailingslashit( $stylesheet_dir_uri ), '', $stylesheet_uri );

		/* Change the stylesheet name to 'style.min.css'. */
		$stylesheet = str_replace( '.css', "{$suffix}.css", $stylesheet );

		/* If the stylesheet exists in the stylesheet directory, set the stylesheet URI to the dev stylesheet. */
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $stylesheet ) )
			$stylesheet_uri = trailingslashit( $stylesheet_dir_uri ) . $stylesheet;
	}

	/* Return the theme stylesheet. */
	return $stylesheet_uri;
}

/**
 * Filters `locale_stylesheet_uri` with a more robust version for checking locale/language/region/direction 
 * stylesheets.
 *
 * @since 1.0.0
 * @access public
 * @param string $stylesheet_uri
 * @return string
 */
function hoot_locale_stylesheet_uri( $stylesheet_uri ) {

	$locale_style = hoot_get_locale_style();

	return !empty( $locale_style ) ? $locale_style : $stylesheet_uri;
}

/**
 * Searches for a locale stylesheet.  This function looks for stylesheets in the `css` folder in the following 
 * order:  1) $lang-$region.css, 2) $region.css, 3) $lang.css, and 4) $text_direction.css.  It first checks 
 * the child theme for these files.  If they are not present, it will check the parent theme.  This is much 
 * more robust than the WordPress locale stylesheet, allowing for multiple variations and a more flexible 
 * hierarchy.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function hoot_get_locale_style() {

	$styles = array();

	/* Get the locale, language, and region. */
	$locale = strtolower( str_replace( '_', '-', get_locale() ) );
	$lang   = strtolower( hoot_get_language() );
	$region = strtolower( hoot_get_region() );

	$styles[] = "css/{$locale}.css";

	if ( $region !== $locale )
		$styles[] = "css/{$region}.css";

	if ( $lang !== $locale )
		$styles[] = "css/{$lang}.css";

	$styles[] = is_rtl() ? 'css/rtl.css' : 'css/ltr.css';

	return hoot_locate_theme_file( $styles );
}
