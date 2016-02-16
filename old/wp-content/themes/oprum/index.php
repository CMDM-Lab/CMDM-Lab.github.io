<?php
/**
 * The main template file.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Oprum
 */
$options = get_option('oprum_theme_settings');
get_header(); ?>

<?php if ( !is_front_page() ) : ?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

<?php if ( have_posts() ) : ?>
<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					*/
					get_template_part( 'content', get_post_format() );
				?>

			<?php endwhile; ?>

<?php oprum_paging_nav(); ?>
<?php else : ?>
<?php get_template_part( 'no-results', 'index' ); ?>
<?php endif;  /* END the Loop */ ?>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>

<?php else : /*!is_front_page()*/ ?>
	<?php get_template_part( 'content', 'home' ); ?>
<?php endif; //!is_front_page() ?>

<?php get_footer(); ?>