<?php
/**
 * Theme Customizer
 *
 * @package Oprum
 */

if ( class_exists( 'WP_Customize_Control' ) ) {
	class Oprum_Textarea_Control extends WP_Customize_Control {
	    public $type = 'textarea';
		public function render_content() {
		?>
	        <label>
	        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
	        <textarea rows="5" class="custom-textarea" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
	        </label>
	        <?php
		}
	}
}

function oprum_register_theme_customizer( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

    $wp_customize->add_setting(
        'oprum_headerbg_color',
        array(
            'default'     => '#f16272',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'oprum_link_color',
        array(
            'default'     => '#00a5e7',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'oprum_hover_color',
        array(
            'default'     => '#f16272',
	'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_setting(
        'oprum_menu_color',
        array(
            'default'     => '#2c2c2c',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'oprum_menu_current',
        array(
            'default'     => '#e8e8e8',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'oprum_addit_color',
        array(
            'default'     => '#333333',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'oprum_footer_color',
        array(
            'default'     => '#cccccc',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'oprum_footerbg_color',
        array(
            'default'     => '#2c2c2c',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );

 //CONTROL

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'headerbg_color',
            array(
                'label'      => __( 'Header BG Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 10,
                'settings'   => 'oprum_headerbg_color'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'link_color',
            array(
                'label'      => __( 'Link Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 11,
                'settings'   => 'oprum_link_color'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'hover_color',
            array(
                'label'      => __( 'Hover Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 20,
                'settings'   => 'oprum_hover_color'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_color',
            array(
                'label'      => __( 'Menu Bar Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 30,
                'settings'   => 'oprum_menu_color'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_current',
            array(
                'label'      => __( 'Menu Bar Current', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 40,
                'settings'   => 'oprum_menu_current'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'addit_color',
            array(
                'label'      => __( 'Additional Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 50,
                'settings'   => 'oprum_addit_color'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'footer_color',
            array(
                'label'      => __( 'Footer TXT Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 200,
                'settings'   => 'oprum_footer_color'
            )
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'footerbg_color',
            array(
                'label'      => __( 'Footer BG Color', 'oprum' ),
                'section'    => 'colors',
	'priority'  => 201,
                'settings'   => 'oprum_footerbg_color'
            )
        )
    );

	/*-----------------------------------------------------------
	 * Logo section
	 *-----------------------------------------------------------*/
	$wp_customize->add_section(
		'oprum_logo_options',
		array(
			'title'     => __( 'Logo Options', 'oprum' ),
			'priority'  => 200
		)
	);


	/* Logo Image Upload */
	$wp_customize->add_setting(
		'logo_upload',
		array(
		'sanitize_callback' => 'esc_url_raw'
		)
	);

//Logo Image CONTROL
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'logo_upload', array(
		'label' => __( 'Logo Image', 'oprum' ),
		'section' =>  'oprum_logo_options',
		'settings' => 'logo_upload'
	) ) );

	/*-----------------------------------------------------------
	 * Home Tagline section
	 *-----------------------------------------------------------*/
	$wp_customize->add_section(
		'oprum_home_tagline',
		array(
			'title'     => __( 'Home Tagline', 'oprum' ),
			'priority'  => 300
		)
	);
		$wp_customize->add_setting(
			'home_tagline',
			array(
			'default' => '',
			'sanitize_callback' => 'oprum_sanitize_textarea',
			'transport'   => 'postMessage'
			)
		);
    $wp_customize->add_setting(
        'home_tagline_bgcolor',
        array(
            'default'     => '#f16272',
	'sanitize_callback' => 'sanitize_hex_color',
            'transport'   => 'postMessage'
        )
    );
	$wp_customize->add_setting(
		'home_tagline_bgimg',
		array(
		'default'     => get_template_directory_uri().'/img/tagline.jpg',
		'sanitize_callback' => 'esc_url_raw'
		)
	);
		$wp_customize->add_setting(
			'sub_tagline',
			array(
			'default' => 'Multi-purpose theme for <a href="#">creativity and business</a>',
			'sanitize_callback' => 'oprum_sanitize_textarea',
			'transport'   => 'postMessage'
			)
		);

//Home TagLine CONTROL
		$wp_customize->add_control( new Oprum_Textarea_Control( $wp_customize, 'home_tagline', array(
			'label' => __( 'Tagline Text', 'oprum' ),
			'section' => 'oprum_home_tagline',
			'settings' => 'home_tagline',
			//'priority' => 27,
			'type' => 'text',
		) ) );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'home_tagline_bgcolor',
            array(
                'label'      => __( 'Background Color', 'oprum' ),
                'section'    => 'oprum_home_tagline',
                'settings'   => 'home_tagline_bgcolor'
            )
        )
    );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'home_tagline_bgimg', array(
		'label' => __( 'Background Image', 'oprum' ),
		'section' =>  'oprum_home_tagline',
		'settings' => 'home_tagline_bgimg'
	) ) );
		$wp_customize->add_control( new Oprum_Textarea_Control( $wp_customize, 'sub_tagline', array(
			'label' => __( 'SubTagline Txt', 'oprum' ),
			'section' => 'oprum_home_tagline',
			'settings' => 'sub_tagline',
			//'priority' => 27,
			'type' => 'text',
		) ) );
	/*-----------------------------------------------------------
	 * Call to Action section
	 *-----------------------------------------------------------*/
	$wp_customize->add_section(
		'oprum_call_action',
		array(
			'title'     => __( 'Call to Action', 'oprum' ),
			'priority'  => 400
		)
	);
	$wp_customize->add_setting(
		'callaction_txt',
		array(
			'default'            => 'Here the call to action!',
			'sanitize_callback'  => 'oprum_sanitize_txt',
			'transport'          => 'postMessage'
		)
	);
	$wp_customize->add_setting(
		'callaction_but_txt',
		array(
			'default'            => 'Button Text',
			'sanitize_callback'  => 'oprum_sanitize_txt',
			'transport'          => 'postMessage'
		)
	);
	$wp_customize->add_setting(
		'callaction_url',
		array(
		'default'            => '#',
		'sanitize_callback' => 'esc_url_raw'
		)
	);
//Call to Action CONTROL
	$wp_customize->add_control(
		'callaction_txt',
		array(
			'section'  => 'oprum_call_action',
			'label'    => __( 'Call Action Text', 'oprum' ),
			'type'     => 'text'
		)
	);
	$wp_customize->add_control(
		'callaction_but_txt',
		array(
			'section'  => 'oprum_call_action',
			'label'    => __( 'Button Text', 'oprum' ),
			'type'     => 'text'
		)
	);
	$wp_customize->add_control(
		'callaction_url',
		array(
			'section'  => 'oprum_call_action',
			'label'    => __( 'URL Button', 'oprum' ),
			'type'     => 'text'
		)
	);
	/*-----------------------------------------------------------
	 * Display Options section
	 *-----------------------------------------------------------*/
	$wp_customize->add_section(
		'oprum_display_options',
		array(
			'title'     => __( 'Display Options', 'oprum' ),
			'priority'  => 600
		)
	);
	$wp_customize->add_setting(
		'recentposts_title',
		array(
			'default'            => __( 'Recent Posts', 'oprum' ),
			'sanitize_callback'  => 'oprum_sanitize_txt',
		)
	);
//Title Recent Posts CONTROL
	$wp_customize->add_control(
		'recentposts_title',
		array(
			'section'  => 'oprum_display_options',
			'label'    => __( 'Title Recent Posts', 'oprum' ),
			'type'     => 'text'
		)
	);
	$wp_customize->add_setting(
		'number_homeposts',
		array(
			'default'            => '',
			'sanitize_callback'  => 'absint',
			//'type' => 'option',
		)
	);
//Display Options CONTROL
	$wp_customize->add_control(
		'number_homeposts',
		array(
			'section'  => 'oprum_display_options',
			'label'    => __( 'Number Posts on Home', 'oprum' ),
			'type' => 'select',
			'choices' => array(
				'0' => '0',
				'4' => '4',
				'8' => '8',
				'12' => '12',
			)
		)
	);

	/*-----------------------------------------------------------
	 * Copyright section
	 *-----------------------------------------------------------*/
	$wp_customize->add_section(
		'oprum_custom_copyright',
		array(
			'title'     => __( 'Footer Copyright', 'oprum' ),
			'priority'  => 700
		)
	);
	$wp_customize->add_setting(
		'copyright_txt',
		array(
			'default'            => 'All rights reserved',
			'sanitize_callback'  => 'oprum_sanitize_txt',
			'transport'          => 'postMessage'
		)
	);
//Copyright CONTROL
	$wp_customize->add_control(
		'copyright_txt',
		array(
			'section'  => 'oprum_custom_copyright',
			'label'    => __( 'Copyright', 'oprum' ),
			'type'     => 'text'
		)
	);


}
add_action( 'customize_register', 'oprum_register_theme_customizer' );

	/*-----------------------------------------------------------*
	 * Sanitize
	 *-----------------------------------------------------------*/
function oprum_sanitize_textarea( $input ) {
	return wp_kses_post(force_balance_tags($input));
}
function oprum_sanitize_txt( $input ) {
	return strip_tags( stripslashes( $input ) );
}

	/*-----------------------------------------------------------*
	 * Styles print
	 *-----------------------------------------------------------*/
function oprum_customizer_css() {
    ?>
    <style type="text/css">
button,
html input[type="button"],
input[type="reset"],
input[type="submit"] { background: <?php echo get_theme_mod( 'oprum_link_color', '#00a5e7' ); ?>; }
button:hover,
html input[type="button"]:hover,
input[type="reset"]:hover,
input[type="submit"]:hover { background: <?php echo get_theme_mod( 'oprum_hover_color', '#f16272' ); ?>; }
        .site-content a, #home-tagline h1, cite { color: <?php echo get_theme_mod( 'oprum_link_color', '#00a5e7' ); ?>; }
        #content a:hover, .site-content a:hover, .site-footer a:hover { color: <?php echo get_theme_mod( 'oprum_hover_color', '#f16272' ); ?>; }
        .main-navigation { background: <?php echo get_theme_mod( 'oprum_menu_color', '#2c2c2c' ); ?>; }
h1.page-title, .call-action-txt span { color: <?php echo get_theme_mod( 'oprum_menu_color', '#2c2c2c' ); ?>; }
#home-txt span, .page-header, .widget-title span { border-bottom-color: <?php echo get_theme_mod( 'oprum_addit_color', '#333333' ); ?>; }
.single footer.entry-meta, cite { border-top-color: <?php echo get_theme_mod( 'oprum_addit_color', '#333333' ); ?>; }
#home-widget .widget-title, h3.widget-title, h3#reply-title{ color: <?php echo get_theme_mod( 'oprum_addit_color', '#333333' ); ?>; }
#home-txt, .entry-header p { color: <?php echo get_theme_mod( 'oprum_addit_color', '#333333' ); ?>; }
.site-footer, .site-footer a { color: <?php echo get_theme_mod( 'oprum_footer_color', '#cccccc' ); ?>; }
.site-footer { background: <?php echo get_theme_mod( 'oprum_footerbg_color', '#2c2c2c' ); ?>; }
	.nav-menu li:hover,
	.nav-menu li.sfHover,
	.nav-menuu a:focus,
	.nav-menu a:hover, 
	.nav-menu a:active,
.main-navigation li ul li a:hover  { background: <?php echo get_theme_mod( 'oprum_hover_color', '#f16272' ); ?>; }
	.nav-menu .current_page_item a,
	.nav-menu .current-post-ancestor a,
	.nav-menu .current-menu-item a { background: <?php echo get_theme_mod( 'oprum_menu_current', '#e8e8e8' ); ?>; }
.site-branding { background: <?php echo get_theme_mod( 'oprum_headerbg_color', '#f16272' ); ?>; }
    </style>
    <?php
}
add_action( 'wp_head', 'oprum_customizer_css' );

	/*-----------------------------------------------------------*
	 * Live Preview Script
	 *-----------------------------------------------------------*/
function oprum_customizer_live_preview() {
    wp_enqueue_script(
        'oprum-customizer',
        get_template_directory_uri() . '/js/customizer.js',
        array( 'jquery', 'customize-preview' ),
        '311214',
        true
    );
}
add_action( 'customize_preview_init', 'oprum_customizer_live_preview' );