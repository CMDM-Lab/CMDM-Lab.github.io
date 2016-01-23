<?php
/**
 * The template for Testimonials by WooThemes
 * Template Name: WooTestimonials
 *
 * @package Oprum
 */

get_header(); ?>


<header class="page-header">
	<h1 class="page-title"><?php the_title(); ?></h1>
	<?php oprum_breadcrumb(); ?>
</header>


	<div id="primary" class="content-area">
		<main id="main-woocommerce" class="site-main" role="main">

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
			<?php do_action( 'woothemes_testimonials', array( 'limit' => 10, 'size' => 100, 'per_row' => 2 ) ); ?>
	</div><!-- .entry-content -->

</article><!-- #post-## -->

		</main><!-- #main -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>

<?php get_footer(); ?>
