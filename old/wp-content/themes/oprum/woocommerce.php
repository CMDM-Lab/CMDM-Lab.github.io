<?php
/**
 * The template for WooCommerce Shop
 * Template Name: WooCommerce
 *
 * @package Oprum
 */

get_header(); ?>

<header class="page-header">
<div class="wooshop-title">
	<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
<?php
	$args = array(
		'delimiter' => ' &#8250; ',
	);
?>
	<?php woocommerce_breadcrumb( $args ); ?>
</div>

<?php
if ( is_plugin_active('woocommerce/woocommerce.php') && empty( $right ) ) {
?>
<ul class="header-cart">
	<?php oprum_cart_link(); ?>
</ul>
<?php } ?>

</header>

	<div id="primary" class="content-area">
		<main id="main-woocommerce" class="site-main" role="main">

			<?php woocommerce_content(); ?>

		</main><!-- #main-woocommerce -->
	</div><!-- #primary -->
<?php get_sidebar('store'); ?>

<?php get_footer(); ?>
