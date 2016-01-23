<?php
/**
 * The Template for displaying single post.
 *
 * @package Oprum
 */

get_header(); ?>

<header class="page-header">
	<nav id="single-nav">
		<?php previous_post_link('<div id="single-nav-right">%link</div>', '<i class="fa fa-chevron-left"></i>', false); ?>
		<?php next_post_link('<div id="single-nav-left">%link</div>', '<i class="fa fa-chevron-right"></i>', false); ?>
	</nav><!-- /single-nav -->

		<?php oprum_breadcrumb(); ?>
</header>

	<div id="primary" class="content-area<?php if ( !is_active_sidebar( 'sidebar-1' ) ) { ?> no-sidebar<?php } ?>">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php if ( has_post_format(array('aside', 'quote', 'link', 'status', 'gallery')) ) : ?>

				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php else : ?>
				<?php get_template_part( 'content', 'single' ); ?>

			<?php endif; ?>

			<?php oprum_content_nav( 'nav-below' ); ?>

			<?php
				if ( comments_open() || '0' != get_comments_number() )
					comments_template();
			?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php if ( is_active_sidebar( 'sidebar-1' ) ) { get_sidebar(); } ?>
<?php get_footer(); ?>