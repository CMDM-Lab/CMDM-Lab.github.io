<?php
/**
 * The template for displaying pages.
 *
 * @package Oprum
 */

get_header(); ?>

<?php if ( !is_front_page() ) : ?>

<header class="page-header">
	<h1 class="page-title"><?php the_title(); ?></h1>
	<?php oprum_breadcrumb(); ?>
</header>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || '0' != get_comments_number() )
						comments_template();
				?>

			<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>

<?php endif; // !is_front_page() ?>

<?php if ( is_front_page() ) : ?>	
	<div class="page-template-template-fullpage-php">
	<div id="primary" class="site-content">
		<main id="main" class="site-main" role="main">

	<?php if ( has_post_thumbnail() ) {
the_post_thumbnail( 'oprum-big' );
	} ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>


			<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- .fullpage-php -->
	</div><!-- #primary -->
<?php endif; // is_front_page() ?>

<?php get_footer(); ?>
