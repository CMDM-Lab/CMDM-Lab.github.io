<?php
/**
 * Call To Action Widget
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/**
* Class Hoot_CTA_Widget
*/
class Hoot_CTA_Widget extends Hoot_WP_Widget {

	function __construct() {
		parent::__construct(

			//id
			'hoot-cta-widget',

			//name
			__( 'Hoot > Call To Action', 'responsive-brix' ),

			//widget_options
			array(
				'description'	=> __('Display Call To Action block.', 'responsive-brix'),
				'class'			=> 'hoot-cta-widget', // CSS class applied to frontend widget container via 'before_widget' arg
			),

			//control_options
			array(),

			//form_options
			//'name' => can be empty or false to hide the name
			array(
				array(
					'name'		=> __( 'Headline', 'responsive-brix' ),
					'id'		=> 'headline',
					'type'		=> 'text',
				),
				array(
					'name'		=> __( 'Description', 'responsive-brix' ),
					'id'		=> 'description',
					'type'		=> 'textarea',
				),
				array(
					'name'		=> __( 'Button Text', 'responsive-brix' ),
					'desc'		=> __( 'Leave empty if you dont want to show button', 'responsive-brix' ),
					'id'		=> 'button_text',
					'type'		=> 'text',
					'std'		=> __( 'KNOW MORE', 'responsive-brix' ),
				),
				array(
					'name'		=> __( 'URL', 'responsive-brix' ),
					'desc'		=> __( 'Leave empty if you dont want to show button', 'responsive-brix' ),
					'id'		=> 'url',
					'type'		=> 'text',
					'sanitize'	=> 'url',
				),
				array(
					'name'		=> __( 'Border', 'responsive-brix' ),
					'desc'		=> __( 'Top and bottom borders.', 'responsive-brix' ),
					'id'		=> 'border',
					'type'		=> 'select',
					'std'		=> 'none none',
					'options'	=> array(
						'line line'	=> __( 'Top - Line || Bottom - Line', 'responsive-brix' ),
						'line shadow'	=> __( 'Top - Line || Bottom - DoubleLine', 'responsive-brix' ),
						'line none'	=> __( 'Top - Line || Bottom - None', 'responsive-brix' ),
						'shadow line'	=> __( 'Top - DoubleLine || Bottom - Line', 'responsive-brix' ),
						'shadow shadow'	=> __( 'Top - DoubleLine || Bottom - DoubleLine', 'responsive-brix' ),
						'shadow none'	=> __( 'Top - DoubleLine || Bottom - None', 'responsive-brix' ),
						'none line'	=> __( 'Top - None || Bottom - Line', 'responsive-brix' ),
						'none shadow'	=> __( 'Top - None || Bottom - DoubleLine', 'responsive-brix' ),
						'none none'	=> __( 'Top - None || Bottom - None', 'responsive-brix' ),
					),
				),
			)
		);
	}

	/**
	 * Echo the widget content
	 */
	function display_widget( $instance, $before_title = '', $title='', $after_title = '' ) {
		extract( $instance, EXTR_SKIP );
		include( hoot_locate_widget( 'cta' ) ); // Loads the widget/cta or template-parts/widget-cta.php template.
	}

}

/**
 * Register Widget
 */
function hoot_cta_widget_register(){
	register_widget('Hoot_CTA_Widget');
}
add_action('widgets_init', 'hoot_cta_widget_register');