<?php
// Dispay Sidebar if not a one-column layout
$sidebar_size = hoot_main_layout( 'primary-sidebar' );
if ( !empty( $sidebar_size ) ) :
?>

	<aside <?php hoot_attr( 'sidebar', 'primary' ); ?>>

		<?php
		if ( is_active_sidebar( 'primary-sidebar' ) ) : // If the sidebar has widgets.

			dynamic_sidebar( 'primary-sidebar' ); // Displays the primary sidebar.

		else : // If the sidebar has no widgets.

			the_widget(
				'WP_Widget_Text',
				array(
					'title'  => __( 'Example Widget', 'responsive-brix' ),
					/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
					'text'   => sprintf( __( 'This is an example widget to show how the Primary sidebar looks by default. You can add custom widgets from the %swidgets screen%s in the admin.', 'responsive-brix' ), current_user_can( 'edit_theme_options' ) ? '<a href="' . admin_url( 'widgets.php' ) . '">' : '', current_user_can( 'edit_theme_options' ) ? '</a>' : '' ),
					'filter' => true,
				),
				array(
					'before_widget' => '<section class="widget widget_text">',
					'after_widget'  => '</section>',
					'before_title'  => '<h3 class="widget-title">',
					'after_title'   => '</h3>'
				)
			);

		endif; // End widgets check.
		?>

	</aside><!-- #sidebar-primary -->

<?php
endif; // End layout check.
?>