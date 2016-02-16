<?php
/**
 * Identity Theme Customizer
 *
 * @package Identity
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function identity_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/**
	 * Add user selection to the customizer.
	 */
	class User_Dropdown_Custom_Control extends WP_Customize_Control {

		private $users = false;

		public function __construct($manager, $id, $args = array(), $options = array()) {
			$this->users = get_users( $options );
			parent::__construct( $manager, $id, $args );
		}

		/**
		 * Render the control's content.
		 *
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 *
		 * @since   01/13/2013
		 * @return  void
		 */
		public function render_content() {

			if(empty($this->users)) {
				return false;
			} ?>
			
			<label>
				<span class="customize-control-title" ><?php echo esc_html( $this->label ); ?></span>
				<select <?php $this->link(); ?>>
				<?php foreach( $this->users as $user ) {
					printf('<option value="%s" %s>%s</option>',
							$user->data->ID,
							selected($this->value(), $user->data->ID, false),
							$user->data->display_name); 
				} ?>
				</select>
			</label>
			<?php
		}
	} // end class

	/* Theme options panel */
	$wp_customize->add_panel( 'identity_theme_options', array(
		'priority'       => 130,
		'title'          => __( 'Theme Options', 'identity' ),
		'description'    => 'This theme supports a number of options which you can set using this panel.',
	) );

	/* Theme options header section */
	$wp_customize->add_section( 'identity_header_options', array(
		'title'    => __( 'Header Options', 'identity' ),
		'priority' => 10,
		'panel'  => 'identity_theme_options',
		'description'    => 'To customize the appearance of the header section choose any of the options below.',
	) );

	/* Theme options sidebar section */
	$wp_customize->add_section( 'identity_sidebar_options', array(
		'title'    => __( 'Sidebar Options', 'identity' ),
		'priority' => 20,
		'panel'  => 'identity_theme_options',
		'description'    => 'Select whether the sidebar should be displayed at the right or left side of the content.',
	) );

	/* Theme options footer section */
	$wp_customize->add_section( 'identity_footer_options', array(
		'title'    => __( 'Footer Options', 'identity' ),
		'priority' => 30,
		'panel'  => 'identity_theme_options',
		'description'    => 'Select the number of widgets to be displayed next to each other in the footer.',
	) );

	/* Show custom header image or show post thumbnail in the header. */
	$wp_customize->add_setting( 'identity_show_header_image', array(
		'default'           => 'header-image',
		'sanitize_callback' => 'identity_sanitize_show_header_image',
	) );
	$wp_customize->add_control( 'identity_show_header_image', array(
		'label'             => __( 'Header image: ', 'identity' ),
		'section'           => 'identity_header_options',
		'priority'          => 1,
		'type'              => 'radio',
		'choices'           => array(
			'header-image'	=> __( 'Custom header image', 'identity' ),
			'post-thumbnail'=> __( 'Post thumbnail', 'identity' ),
		),
	) );

	/* Show header content, frontpage only or do not show. */
	$wp_customize->add_setting( 'identity_show_header_content', array(
		'default'           => 'show',
		'sanitize_callback' => 'identity_sanitize_show_header_content',
	) );
	$wp_customize->add_control( 'identity_show_header_content', array(
		'label'             => __( 'Show header content: ', 'identity' ),
		'section'           => 'identity_header_options',
		'priority'          => 2,
		'type'              => 'radio',
		'choices'           => array(
			'show'			=> __( 'On all pages', 'identity' ),
			'fp-only'		=> __( 'Frontpage only', 'identity' ),
			'no-show'		=> __( 'Not at all', 'identity' ),
		),
	) );

	/* Header default, biography or page */
	$wp_customize->add_setting( 'identity_header_content', array(
		'default'           => 'default',
		'sanitize_callback' => 'identity_sanitize_header_content',
	) );
	$wp_customize->add_control( 'identity_header_content', array(
		'label'             => __( 'Header content: ', 'identity' ),
		'section'           => 'identity_header_options',
		'priority'          => 3,
		'type'              => 'radio',
		'choices'           => array(
			'default'	=> __( 'Default', 'identity' ),
			'biography'	=> __( 'Biography', 'identity' ),
			'page'		=> __( 'Page', 'identity' ),
		),
	) );

	/* Header user selection */
	$wp_customize->add_setting( 'identity_user_biography', array(
		'default'           => '',
		'sanitize_callback' => 'identity_sanitize_dropdown_user_biography',
	) );
	$wp_customize->add_control( new User_Dropdown_Custom_Control ( $wp_customize, 'identity_user_biography', array(
		'label'             => __( 'Biography selection:', 'identity' ),
		'section'           => 'identity_header_options',
		'priority'          => 4,
	) ) );

	/* Header page selection */
	$wp_customize->add_setting( 'identity_page_selection', array(
		'default'           => '',
		'sanitize_callback' => 'identity_sanitize_dropdown_pages',
	) );
	$wp_customize->add_control( 'identity_page_selection', array(
		'label'             => __( 'Page selection:', 'identity' ),
		'section'           => 'identity_header_options',
		'priority'          => 5,
		'type'              => 'dropdown-pages',
	) );

	/* Left sidebar, right sidebar or no sidebar */
	$wp_customize->add_setting( 'identity_sidebar', array(
		'default'           => 'right-sidebar',
		'sanitize_callback' => 'identity_sanitize_sidebar',
	) );
	$wp_customize->add_control( 'identity_sidebar', array(
		'label'             => __( 'Sidebar: ', 'identity' ),
		'section'           => 'identity_sidebar_options',
		'priority'          => 1,
		'type'              => 'radio',
		'choices'           => array(
			'right-sidebar'	=> __( 'Right sidebar', 'identity' ),
			'left-sidebar'	=> __( 'Left sidebar', 'identity' ),
			'no-sidebar'	=> __( 'No sidebar', 'identity' ),
		),
	) );

	/* Number of widgets that should be displayed next to each other. */
	$wp_customize->add_setting( 'identity_footer_widgets', array(
		'default'           => 'three-widgets',
		'sanitize_callback' => 'identity_sanitize_footer_widgets',
	) );
	$wp_customize->add_control( 'identity_footer_widgets', array(
		'label'             => __( 'Number of footer widgets: ', 'identity' ),
		'section'           => 'identity_footer_options',
		'priority'          => 1,
		'type'              => 'radio',
		'choices'           => array(
			'no-widgets'	=> __( 'No widgets', 'identity' ),
			'two-widgets'	=> __( 'Two widgets', 'identity' ),
			'three-widgets'	=> __( 'Three widgets', 'identity' ),
			'four-widgets'	=> __( 'Four widgets', 'identity' ),
		),
	) );

}
add_action( 'customize_register', 'identity_customize_register' );

/**
 * Sanitize custom header image or post thumbnail selection.
 *
 * @param string $input.
 * @return string (user|page).
 */
function identity_sanitize_show_header_image( $input ) {
	if ( ! in_array( $input, array( 'header-image', 'post-thumbnail' ) ) ) {
		$input = 'header-image';
	}
	return $input;
}

/**
 * Sanitize show header content selection.
 *
 * @param string $input.
 * @return string (user|page).
 */
function identity_sanitize_show_header_content( $input ) {
	if ( ! in_array( $input, array( 'show', 'fp-only', 'no-show' ) ) ) {
		$input = 'show';
	}
	return $input;
}

/**
 * Sanitize user or page selection.
 *
 * @param string $input.
 * @return string (user|page).
 */
function identity_sanitize_header_content( $input ) {
	if ( ! in_array( $input, array( 'default', 'biography', 'page' ) ) ) {
		$input = 'default';
	}
	return $input;
}

/**
 * Sanitize the dropdown users.
 *
 * @param interger $input.
 * @return interger.
 */
function identity_sanitize_dropdown_user_biography( $input ) {
	if ( is_numeric( $input ) ) {
		return intval( $input );
	}
}

/**
 * Sanitize the dropdown pages.
 *
 * @param interger $input.
 * @return interger.
 */
function identity_sanitize_dropdown_pages( $input ) {
	if ( is_numeric( $input ) ) {
		return intval( $input );
	}
}


/**
 * Sanitize sidebar selection.
 *
 * @param string $input.
 * @return string (user|page).
 */
function identity_sanitize_sidebar( $input ) {
	if ( ! in_array( $input, array( 'left-sidebar', 'right-sidebar', 'no-sidebar' ) ) ) {
		$input = 'right-sidebar';
	}
	return $input;
}

/**
 * Sanitize footer widget selection.
 *
 * @param string $input.
 * @return string (user|page).
 */
function identity_sanitize_footer_widgets( $input ) {
	if ( ! in_array( $input, array( 'no-widgets', 'two-widgets', 'three-widgets', 'four-widgets' ) ) ) {
		$input = 'three-widgets';
	}
	return $input;
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function identity_customize_preview_js() {
	wp_enqueue_script( 'identity_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'identity_customize_preview_js' );
