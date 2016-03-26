<?php
/**
 * The Template for displaying product archives, including the main
 * shop page which is a post type archive.
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

					<?php
					/**
					 * woocommerce_before_shop_loop hook
					 *
					 * @hooked woocommerce_result_count - 20
					 * @hooked woocommerce_catalog_ordering - 30
					 */
					do_action( 'woocommerce_before_shop_loop' );
					?>

					<?php woocommerce_product_loop_start(); ?>

						<?php woocommerce_product_subcategories(); ?>

						<?php while ( have_posts() ) : the_post(); ?>

							<?php wc_get_template_part( 'content', 'product' ); ?>

						<?php endwhile; ?>

					<?php woocommerce_product_loop_end(); ?>

					<?php
					/**
					 * woocommerce_after_shop_loop hook
					 *
					 * @hooked woocommerce_pagination - 10
					 */
					do_action( 'woocommerce_after_shop_loop' );
					?>

				</div><!-- #content-wrap -->

			<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

				<?php wc_get_template( 'loop/no-products-found.php' ); ?>

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
