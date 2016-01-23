<li <?php hoot_attr( 'comment' ); ?>>

	<header class="comment-meta comment-ping">
		<cite <?php hoot_attr( 'comment-author' ); ?>><?php comment_author_link(); ?></cite>
		<br />
		<div class="comment-meta-block">
			<time <?php hoot_attr( 'comment-published' ); ?>><?php printf( __( '%s ago', 'responsive-brix' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
		</div>
		<div class="comment-meta-block">
			<a <?php hoot_attr( 'comment-permalink' ); ?>><?php _e( 'Permalink', 'responsive-brix' ); ?></a>
		</div>
		<?php edit_comment_link(); ?>
	</header><!-- .comment-meta -->

<?php /* No closing </li> is needed.  WordPress will know where to add it. */ ?>