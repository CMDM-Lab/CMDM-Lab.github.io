<?php
/**
 * Internationalization and translation functions. This file provides a few functions for use by theme 
 * authors.  It also handles properly loading translation files for both the parent and child themes.  Part 
 * of the functionality below handles consolidating the framework's textdomains with the textdomain of the 
 * parent theme to avoid having multiple translation files.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/* Overrides the load textdomain function for the 'responsive-brix' domain. */
// @disabled: WordPress standards allow only 1 text domain (theme slug), so we will stick to that.
// add_filter( 'override_load_textdomain', 'hoot_override_load_textdomain', 5, 3 );

/* Filter the textdomain mofile to allow child themes to load the parent theme translation. */
add_filter( 'load_textdomain_mofile', 'hoot_load_textdomain_mofile', 10, 2 );

/**
 * Overrides the load textdomain functionality when 'responsive-brix' is the domain in use.  The purpose of 
 * this is to allow theme translations to handle the framework's strings.  What this function does is 
 * sets the 'responsive-brix' domain's translations to the theme's.  That way, we're not loading multiple 
 * of the same MO files.
 *
 * @since 1.0.0
 * @access public
 * @globl  array   $l10n
 * @param bool     $override
 * @param string   $domain
 * @param string   $mofile
 * @return bool
 */
function hoot_override_load_textdomain( $override, $domain, $mofile ) {

	/* Set up array of domains to catch. */
	$text_domains = array( 'responsive-brix' );

	/* Check if the domain is one of our framework domains. */
	if ( in_array( $domain, $text_domains ) ) {
		global $l10n;

		/* Get the theme's textdomain. */
		$theme_textdomain = hoot_get_parent_textdomain();

		/* If the theme's textdomain is loaded, use its translations instead. */
		if ( !empty( $theme_textdomain ) && isset( $l10n[ $theme_textdomain ] ) )
			$l10n[ $domain ] = $l10n[ $theme_textdomain ];

		/* Always override.  We only want the theme to handle translations. */
		$override = true;
	}

	return $override;
}

/**
 * Checks if a textdomain's translation files have been loaded.  This function behaves differently from 
 * WordPress core's is_textdomain_loaded(), which will return true after any translation function is run over 
 * a text string with the given domain.  The purpose of this function is to simply check if the translation files 
 * are loaded.
 *
 * @since 1.0.0
 * @access public          This is only used internally by the framework for checking translations.
 * @param string  $domain The textdomain to check translations for.
 */
function hoot_is_textdomain_loaded( $domain ) {
	global $hoot;

	return ( isset( $hoot->textdomain_loaded[ $domain ] ) && true === $hoot->textdomain_loaded[ $domain ] ) ? true : false;
}

/**
 * Loads an empty MO file for the framework textdomain.  This will be overwritten.  The framework domain 
 * will be merged with the theme domain.
 *
 * @since 1.0.0
 * @access public
 * @param string $domain The name of the framework's textdomain.
 * @return bool           Whether the MO file was loaded.
 */
function hoot_load_framework_textdomain( $domain ) {
	return load_textdomain( $domain, '' );
}

/**
 * Gets the parent theme textdomain. This allows the framework to recognize the proper textdomain of the 
 * parent theme.
 *
 * Important! Do not use this for translation functions in your theme.  Hardcode your textdomain string.  Your 
 * theme's textdomain should match your theme's folder name.
 *
 * @since 1.0.0
 * @access public
 * @global object $hoot
 * @return string The textdomain of the theme.
 */
function hoot_get_parent_textdomain() {
	global $hoot;

	/* If the global textdomain isn't set, define it. Plugin/theme authors may also define a custom textdomain. */
	if ( empty( $hoot->parent_textdomain ) ) {

		$theme = wp_get_theme( get_template() );

		$textdomain = $theme->get( 'TextDomain' ) ? $theme->get( 'TextDomain' ) : get_template();

		$hoot->parent_textdomain = sanitize_key( apply_filters( 'hoot_parent_textdomain', $textdomain ) );
	}

	/* Return the expected textdomain of the parent theme. */
	return $hoot->parent_textdomain;
}

/**
 * Gets the child theme textdomain. This allows the framework to recognize the proper textdomain of the 
 * child theme.
 *
 * Important! Do not use this for translation functions in your theme.  Hardcode your textdomain string.  Your 
 * theme's textdomain should match your theme's folder name.
 *
 * @since 1.0.0
 * @access public
 * @global object $hoot
 * @return string The textdomain of the child theme.
 */
function hoot_get_child_textdomain() {
	global $hoot;

	/* If a child theme isn't active, return an empty string. */
	if ( !is_child_theme() )
		return '';

	/* If the global textdomain isn't set, define it. Plugin/theme authors may also define a custom textdomain. */
	if ( empty( $hoot->child_textdomain ) ) {

		$theme = wp_get_theme();

		$textdomain = $theme->get( 'TextDomain' ) ? $theme->get( 'TextDomain' ) : get_stylesheet();

		$hoot->child_textdomain = sanitize_key( apply_filters( 'hoot_child_textdomain', $textdomain ) );
	}

	/* Return the expected textdomain of the child theme. */
	return $hoot->child_textdomain;
}

/**
 * Filters the 'load_textdomain_mofile' filter hook so that we can change the directory and file name 
 * of the mofile for translations.  This allows child themes to have a folder called /languages with translations
 * of their parent theme so that the translations aren't lost on a parent theme upgrade.
 *
 * @since 1.0.0
 * @access public
 * @param string $mofile File name of the .mo file.
 * @param string $domain The textdomain currently being filtered.
 * @return string
 */
function hoot_load_textdomain_mofile( $mofile, $domain ) {

	/* If the $domain is for the parent or child theme, search for a $domain-$locale.mo file. */
	if ( $domain == hoot_get_parent_textdomain() || $domain == hoot_get_child_textdomain() ) {

		/* Check for a $domain-$locale.mo file in the parent and child theme root and /languages folder. */
		$locale = get_locale();
		$locate_mofile = locate_template( array( "languages/{$domain}-{$locale}.mo", "{$domain}-{$locale}.mo" ) );

		/* If a mofile was found based on the given format, set $mofile to that file name. */
		if ( !empty( $locate_mofile ) )
			$mofile = $locate_mofile;
	}

	/* Return the $mofile string. */
	return $mofile;
}

/**
 * Gets the language for the currently-viewed page.  It strips the region from the locale if needed 
 * and just returns the language code.
 *
 * @since 1.0.0
 * @access public
 * @param string  $locale
 * @return string
 */
function hoot_get_language( $locale = '' ) {

	if ( empty( $locale ) )
		$locale = get_locale();

	return preg_replace( '/(.*?)_.*?$/i', '$1', $locale );
}

/**
 * Gets the region for the currently viewed page.  It strips the language from the locale if needed.  Note that 
 * not all locales will have a region, so this might actually return the same thing as `hoot_get_language()`.
 *
 * @since 1.0.0
 * @access public
 * @param string  $locale
 * @return string
 */
function hoot_get_region( $locale = '' ) {

	if ( empty( $locale ) )
		$locale = get_locale();

	return preg_replace( '/.*?_(.*?)$/i', '$1', $locale );
}
