<?php
/**
 * Social Icons Widget
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/**
* Class Hoot_Social_Icons_Widget
*/
class Hoot_Social_Icons_Widget extends Hoot_WP_Widget {

	function __construct() {
		parent::__construct(

			//id
			'hoot-social-icons-widget',

			//name
			__( 'Hoot > Social Icons', 'responsive-brix' ),

			//widget_options
			array(
				'description'	=> __('Display Social Icons', 'responsive-brix'),
				'class'			=> 'hoot-social-icons-widget', // CSS class applied to frontend widget container via 'before_widget' arg
			),

			//control_options
			array(),

			//form_options
			//'name' => can be empty or false to hide the name
			array(
				array(
					'name'		=> __( 'Icon Size', 'responsive-brix' ),
					'id'		=> 'size',
					'type'		=> 'select',
					'std'		=> 'medium',
					'options'	=> array(
						'small'		=> __( 'Small', 'responsive-brix' ),
						'medium'	=> __( 'Medium', 'responsive-brix' ),
						'large'		=> __( 'Large', 'responsive-brix' ),
						'huge'		=> __( 'Huge', 'responsive-brix' ),
					),
				),
				array(
					'name'		=> __( 'Social Icons', 'responsive-brix' ),
					'id'		=> 'icons',
					'type'		=> 'group',
					'options'	=> array(
						'item_name'	=> __( 'Icon', 'responsive-brix' ),
					),
					'fields'	=> array(
						array(
							'name'		=> __( 'Social Icon', 'responsive-brix' ),
							'id'		=> 'icon',
							'type'		=> 'select',
							'options'	=> array(
								'fa-behance'	=> __( 'Behance', 'responsive-brix' ),
								'fa-bitbucket'	=> __( 'Bitbucket', 'responsive-brix' ),
								'fa-btc'		=> __( 'BTC', 'responsive-brix' ),
								'fa-codepen'	=> __( 'Codepen', 'responsive-brix' ),
								'fa-delicious'	=> __( 'Delicious', 'responsive-brix' ),
								'fa-deviantart'	=> __( 'Deviantart', 'responsive-brix' ),
								'fa-digg'		=> __( 'Digg', 'responsive-brix' ),
								'fa-dribbble'	=> __( 'Dribbble', 'responsive-brix' ),
								'fa-dropbox'	=> __( 'Dropbox', 'responsive-brix' ),
								'fa-envelope'	=> __( 'Email', 'responsive-brix' ),
								'fa-facebook'	=> __( 'Facebook', 'responsive-brix' ),
								'fa-flickr'		=> __( 'Flickr', 'responsive-brix' ),
								'fa-foursquare'	=> __( 'Foursquare', 'responsive-brix' ),
								'fa-github'		=> __( 'Github', 'responsive-brix' ),
								'fa-google-plus'=> __( 'Google Plus', 'responsive-brix' ),
								'fa-instagram'	=> __( 'Instagram', 'responsive-brix' ),
								'fa-lastfm'		=> __( 'Last FM', 'responsive-brix' ),
								'fa-linkedin'	=> __( 'Linkedin', 'responsive-brix' ),
								'fa-pinterest'	=> __( 'Pinterest', 'responsive-brix' ),
								'fa-reddit'		=> __( 'Reddit', 'responsive-brix' ),
								'fa-rss'		=> __( 'RSS', 'responsive-brix' ),
								'fa-skype'		=> __( 'Skype', 'responsive-brix' ),
								'fa-slack'		=> __( 'Slack', 'responsive-brix' ),
								'fa-slideshare'	=> __( 'Slideshare', 'responsive-brix' ),
								'fa-soundcloud'	=> __( 'Soundcloud', 'responsive-brix' ),
								'fa-stack-exchange'	=> __( 'Stack Exchange', 'responsive-brix' ),
								'fa-stack-overflow'	=> __( 'Stack Overflow', 'responsive-brix' ),
								'fa-steam'		=> __( 'Steam', 'responsive-brix' ),
								'fa-stumbleupon'=> __( 'Stumbleupon', 'responsive-brix' ),
								'fa-tumblr'		=> __( 'Tumblr', 'responsive-brix' ),
								'fa-twitch'		=> __( 'Twitch', 'responsive-brix' ),
								'fa-twitter'	=> __( 'Twitter', 'responsive-brix' ),
								'fa-vimeo-square'=> __( 'Vimeo', 'responsive-brix' ),
								'fa-wordpress'	=> __( 'Wordpress', 'responsive-brix' ),
								'fa-yelp'		=> __( 'Yelp', 'responsive-brix' ),
								'fa-youtube'	=> __( 'Youtube', 'responsive-brix' ),
							),
						),
						array(
							'name'		=> __( 'URL', 'responsive-brix' ),
							'id'		=> 'url',
							'type'		=> 'text',
							'sanitize'	=> 'url',
						),
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
		include( hoot_locate_widget( 'social-icons' ) ); // Loads the widget/social-icons or template-parts/widget-social-icons.php template.
	}

}

/**
 * Register Widget
 */
function hoot_social_icons_widget_register(){
	register_widget('Hoot_Social_Icons_Widget');
}
add_action('widgets_init', 'hoot_social_icons_widget_register');