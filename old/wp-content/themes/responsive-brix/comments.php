<?php
/* If a post password is required or no comments are given and comments/pings are closed, return. */
if ( post_password_required() || ( !have_comments() && !comments_open() && !pings_open() ) )
	return;
?>

<?php
if ( current_theme_supports( 'woocommerce' ) ) {
	if ( is_cart() || is_checkout() )
		return;
}
?>

<section id="comments-template">

	<?php if ( have_comments() ) : // Check if there are any comments. ?>

		<div id="comments">

			<h3 id="comments-number"><?php comments_number(); ?></h3>

			<ol class="comment-list">
				<?php wp_list_comments(
					array(
						'style'        => 'ol',
						'callback'     => 'hoot_comments_callback',
						'end-callback' => 'hoot_comments_end_callback'
					)
				); ?>
			</ol><!-- .comment-list -->

			<?php get_template_part( 'template-parts/comments-nav' ); // Loads the template-parts/comments-nav.php template. ?>

		</div><!-- #comments-->

	<?php endif; // End check for comments. ?>

	<?php get_template_part( 'template-parts/comments-error' ); // Loads the template-parts/comments-error.php template. ?>

	<?php comment_form(); // Loads the comment form. ?>

</section><!-- #comments-template -->