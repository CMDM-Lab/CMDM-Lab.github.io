<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Oprum
 */

get_header(); ?>

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'oprum' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</header><!-- .page-header -->

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'search' ); ?>

			<?php endwhile; ?>

<?php
	if ( class_exists( 'Jetpack' ) && ! Jetpack::is_module_active( 'infinite-scroll' ) ) {
		oprum_content_nav( 'nav-below' );
	}
?>

		<?php else : ?>

			<header class="page-header">
				<h1 class="page-title"><?php _e( 'Nothing Found', 'oprum' ); ?></h1>
			</header><!-- .page-header -->

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php get_template_part( 'no-results', 'search' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>