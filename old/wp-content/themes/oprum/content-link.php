<?php
/**
 * The template for Link post format
 * @package Oprum
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( is_search() ) : // Only display Excerpts for Search ?>
		<div class="entry-summary">
	<?php the_excerpt(); ?>
		</div><!-- .entry-summary -->
	<?php else : ?>
		<div class="entry-content">
	<?php
$textlink = get_the_title();
$content = get_the_content();

$content = apply_filters('the_content', $content);

$repl = array( '<p>', '</p>', '<br />', 'http://' );
$torepl   = array( '', '">' . $textlink . '</a>', '', '' );

$content = '<div class="metka genericon genericon-' . get_post_format() . '"></div><a href="http://' . str_replace( $repl, $torepl, $content );

echo $content;
?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'oprum' ),
				'after'  => '</div>',
			) );
		?>
		</div><!-- .entry-content -->
	<?php endif; ?>
	<footer class="entry-meta<?php if ( !is_active_sidebar( 'sidebar-1' ) ) { ?> no-sidebar<?php } ?>">
			
                                <?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
		<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'oprum' ), __( '1 Comment', 'oprum' ), __( 'Comments: %', 'oprum' ) ); ?></span>
		<?php endif; ?>

		<?php edit_post_link( __( 'Edit', 'oprum' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
