<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Oprum
 */
?>
	<div id="secondary" class="widget-area" role="complementary">
		<?php do_action( 'before_sidebar' ); ?>
<?php if ( is_page() ) { //other sidebar for page ?>

		<?php if ( ! dynamic_sidebar( 'sidebar-2' ) ) : ?>

			<aside id="search" class="widget widget_search">
				<?php get_search_form(); ?>
			</aside>


		<?php endif; // end sidebar widget area ?>

<?php }else{ ?>
		<?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>

			<aside id="search" class="widget widget_search">
				<?php get_search_form(); ?>
			</aside>

		<?php endif; // end sidebar widget area ?>
<?php } ?>
	</div><!-- #secondary -->
