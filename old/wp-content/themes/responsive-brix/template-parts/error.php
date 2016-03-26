<article <?php hoot_attr( 'post' ); ?>>

	<header class="entry-header">
		<h1 class="entry-title"><?php _e( 'Nothing found', 'responsive-brix' ); ?></h1>
	</header><!-- .entry-header -->

	<div <?php hoot_attr( 'entry-content', '', 'no-shadow' ); ?>>
		<?php echo wpautop( __( 'Apologies, but no entries were found.', 'responsive-brix' ) ); ?>
	</div><!-- .entry-content -->

</article><!-- .entry -->