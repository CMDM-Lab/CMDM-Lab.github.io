<?php
/**
 * Identity functions and definitions
 *
 * @package Identity
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 720; /* pixels */
}

if ( ! function_exists( 'identity_content-width' ) ) :
/**
 * Adjust content_width value for the full width page.
 */
function identity_content_width() {
	global $content_width;

	if ( is_page_template( 'page-templates/full-width-page.php' ) ) {
		$content_width = 1140;
	}
}
endif; // identity_content_width
add_action( 'template_redirect', 'identity_content_width' );

if ( ! function_exists( 'identity_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function identity_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Identity, use a find and replace
	 * to change 'identity' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'identity', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 720, 9999, false );
	add_image_size( 'identity-logo', 224, 224 );
	add_image_size( 'identity-header', '1440', '520', false );
	add_image_size( 'identity-full-width-thumbnail', '1140', '9999', false );

	// Declare theme support for Site Logo.
	add_theme_support( 'site-logo', array(
		'size' => 'identity-logo',
	) );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'identity' ),
		'social' => __( 'Social Menu', 'identity' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'gallery', 'video', 'quote', 'link', 'status', 'audio', 'chat',
	) );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'editor-style.css', 'genericons/genericons.css', identity_fonts_url() ) );
	
}
endif; // identity_setup
add_action( 'after_setup_theme', 'identity_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function identity_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar Widget Area', 'identity' ),
		'id'            => 'sidebar-1',
		'description'   => 'This widget area is located on the left or right side of the content.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Widget Area', 'identity' ),
		'id'            => 'sidebar-2',
		'description'   => 'This widget area is located in the footer below the content. ',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'identity_widgets_init' );

if ( ! function_exists( 'identity_fonts_url' ) ) :
/**
 * Register Google fonts for Twenty Fifteen.
 *
 * @since Twenty Fifteen 1.0
 *
 * @return string Google fonts URL for the theme.
 */
function identity_fonts_url() {
	$fonts   = array();
	$subsets = 'latin';

	/* translators: If there are characters in your language that are not supported by Noto Sans, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Droid sans: on or off', 'identity' ) ) {
		$fonts[] = 'Droid Sans:400,700';
	}

	/* translators: If there are characters in your language that are not supported by Noto Serif, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Roboto font: on or off', 'identity' ) ) {
		$fonts[] = 'Roboto:300italic,400,700,900';
	}

	return add_query_arg( array(
		'family' => urlencode( implode( '|', $fonts ) ),
		'subset' => urlencode( $subsets ),
	), '//fonts.googleapis.com/css' );
}
endif;

/**
 * Enqueue scripts and styles.
 */
function identity_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'identity-fonts', identity_fonts_url(), array(), null );

	// Add Genericons, used in the main stylesheet.
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.3.0' );

	// Load our main stylesheet.
	wp_enqueue_style( 'identity-style', get_stylesheet_uri(), array( 'identity-fonts', 'genericons' ) );

	// Load the theme custom script file.
	wp_enqueue_script( 'identity-script', get_stylesheet_directory_uri() . '/js/identity.js', array( 'jquery' ), '20150504', true );

	// Load the theme navigation script file.
	wp_enqueue_script( 'identity-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	// Load the theme skip link focus fix script file.
	wp_enqueue_script( 'identity-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'identity_scripts' );

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
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';
