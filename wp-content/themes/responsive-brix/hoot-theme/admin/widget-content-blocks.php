<?php
/**
 * Content Blocks Widget
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/**
* Class Hoot_Content_Blocks_Widget
*/
class Hoot_Content_Blocks_Widget extends Hoot_WP_Widget {

	function __construct() {
		parent::__construct(

			//id
			'hoot-content-blocks-widget',

			//name
			__( 'Hoot > Content Blocks', 'responsive-brix' ),

			//widget_options
			array(
				'description'	=> __('Display Styled Content Blocks.', 'responsive-brix'),
				'class'			=> 'hoot-content-blocks-widget', // CSS class applied to frontend widget container via 'before_widget' arg
			),

			//control_options
			array(),

			//form_options
			//'name' => can be empty or false to hide the name
			array(
				array(
					'name'		=> __( 'Blocks Style', 'responsive-brix' ),
					'id'		=> 'style',
					'type'		=> 'images',
					'std'		=> 'style1',
					'options'	=> array(
						'style1'	=> trailingslashit( HOOT_THEMEURI ) . 'admin/images/content-block-style-1.png',
						'style2'	=> trailingslashit( HOOT_THEMEURI ) . 'admin/images/content-block-style-2.png',
						'style3'	=> trailingslashit( HOOT_THEMEURI ) . 'admin/images/content-block-style-3.png',
						'style4'	=> trailingslashit( HOOT_THEMEURI ) . 'admin/images/content-block-style-4.png',
					),
				),
				array(
					'name'		=> __( 'No. Of Columns', 'responsive-brix' ),
					'id'		=> 'columns',
					'type'		=> 'select',
					'std'		=> '3',
					'options'	=> array(
						'1'	=> __( '1', 'responsive-brix' ),
						'2'	=> __( '2', 'responsive-brix' ),
						'3'	=> __( '3', 'responsive-brix' ),
						'4'	=> __( '4', 'responsive-brix' ),
						'5'	=> __( '5', 'responsive-brix' ),
					),
				),
				array(
					'name'		=> __( 'Icon Style', 'responsive-brix' ),
					'desc'		=> __( "Not applicable if 'Featured Image' is seected below.", 'responsive-brix' ),
					'id'		=> 'icon_style',
					'type'		=> 'select',
					'std'		=> 'circle',
					'options'	=> array(
						'none'		=> __( 'None', 'responsive-brix' ),
						'circle'	=> __( 'Circle', 'responsive-brix' ),
						'square'	=> __( 'Square', 'responsive-brix' ),
					),
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
				array(
					'name'		=> __( "Use 'Featured Image' of page instead of icons.", 'responsive-brix' ),
					'id'		=> 'image',
					'type'		=> 'checkbox',
				),
				array(
					'name'		=> __( "Display excerpt instead of full content (Read More link will be automatically inserted if no Custom Link URL is provided below)", 'responsive-brix' ),
					'id'		=> 'excerpt',
					'type'		=> 'checkbox',
				),
				array(
					'name'		=> __( 'Content Boxes', 'responsive-brix' ),
					'id'		=> 'boxes',
					'type'		=> 'group',
					'options'	=> array(
						'item_name'	=> __( 'Content Box', 'responsive-brix' ),
					),
					'fields'	=> array(
						array(
							'name'		=> __('Icon', 'responsive-brix'),
							'desc'		=> __( "Not applicable if 'Featured Image' is selected above.", 'responsive-brix' ),
							'id'		=> 'icon',
							'type'		=> 'icon'),
						array(
							'name'		=> __( 'Page', 'responsive-brix' ),
							'id'		=> 'page',
							'type'		=> 'select',
							'options'	=> Hoot_WP_Widget::get_wp_list('page'),
						),
						array(
							'name'		=> __('Link Text (optional)', 'responsive-brix'),
							'id'		=> 'link',
							'type'		=> 'text'),
						array(
							'name'		=> __('Link URL', 'responsive-brix'),
							'id'		=> 'url',
							'std'		=> 'http://',
							'type'		=> 'text',
							'sanitize'	=> 'url'),
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
		include( hoot_locate_widget( 'content-blocks' ) ); // Loads the widget/content-blocks or template-parts/widget-content-blocks.php template.
	}

}

/**
 * Register Widget
 */
function hoot_content_blocks_widget_register(){
	register_widget('Hoot_Content_Blocks_Widget');
}
add_action('widgets_init', 'hoot_content_blocks_widget_register');