<?php
/**
 * Helper functions for working with the WordPress sidebar system.  Currently, the framework creates a 
 * simple function for registering HTML5-ready sidebars instead of the default WordPress unordered lists.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/**
 * Wrapper function for WordPress' register_sidebar() function.  This function exists so that theme authors 
 * can more quickly register sidebars with an HTML5 structure instead of having to write the same code 
 * over and over.  Theme authors are also expected to pass in the ID, name, and description of the sidebar. 
 * This function can handle the rest at that point.
 *
 * @since 1.0.0
 * @access public
 * @param array   $args
 * @return string  Sidebar ID.
 */
function hoot_register_sidebar( $args ) {

	/* Set up some default sidebar arguments. */
	$defaults = array(
		'id'            => '',
		'name'          => '',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	);

	/* Allow developers to filter the default sidebar arguments. */
	$defaults = apply_filters( 'hoot_sidebar_defaults', $defaults );

	/* Parse the arguments. */
	$args = wp_parse_args( $args, $defaults );

	/* Allow developers to filter the sidebar arguments. */
	$args = apply_filters( 'hoot_sidebar_args', $args );

	/* Remove action. */
	remove_action( 'widgets_init', '__return_false', 95 );

	/* Register the sidebar. */
	return register_sidebar( $args );
}

/* Compatibility for when a theme doesn't register any sidebars. */
add_action( 'widgets_init', '__return_false', 95 );

/**
 * Registers footer widget areas.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_footer_register_sidebars() {

	$columns = hoot_get_option_footer();

	if( $columns ) :
		$alphas = range('a', 'z');
		for ( $i=0; $i < $columns; $i++ ) :
			if ( isset( $alphas[ $i ] ) ) :
				hoot_register_sidebar(
					array(
						'id'          => 'footer-' . $alphas[ $i ],
						'name'        => sprintf( _x( 'Footer %s', 'sidebar', 'responsive-brix' ), strtoupper( $alphas[ $i ] ) ),
						'description' => __( 'You can set footer columns in Theme Options page.', 'responsive-brix' )
					)
				);
			endif;
		endfor;
	endif;

}

/* Hook into action */
add_action( 'widgets_init', 'hoot_footer_register_sidebars' );

/**
 * Get footer column option.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_get_option_footer() {
	$footers = hoot_get_option( 'footer' );
	$columns = ( $footers ) ? intval( substr( $footers, 0, 1 ) ) : false;
	$columns = ( is_numeric( $columns ) && 0 < $columns ) ? $columns : false;
	return $columns;
}

/**
 * Registers footer widget areas.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_widgetized_template_register_sidebars() {

	if ( current_theme_supports( 'hoot-widgetized-template' ) ) :

		/* Set up some default widget areas. */
		$defaults = array(
			'area_a' => __('Widgetized Template - Area A', 'responsive-brix'),
			'area_b' => __('Widgetized Template - Area B', 'responsive-brix'),
			'area_c' => __('Widgetized Template - Area C', 'responsive-brix'),
			'area_d_1' => __('Widgetized Template - Area D Left', 'responsive-brix'),
			'area_d_2' => __('Widgetized Template - Area D Right', 'responsive-brix'),
		);

		/* Allow changing widget areas by the theme. */
		$defaults = apply_filters( 'hoot_widgetized_template_widget_areas', $defaults );

		foreach ( $defaults as $key => $name ) {
			hoot_register_sidebar(
				array(
					'id'          => 'widgetized-template-' . $key,
					'name'        => $name,
					'description' => __( 'You can order Widgetized Template areas in Theme Options page.', 'responsive-brix' )
				)
			);
		}

	endif;

}

/* Hook into action */
add_action( 'widgets_init', 'hoot_widgetized_template_register_sidebars' );
