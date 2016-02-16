<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Identity
 */

if ( ! function_exists( 'the_posts_navigation' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 */
function the_posts_navigation() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}
	?>
	<nav class="navigation posts-navigation" role="navigation">
		<h2 class="screen-reader-text"><?php _e( 'Posts navigation', 'identity' ); ?></h2>
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous"><?php next_posts_link( __( 'Older posts', 'identity' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts', 'identity' ) ); ?></div>
			<?php endif; ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'the_post_navigation' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 */
function the_post_navigation() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}
	?>
	<nav class="navigation post-navigation" role="navigation">
		<h2 class="screen-reader-text"><?php _e( 'Post navigation', 'identity' ); ?></h2>
		<div class="nav-links">
			<?php
				previous_post_link( '<div class="nav-previous">%link</div>', '%title' );
				next_post_link( '<div class="nav-next">%link</div>', '%title' );
			?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'identity_post_category' ) ) :
/**
 * Prints HTML with meta information for the post categories.
 */
function identity_post_category() {
	// Hide category and tag text for pages.
	if ( 'post' == get_post_type() ) {

		if ( is_sticky() ) {
			printf( '<span class="sticky-post"><span class="genericon genericon-pinned" aria-hidden="true"></span>' . __( 'Sticky', 'identity' ) . '</span>' );
		}

		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( __( ', ', 'identity' ) );
		if ( $categories_list && identity_categorized_blog() ) {
			printf( '<span class="cat-links">' . $categories_list . '</span>' );
		}

	}
}
endif;

if ( ! function_exists( 'identity_posted_date' ) ) :
/**
 * Returns the current post date.
 *
 * @return string
 */
function identity_posted_date() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_date = sprintf(
		'<span class="genericon genericon-time" aria-hidden="true"></span><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	return $posted_date;
}
endif;


if ( ! function_exists( 'identity_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function identity_posted_on() {
	$posted_on = identity_posted_date();

	$byline = sprintf(
		'<span class="sep"> | </span><span class="author vcard"><span class="genericon genericon-user" aria-hidden="true"></span><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>';

	edit_post_link( __( 'Edit', 'identity' ), '<span class="sep"> | </span><span class="edit-link"><span class="genericon genericon-edit" aria-hidden="true"></span>', '</span>' );
}
endif;

if ( ! function_exists( 'identity_entry_footer' ) ) :
/**
 * Prints HTML with meta information for the tags and comments.
 */
function identity_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' == get_post_type() ) {

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', __( ', ', 'identity' ) );

		//Retrieve the date from function.
		$posted_on = identity_posted_date();

		if ( $tags_list && ! has_post_format( 'quote' ) && ! has_post_format( 'link' ) && ! has_post_format( 'chat' ) && ! has_post_format( 'status' ) && ! has_post_format( 'aside' ) ) {
			printf( '<span class="tags-links"><span class="genericon genericon-tag" aria-hidden="true"></span>' . $tags_list . '</span>' );
		}

		elseif ( has_post_format( 'quote' ) || has_post_format( 'link' ) || has_post_format( 'chat' ) || has_post_format( 'status' ) || has_post_format( 'aside' ) ) {
			echo '<span class="posted-on">' . $posted_on . '</span>';
		}
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link"><span class="genericon genericon-comment" aria-hidden="true"></span>';
		comments_popup_link( __( 'Leave a comment', 'identity' ), __( '1 Comment', 'identity' ), __( '% Comments', 'identity' ) );
		echo '</span>';
	}
}
endif;

if ( ! function_exists( 'the_archive_title' ) ) :
/**
 * Shim for `the_archive_title()`.
 *
 * Display the archive title based on the queried object.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 *
 * @param string $before Optional. Content to prepend to the title. Default empty.
 * @param string $after  Optional. Content to append to the title. Default empty.
 */
function the_archive_title( $before = '', $after = '' ) {
	if ( is_category() ) {
		$title = sprintf( __( 'Category: %s', 'identity' ), single_cat_title( '', false ) );
	} elseif ( is_tag() ) {
		$title = sprintf( __( 'Tag: %s', 'identity' ), single_tag_title( '', false ) );
	} elseif ( is_author() ) {
		$title = sprintf( __( 'Author: %s', 'identity' ), '<span class="vcard">' . get_the_author() . '</span>' );
	} elseif ( is_year() ) {
		$title = sprintf( __( 'Year: %s', 'identity' ), get_the_date( _x( 'Y', 'yearly archives date format', 'identity' ) ) );
	} elseif ( is_month() ) {
		$title = sprintf( __( 'Month: %s', 'identity' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'identity' ) ) );
	} elseif ( is_day() ) {
		$title = sprintf( __( 'Day: %s', 'identity' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'identity' ) ) );
	} elseif ( is_tax( 'post_format' ) ) {
		if ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = _x( 'Asides', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = _x( 'Galleries', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = _x( 'Images', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = _x( 'Videos', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = _x( 'Quotes', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = _x( 'Links', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = _x( 'Statuses', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = _x( 'Audio', 'post format archive title', 'identity' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = _x( 'Chats', 'post format archive title', 'identity' );
		}
	} elseif ( is_post_type_archive() ) {
		$title = sprintf( __( 'Archives: %s', 'identity' ), post_type_archive_title( '', false ) );
	} elseif ( is_tax() ) {
		$tax = get_taxonomy( get_queried_object()->taxonomy );
		/* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
		$title = sprintf( __( '%1$s: %2$s', 'identity' ), $tax->labels->singular_name, single_term_title( '', false ) );
	} else {
		$title = __( 'Archives', 'identity' );
	}

	/**
	 * Filter the archive title.
	 *
	 * @param string $title Archive title to be displayed.
	 */
	$title = apply_filters( 'get_the_archive_title', $title );

	if ( ! empty( $title ) ) {
		echo $before . $title . $after;
	}
}
endif;

if ( ! function_exists( 'the_archive_description' ) ) :
/**
 * Shim for `the_archive_description()`.
 *
 * Display category, tag, or term description.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 *
 * @param string $before Optional. Content to prepend to the description. Default empty.
 * @param string $after  Optional. Content to append to the description. Default empty.
 */
function the_archive_description( $before = '', $after = '' ) {
	$description = apply_filters( 'get_the_archive_description', term_description() );

	if ( ! empty( $description ) ) {
		/**
		 * Filter the archive description.
		 *
		 * @see term_description()
		 *
		 * @param string $description Archive description to be displayed.
		 */
		echo $before . $description . $after;
	}
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function identity_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'identity_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'identity_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so identity_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so identity_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in identity_categorized_blog.
 */
function identity_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'identity_categories' );
}
add_action( 'edit_category', 'identity_category_transient_flusher' );
add_action( 'save_post',     'identity_category_transient_flusher' );

if ( ! function_exists( 'identity_post_thumbnail' ) ) :
/**
 * Display an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 *
 * @since Identity 2.0
 */
function identity_post_thumbnail() {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() && ! is_page_template( 'page-templates/full-width-page.php' ) ) :
	?>

		<div class="entry-thumbnail">
			<?php the_post_thumbnail(); ?>
		</div><!-- .entry-thumbnail -->

	<?php elseif ( is_page_template( 'page-templates/full-width-page.php' ) ) : ?>

		<div class="entry-thumbnail">
			<?php the_post_thumbnail( 'identity-full-width-thumbnail' ); ?>
		</div><!-- .entry-thumbnail -->

	<?php else : ?>

		<div class="entry-thumbnail">
			<a class="entry-thumbnail-link" href="<?php the_permalink(); ?>" aria-hidden="true">
				<?php
					the_post_thumbnail( 'post-thumbnail', array( 'alt' => get_the_title() ) );
				?>
			</a>
		</div><!-- .entry-thumbnail -->	
	
	<?php endif; // End is_singular()
}
endif;

if ( ! function_exists( 'identity_get_link_url' ) ) :
/**
 * Return the post URL.
 *
 * Falls back to the post permalink if no URL is found in the post.
 *
 * @since Identity 2.0
 *
 * @see get_url_in_content()
 *
 * @return string The Link format URL.
 */
function identity_get_link_url() {
	$has_url = get_url_in_content( get_the_content() );

	return $has_url ? $has_url : apply_filters( 'the_permalink', get_permalink() );
}
endif;
