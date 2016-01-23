<?php
/**
 * Miscellaneous template functions.
 * These functions are for use throughout the theme's various template files.
 * This file is loaded via the 'after_setup_theme' hook at priority '10'
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/**
 * Display <title> tag in <head>
 * This function is for backward compatibility only. For WP version greater than 4.1, theme
 * support for 'title-tag' is added in /hoot-theme/hoot-theme.php
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_title_tag() {
	echo "\n";
	?><title><?php wp_title(); ?></title><?php
	echo "\n";
}

/**
 * Outputs the favicon link.
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_favicon() {
	$favicon = hoot_get_option( 'favicon' );
	if ( !empty( $favicon ) ) {
		echo '<link rel="shortcut icon" href="' . esc_url( $favicon ) . '" type="image/x-icon">' . "\n";
	}
}

/**
 * Displays the branding logo
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_logo() {
	$display = '';

	$hoot_logo = hoot_get_option( 'logo' );
	if ( 'text' == $hoot_logo || 'custom' == $hoot_logo ) {

		$title_icon = hoot_get_option( 'site_title_icon', NULL );
		$class = ( $title_icon ) ? ' class="site-logo-with-icon"' : '';

		$display .= '<div id="site-logo-' . $hoot_logo . '"' . $class . '>';

			if ( $title_icon )
				$title_icon = '<i class="fa ' . sanitize_html_class( $title_icon ) . '"></i>';

			$display .= '<h1 ' . hoot_get_attr( 'site-title' ) . '>' .
						'<a href="' . home_url() . '" rel="home">' .
						$title_icon;
			$title = '';

			if ( 'text' == $hoot_logo ) {

				$title = get_bloginfo( 'name' );

			} elseif ( 'custom' == $hoot_logo ) {

				$title = hoot_get_custom_text_logo();

			}

			$display .= apply_filters( 'hoot_site_title', $title );
			$display .= '</a></h1>';

			if ( hoot_get_option( 'show_tagline' ) )
				$display .= hoot_get_site_description();

		$display .= '</div><!--logotext-->';

	} elseif ( 'mixed' == $hoot_logo || 'mixedcustom' == $hoot_logo ) {

		$logo_image = hoot_get_option( 'logo_image' );
		$class = ( $logo_image ) ? ' class="site-logo-with-image"' : '';

		$display .= '<div id="site-logo-' . $hoot_logo . '"' . $class . '>';

			if ( $logo_image )
				$logo_image = '<div class="site-logo-mixed-image"><img src="' . esc_url( $logo_image ) . '" /></div>';

			$display .= '<h1 ' . hoot_get_attr( 'site-title' ) . '>' .
						'<a href="' . home_url() . '" rel="home" class="site-logo-mixed-link">' .
						$logo_image;
			$title = '';

			if ( 'mixed' == $hoot_logo ) {

				$title = get_bloginfo( 'name' );

			} elseif ( 'mixedcustom' == $hoot_logo ) {

				$title = hoot_get_custom_text_logo();

			}

			$display .= '<div class="site-logo-mixed-text">' . 
						 apply_filters( 'hoot_site_title', $title ) .
						 '</div>';
			$display .= '</a></h1>';

			if ( hoot_get_option( 'show_tagline' ) )
				$display .= hoot_get_site_description();

		$display .= '</div><!--logotext-->';

	} elseif ( 'image' == $hoot_logo ) {
		$display .= hoot_get_logo_image();
	}

	echo apply_filters( 'hoot_display_logo', $display );
}

/**
 * Returns the custom text logo
 *
 * @since 1.3
 * @access public
 * @return string
 */
function hoot_get_custom_text_logo() {
	$title = '';
	$title_custom = apply_filters( 'hoot_logo_custom_text', hoot_get_option( 'logo_custom' ) );
	if ( is_array( $title_custom ) && !empty( $title_custom ) ) {
		$lcount = 1;

		foreach ( $title_custom as $title_line ) {
			$title_line = wp_parse_args( $title_line, array( 'text' => '', 'font' => '', 'size' => '' ) );
			if ( !empty( $title_line['text'] ) ) {

				$title_line_class = 'site-title-line site-title-line' . $lcount;
				$title_line_class .= ( $title_line['font'] == 'standard' ) ? ' site-title-body-font' : '';
				$title .= '<span class="' . $title_line_class . '">' . esc_html( $title_line['text'] ) . '</span>';

			}
			$lcount++;
		}

	}
	return $title;
}

/**
 * Returns the image logo
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_get_logo_image() {
	$logo_image = hoot_get_option( 'logo_image' );
	$title = get_bloginfo( 'name' );
	if ( !empty( $logo_image ) ) {
		return '<div id="site-logo-img">'
				. '<h1 ' . hoot_get_attr( 'site-title' ) . '>'
				. '<a href="' . home_url() . '" rel="home">'
				. '<span class="hide-text forcehide">' . $title . '</span>'
				. '<img src="' . esc_url( $logo_image ) . '">'
				. '</a>'
				. '</h1>'
				.'</div>';
	}
}

/**
 * Display a friendly reminder to update outdated browser
 *
 * @since 1.0
 * @access public
 */
function hoot_update_browser() {
	$notice = '<!--[if lte IE 8]><p class="chromeframe">' .
			  sprintf( __( 'You are using an outdated browser (IE 8 or before). For a better user experience, we recommend %1supgrading your browser today%2s or %3sinstalling Google Chrome Frame%4s', 'responsive-brix' ), '<a href="http://browsehappy.com/">', '</a>', '<a href="http://www.google.com/chromeframe/?redirect=true">', '</a>' ) .
			  '</p><![endif]-->';
	echo apply_filters( 'hoot_update_browser_notice', $notice );
}

/**
 * Display title area content
 *
 * @since 1.8
 * @access public
 * @return void
 */
function hoot_display_loop_title_content( $context = 'pre', $vars = array( '', 0, 0 ) ) {

	// Allow manipulation by child themes
	$vars = apply_filters( 'hoot_display_loop_title_content', $vars, $context );

	list($pre_title_content, $pre_title_content_stretch, $pre_title_content_post) = $vars;

	if ( !empty( $pre_title_content ) ) :
		if (
			( $context == 'pre' && !$pre_title_content_post ) ||
			( $context == 'post' && $pre_title_content_post )
			) :

			?><div id="custom-content-title-area" class="<?php echo $context ?>-content-title-area <?php if ($pre_title_content_stretch) echo 'content-title-area-stretch'; else echo 'content-title-area-grid'; ?>"><?php

			if ( !$pre_title_content_stretch ) :
				?>
				<div class="grid">
					<div class="grid-row">
				<?php
			endif;

				echo do_shortcode( $pre_title_content );

			if ( !$pre_title_content_stretch ) :
				?>
					</div>
				</div>
				<?php
			endif;

			?></div><?php

		endif;
	endif;
}

/**
 * Display the meta information HTML for single post/page
 *
 * @since 1.0
 * @access public
 * @param array $display information to display
 * @param string $context context in which meta blocks are being displayed
 * @return void
 */
function hoot_meta_info_blocks( $display = array(), $context = '' ) {
	$default_display = array(
		'author' => true,
		'date' => true,
		'cats' => true,
		'tags' => true,
		'comments' => true,
	);
	$display = wp_parse_args( $display, $default_display );

	$display = apply_filters( 'hoot_meta_info_blocks_display', $display, $context );

	if ( is_page() )
		$display['cats'] = $display['tags'] = false;

	$skip = true;
	foreach ( $display as $check )
		$skip = ( $check ) ? false : $skip;
	if ( $skip ) return;
	?>

	<div class="entry-byline">

		<?php
		$blocks = array();

		if ( !empty( $display['author'] ) ) :
			$blocks['author']['label'] = __( 'By:', 'responsive-brix' );
			ob_start();
			the_author_posts_link();
			$blocks['author']['content'] = '<span ' . hoot_get_attr( 'entry-author' ) . '>' . ob_get_clean() . '</span>';
		endif;

		if ( !empty( $display['date'] ) ) :
			$blocks['date']['label'] = __( 'On:', 'responsive-brix' );
			$blocks['date']['content'] = '<time ' . hoot_get_attr( 'entry-published' ) . '>' . get_the_date() . '</time>';
		endif;

		if ( !empty( $display['cats'] ) ) :
			$category_list = get_the_category_list(', ');
			if ( !empty( $category_list ) ) :
				$blocks['cats']['label'] = __( 'In:', 'responsive-brix' );
				$blocks['cats']['content'] = $category_list;
			endif;
		endif;

		if ( !empty( $display['tags'] ) && get_the_tags() ) :
			$blocks['tags']['label'] = __( 'Tagged:', 'responsive-brix' );
			$blocks['tags']['content'] = ( ! get_the_tags() ) ? __( 'No Tags', 'responsive-brix' ) : get_the_tag_list( '', ', ', '' );
		endif;

		if ( !empty( $display['comments'] ) && comments_open() ) :
			$blocks['comments']['label'] = __( 'With:', 'responsive-brix' );
			ob_start();
			comments_popup_link(__( '0 Comments', 'responsive-brix' ),
								__( '1 Comment', 'responsive-brix' ),
								__( '% Comments', 'responsive-brix' ), 'comments-link', '' );
			$blocks['comments']['content'] = ob_get_clean();
		endif;

		if ( $edit_link = get_edit_post_link() ) :
			$blocks['editlink']['label'] = '';
			$blocks['editlink']['content'] = '<a href="' . $edit_link . '">' . __( 'Edit This', 'responsive-brix' ) . '</a>';
		endif;

		$blocks = apply_filters( 'hoot_meta_info_blocks', $blocks, $context );

		foreach ( $blocks as $key => $block ) {
			if ( !empty( $block['content'] ) ) {
				echo ' <div class="entry-byline-block entry-byline-' . $key . '">';
					if ( !empty( $block['label'] ) )
						echo ' <span class="entry-byline-label">' . $block['label'] . '</span> ';
					echo $block['content'];
				echo ' </div>';
			}
		}
		?>

	</div><!-- .entry-byline -->

	<?php
}

/**
 * Display the post thumbnail image
 *
 * @since 1.0
 * @access public
 * @param string $classes additional classes
 * @param string $size span or column size or actual image size name. Default is content width span.
 * @param bool $crop true|false|null Using null will return closest matched image irrespective of its crop setting
 * @return void
 */
function hoot_post_thumbnail( $classes = '', $size = '', $crop = NULL ) {

	/* Add custom Classes if any */
	$custom_class = '';
	if ( !empty( $classes ) ) {
		$classes = explode( " ", $classes );
		foreach ( $classes as $class ) {
			$custom_class .= ' ' . sanitize_html_class( $class );
		}
	}

	/* Calculate the size to display */
	if ( !empty( $size ) ) {
		if ( 0 === strpos( $size, 'span-' ) || 0 === strpos( $size, 'column-' ) )
			$thumbnail_size = hoot_get_image_size_name( $size, $crop );
		else
			$thumbnail_size = $size;
	} else {
		$size = 'span-' . hoot_main_layout( 'content' );
		$thumbnail_size = hoot_get_image_size_name( $size, $crop );
	}

	/* Let child themes filter the size name */
	$thumbnail_size = apply_filters( 'hoot_post_thumbnail' , $thumbnail_size );

	/* Finally display the image */
	the_post_thumbnail( $thumbnail_size, array( 'class' => "attachment-$thumbnail_size $custom_class", 'itemscope' => '' ) );

}

/**
 * Utility function to extract border class for widget based on user option.
 *
 * @since 1.0
 * @access public
 * @param string $val string value separated by spaces
 * @param int $index index for value to extract from $val
 * @prefix string $prefix prefixer for css class to return
 * @return void
 */
function hoot_widget_border_class( $val, $index=0, $prefix='' ) {
	$val = explode( " ", trim( $val ) );
	if ( isset( $val[ $index ] ) )
		return $prefix . trim( $val[ $index ] );
	else
		return '';
}

/**
 * Utility function to map footer sidebars structure to CSS span architecture.
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_footer_structure() {
	$footers = hoot_get_option( 'footer' );
	$structure = array(
				'1-1' => array( 12, 12, 12, 12 ),
				'2-1' => array(  6,  6, 12, 12 ),
				'2-2' => array(  4,  8, 12, 12 ),
				'2-3' => array(  8,  4, 12, 12 ),
				'3-1' => array(  4,  4,  4, 12 ),
				'3-2' => array(  6,  3,  3, 12 ),
				'3-3' => array(  3,  6,  3, 12 ),
				'3-4' => array(  3,  3,  6, 12 ),
				'4-1' => array(  3,  3,  3,  3 ),
				);
	if ( isset( $structure[ $footers ] ) )
		return $structure[ $footers ];
	else
		return array( 12, 12, 12, 12 );
}

/**
 * Utility function to map 2 column widths to CSS span architecture.
 *
 * @since 1.0
 * @access public
 * @return void
 */
function hoot_2_col_width_to_span( $col_width ) {
	$return = array();
	switch( $col_width ):
		case '33-66':
			$return[0] = 'grid-span-4';
			$return[1] = 'grid-span-8';
			break;
		case '66-33':
			$return[0] = 'grid-span-8';
			$return[1] = 'grid-span-4';
			break;
		case '25-75':
			$return[0] = 'grid-span-3';
			$return[1] = 'grid-span-9';
			break;
		case '75-25':
			$return[0] = 'grid-span-9';
			$return[1] = 'grid-span-3';
			break;
		case '50-50': default:
			$return[0] = $return[1] = 'grid-span-6';
	endswitch;
	return $return;
}

/**
 * Wrapper function for hoot_main_layout() to get the class names for current context.
 * Can only be used after 'posts_selection' action hook i.e. in 'wp' hook or later.
 *
 * @since 1.0
 * @access public
 * @param string $context content|primary-sidebar|sidebar|sidebar-primary
 * @return string
 */
function hoot_main_layout_class( $context ) {
	return hoot_main_layout( $context, 'class' );
}

/**
 * Utility function to return layout size or classes for the context.
 * Can only be used after 'posts_selection' action hook i.e. in 'wp' hook or later.
 *
 * @since 1.0
 * @access public
 * @param string $context content|primary-sidebar|sidebar|sidebar-primary
 * @param string $return class|size return class name or just the span size integer
 * @return string
 */
function hoot_main_layout( $context, $return = 'size' ) {

	// Set layout
	global $hoot_theme;
	if ( !isset( $hoot_theme->currentlayout ) )
		hoot_set_main_layout();

	$span_sidebar = $hoot_theme->currentlayout['sidebar'];
	$span_content = $hoot_theme->currentlayout['content'];

	// Return Class or Span Size for the Content/Sidebar
	if ( $context == 'content' ) {

		if ( $return == 'class' ) {
			$extra_class = ( empty( $span_sidebar ) ) ? ' no-sidebar' : '';
			return ' grid-span-' . $span_content . $extra_class . ' ';
		} elseif ( $return == 'size' ) {
			return intval( $span_content );
		}

	} elseif ( $context == 'primary-sidebar' || $context == 'sidebar' ||  $context == 'sidebar-primary' ) {

		if ( $return == 'class' ) {
			if ( !empty( $span_sidebar ) )
				return ' grid-span-' . $span_sidebar . ' ';
			else
				return '';
		} elseif ( $return == 'size' ) {
			return intval( $span_sidebar );
		}

	}

	return '';

}

/**
 * Utility function to calculate and set main (content+aside) layout according to the sidebar layout
 * set by user for the current view.
 * Can only be used after 'posts_selection' action hook i.e. in 'wp' hook or later.
 *
 * @since 1.0
 * @access public
 */
function hoot_set_main_layout() {

	// Apply Sidebar Layout for Posts
	if ( is_singular( 'post' ) ) {
		$sidebar = hoot_get_option( 'sidebar_posts' );
	}
	// Check for attachment before page (to handle images attached to a page - true for is_page and is_attachment)
	// Apply 'Full Width'
	elseif ( is_attachment() ) {
		$sidebar = 'none';
	}
	elseif ( is_page() ) {
		if ( hoot_is_404() )
			// Apply 'Full Width' if this page is being displayed as a custom 404 page
			$sidebar = 'none';
		else
			// Apply Sidebar Layout for Pages
			$sidebar = hoot_get_option( 'sidebar_pages' );
	}
	// Apply Sidebar Layout for Site
	else {
		$sidebar = hoot_get_option( 'sidebar' );
	}

	/* Allow for custom manipulation of the layout by child themes */
	$sidebar = apply_filters( 'hoot_main_layout', $sidebar );
	$spans = apply_filters( 'hoot_main_layout_spans', array(
		'none' => array(
			'content' => 9,
			'sidebar' => 0,
		),
		'narrow-right' => array(
			'content' => 9,
			'sidebar' => 3,
		),
		'wide-right' => array(
			'content' => 8,
			'sidebar' => 4,
		),
		'default' => array(
			'content' => 8,
			'sidebar' => 4,
		),
	) );

	/* Finally, set the layout for current view */
	global $hoot_theme;
	if ( isset( $spans[ $sidebar ] ) ) {
		$hoot_theme->currentlayout['content'] = $spans[ $sidebar ]['content'];
		$hoot_theme->currentlayout['sidebar'] = $spans[ $sidebar ]['sidebar'];
	} else {
		$hoot_theme->currentlayout['content'] = $spans['default']['content'];
		$hoot_theme->currentlayout['sidebar'] = $spans['default']['sidebar'];
	}

}

/**
 * Utility function to determine the location of page header
 *
 * @since 1.0
 * @access public
 */
function hoot_page_header_attop() {

	$full = wp_parse_args( hoot_get_option( 'page_header_full' ), array(
		'default' => '1',
		'posts' => 0,
		'pages' => '1',
		'no-sidebar' => '1',
		) );


	/* Override For Full Width Pages (including 404 page) */
	if ( $full['no-sidebar'] ) {
		$sidebar_size = hoot_main_layout( 'primary-sidebar' );
		if ( empty( $sidebar_size ) || hoot_is_404() )
			return apply_filters( 'hoot_page_header_attop', true );
	}

	/* For Posts */
	if ( is_singular( 'post' ) ) {
		if ( $full['posts'] )
			return apply_filters( 'hoot_page_header_attop', true );
		else
			return apply_filters( 'hoot_page_header_attop', false );
	}

	/* For Pages */
	if ( is_page() ) {
		if ( $full['pages'] )
			return apply_filters( 'hoot_page_header_attop', true );
		else
			return apply_filters( 'hoot_page_header_attop', false );
	}

	/* Default */
	if ( $full['default'] )
		return apply_filters( 'hoot_page_header_attop', true );
	else
		return apply_filters( 'hoot_page_header_attop', false );

}