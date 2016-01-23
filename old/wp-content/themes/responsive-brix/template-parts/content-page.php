<?php
/**
 * Template to display single static page content
 */

/**
 * If viewing a single page (pages can occur in archive lists as well. Example: search results)
 */
if ( is_page() ) :
?>

	<article <?php hoot_attr( 'page' ); ?>>

		<?php /* The current template displays heading outside the article. So to conform to html5 structure (document outline i.e. <header> is inside <article>) we add the Article Heading as screen-reader-text */ ?>
		<header class="entry-header screen-reader-text">
			<h1 <?php hoot_attr( 'entry-title' ); ?>><?php single_post_title(); ?></h1>
		</header><!-- .entry-header -->

		<?php
		if ( hoot_get_option( 'post_featured_image' ) && !hoot_is_404() ) {
			$img_size = apply_filters( 'hoot_post_image_page', '' );
			hoot_post_thumbnail( 'entry-content-featured-img', $img_size );
		}
		?>

		<?php $entry_content_class = ( hoot_is_404() ) ? 'no-shadow' : ''; ?>
		<div <?php hoot_attr( 'entry-content', '', $entry_content_class ); ?>>

			<div class="entry-the-content">
				<?php the_content(); ?>
			</div>
			<?php wp_link_pages(); ?>

		</div><!-- .entry-content -->

		<div class="screen-reader-text" itemprop="datePublished" itemtype="https://schema.org/Date"><?php echo get_the_date('Y-m-d'); ?></div>

		<?php 
		$hide_meta_info = '';
		$hide_meta_info = apply_filters( 'hoot_hide_meta_info', $hide_meta_info, 'bottom' );
		?>
		<?php if ( !$hide_meta_info && 'bottom' == hoot_get_option( 'post_meta_location' ) ): ?>
			<footer class="entry-footer">
				<?php hoot_meta_info_blocks( hoot_get_option('page_meta') ); ?>
			</footer><!-- .entry-footer -->
		<?php endif; ?>

	</article><!-- .entry -->

<?php
/**
 * If not viewing a single page i.e. viewing the page in a list index (Example: search results)
 */
else :

	global $hoot_theme;
	if ( empty( $hoot_theme->searchresults_hide_pages ) ) {

		$archive_type = 'big';

		// Loads the template-parts/content-archive-{type}.php template.
		get_template_part( 'template-parts/content-archive', $archive_type );

	}

endif;
?>