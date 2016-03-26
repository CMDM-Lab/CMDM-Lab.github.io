<?php
/**
 * @package Identity
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="article-inner">
		<header class="entry-header">
			<div class="entry-category">
				<?php identity_post_category(); ?>
			</div><!-- .entry-category -->

			<?php if ( is_single() ) :
					the_title( '<h1 class="entry-title">', '</h1>' );
				else :
					the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' );
				endif; ?>

			<?php if ( 'post' == get_post_type() ) : ?>
			<div class="entry-meta">
				<?php identity_posted_on(); ?>
			</div><!-- .entry-meta -->
			<?php endif; ?>
		</header><!-- .entry-header -->

		<?php identity_post_thumbnail(); ?>

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

		<footer class="entry-footer">
			<?php identity_entry_footer(); ?>
		</footer><!-- .entry-footer -->
	</div><!-- .article-inner -->
</article><!-- #post-## -->