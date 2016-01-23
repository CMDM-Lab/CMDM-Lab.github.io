<?php
/**
 * @package Oprum
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>




<header class="entry-header">

	<?php if ( has_post_thumbnail() && !has_post_format() ) : ?>
		<?php the_post_thumbnail( 'oprum-medium' ); ?>
	<?php endif; //has_post_thumbnail ?>

	<?php if ( has_excerpt() ) : ?>
		<?php the_excerpt(); ?>
	<?php endif; //has_excerpt() ?>	

</header><!-- .entry-header -->

	<div class="entry-content">
	<h1 class="page-title"><?php the_title(); ?></h1>
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'oprum' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-meta<?php if ( !is_active_sidebar( 'sidebar-1' ) ) { ?> no-sidebar<?php } ?>">

		<div class="posted">
			<?php oprum_posted_on(); ?>
		</div>
		<div class="extrameta">
		<?php
			/* translators: used between list items, there is a space after the comma */
			$category_list = '<span class="cat-links"> ' . get_the_category_list( __( ', ', 'oprum' ) ) . '</span>';

			/* translators: used between list items, there is a space after the comma */
			$tag_list = '<span class="tags-links"> ' . get_the_tag_list( '', __( ', ', 'oprum' ) ) . '</span>';

			if ( ! oprum_categorized_blog() ) {
				if ( '' != $tag_list ) {
					$meta_text = __( '%2$s', 'oprum' );
				} else {
					$meta_text = '';
				}

			} else {
				if ( '' != $tag_list ) {
					$meta_text = __( '%1$s %2$s', 'oprum' );
				} else {
					$meta_text = '';
				}

			} // end check for categories on this blog

			printf(
				$meta_text,
				$category_list,
				$tag_list,
				get_permalink()
			);
		?>
		</div>

		<?php edit_post_link( __( 'Edit', 'oprum' ), '<span class="edit-link">', '</span>' ); ?>

	</footer><!-- .entry-meta -->

</article><!-- #post-## -->
