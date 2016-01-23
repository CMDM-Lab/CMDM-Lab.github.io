<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Identity
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="page-inner">
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header><!-- .entry-header -->

		<?php if ( has_post_thumbnail() ) { ?>
			<div class="entry-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div><!-- .entry-thumbnail -->
		<?php } ?>
		
		<div class="entry-content">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'identity' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry-content -->

		<footer class="entry-footer">
			<?php edit_post_link( __( 'Edit', 'identity' ), '<span class="edit-link">', '</span>' ); ?>
		</footer><!-- .entry-footer -->
	</div><!-- .page-inner -->
</article><!-- #post-## -->
