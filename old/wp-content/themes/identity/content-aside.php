<?php
/**
 * @package Identity
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="article-inner">

		<div class="inner-flex">

			<div class="inner-left">

					<span class="genericon genericon-aside"></span>

			</div><!-- .inner-left -->	

			<div class="inner-right">

				<header class="entry-header">
					
					<?php the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>

				</header><!-- .entry-header -->

				<div class="entry-content">
					<?php
						/* translators: %s: Name of current post */
						the_content( sprintf(
							__( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'identity' ),
							the_title( '<span class="screen-reader-text">"', '"</span>', false )
						) );
					?>

					<?php
						wp_link_pages( array(
							'before' => '<div class="page-links">' . __( 'Pages:', 'identity' ),
							'after'  => '</div>',
						) );
					?>
				</div><!-- .entry-content -->

			</div><!-- .inner-right -->
			
		</div><!-- .inner-flex -->

		<footer class="entry-footer">
			<?php identity_entry_footer(); ?>
		</footer><!-- .entry-footer -->
	</div><!-- .article-inner -->
</article><!-- #post-## -->
