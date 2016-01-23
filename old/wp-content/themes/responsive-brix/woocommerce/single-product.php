<?php
/**
 * The Template for displaying all single products.
 */
?>

<?php get_header( 'shop' ); ?>

<?php
// Dispay Loop Meta at top
if ( hoot_page_header_attop() ) {
	get_template_part( 'template-parts/loop-meta', 'shop' ); // Loads the template-parts/loop-meta-shop.php template to display Title Area with Meta Info (of the loop)
}
?>

<div class="grid">

	<div class="grid-row">

		<main <?php hoot_attr( 'content' ); ?>>

			<?php
			/**
			 * woocommerce_before_main_content hook
			 *
			 * removed @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
			 * @hooked woocommerce_breadcrumb - 20
			 */
			do_action( 'woocommerce_before_main_content' );
			?>

			<?php if ( have_posts() ) : ?>

				<div id="content-wrap">

					<?php
					// Dispay Loop Meta in content wrap
					if ( ! hoot_page_header_attop() ) {
						get_template_part( 'template-parts/loop-meta', 'shop' ); // Loads the template-parts/loop-meta-shop.php template to display Title Area with Meta Info (of the loop)
					}
					?>

					<?php while ( have_posts() ) : the_post(); ?>

						<?php wc_get_template_part( 'content', 'single-product' ); ?>

					<?php endwhile; ?>

				</div><!-- #content-wrap -->

			<?php else : ?>

				<?php
				// Loads the template-parts/error.php template.
				get_template_part( 'template-parts/error' );
				?>

			<?php endif; ?>

			<?php
			/**
			 * woocommerce_after_main_content hook
			 *
			 * removed @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
			 */
			do_action( 'woocommerce_after_main_content' );
			?>

		</main><!-- #content -->

		<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
		?>

	</div><!-- .grid-row -->

</div><!-- .grid -->

<?php get_footer( 'shop' ); ?>
