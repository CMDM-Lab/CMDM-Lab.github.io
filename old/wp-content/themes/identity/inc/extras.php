<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Identity
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function identity_body_classes( $classes ) {
	// Adds a class depending on whether sidebar is active and the selection in the customizer.
	$sidebar = get_theme_mod( 'identity_sidebar', 'right-sidebar' );

	if ( ! is_active_sidebar( 'sidebar-1' ) || $sidebar == 'no-sidebar' ) {
		$classes[] = 'no-sidebar';
	}

	elseif ( is_active_sidebar( 'sidebar-1' ) && $sidebar == 'right-sidebar' ) {
		$classes[] = 'right-sidebar';
	}

	elseif ( is_active_sidebar( 'sidebar-1' ) && $sidebar == 'left-sidebar' ) {
		$classes[] = 'left-sidebar';
	}

	// Adds a class when the footer widget area is active and what is selected in the customizer.
	$footer = get_theme_mod( 'identity_footer_widgets', 'three-widgets' );

	if ( ! is_active_sidebar( 'sidebar-2' ) || $footer == 'no-widgets' ) {
		$classes[] = 'no-footer-widgets';
	}

	elseif ( is_active_sidebar( 'sidebar-2' ) && $footer == 'two-widgets' ) {
		$classes[] = 'two-footer-widgets';
	}

	elseif ( is_active_sidebar( 'sidebar-2' ) && $footer == 'three-widgets' ) {
		$classes[] = 'three-footer-widgets';
	}

	elseif ( is_active_sidebar( 'sidebar-2' ) && $footer == 'four-widgets' ) {
		$classes[] = 'four-footer-widgets';
	}

	// Adds a class when the full width page template is being used.
	if ( is_page_template( 'page-templates/full-width.php' ) ) {
		$classes[] = 'full-width';
	}

	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
add_filter( 'body_class', 'identity_body_classes' );

/**
 * If the given post is a single post, then add a class to this post.
 *
 * @since 1.0
 *
 * @param The array of classes being rendered on a single post element.
 * @return The array of classes being rendered on a single post element.
 * @link https://tommcfarlin.com/add-class-to-single-post/
 */
function identity_add_post_class_to_single_post( $classes ) {

	if ( is_single() ) {
		array_push( $classes, 'single-post' );
	} // end if

	return $classes;
}
add_filter( 'post_class', 'identity_add_post_class_to_single_post' );

if ( version_compare( $GLOBALS['wp_version'], '4.1', '<' ) ) :
	/**
	 * Filters wp_title to print a neat <title> tag based on what is being viewed.
	 *
	 * @param string $title Default title text for current view.
	 * @param string $sep Optional separator.
	 * @return string The filtered title.
	 */
	function identity_wp_title( $title, $sep ) {
		if ( is_feed() ) {
			return $title;
		}

		global $page, $paged;

		// Add the blog name
		$title .= get_bloginfo( 'name', 'display' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}

		// Add a page number if necessary:
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title .= " $sep " . sprintf( __( 'Page %s', 'identity' ), max( $paged, $page ) );
		}

		return $title;
	}
	add_filter( 'wp_title', 'identity_wp_title', 10, 2 );

	/**
	 * Title shim for sites older than WordPress 4.1.
	 *
	 * @link https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
	 * @todo Remove this function when WordPress 4.3 is released.
	 */
	function identity_render_title() {
		?>
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<?php
	}
	add_action( 'wp_head', 'identity_render_title' );
endif;
