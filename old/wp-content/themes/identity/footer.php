<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Identity
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-footer-inner">
			<?php get_sidebar( 'footer' ); ?>
			<div class="site-info">
				<span class="site-info-left"><?php echo __( 'Proudly powered by ', 'identity' ) ?><a href="<?php echo esc_url( __( 'http://wordpress.org/', 'identity' ) ); ?>" rel="generator">WordPress</a></span>
				<span class="site-info-right"><?php printf( __( 'Theme: %1$s by %2$s', 'identity' ), '<a href="http://michaelvandenberg.com/portfolio/themes/identity/" rel="theme">Identity</a>', '<a href="http://michaelvandenberg.com/" rel="designer">Michael Van Den Berg</a>' ); ?></span>
			</div><!-- .site-info -->
		</div><!-- .footer-inner -->
	</footer><!-- #colophon -->

	<a href="#content" class="back-to-top"><span class="screen-reader-text">Back To Top</span><span class="genericon genericon-top" aria-hidden="true"></span></a>
	
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
