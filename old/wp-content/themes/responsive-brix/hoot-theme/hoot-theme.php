<?php
/**
 * Hoot Theme hooked into the framework
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/* Sets premium theme features when available */
if ( ! defined( 'HOOT_PREMIUM' ) )
	define( 'HOOT_PREMIUM', false );

/**
 * The Hoot Theme class launches the theme setup.
 *
 * Theme setup functions are performed on the 'after_setup_theme' hook with a priority of 10.
 * Child themes can add theme setup function with a priority of 11. This allows the Hoot
 * framework class to load theme-supported features on the 'after_setup_theme' hook with a
 * priority of 12.
 * Also, hoot constants are available at 'after_setup_theme' hook at a priority of 10 or later.
 * 
 * @since responsive-brix 1.0
 * @access public
 */
if ( !class_exists( 'Hoot_Theme' ) ) {
	class Hoot_Theme {

		/**
		 * Constructor method to controls the load order of the required files
		 *
		 * @since 1.0
		 * @access public
		 * @return void
		 */
		function __construct() {

			/* Set up an empty class */
			global $hoot_theme;
			$hoot_theme = new stdClass;

			/* Load theme includes. Must keep priority 10 for theme constants to be available. */
			add_action( 'after_setup_theme', array( $this, 'includes' ), 10 );

			/* Theme setup on the 'after_setup_theme' hook. Must keep priority 10 for framework to load properly. */
			add_action( 'after_setup_theme', array( $this, 'theme_setup' ), 10 );

			/* Theme setup on the 'wp' hook. Only used for special scenarios (like enqueueing scripts/styles) based on conditional tags. */
			add_action( 'wp', array( $this, 'conditional_theme_setup' ), 10 );

			/* Handle content width for embeds and images. Hooked into 'init' so that we can pull custom content width from theme options */
			add_action( 'init', array( $this, 'content_width' ), 10 );

			/* Modify the '[...]' Read More Text */
			add_filter( 'the_content_more_link', array( $this, 'modify_read_more_link' ) );
			if ( apply_filters( 'hoot_force_excerpt_readmore', true ) ) {
				add_filter( 'excerpt_more', array( $this, 'insert_excerpt_readmore_quicktag' ), 11 );
				add_filter( 'wp_trim_excerpt', array( $this, 'replace_excerpt_readmore_quicktag' ), 11, 2 );
			} else {
				add_filter( 'excerpt_more', array( $this, 'modify_read_more_link' ) );
			}

			/* Modify the exceprt length. Make sure to set the priority correctly such as 999, else the default WordPress filter on this function will run last and override settng here.  */
			add_filter( 'excerpt_length', array( $this, 'custom_excerpt_length' ), 999 );

		}

		/**
		 * Loads the theme files supported by themes and template-related functions/classes.  Functionality 
		 * in these files should not be expected within the theme setup function.
		 *
		 * @since 1.0
		 * @access public
		 * @return void
		 */
		function includes() {

			/* Load enqueue functions */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'enqueue.php' );

			/* Load image sizes. */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'media.php' );

			/* Load menus */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'menus.php' );

			/* Load sidebars */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'sidebars.php' );

			/* Load the custom css functions. */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'css.php' );

			/* Load the Theme Specific HTML attributes */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'attr.php' );

			/* Load the misc template functions. */
			require_once( trailingslashit( HOOT_THEMEDIR ) . 'template-helpers.php' );

		}

		/**
		 * Add theme supports. This is how the theme hooks into the framework and loads proper modules.
		 *
		 * @since 1.0
		 * @access public
		 * @return void
		 */
		function theme_setup() {

			/** Misc **/

			// Display <title> tag in <head>
			add_theme_support( 'title-tag' );
			// Backward compatibility for WP version before 4.1
			if ( ! function_exists( '_wp_render_title_tag' ) )
				add_action( 'wp_head', 'hoot_title_tag', 1 );

			// Enable Font Icons
			// Disable this (remove this line) if the theme doesnt use font icons,
			// or if the font-awesome library is being enqueued by some other plugin using
			// a handle other than 'font-awesome' or 'fontawesome' (to avoid loading the
			// library twice)
			add_theme_support( 'font-awesome' );

			// Enable google fonts (fixed fonts, or entire library)
			add_theme_support( 'google-fonts' );

			// Enable widgetized template (options in Admin Panel)
			add_theme_support( 'hoot-widgetized-template' );

			/** WordPress **/

			// Adds theme support for WordPress 'featured images'.
			add_theme_support( 'post-thumbnails' );

			// Automatically add feed links to <head>.
			add_theme_support( 'automatic-feed-links' );

			/** Hoot Extensions **/

			// Enable custom widgets
			add_theme_support( 'hoot-core-widgets' );

			// Pagination.
			add_theme_support( 'loop-pagination' );

			// Nicer [gallery] shortcode implementation when Jetpack tiled-gallery is not active
			if ( !class_exists( 'Jetpack' ) || !Jetpack::is_module_active( 'tiled-gallery' ) ) 
				add_theme_support( 'cleaner-gallery' );

			// Better captions for themes to style.
			add_theme_support( 'cleaner-caption' );

			/** WooCommerce **/
			if ( class_exists( 'WooCommerce' ) ) {
				add_theme_support( 'woocommerce' );
				get_template_part( 'woocommerce/functions' );
			}

		}

		/**
		 * Theme setup on the 'wp' hook. Only used for special scenarios based on conditional tags.
		 * Like enqueueing scripts/styles conditionally, or adding theme support so that enqueue functions
		 * hooked into 'wp_enqueue_scripts' load the script/styles.
		 *
		 * @since 1.0
		 * @access public
		 * @return void
		 */
		function conditional_theme_setup() {

			/* Enable Light Slider if its the 'Widgetized Template' */
			if ( is_page_template() ) {
				$template_slug = get_page_template_slug();
				if ( 'page-templates/template-widgetized.php' == $template_slug ) {
					add_theme_support( 'light-slider' );
				}
			}

		}

		/**
		 * Handle content width for embeds and images.
		 *
		 * @since 1.0
		 * @access public
		 * @return void
		 */
		function content_width() {
			$width = intval( hoot_get_option( 'site_width' ) );
			$width = !empty( $width ) ? $width : 1260;
			hoot_set_content_width( $width );
		}

		/**
		 * Modify the '[...]' Read More Text
		 *
		 * @since 1.0
		 * @access public
		 * @return string
		 */
		function modify_read_more_link( $more = '[...]' ) {
			$read_more = hoot_get_option('read_more');
			$read_more = ( empty( $read_more ) ) ? sprintf( __( 'Read More %s', 'responsive-brix' ), '&rarr;' ) : $read_more;
			global $post;
			$read_more = '<a class="more-link" href="' . get_permalink( $post->ID ) . '">' . $read_more . '</a>';
			return apply_filters( 'hoot_readmore', $read_more ) ;
		}

		/**
		 * Always display the 'Read More' link in Excerpts.
		 * Insert quicktag to be replaced later in 'wp_trim_excerpt()'
		 *
		 * @since 1.6
		 * @access public
		 * @return string
		 */
		function insert_excerpt_readmore_quicktag( $more = '' ) {
			return '<!--hoot-read-more-quicktag-->';
		}

		/**
		 * Always display the 'Read More' link in Excerpts.
		 * Replace quicktag with read more link
		 *
		 * @since 1.6
		 * @access public
		 * @return string
		 */
		function replace_excerpt_readmore_quicktag( $text, $raw_excerpt ) {
			$read_more = $this->modify_read_more_link();
			$text = str_replace( '<!--hoot-read-more-quicktag-->', '', $text );
			return $text . $read_more;
		}

		/**
		 * Modify the exceprt length.
		 *
		 * @since 1.0
		 * @access public
		 * @return void
		 */
		function custom_excerpt_length( $length ) {
			$excerpt_length = intval( hoot_get_option('excerpt_length') );
			if ( !empty( $excerpt_length ) )
				return $excerpt_length;
			return 105;
		}

	}
}