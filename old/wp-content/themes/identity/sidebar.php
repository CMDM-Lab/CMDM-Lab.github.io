<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package Identity
 */

$sidebar = get_theme_mod( 'identity_sidebar', 'right-sidebar' );

if ( ! is_active_sidebar( 'sidebar-1' ) || $sidebar == 'no-sidebar' ) {
	return;
}
?>

<div id="secondary" class="sidebar-container" role="complementary">
	<div class="sidebar-inner">
		<div class="widget-area">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div><!-- .widget-area -->
	</div><!-- .sidebar-inner -->
</div><!-- #secondary -->
