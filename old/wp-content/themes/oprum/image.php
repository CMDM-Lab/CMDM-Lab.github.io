<?php
/**
 * The template for displaying image attachments.
 *
 * @package Oprum
 */

get_header(); ?>

<header class="page-header">

<?php
	the_title( '<h1 style="display:inline-block;">', '</h1>' );
?>
	<nav id="single-nav">
		<div id="single-nav-right"><?php previous_image_link('%link', '<i class="fa fa-chevron-left"></i>', false); ?></div>
		<div id="single-nav-left"><?php next_image_link('%link', '<i class="fa fa-chevron-right"></i>', false); ?></div>
	</nav><!-- /single-nav -->

</header>

	<div id="primary" class="content-area image-attachment">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="entry-content">

					<div class="entry-attachment">
						<div class="attachment">
							<?php oprum_the_attached_image(); ?>
						</div><!-- .attachment -->

						<?php if ( has_excerpt() ) : ?>
						<div class="entry-caption">
							<?php the_excerpt(); ?>
						</div><!-- .entry-caption -->
						<?php endif; ?>
					</div><!-- .entry-attachment -->

					<div class="entry-meta">
						<?php
							$metadata = wp_get_attachment_metadata();
							printf( __( 'Published <span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span> at <a href="%3$s">%4$s &times; %5$s</a> in <a href="%6$s" rel="gallery">%7$s</a>', 'oprum' ),
								esc_attr( get_the_date( 'c' ) ),
								esc_html( get_the_date() ),
								esc_url( wp_get_attachment_url() ),
								$metadata['width'],
								$metadata['height'],
								esc_url( get_permalink( $post->post_parent ) ),
								get_the_title( $post->post_parent )
							);
						?>
					</div><!-- .entry-meta -->

				</div><!-- .entry-content -->

<?php edit_post_link( __( 'Edit', 'oprum' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>

			</article><!-- #post-## -->

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
