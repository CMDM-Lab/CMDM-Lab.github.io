<?php
/**
 * Helper Functions available in both admin and frontend for Hoot Options Framework.
 * Remember that Hoot Options Framework is loaded only in wpadmin (not loaded in frontend) and only for users
 * with 'edit_theme_options' capability. So functions here should be independent of Hoot Options Framework.
 *
 * This file is loaded at 'after_setup_theme' hook with 4 priority.
 *
 * @package hoot
 * @subpackage options-framework
 * @since hoot 1.0.0
 */

/**
 * Helper function to return the theme option value.
 * If no value has been saved, it returns $default.
 * If no $default provided, it checks the default options array.
 * 
 * @since 1.0.0
 * @access public
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function hoot_get_option( $name, $default = NULL ) {

	/*** Return option value if set ***/

	static $options = NULL; // cache

	// Get the values from database
	if ( $options === NULL ) {
		$option_name = hootoptions_option_name();
		$options = get_option( $option_name );
	}

	// Ideally, this should not have any expensive function, as hoot_get_option() is used quite often.
	$options = apply_filters( 'hoot_get_option', $options );

	if ( isset( $options[$name] ) ) {
		// Add exceptions: If a value has been set but is empty, this gives the option to return default values in such cases. Simply return $override as (bool)true.
		$override = apply_filters( 'hoot_get_option_empty_values', false, $options[$name] );
		if ( $override !== true )
			return $options[$name];
	}

	/*** Return default if provided ***/

	if ( $default !== NULL )
		return $default;

	/*** Return default theme option value ***/

	static $defaults = NULL; // cache

	// Get the default values from options array
	if ( $defaults === NULL ) {
		$options_array = hoot_get_theme_options_array();

		// $defaults is created similar to get_default_values() in class-hoot-options-admin
		if ( $options_array ) {
			foreach ( $options_array as $op ) {
				if ( isset( $op['id'] ) && isset( $op['std'] ) && isset( $op['type'] ) ) {
					if ( has_filter( 'hoot_of_sanitize_' . $op['type'] ) ) {
						$defaults[ $op['id'] ] = apply_filters( 'hoot_of_sanitize_' . $op['type'], $op['std'], $op );
					} else {
						$defaults[ $op['id'] ] = $op['std'];
					}
				}
			}
		}

	}

	if ( isset( $defaults[$name] ) )
		return $defaults[$name];

	/*** We dont have anything! ***/
	return false;
}

/**
 * Helper function to get the location of options file for the theme, and load the options array.
 * Allows for manipulating or setting options via 'hoot_theme_options' filter
 * 
 * @since 1.0.0
 */
function hoot_get_theme_options_array() {
	// cache, and to make sure only to include options.php once
	static $options = null;

	if ( !$options ) {
		// Load options from options.php file (if it exists)
		$base = str_replace( trailingslashit( THEME_DIR ), '', trailingslashit( HOOT_THEMEDIR ) );
		$location = apply_filters( 'hoot_options_location', $base . 'admin/options.php' );
		if ( $optionsfile = locate_template( array( $location ) ) ) {
			$maybe_options = require_once $optionsfile;
			if ( is_array( $maybe_options ) ) {
				$options = $maybe_options;
			} else if ( function_exists( 'hootoptions_options' ) ) {
				$options = hootoptions_options();
			}
		}

		// Allow setting/manipulating options via filters
		$options = apply_filters( 'hoot_theme_options', $options );
	}

	return $options;
}

/**
 * Utility function to map a sortlist option value to Sort and Display arrays
 *
 * @since 1.0.0
 * @access public
 * @param array $value
 * @param array $sanitize_array Optional array to sanitize return values
 * @param string $return Can have value 'order' or 'display' or empty to return an array of both
 * @return void
 */
function hoot_map_sortlist( $value, $sanitize_array = array(), $return = '' ) {
	$list = array(
		'order' => array(),
		'display' => array(),
		);
	if ( !is_array( $value ) )
		return $list;

	foreach( $value as $key => $val ) {
		$valparts = explode( ",", trim( $val ) );

		if ( !empty( $sanitize_array ) )
			$sanitzed = ( isset ( $sanitize_array[ $key ] ) ) ? true : false;
		else
			$sanitzed = true;

		if ( $sanitzed ) {
			$list['order'][ $key ] = intval( $valparts[0] );
			$list['display'][ $key ] = intval( $valparts[1] );
		}
	};

	asort( $list['order'] );

	if ( 'order' == $return )
		return $list['order'];
	elseif ( 'display' == $return )
		return $list['display'];
	else
		return $list;
}

/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * 
 * @since 1.0.0
 */
function hootoptions_option_name() {
	if ( defined( 'THEME_SLUG' ) )
		return THEME_SLUG . '-options';
	// fallback name
	$theme_name = get_option( 'stylesheet' );
	$theme_name = preg_replace( "/\W/", "_", strtolower( $theme_name ) );
	return $theme_name . '-options'; // fallback return 'hoot-options';
}

/**
 * A special sorting function for usort'ing sortlist option types
 * 
 * @since 1.0.0
 */
function hoot_sort_slarray( $a, $b ) {
	$a = explode( ",", $a );
	$b = explode( ",", $b );
	$c1 = intval( $a[0] );
	$c2 = intval( $b[0] );
	if ( $c1 == $c2 )
		return 0;
	return ( $c1 < $c2 ) ? -1 : 1;
}

/**
 * A class of helper functions to build the options
 * 
 * @since 1.0.0
 */
class Hoot_Options_Helper {

	/**
	 * Pull all the categories into an array
	 * @todo add optional params to limit number returned
	 *
	 * @since 1.0.0
	 * @return array
	 */
	static function categories(){
		static $options_categories = array();
		if ( empty( $options_categories ) ) {
			$options_categories_obj = get_categories();
			foreach ($options_categories_obj as $category) {
				$options_categories[$category->cat_ID] = $category->cat_name;
			}
		}
		return $options_categories;
	}

	/**
	 * Pull all the tags into an array
	 * @todo add optional params to limit number returned
	 *
	 * @since 1.0.0
	 * @return array
	 */
	static function tags(){
		static $options_tags = array();
		if ( empty( $options_tags ) ) {
			$options_tags_obj = get_tags();
			foreach ( $options_tags_obj as $tag ) {
				$options_tags[$tag->term_id] = $tag->name;
			}
		}
		return $options_tags;
	}

	/**
	 * Pull all the pages into an array
	 *
	 * @since 1.0.0
	 * @param int $number Set to -1 for all pages
	 * @return array
	 */
	static function pages( $number = false ){
		$number = intval( $number );
		if ( false === $number || empty( $number ) ) {
			$number = HOOT_ADMIN_LIST_ITEM_COUNT;
			static $options_pages = array(); // cache
			if ( empty( $options_pages ) )
				$options_pages = self::get_pages( $number );
			return $options_pages;
		} else {
			$pages = self::get_pages( $number );
			return $pages;
		}
	}

	/**
	 * Get pages array
	 *
	 * @since 1.0.0
	 * @param int $number Set to -1 for all pages
	 * @param string $post_type for custom post types
	 * @return array
	 */
	static function get_pages( $number, $post_type = 'page' ){
		// Doesnt allow -1 as $number
		// $options_pages_obj = get_pages("sort_column=post_parent,menu_order&number=$number");
		// $options_pages[''] = __( 'Select a page:', 'responsive-brix' );
		$options_pages = array();
		$number = intval( $number );
		$the_query = new WP_Query( array( 'post_type' => $post_type, 'posts_per_page' => $number, 'orderby' => 'post_title', 'order' => 'ASC', 'post_status' => 'publish' ) );
		if ( $the_query->have_posts() ) :
			while ( $the_query->have_posts() ) : $the_query->the_post();
				$options_pages[ get_the_ID() ] = get_the_title();
			endwhile;
			wp_reset_postdata();
		endif;
		return $options_pages;
	}

	/**
	 * Pull all the cpt posts into an array
	 *
	 * @since 1.1.1
	 * @param string $post_type for custom post types
	 * @param int $number Set to -1 for all pages
	 * @param string $append Append a value
	 * @return array
	 */
	static function cpt( $post_type = 'page', $number = false, $append = false ){
		$return = array();
		$number = intval( $number );
		if ( false === $number || empty( $number ) ) {
			$number = HOOT_ADMIN_LIST_ITEM_COUNT;
			static $options_cpt = array(); // cache
			if ( empty( $options_cpt[ $post_type ] ) )
				$options_cpt[ $post_type ] = self::get_pages( $number, $post_type );
			$return = $options_cpt[ $post_type ];
		} else {
			$return = self::get_pages( $number, $post_type );
		}
		$return = ( $append ) ? array( $append ) + $return : $return;
		return $return;
	}

	/**
	 * Pull all the posts into an array
	 *
	 * @since 1.0.0
	 * @param int $number Set to -1 for all posts
	 * @return array
	 */
	static function posts( $number = false ){
		$number = intval( $number );
		if ( false === $number || empty( $number ) ) {
			$number = HOOT_ADMIN_LIST_ITEM_COUNT;
			static $options_posts = array(); // cache
			if ( empty( $options_posts ) )
				$options_posts = self::get_posts( $number );
			return $options_posts;
		} else {
			$posts = self::get_posts( $number );
			return $posts;
		}
	}

	/**
	 * Get posts array
	 *
	 * @since 1.0.0
	 * @param int $number Set to -1 for all posts
	 * @return array
	 */
	static function get_posts( $number ){
		$options_posts[0] = __( 'Select a post:', 'responsive-brix' );
		$number = intval( $number );
		$options_posts_obj = get_posts("posts_per_page=$number");
		foreach ( $options_posts_obj as $post ) {
			$options_posts[ $post->ID ] = $post->post_title;
		}
		return $options_posts;
	}

	/**
	 * Create default typography options array
	 *
	 * @since 1.0.0
	 * @return array
	 */
	static function typography_defaults() {
		$fonts = self::fonts( 'websafe' );
		$font = ( is_array( $fonts ) ) ? current( array_keys( $fonts ) ) : 'Arial, Helvetica, sans-serif';
		return apply_filters( 'hoot_of_default_typography', array(
			'size' => '14px',
			'face' => $font,
			'style' => 'normal',
			'color' => '#333333' ) );
	}

	/**
	 * Create font families array
	 *
	 * @since 1.0.0
	 * @param string $return array to return websafe|google-fonts
	 * @return array
	 */
	static function fonts( $return = 'websafe' ) {
		if ( $return == 'websafe' ) {
			if ( ! function_exists('hoot_fonts_list') ) {
				if ( file_exists( trailingslashit( HOOTOPTIONS_DIR ) . 'includes/fonts.php' ) )
					require_once( trailingslashit( HOOTOPTIONS_DIR ) . 'includes/fonts.php' );
				else
					return array();
			}
			return hoot_fonts_list();
		}

		if ( $return == 'google-fonts' || $return == 'google-font' ) {
			if ( ! function_exists('hoot_googlefonts_list') ) {
				if ( file_exists( trailingslashit( HOOTOPTIONS_DIR ) . 'includes/fonts-google.php' ) )
					require_once( trailingslashit( HOOTOPTIONS_DIR ) . 'includes/fonts-google.php' );
				else
					return array();
			}
			return hoot_googlefonts_list();
		}
	}

	/**
	 * Return icon list array
	 *
	 * @since 1.0.0
	 * @param string $return array to return list|icons|sections
	 * @return array
	 */
	static function icons( $return = 'list' ) {
		if ( ! function_exists('hoot_fonticons_list') )
			include_once( trailingslashit( HOOTOPTIONS_DIR ) . 'includes/icons.php' );

		if ( $return == 'sections' || $return == 'section' )
			return hoot_fonticons_list('sections');

		$iconsArray = hoot_fonticons_list('icons');

		if ( $return == 'icons' || $return == 'icon' )
			return $iconsArray;

		if ( $return == 'lists' || $return == 'list' ) {
			$iconsList = array();
			foreach ( $iconsArray as $name => $array ) {
				$iconsList = array_merge( $iconsList, $array );
			}
			return $iconsList;
		}

		return array();
	}

}