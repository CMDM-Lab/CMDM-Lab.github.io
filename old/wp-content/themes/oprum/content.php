<?php
/**
 * @package Oprum
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>

<?php if ( 'post' == get_post_type() ) : ?>

		<div class="entry-meta">
			<?php oprum_posted_on(); ?>

<?php if ( !has_post_format( array('aside', 'quote', 'link', 'chat', 'image', 'video', 'audio', 'gallery') ) ) : ?>

                              <!--begin more meta-->
                             <?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
			<?php
				/* translators: used between list items, there is a space after the comma */
				$categories_list = get_the_category_list( __( ', ', 'oprum' ) );
				if ( $categories_list && oprum_categorized_blog() ) :
			?>
			<span class="cat-links">
				<?php printf( __( '%1$s', 'oprum' ), $categories_list ); ?>
			</span>
				<?php endif; // End if categories ?>

			<?php
				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list( '', __( ', ', 'oprum' ) );
				if ( $tags_list ) :
			?>
			<span class="tags-links">
				<?php printf( __( '%1$s', 'oprum' ), $tags_list ); ?>
			</span>
				<?php endif; // End if $tags_list ?>
		<?php endif; // End if 'post' == get_post_type() ?>
                                <!--end more meta-->

<?php endif;//more meta has_post_format() ?>

		</div><!-- .entry-meta -->

<?php endif; ?>

	</header><!-- .entry-header -->

<?php if ( is_search() ) : // Only display Excerpts for Search ?>

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->

<?php else : ?>

	<div class="entry-content">

		<?php if ( has_post_thumbnail() && !has_post_format() ) : ?>

	<div class="entry-thumbnail">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('oprum-big'); ?></a>
	</div>
	<div class="entry-excerpt">
		<?php echo mb_substr( strip_tags( get_the_content() ), 0, 380 ) . '...'; //replacement excerpt ?>
	</div>
	<div class="clear"></div>

<?php else : //has_post_thumbnail() ?>

<?php if ( has_post_format( array('aside', 'quote', 'link', 'chat', 'image', 'video', 'audio')) ) : ?>
	<?php the_content(); ?>
<?php else ://post_format() ?>
	<?php the_excerpt(); ?>
<?php endif; //post_format() ?>

		<?php endif; //has_post_thumbnail() ?>

		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'oprum' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->
	<?php endif; ?>
	<footer class="entry-meta">
			
                                <?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
		<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'oprum' ), __( '1 Comment', 'oprum' ), __( 'Comments: %', 'oprum' ) ); ?></span>
		<?php endif; ?>

		<?php edit_post_link( __( 'Edit', 'oprum' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
