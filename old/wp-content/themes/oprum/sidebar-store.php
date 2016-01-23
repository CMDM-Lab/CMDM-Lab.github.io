<?php
/**
 * The Sidebar containing the WooCommerce widget areas.
 *
 * @package Oprum
 */
?>
	<div id="secondary" class="widget-area" role="complementary">
		<?php do_action( 'before_sidebar' ); ?>

		<?php if ( ! dynamic_sidebar( 'store' ) ) : ?>

			<aside id="search" class="widget widget_search">
				<?php get_search_form(); ?>
			</aside>

		<?php endif; // end sidebar widget area ?>


	</div><!-- #secondary -->
