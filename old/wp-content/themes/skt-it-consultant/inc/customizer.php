<?php
/**
 * SKT IT Consultant Theme Customizer
 *
 * @package SKT IT Consultant
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function skt_itconsultant_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->remove_control('display_header_text');
	$wp_customize->remove_control('header_textcolor');
	
	class Itconsultant_info extends WP_Customize_Control{
		public $type = 'info';
		public $label	= '';
		public function render_content(){
				?>
                	<h3 style="text-decoration:underline; color:#DA4141; text-transform:uppercase;"><?php echo esc_html($this->label); ?></h3>
			<?php }
		}
		
	class WP_Customize_Textarea_Control extends WP_Customize_Control {
    public $type = 'textarea';
 
    public function render_content() {
        ?>
            <label>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
                <textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
            </label>
        <?php
    }
}
		
	$wp_customize->add_section(
        'logo_sec',
        array(
            'title' => __('Logo (PRO Version)', 'itconsultant'),
            'priority' => 1,
            'description' => sprintf( __( 'Logo Settings available in %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_PRO_THEME_URL.'"' ), __( 'PRO Version', 'itconsultant' ))),			
        )
    );
	
	$wp_customize->add_setting('itconsultant_option[logo-info]',array(
			'sanitize_callback'	=> 'sanitize_text_field',
			'type'				=> 'info_control',
			'capability'		=> 'edit_theme_options',
	));
	
	$wp_customize->add_control(
		new Itconsultant_info(
			$wp_customize,
			'itconsultant_option[logo-info]',
			array(
				  'setting'	=> 'itconsultant_option[logo-info]',
				  'section'	=> 'logo_sec',
				  'priority'	=> null
			)
		)
	);
	
	$wp_customize->add_setting('color_scheme',array(
			'default'	=> '#b40000',
			'sanitize_callback'	=> 'sanitize_hex_color'
	));	

	
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'color_scheme',array(
			'label' => __('Color Scheme','itconsultant'),			
			 'description' => sprintf( __( 'More color options in %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_PRO_THEME_URL.'"' ), __( 'PRO Version', 'itconsultant' ))),			
			'section' => 'colors',
			'settings' => 'color_scheme'
		))
	);
	
	
	
	$wp_customize->add_section(
        'slider_sec',
        array(
            'title' => __('Slider Settings', 'itconsultant'),
            'priority' => null,
            'description' => sprintf( __( 'Featured Image Size Should be ( 1400x648 ) More slider settings available in %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_PRO_THEME_URL.'"' ), __( 'PRO Version', 'itconsultant' ))),			
        )
    );
	
	
	
	$wp_customize->add_setting('slide1',array(
			'default'	=> get_template_directory_uri().'/images/slides/slide_01.jpg',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'slide1',
			array(
				'label'	=> __('Add slide image 1','itconsultant'),
				'setting'	=> 'slide1',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slidetitle1',array(
			'default'	=> __('Use information technology to meet your business objectives.','itconsultant'),
			'sanitize_callback'	=> 'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'slidetitle1',
			array(
				'label'	=>	__('Add slide caption 1 here','itconsultant'),
				'setting'	=>	'slidetitle1',
				'section'	=>	'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slideurl1',array(
			'default'	=> '#1',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('slideurl1',array(
			'label'	=> __('Add slide link 1 here.','itconsultant'),
			'setting'	=> 'slideurl1',
			'section'	=> 'slider_sec'
	));
	
	$wp_customize->add_setting('slide2',array(
			'default'	=> get_template_directory_uri().'/images/slides/slide_02.jpg',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'slide2',
			array(
				'label'	=> __('Add slide image 2','itconsultant'),
				'setting'	=>	'slide2',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slidetitle2',array(
			'default'	=> __('Use information technology to meet your business objectives','itconsultant'),
			'sanitize_callback'	=>	'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'slidetitle2',
			array(
				'label'	=>	__('Add slide caption 2 here','itconsultant'),
				'setting'	=>	'slidetitle2',
				'section'	=>	'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slideurl2',array(
			'default'	=>	'#2',
			'sanitize_callback'	=> 'esc_url_raw',
	));
	
	$wp_customize->add_control('slideurl2',array(
			'label'	=>	__('Add slide link 2 here','itconsultant'),
			'setting'	=> 'slideurl2',
			'section'	=> 'slider_sec'
	));
	
	$wp_customize->add_setting('slide3',array(
			'default'	=> get_template_directory_uri().'/images/slides/slide_03.jpg',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'slide3',
			array(
				'label'	=> __('Add slide image 3','itconsultant'),
				'setting'	=> 'slide3',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slidetitle3',array(
			'default'	=> __('Use information technology to meet your business objectives','itconsultant'),
			'sanitize_callback'	=> 'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'slidetitle3',
			array(
				'label'	=> __('Add slide caption 3 here','itconsultant'),
				'setting'	=> 'slidetitle3',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slideurl3',array(
			'default'	=> '#3',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('slideurl3',array(
			'label'	=>	__('Add slide link 3 here','itconsultant'),
			'setting'	=> 'slideurl3',
			'section'	=> 'slider_sec'
	));
	
	$wp_customize->add_setting('slide4',array(
			'default'	=>	get_template_directory_uri().'/images/slides/slide_04.jpg',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'slide4',
			array(
				'label'	=> __('Add slide image 4','itconsultant'),
				'setting'	=> 'slide4',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slidetitle4',array(
			'default'	=>	__('Use information technology to meet your business objectives','itconsultant'),
			'sanitize_callback'	=> 'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'slidetitle4',
			array(
				'label'	=> __('Add slide caption 4 here','itconsultant'),
				'setting'	=> 'slidetitle4',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slideurl4',array(
			'default'	=> '#4',
			'sanitize_callback'	=>	'esc_url_raw'
	));
	
	$wp_customize->add_control('slideurl4',array(
			'label'	=> __('Add slide link 4 here','itconsultant'),
			'setting'	=> 'slideurl4',
			'section'	=> 'slider_sec'
	));
	
	$wp_customize->add_setting('slide5',array(
			'default'	=> '',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'slide5',
			array(
				'label'	=> __('Add slide image 5','itconsultant'),
				'setting'	=> 'slide5',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slidetitle5',array(
			'default'	=> '',
			'sanitize_callback'	=> 'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'slidetitle5',
			array(
				'label'	=> __('Add slide caption 5 here','itconsultant'),
				'setting'	=> 'slidetitle5',
				'section'	=> 'slider_sec'
			)
		)
	);
	
	$wp_customize->add_setting('slideurl5',array(
			'default'	=> '',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('slideurl5',array(
			'label'	=> __('Add slide link 5','itconsultant'),
			'setting'	=> 'slideurl5',
			'section'	=> 'slider_sec'
	));
	
	$wp_customize->add_section('homepage_sec',array(
			'title'	=> __('Homepage Boxes','itconsultant'),
			'description'	=>	__('Select page for homepage boxes','itconsultant'),
			'priority'	=> null	
	));
	
	// Page settings 
	$wp_customize->add_section('page_boxes',array(
		'title'	=> __('Homepage Boxes','itconsultant'),
		'description'	=> __('Select Pages from the dropdown','itconsultant'),
		'priority'	=> null
	));
	
	$wp_customize->add_setting(
    'page-setting1',
		array(
			'sanitize_callback' => 'itconsultant_sanitize_integer',
		)
	);
 
	$wp_customize->add_control(
		'page-setting1',
		array(
			'type' => 'dropdown-pages',
			'label' => __('Choose a page for box one:','itconsultant'),
			'section' => 'page_boxes',
		)
	);
	
	$wp_customize->add_setting(
    'page-setting2',
		array(
			'sanitize_callback' => 'itconsultant_sanitize_integer',
		)
	);
	
	$wp_customize->add_control(
		'page-setting2',
		array(
			'type' => 'dropdown-pages',
			'label' => __('Choose a page for box Two:','itconsultant'),
			'section' => 'page_boxes',
		)
	);
	
	$wp_customize->add_setting(
    'page-setting3',
		array(
			'sanitize_callback' => 'itconsultant_sanitize_integer',
		)
	);
	
	$wp_customize->add_control(
		'page-setting3',
		array(
			'type' => 'dropdown-pages',
			'label' => __('Choose a page for box Three:','itconsultant'),
			'section' => 'page_boxes',
		)
	);
	
	$wp_customize->add_setting(
    'page-setting4',
		array(
			'sanitize_callback' => 'itconsultant_sanitize_integer',
		)
	);
	
	$wp_customize->add_control(
		'page-setting4',
		array(
			'type' => 'dropdown-pages',
			'label' => __('Choose a page for box Four:','itconsultant'),
			'section' => 'page_boxes',
		)
	);
	
	$wp_customize->add_section('footer_sec',array(
			'title'	=> __('Footer','itconsultant'),
			'description'	=> __('Customize footer content from here.','itconsultant'),
			'priority'	=> null
	));
	
	$wp_customize->add_setting('footcolonetitle',array(
			'default'	=> __('john doe','itconsultant'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('footcolonetitle',array(
			'label'	=> __('Add footer column one title','itconsultant'),
			'setting'	=> 'footcolonetitle',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('footcolonecontent',array(
			'default'	=> __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque augue eros, posuere a condimentum sit amet, rhoncus eu libero. Maecenas in tincidunt turpis, ut rhoncus neque. Nullam sit amet porta odio. Maecenas mattis nulla ac aliquet facilisis.','itconsultant'),
			'sanitize_callback'	=> 'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'footcolonecontent',
			array(
				'label'	=> __('Add footer column one content','itconsultant'),
				'setting'	=> 'footcolonecontent',
				'section'	=> 'footer_sec'
			)
		)
	);
	
	$wp_customize->add_setting('social_title',array(
			'default'	=> __('Connect with us','itconsultant'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('social_title',array(
			'label'	=> __('Social Icons','itconsultant'),
			'description'	=> __('Add social title here','itconsultant'),
			'setting'	=> 'social_title',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('fb_link',array(
			'default'	=> '#facebook',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('fb_link',array(
			'description'	=> __('Add facebook link here','itconsultant'),
			'setting'	=> 'fb_link',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('twitt_link',array(
			'default'	=> '#twitter',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('twitt_link',array(
			'description'	=> __('Add twitter link here','itconsultant'),
			'setting'	=> 'twitt_link',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('linked_link',array(
			'default'	=> '#linkedin',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('linked_link',array(
			'description'	=> __('Add Linkedin link here','itconsultant'),
			'setting'	=> 'itconsultant',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('gplus_link',array(
			'default'	=> '#gplus',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('gplus_link',array(
			'description'	=> __('Add google plus link here','itconsultant'),
			'setting'	=> 'gplus_link',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('contact_info',array(
			'default'	=> __('Contact info','itconsultant'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('contact_info',array(
			'label'	=> __('Contact details','itconsultant'),
			'description'	=> __('Add contact title here','itconsultant'),
			'setting'	=> 'contact_info',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('contact_add',array(
			'default'	=> __('Office Blvd No. 000-000, Farmville Town, LA 12345','itconsultant'),
			'sanitize_callback'	=> 'wp_htmledit_pre'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'contact_add',
			array(
				'label'	=> __('Add contact address here','itconsultant'),
				'setting'	=> 'contact_add',
				'section'	=> 'footer_sec'
			)
		)
	);
	
	$wp_customize->add_setting('contact_call',array(
			'default'	=>	'+62 500 800 123',
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('contact_call',array(
			'description'	=> __('Add contact number here','itconsultant'),
			'setting'	=> 'contact_call',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('contact_fax',array(
			'default'	=> '+62 500 800 112',
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('contact_fax',array(
			'description'	=> __('Add fax number here','itconsultant'),
			'setting'	=> 'contact_fax',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_setting('contact_mail',array(
			'default'	=> 'testmail@yourdomain.com',
			'sanitize_callback'	=> 'sanitize_email'
	));
	
	$wp_customize->add_control('contact_mail',array(
			'description'	=>	__('Add email address here','itconsultant'),
			'setting'	=> 'contact_mail',
			'section'	=> 'footer_sec' 
	));
	
	$wp_customize->add_setting('contact_web',array(
			'default'	=> 'www.yourdomain.com',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('contact_web',array(
			'description'	=> __('Add web url here','itconsultant'),
			'setting'	=> 'contact_web',
			'section'	=> 'footer_sec'
	));
	
	$wp_customize->add_section('copy_sec',array(
			'title'	=> __('Copyright','itconsultant'),
			'description'	=> __('Add copyright text here.','itconsultant'),
			'priority'	=> null
	));
	
	$wp_customize->add_setting('copy_text',array(
			'default'	=> __('IT Consultant 2015','itconsultant'),
			'sanitize_callback'	=> 'wp_kses_post'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Textarea_Control(
			$wp_customize,
			'copy_text',
			array(
				'setting'	=> 'copy_text',
				'section'	=> 'copy_sec'
			)
		)
	);
	
	$wp_customize->add_section('homepagecontent_sec',array(
			'title'	=>	__('Homepage Sections (PRO Version)','itconsultant'),			
			 'description' => sprintf( __( 'Homepage sections available in %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_PRO_THEME_URL.'"' ), __( 'PRO Version', 'itconsultant' ))),				
				
	));
	
	$wp_customize->add_setting('itconsultant[home-info]',array(
			'sanitize_callback'	=> 'sanitize_text_field',
			'type'	=> 'info_control',
			'capability'	=> 'edit_theme_options'
	));
	
	$wp_customize->add_control(
		new Itconsultant_info(
			$wp_customize,
			'itconsultant[home-info]',
			array(
				'setting'	=> 'itconsultant[home-info]',
				'section'	=> 'homepagecontent_sec',
				'priority'	=> null
			)
		)
	);
	
	$wp_customize->add_section('typography',array(
			'title'	=> __('Typography (PRO Version)','itconsultant'),			
			 'description' => sprintf( __( 'Typography option available in %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_PRO_THEME_URL.'"' ), __( 'PRO Version', 'itconsultant' ))),
			'priority'	=> null
	));
	
	$wp_customize->add_setting('typography[info]',array(
			'sanitize_callback'	=> 'sanitize_text_field',
			'type'	=> 'info_control',
			'capability'	=> 'edit_theme_options'
	));
	
	$wp_customize->add_control(
		new Itconsultant_info(
			$wp_customize,
			'typography[info]',
			array(
				'setting'	=> 'typography[info]',
				'section'	=> 'typography',
				'priority'	=> null
			)
		)
	);
	
	$wp_customize->add_section('layout_sec',array(
			'title'	=> __('Layout Settings (PRO Version)','itconsultant'),			
			'description' => sprintf( __( 'Layout settings available in %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_PRO_THEME_URL.'"' ), __( 'PRO Version', 'itconsultant' ))),			
			'priority'	=> null
	));
	
	$wp_customize->add_setting('layout[info]',array(
			'sanitize_callback'	=> 'sanitize_text_field',
			'type'	=> 'info_control',
			'capability'	=> 'edit_theme_options'
	));
	
	$wp_customize->add_control(
		new Itconsultant_info(
			$wp_customize,
			'layout[info]',
			array(
				'setting'	=> 'layout[info]',
				'section'	=> 'layout_sec',
				'priority'	=> null
			)
		)
	);
	
	$wp_customize->add_section(
        'theme_doc_sec',
        array(
            'title' => __('Documentation &amp; Support', 'itconsultant'),
            'priority' => null,		
			 'description' => sprintf( __( 'For documentation and support check this link %s.', 'itconsultant' ), sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( '"'.SKT_THEME_DOC.'"' ), __( 'IT Consultant Documentation', 'itconsultant' ))),
			
        )  );  
		
    $wp_customize->add_setting('itconsultant_options[info]', array(
			'sanitize_callback' => 'sanitize_text_field',
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
        )
    );
    $wp_customize->add_control( new Itconsultant_info( $wp_customize, 'doc_section', array(
        'section' => 'theme_doc_sec',
        'settings' => 'itconsultant_options[info]',
        'priority' => 10
        ) )
    );
	
}
add_action( 'customize_register', 'skt_itconsultant_customize_register' );

//Integer
function itconsultant_sanitize_integer( $input ) {
    if( is_numeric( $input ) ) {
        return intval( $input );
    }
}

function itconsultant_custom_css(){
	?>
    	<style type="text/css">
			a,
			h4.phone span,
			.welcome_text .one_fourth .read,
			.postmeta a:hover,
			#footer a:hover, 
			#copyright a:hover,
			.sidebar-area ul li a:hover,
			#copyright ul li:hover a, 
			#copyright ul li.current_page_item a{
				color:<?php echo get_theme_mod('color_scheme','#b40000'); ?>
			}
			nav ul li a:hover, 
			nav ul li.current_page_item a, 
			nav ul li.current_page_parent a.parent, 
			nav ul li:hover ul li:hover, 
			nav ul li:hover ul,
			.theme-default .nivo-controlNav a.active, 
			#commentform input#submit:hover,
			.pagination ul li span.current, 
			.pagination ul li:hover a,
			.social-icons .icon:hover,
			.welcome_text .one_fourth:hover .thumbox, 
			.welcome_text .one_fourth .thumbox{
				background-color:<?php echo get_theme_mod('color_scheme','#b40000'); ?>
			}
		</style>
	<?php }
add_action('wp_head','itconsultant_custom_css');

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function skt_itconsultant_customize_preview_js() {
	wp_enqueue_script( 'skt_itconsultant_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'skt_itconsultant_customize_preview_js' );

function skt_itconsultant_custom_customize_enqueue() {
	wp_enqueue_script( 'skt-itconsultant-custom-customize', get_template_directory_uri() . '/js/custom.customize.js', array( 'jquery', 'customize-controls' ), false, true );
}
add_action( 'customize_controls_enqueue_scripts', 'skt_itconsultant_custom_customize_enqueue' );