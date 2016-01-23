<?php
/**
 * The template for displaying the footer.
 * @package Oprum
 */
$options = get_option('oprum_theme_settings');
?>

<div class="clearfix"></div>
	<?php if ( !is_404() ) { ?>
<?php
	$callaction = get_theme_mod('callaction_txt');
	$callbut = get_theme_mod('callaction_but_txt');
	$callurl = get_theme_mod('callaction_url');

	if( $callaction ) { ?>
<section id="callaction">
	<div class="callaction">
		<div class="call-action-content">
<div class="call-action-txt"><span><?php echo esc_attr( get_theme_mod( 'callaction_txt', 'Here the call to action!' ) ); ?></span></div>
	<?php if( !empty( $callbut )&&!empty( $callurl ) ) { ?>
<div class="call-action-but"><a href="<?php echo esc_url( get_theme_mod( 'callaction_url', '#' ) ); ?>" class="btn red"><?php echo esc_attr( get_theme_mod( 'callaction_but_txt', 'Your button text' ) ); ?></a></div>
	<?php } ?>
		</div>
	</div><!--.callaction-->
</section>
	<?php } //if( $callaction ) ?>

<?php } !is_404() ?>

	</div><!-- #content -->
</div><!--#wrap-content-->

	<footer id="colophon" class="wrap site-footer" role="contentinfo">

<div class="grid3">
 <div class="col">
	<?php dynamic_sidebar('footer1'); ?>
</div>
 <div class="col">
	<?php dynamic_sidebar('footer2'); ?>
</div>
 <div class="col">
	<?php dynamic_sidebar('footer3'); ?>
</div>
<div class="clearfix"></div>
</div><!--.grid3-->

		<div class="site-info">
<div class="grid2">
 	<div class="col">
		<?php echo '&copy; '.date('Y'); ?>&nbsp;
<span id="footer-copyright"><?php echo esc_attr( get_theme_mod( 'copyright_txt', 'All rights reserved' ) ); ?></span><span class="sep"> &middot; </span>
		<?php do_action( 'oprum_credits' ); ?>
	</div>
	 <div class="col">
<?php if ( has_nav_menu( 'social' ) ) {
wp_nav_menu(
	array(
	'theme_location'  => 'social',
	'menu_id'         => 'menu-social',
	'depth'           => 1,
	'link_before'     => '<span class="screen-reader-text">',
	'link_after'      => '</span>',
	'fallback_cb'     => '',
	)
);
} ?>
	</div>
<div class="clearfix"></div>
</div><!--grid2-->
		</div><!-- .site-info -->

            <div id="back-to-top">
<a href="#toplink" title="<?php _e('Scroll Up', 'oprum'); ?>"><i class="fa fa-arrow-up"></i><br /><?php _e('Scroll Up', 'oprum'); ?></a>
            </div><!-- /back-to-top -->

	</footer><!-- #colophon -->

<?php wp_footer(); ?>

</body>
</html>