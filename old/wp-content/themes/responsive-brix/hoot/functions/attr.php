<?php
/**
 * HTML attribute functions and filters.  The purposes of this is to provide a way for theme/plugin devs 
 * to hook into the attributes for specific HTML elements and create new or modify existing attributes.  
 * This is sort of like `body_class()`, `post_class()`, and `comment_class()` on steroids.  Plus, it 
 * handles attributes for many more elements.  The biggest benefit of using this is to provide richer 
 * microdata while being forward compatible with the ever-changing Web.  Currently, the default microdata 
 * vocabulary supported is Schema.org.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/* Attributes for major structural elements. */
add_filter( 'hoot_attr_body',    'hoot_attr_body',    5    );
add_filter( 'hoot_attr_header',  'hoot_attr_header',  5    );
add_filter( 'hoot_attr_footer',  'hoot_attr_footer',  5    );
add_filter( 'hoot_attr_content', 'hoot_attr_content', 5    );
add_filter( 'hoot_attr_sidebar', 'hoot_attr_sidebar', 5, 2 );
add_filter( 'hoot_attr_menu',    'hoot_attr_menu',    5, 2 );

/* Header attributes. */
add_filter( 'hoot_attr_site-title',       'hoot_attr_site_title',       5 );
add_filter( 'hoot_attr_site-description', 'hoot_attr_site_description', 5 );

/* Loop attributes. */
add_filter( 'hoot_attr_loop-meta',        'hoot_attr_loop_meta',        5 );
add_filter( 'hoot_attr_loop-title',       'hoot_attr_loop_title',       5 );
add_filter( 'hoot_attr_loop-description', 'hoot_attr_loop_description', 5 );

/* Post-specific attributes. */
add_filter( 'hoot_attr_post',            'hoot_attr_post',            5    );
add_filter( 'hoot_attr_entry',           'hoot_attr_post',            5    ); // Alternate for "post".
add_filter( 'hoot_attr_page',            'hoot_attr_post',            5    ); // Alternate for "post".
add_filter( 'hoot_attr_entry-title',     'hoot_attr_entry_title',     5    );
add_filter( 'hoot_attr_entry-author',    'hoot_attr_entry_author',    5    );
add_filter( 'hoot_attr_entry-published', 'hoot_attr_entry_published', 5    );
add_filter( 'hoot_attr_entry-content',   'hoot_attr_entry_content',   5    );
add_filter( 'hoot_attr_entry-summary',   'hoot_attr_entry_summary',   5    );
add_filter( 'hoot_attr_entry-terms',     'hoot_attr_entry_terms',     5, 2 );

/* Comment specific attributes. */
add_filter( 'hoot_attr_comment',           'hoot_attr_comment',           5 );
add_filter( 'hoot_attr_comment-author',    'hoot_attr_comment_author',    5 );
add_filter( 'hoot_attr_comment-published', 'hoot_attr_comment_published', 5 );
add_filter( 'hoot_attr_comment-permalink', 'hoot_attr_comment_permalink', 5 );
add_filter( 'hoot_attr_comment-content',   'hoot_attr_comment_content',   5 );

/**
 * Outputs an HTML element's attributes.
 *
 * @since 1.0.0
 * @access public
 * @param string  $slug     The slug/ID of the element (e.g., 'sidebar').
 * @param string $context  A specific context (e.g., 'primary').
 * @param string $class    Addisitonal css classes to add.
 * @return void
 */
function hoot_attr( $slug, $context = '', $class = '' ) {
	echo hoot_get_attr( $slug, $context, $class );
}

/**
 * Gets an HTML element's attributes.  This function is actually meant to be filtered by theme authors, plugins, 
 * or advanced child theme users.  The purpose is to allow folks to modify, remove, or add any attributes they 
 * want without having to edit every template file in the theme.  So, one could support microformats instead 
 * of microdata, if desired.
 *
 * @since 1.0.0
 * @access public
 * @param string  $slug    The slug/ID of the element (e.g., 'sidebar').
 * @param string $context  A specific context (e.g., 'primary').
 * @param string $classes  Addisitonal css classes to add.
 * @return string
 */
function hoot_get_attr( $slug, $context = '', $classes = '' ) {

	$out    = '';
	$attr   = apply_filters( "hoot_attr_{$slug}", array(), $context );

	if ( empty( $attr ) )
		$attr['class'] = $slug;

	/* Add custom Classes if any */
	if ( !empty( $classes ) ) {
		$custom_class = '';
		$classes = explode( " ", $classes );
		foreach ( $classes as $class ) {
			$custom_class .= ' ' . sanitize_html_class( $class );
		}
		$attr['class'] = empty( $attr['class'] ) ? $custom_class : $attr['class'] . ' ' . $custom_class;
	}

	/* Create attributes */
	foreach ( $attr as $name => $value )
		$out .= !empty( $value ) ? sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) ) : esc_html( " {$name}" );

	return trim( $out );
}

/* === Structural === */

/**
 * <body> element attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_body( $attr ) {

	$attr['class']     = join( ' ', get_body_class() );
	$attr['dir']       = is_rtl() ? 'rtl' : 'ltr';

	return $attr;
}

/**
 * Page <header> element attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_header( $attr ) {

	$attr['id']        = 'header';
	$attr['class']     = 'site-header';
	$attr['role']      = 'banner';
	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/WPHeader';

	return $attr;
}

/**
 * Page <footer> element attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_footer( $attr ) {

	$attr['id']        = 'footer';
	$attr['role']      = 'contentinfo';
	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/WPFooter';

	return $attr;
}

/**
 * Main content container of the page attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_content( $attr ) {

	$attr['id']       = 'content';
	$attr['class']    = 'content';
	$attr['role']     = 'main';

	if ( is_page_template() ) {
		$template_slug = basename( get_page_template(), '.php' );
		$attr['class'] .= ' ' . sanitize_html_class( 'content-' . $template_slug );
	}

	if ( hoot_is_404() )
		$attr['class'] .= ' custom-404-content';

	return $attr;
}

/**
 * Sidebar attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
function hoot_attr_sidebar( $attr, $context ) {

	if ( !empty( $context ) )
		$attr['id'] = "sidebar-" . sanitize_html_class( $context );

	$attr['class']     = 'sidebar';
	$attr['role']      = 'complementary';

	if ( !empty( $context ) ) {
		/* Translators: The %s is the sidebar name. This is used for the 'aria-label' attribute. */
		$attr['aria-label'] = esc_attr( sprintf( _x( '%s Sidebar', 'sidebar aria label', 'responsive-brix' ), hoot_get_sidebar_name( $context ) ) );
	}

	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/WPSideBar';

	return $attr;
}

/**
 * Nav menu attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
function hoot_attr_menu( $attr, $context ) {

	if ( !empty( $context ) )
		$attr['id'] = "menu-{$context}";

	$attr['class']      = 'menu';
	$attr['role']       = 'navigation';

	if ( !empty( $context ) ) {
		/* Translators: The %s is the menu name. This is used for the 'aria-label' attribute. */
		$attr['aria-label'] = esc_attr( sprintf( _x( '%s Menu', 'nav menu aria label', 'responsive-brix' ), hoot_get_menu_location_name( $context ) ) );
	}

	$attr['itemscope']  = 'itemscope';
	$attr['itemtype']   = 'http://schema.org/SiteNavigationElement';

	return $attr;
}

/* === header === */

/**
 * Site title attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_site_title( $attr ) {

	$attr['id']       = 'site-title';
	$attr['itemprop'] = 'headline';

	return $attr;
}

/**
 * Site description attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_site_description( $attr ) {

	$attr['id']       = 'site-description';
	$attr['itemprop'] = 'description';

	return $attr;
}

/* === loop === */

/**
 * Loop meta attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_loop_meta( $attr ) {

	$attr['class']     = 'loop-meta';
	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/WebPageElement';

	return $attr;
}

/**
 * Loop title attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_loop_title( $attr ) {

	$attr['class']     = 'loop-title';
	$attr['itemprop']  = 'headline';

	return $attr;
}

/**
 * Loop description attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_loop_description( $attr ) {

	$attr['class']     = 'loop-description';
	$attr['itemprop']  = 'text';

	return $attr;
}

/* === posts === */

/**
 * Post <article> element attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_post( $attr ) {

	$post = get_post();

	/* Make sure we have a real post first. */
	if ( !empty( $post ) ) {

		$attr['id']        = 'post-' . get_the_ID();
		$attr['class']     = join( ' ', get_post_class() );
		$attr['itemscope'] = 'itemscope';

		if ( 'post' === get_post_type() ) {

			$attr['itemtype']  = 'http://schema.org/BlogPosting';
			$attr['itemprop']  = 'blogPost';
		}

		elseif ( 'attachment' === get_post_type() && wp_attachment_is_image() ) {

			$attr['itemtype'] = 'http://schema.org/ImageObject';
		}

		elseif ( 'attachment' === get_post_type() && hoot_attachment_is_audio() ) {

			$attr['itemtype'] = 'http://schema.org/AudioObject';
		}

		elseif ( 'attachment' === get_post_type() && hoot_attachment_is_video() ) {

			$attr['itemtype'] = 'http://schema.org/VideoObject';
		}

		else {
			$attr['itemtype']  = 'http://schema.org/CreativeWork';
		}

	} else {

		$attr['id']    = 'post-0';
		$attr['class'] = join( ' ', get_post_class() );
	}

	return $attr;
}

/**
 * Post title attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_entry_title( $attr ) {

	$attr['class']    = 'entry-title';
	$attr['itemprop'] = 'headline';

	return $attr;
}

/**
 * Post author attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_entry_author( $attr ) {

	$attr['class']     = 'entry-author';
	$attr['itemprop']  = 'author';
	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/Person';

	return $attr;
}

/**
 * Post time/published attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_entry_published( $attr ) {

	$attr['class']    = 'entry-published updated';
	$attr['datetime'] = get_the_time( 'Y-m-d\TH:i:sP' );

	/* Translators: Post date/time "title" attribute. */
	$attr['title']    = get_the_time( _x( 'l, F j, Y, g:i a', 'post time format', 'responsive-brix' ) );

	return $attr;
}

/**
 * Post content (not excerpt) attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_entry_content( $attr ) {

	$attr['class'] = 'entry-content';

	if ( 'post' === get_post_type() )
		$attr['itemprop'] = 'articleBody';
	else
		$attr['itemprop'] = 'text';

	return $attr;
}

/**
 * Post summary/excerpt attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_entry_summary( $attr ) {

	$attr['class']    = 'entry-summary';
	$attr['itemprop'] = 'description';

	return $attr;
}

/**
 * Post terms (tags, categories, etc.) attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
function hoot_attr_entry_terms( $attr, $context ) {

	if ( !empty( $context ) ) {

		$attr['class'] = 'entry-terms ' . sanitize_html_class( $context );

		if ( 'category' === $context )
			$attr['itemprop'] = 'articleSection';

		else if ( 'post_tag' === $context )
			$attr['itemprop'] = 'keywords';
	}

	return $attr;
}


/* === Comment elements === */


/**
 * Comment wrapper attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_comment( $attr ) {

	$attr['id']    = 'comment-' . get_comment_ID();
	$attr['class'] = join( ' ', get_comment_class() );

	if ( in_array( get_comment_type(), array( '', 'comment' ) ) ) {

		$attr['itemprop']  = 'comment';
		$attr['itemscope'] = 'itemscope';
		$attr['itemtype']  = 'http://schema.org/UserComments';
	}

	return $attr;
}

/**
 * Comment author attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_comment_author( $attr ) {

	$attr['class']     = 'comment-author';
	$attr['itemprop']  = 'creator';
	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/Person';

	return $attr;
}

/**
 * Comment time/published attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_comment_published( $attr ) {

	$attr['class']    = 'comment-published';
	$attr['datetime'] = get_comment_time( 'Y-m-d\TH:i:sP' );

	/* Translators: Comment date/time "title" attribute. */
	$attr['title']    = get_comment_time( _x( 'l, F j, Y, g:i a', 'comment time format', 'responsive-brix' ) );
	$attr['itemprop'] = 'commentTime';

	return $attr;
}

/**
 * Comment permalink attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_comment_permalink( $attr ) {

	$attr['class']    = 'comment-permalink';
	$attr['href']     = get_comment_link();
	$attr['itemprop'] = 'url';

	return $attr;
}

/**
 * Comment content/text attributes.
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @return array
 */
function hoot_attr_comment_content( $attr ) {

	$attr['class']    = 'comment-content';
	$attr['itemprop'] = 'commentText';

	return $attr;
}
