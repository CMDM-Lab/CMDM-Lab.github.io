<?php
/**
 * Theme functions and definitions
 *
 * @package Oprum
 */

/**
 * Set the content width for theme design
 */
if ( ! isset( $content_width ) ) {
	$content_width = 756; /* pixels */
}

if ( ! function_exists( 'oprum_content_width' ) ) :

	function oprum_content_width() {
		global $content_width;

		if ( is_page_template( 'template-fullpage.php' ) ) {
			$content_width = 1080;
		}
	}

endif;
add_action( 'template_redirect', 'oprum_content_width' );

if ( ! function_exists( 'oprum_setup' ) ) :
function oprum_setup() {

	 /** Markup for search form, comment form, and comments
	 * valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );


	/**
	 * Make theme available for translation
	 */
	load_theme_textdomain( 'oprum', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support WooCommerce
	 */
	add_theme_support( 'woocommerce' );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'css/editor-style.css', oprum_fonts_url() ) );

	/*
	 * Let WordPress 4.1+ manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/**
	 * Enable support for Post Thumbnails on posts and pages
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
                set_post_thumbnail_size( 200, 150, true );
                add_image_size( 'oprum-medium', 1080, 9999, true );
	add_image_size( 'oprum-small', 75, 75, true );
                add_image_size( 'oprum-big', 1200, 9999, true );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'oprum' ),
		'social' => __( 'Social Menu', 'oprum' ),
	) );

	/**
	 * Enable support for Post Formats
	 */
	add_theme_support( 'post-formats', array( 'image', 'video', 'audio', 'quote', 'link', 'aside', 'status', 'gallery' ) );

	/**
	 * Setup the WordPress core custom header image.
	 */
	add_theme_support( 'custom-header', apply_filters( 'oprum_custom_header_args', array(
                                'header-text'            => true,
		'default-text-color'     => 'fff',
		'width'                  => 1020,
		'height'                 => 450,
		'flex-height'            => true,
                                'flex-width'    => true,
		'wp-head-callback'       => 'oprum_header_style',
		'admin-head-callback'    => 'oprum_admin_header_style',
		'admin-preview-callback' => 'oprum_admin_header_image',
	) ) );

	/**
	 * Setup the WordPress core custom background feature.
	 */
	add_theme_support( 'custom-background', apply_filters( 'oprum_custom_background_args', array(
		'default-color' => 'eaeaea',
		'default-image' => get_template_directory_uri().'/img/bg.jpg',
	) ) );
}
endif; // oprum_setup
add_action( 'after_setup_theme', 'oprum_setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 */
function oprum_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar Posts', 'oprum' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Sidebar Pages', 'oprum' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
       register_sidebar(array(
            'name' => __('Home Bottom', 'oprum'),
            'description' => __('Located at the bottom of the Home page', 'oprum'),
            'id' => 'home-bottom',
            'before_title' => '<p class="widget-title"><span>',
            'after_title' => '</span></p>',
            'before_widget' => '<div class="col">',
            'after_widget' => '</div>'
        ));
       register_sidebar(array(
            'name' => __('Footer1', 'oprum'),
            'description' => __('Located in the footer left.', 'oprum'),
            'id' => 'footer1',
            'before_title' => '<h5>',
            'after_title' => '</h5>',
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>'
        ));
       register_sidebar(array(
            'name' => __('Footer2', 'oprum'),
            'description' => __('Located in the footer center.', 'oprum'),
            'id' => 'footer2',
            'before_title' => '<h5>',
            'after_title' => '</h5>',
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>'
        ));
       register_sidebar(array(
            'name' => __('Footer3', 'oprum'),
            'description' => __('Located in the footer right.', 'oprum'),
            'id' => 'footer3',
            'before_title' => '<h5>',
            'after_title' => '</h5>',
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>'
        ));
}
add_action( 'widgets_init', 'oprum_widgets_init' );

/**
 * Register Google fonts for Theme
 * Better way
 */
if ( ! function_exists( 'oprum_fonts_url' ) ) :

function oprum_fonts_url() {
    $fonts_url = '';
 
    $open_sans = _x( 'on', 'Open Sans font: on or off', 'oprum' );
 
    if ( 'off' !== $open_sans ) {
        $font_families = array();
 
        if ( 'off' !== $open_sans ) {
            $font_families[] = 'Open Sans:300italic,400italic,700italic,400,600,700,300';
        }
 
        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),
            'subset' => urlencode( 'latin,cyrillic' ),
        );
 
        $fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
    }
 
    return $fonts_url;
}
endif;

/**
 *=Enqueue scripts
 */
function oprum_scripts() {
                wp_enqueue_style( 'oprum-style', get_stylesheet_uri() );

	wp_enqueue_script( 'oprum-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '13072014', true );

	wp_enqueue_style( 'oprum-fonts', oprum_fonts_url(), array(), null );

	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css?v=3.2' );//Genericons

	wp_enqueue_style( 'awesome', get_template_directory_uri() . '/font-awesome/css/font-awesome.min.css?v=4.2' );//Awesome

	wp_enqueue_script( 'oprum-fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), '1.1', true );

	wp_enqueue_script( 'flexslider-script', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array( 'jquery' ), '2.4.0', true );

	wp_enqueue_style( 'flexslider-style', get_template_directory_uri() . '/css/flexslider.css?v=1307' );

	wp_enqueue_script( 'oprum-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '13072014', true );

	wp_enqueue_script( 'oprum-main', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), '1.0', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'oprum-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '13072014' );
	}
}
add_action( 'wp_enqueue_scripts', 'oprum_scripts' );

/**
 * Shorten excerpt length
 */
function oprum_excerpt_length($length) {
	if ( is_sticky() ) {
		$length = 60;
	} elseif ( is_home() ) {
		$length = 20;
	} else {
		$length = 40;
	}
	return $length;
}
add_filter('excerpt_length', 'oprum_excerpt_length', 999);

/**
 * Replace [...] in excerpts with something new
 */
function oprum_excerpt_more($more) {
	return '&hellip;';
}
add_filter('excerpt_more', 'oprum_excerpt_more');

/**
 * Breadcrumbs
 */
require get_template_directory() . '/inc/breadcrumbs.php';

/**
 * Custom Pagination
 */
require get_template_directory() . '/inc/pagination.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Custom gallery layout
 */
require( get_template_directory() . '/inc/gallery-layout.php');

/**
 * Footer credits.
 */
function oprum_txt_credits() {
	$text = sprintf( __( 'Powered by %s', 'oprum' ), '<a href="http://wordpress.org/">WordPress</a>' );
	$text .= '<span class="sep"> &middot; </span>';
	$text .= sprintf( __( 'Theme by %s', 'oprum' ), '<a href="http://dinevthemes.com/">DinevThemes</a>' );
	echo apply_filters( 'oprum_txt_credits', $text );
}
add_action( 'oprum_credits', 'oprum_txt_credits' );

/**
 * Customizer
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Add Page Select to the Customizer
 */
function oprum_customize_register( $wp_customize ) {

		$wp_customize->add_setting( 'featured_page', array(
			'default'           => '',
			'sanitize_callback' => 'absint'
		) );

		$wp_customize->add_control( 'featured_page', array(
			'label'    => __( 'Featured Page', 'textdomain' ),
			'section'  => 'oprum_display_options',
			'type'     => 'dropdown-pages'
		) );

}
add_action( 'customize_register', 'oprum_customize_register' );

/**
 * Contextual Help Function File
 */
require( get_template_directory() . '/inc/contextual-help.php' );

/**
 *===============EXTRA FUNCTIONS============*
 */

/**
 * Widget Post Formats Sidebar Display
 */
require get_template_directory() . '/inc/format-aside-widget.php';
require get_template_directory() . '/inc/format-quote-widget.php';
require get_template_directory() . '/inc/format-gallery-widget.php';
require get_template_directory() . '/inc/format-video-widget.php';

/**
 * Add metabox Excerpt for Page.
 */
function oprum_add_excerpt_to_pages() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action('init', 'oprum_add_excerpt_to_pages');

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * =Ready WooPlugins
 */
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//plugin activate?

/*=WooTestimonials Plugin*/

if (is_plugin_active('testimonials-by-woothemes/woothemes-testimonials.php')) {

	function oprum_wootestimonials_css() {
		wp_enqueue_style( 'wootestimonials-custom-style', get_template_directory_uri() . '/css/wootestimonial.css' );
	}
add_action( 'wp_enqueue_scripts', 'oprum_wootestimonials_css' );

} // wootestimonials is_plugin_active

/*=WooCommerce Plugin*/

if (is_plugin_active('woocommerce/woocommerce.php')) {

	function oprum_woocommerce_css() {
		wp_enqueue_style( 'woocommerce-custom-style', get_template_directory_uri() . '/css/woocommerce.css' );
	}
add_action( 'wp_enqueue_scripts', 'oprum_woocommerce_css' );

	function woocommerce_widgets_init() {
		register_sidebar(array(
		'name' => __('Store Sidebar', 'oprum'),
		'description' => __('Located in the sidebar woocommerce page.', 'oprum'),
		'id' => 'store',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>'
		));
	}
add_action( 'widgets_init', 'woocommerce_widgets_init' );

} // woocommerce is_plugin_active