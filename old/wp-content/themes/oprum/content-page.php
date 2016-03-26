<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Oprum
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( has_excerpt() ) : ?>
	<header class="entry-header">
		<?php the_excerpt(); ?>
	</header><!-- .entry-header -->
		<?php endif; //has_excerpt() ?>	
<div class="entry-content">
			<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'oprum-big' ); ?>
			<?php endif; //has_post_thumbnail ?>
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'oprum' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<?php edit_post_link( __( 'Edit', 'oprum' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>
</article><!-- #post-## -->
