<?php
/**
 * If viewing a single post page.
 */
if ( is_singular( 'post' ) ) :
?>

	<div class="loop-nav">
		<?php previous_post_link( '<div class="prev">' . __( 'Previous Post: %link', 'responsive-brix' ) . '</div>', '%title' ); ?>
		<?php next_post_link(     '<div class="next">' . __( 'Next Post: %link',     'responsive-brix' ) . '</div>', '%title' ); ?>
	</div><!-- .loop-nav -->

<?php
/**
 * If viewing the blog, an archive, or search results.
 */
elseif ( is_home() || is_archive() || is_search() ) :

	if ( function_exists('wp_pagenavi' ) ) {

		// Load WP-PageNavi plugin if exist
		wp_pagenavi();

	} else {

		hoot_loop_pagination(
			array( 
				'prev_text' => _x( '&larr; Previous', 'posts navigation', 'responsive-brix' ), 
				'next_text' => _x( 'Next &rarr;',     'posts navigation', 'responsive-brix' )
			) 
		);

	}

endif;