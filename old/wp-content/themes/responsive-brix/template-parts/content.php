<?php
/**
 * Template to display 'single' content (post / custom post type / attachment)
 *     - on archive pages (multi post list)
 *     - on single post page
 *
 * This is the default template for 'singular' heirarchy. To customize it, you can duplicate
 * it in the same folder and rename it as 'content-{$post-type}', and the new template will
 * be used for that particular {$post-type}
 * Example : Create 'content-page.php' for content displayed on pages.
 *           Create 'content-attachment.php' for displaying content on attachment pages.
 *           And so on for any other custom post type.
 */


/**
 * If viewing a single post/cpt/attachment
 */
if ( is_singular( get_post_type() ) ) :
?>

	<article <?php hoot_attr( 'post' ); ?>>

		<?php /* The current template displays heading outside the article. So to conform to html5 structure (document outline i.e. <header> is inside <article>) we add the Article Heading as screen-reader-text */ ?>
		<header class="entry-header screen-reader-text">
			<h1 <?php hoot_attr( 'entry-title' ); ?>><?php single_post_title(); ?></h1>
		</header><!-- .entry-header -->

		<?php
		if ( hoot_get_option( 'post_featured_image' ) ) {
			$img_size = apply_filters( 'hoot_post_image_single', '' );
			hoot_post_thumbnail( 'entry-content-featured-img', $img_size );
		}
		?>

		<div <?php hoot_attr( 'entry-content' ); ?>>

			<div class="entry-the-content">
				<?php global $post;
				if ( is_attachment() ) {
					echo wp_get_attachment_image( $post->ID, 'full' );
					the_excerpt();
					the_content();
				}
				else
					the_content(); ?>
			</div>
			<?php wp_link_pages(); ?>
		</div><!-- .entry-content -->

		<div class="screen-reader-text" itemprop="datePublished" itemtype="https://schema.org/Date"><?php echo get_the_date('Y-m-d'); ?></div>

		<?php 
		$hide_meta_info = '';
		$hide_meta_info = apply_filters( 'hoot_hide_meta_info', $hide_meta_info, 'bottom' );
		?>
		<?php if ( !$hide_meta_info && 'bottom' == hoot_get_option( 'post_meta_location' ) && !is_attachment() ): ?>
			<footer class="entry-footer">
				<?php hoot_meta_info_blocks( hoot_get_option('post_meta') ); ?>
			</footer><!-- .entry-footer -->
		<?php endif; ?>

	</article><!-- .entry -->

<?php
/**
 * If not viewing a single post i.e. viewing the post in a list index (archive etc.)
 */
else :

	$archive_type = 'big';

	// Loads the template-parts/content-archive-{type}.php template.
	get_template_part( 'template-parts/content-archive', $archive_type );

endif;
?>