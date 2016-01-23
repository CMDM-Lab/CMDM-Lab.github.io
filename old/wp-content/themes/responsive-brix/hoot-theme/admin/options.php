<?php
/**
 * Theme Options displayed in admin
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

/**
 * Filter the default typography option values for the theme
 * For a list of available font faces, see "/hoot/options/includes/fonts.php"
 *
 * @since 1.0
 * @param array $typography array of 'size', 'face', 'style' and 'color'
 * @return  array
 */
function hootoptions_default_typography( $typography ) {
	$theme_typography = array(
		'size' => '14px',
		'face' => '"Open Sans", sans-serif',
		'style' => 'normal',
		'color' => '#666666' );
	return wp_parse_args( $theme_typography, $typography );
}
add_filter( 'hoot_of_default_typography', 'hootoptions_default_typography' );

/**
 * Filter the widget areas added for 'Widgetized Template'
 *
 * @since 1.0
 * @param array $defaults
 * @return  array
 */
function hootoptions_widgetized_template_widget_areas( $default ) {
	return $default;
}
add_filter( 'hoot_widgetized_template_widget_areas', 'hootoptions_widgetized_template_widget_areas' );

/**
 * Filter the admin panel intro buttons
 *
 * @since 1.0
 * @param array $buttons
 * @return array
 */
function hootoptions_intro_buttons( $buttons ) {
	$buttons = array(
		'demo'    => array( 'text'   => __( 'Demo', 'responsive-brix'),
							'button' => 'secondary',
							'url'    => 'http://demo.wphoot.com/responsive-brix/',
							'icon'   => 'eye' ),
		'docs'    => array( 'text'   => __( 'Documentation', 'responsive-brix'),
							'button' => 'secondary',
							'url'    => 'http://wphoot.com/docs/responsive-brix/',
							'icon'   => 'book' ),
		'support' => array( 'text'   => __( 'Support Forums', 'responsive-brix'),
							'button' => 'secondary',
							'url'    => 'http://wphoot.com/support/',
							'icon'   => 'support' ),
		'rate' =>    array( 'text'   => __( '5<i class="fa fa-star"></i> Rate the Theme', 'responsive-brix'),
							'button' => 'secondary',
							'url'    => 'https://wordpress.org/support/view/theme-reviews/responsive-brix#postform',
							'icon'   => 'none' ),
		);
	$buttons['premium'] = array('text'   => __( 'Go Premium', 'responsive-brix'),
								'button' => 'primary',
								'url'    => 'http://wphoot.com/themes/responsive-brix/',
								'icon'   => 'cubes' );
	return $buttons;
}
add_filter( 'hootoptions_intro_buttons', 'hootoptions_intro_buttons' );

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 * Child themes can modify the options array using the 'hoot_theme_options' filter hook.
 *
 * @since 1.0
 */
function hootoptions_options() {

	// define a directory path for using image radio buttons
	$imagepath =  trailingslashit( HOOT_THEMEURI ) . 'admin/images/';

	$options = array();

	$options[] = array(
		'name' => __('General', 'responsive-brix'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Site Options', 'responsive-brix'),
		'type' => 'subheading');

	$options[] = array(
		'name' => __('Logo Area Background', 'responsive-brix'),
		'id' => 'logo_background_type',
		'std' => 'accent',
		'type' => 'radio',
		'options' => array(
			'transparent' => __('None', 'responsive-brix'),
			'accent' => __('Accent Color', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Site Logo', 'responsive-brix'),
		'desc' => sprintf( __('Use %sSite Title%s as default text logo', 'responsive-brix'), '<a href="' . admin_url('/') . 'options-general.php" target="_blank">', '</a>' ),
		'id' => 'logo',
		'class' => 'logo_selector',
		'std' => 'text',
		'type' => 'radio',
		'options' => array(
			'text' => __('Default Text', 'responsive-brix'),
			'custom' => __('Custom Text', 'responsive-brix'),
			'image' => __('Image Logo', 'responsive-brix'),
			'mixed' => __('Image &amp; Default Text', 'responsive-brix'),
			'mixedcustom' => __('Image &amp; Custom Text', 'responsive-brix'), ) );

		$options[] = array(
			'std' => '<div class="show-on-select" data-selector="logo_selector">',
			'type' => 'html');

		$logosizes = array();
		$logosizerange = range( 14, 110 );
		foreach ( $logosizerange as $isr )
			$logosizes[ $isr . 'px' ] = $isr . 'px';
		$logofont = array(	'standard' => __('Standard Body Font (Open Sans)', 'responsive-brix'),
							'heading' => __('Heading Font', 'responsive-brix'), ) ;

		$options[] = array(
			'name' => __('Site Title Icon (Optional)', 'responsive-brix'),
			'desc' => __('Leave empty to hide icon.', 'responsive-brix'),
			'id' => 'site_title_icon',
			'class' => 'show-on-select-block logo_selector-text logo_selector-custom hide',
			'type' => 'icon');

		$options[] = array(
			'name' => __('Site Title Icon Size', 'responsive-brix'),
			'id' => 'site_title_icon_size',
			'std' => '50px',
			'class' => 'show-on-select-block logo_selector-text logo_selector-custom hide',
			'type' => 'select',
			'options' => $logosizes );

		$options[] = array(
			'name' => __('Upload Logo', 'responsive-brix'),
			'id' => 'logo_image',
			'class' => 'show-on-select-block logo_selector-image logo_selector-mixed logo_selector-mixedcustom hide',
			'type' => 'upload');

		$options[] = array(
			'name' => __('Logo Width', 'responsive-brix'),
			'desc' => __('(in pixels)', 'responsive-brix'),
			'id' => 'logo_image_width',
			'std' => '200',
			'class' => 'show-on-select-block logo_selector-mixed logo_selector-mixedcustom hide',
			'type' => 'text', );

		$options[] = array(
			'name' => __('Custom Logo Text', 'responsive-brix'),
			'id' => 'logo_custom',
			'class' => 'show-on-select-block logo_selector-custom logo_selector-mixedcustom hide',
			'std' => array( 'g0' => array(), 'g1' => array(), 'g2' => array() ),
			'type' => 'group',
			'settings' => array(
				'toggleview' => false,
				),
			'fields' => array(
				array(
					'name' => __('Line Text', 'responsive-brix'),
					'id' => 'text',
					'type' => 'text'),
				array(
					'name' => __('Line Size', 'responsive-brix'),
					'id' => 'size',
					'std' => '20px',
					'type' => 'select',
					'options' => $logosizes ),
				array(
					'name' => __('Line Font', 'responsive-brix'),
					'desc' => '',
					'id' => 'font',
					'std' => 'heading',
					'type' => 'select',
					'options' => $logofont ),
				), );

		$options[] = array(
			'name' => __('Show Tagline', 'responsive-brix'),
			'desc' => __('Display site description as tagline below logo.', 'responsive-brix'),
			'id' => 'show_tagline',
			'class' => 'show-on-select-block logo_selector-text logo_selector-custom logo_selector-mixed logo_selector-mixedcustom hide',
			'std' => '1',
			'type' => 'checkbox');

		$options[] = array(
			'std' => '</div>',
			'type' => 'html');

	$options[] = array(
		'name' => __('Custom Favicon', 'responsive-brix'),
		'desc' => __('Recommnended dimensions are 32x32 pixels.', 'responsive-brix'),
		'id' => 'favicon',
		'type' => 'upload');

	$options[] = array(
		'name' => __('Site Info Text (footer)', 'responsive-brix'),
		'desc' => sprintf( __('Text shown in footer. Useful for showing copyright info etc.<br />Use the <code>&lt;!--default--&gt;</code> tag to show the default Info Text.<br />Use the <code>&lt;!--year--&gt;</code> tag to insert the current year.<br />Always use %sHTML codes%s for symbols. For example, the HTML for &copy; is <code>&amp;copy;</code>', 'responsive-brix'), '<a href="http://ascii.cl/htmlcodes.htm" target="_blank">', '</a>' ),
		'id' => 'site_info',
		'std' => '<!--default-->',
		'type' => 'textarea',
		'settings' => array( 'rows' => 3 ) );

	$options[] = array(
		'name' => __('Sitewide Styling', 'responsive-brix'),
		'type' => 'subheading');

	$options[] = array(
		'name' => __('Headings Font (Free Version)', 'responsive-brix'),
		'desc' => __('The premium version of this theme offers complete typography control with over 600 Google Fonts', 'responsive-brix'),
		'id' => 'headings_fontface',
		'std' => 'standard',
		'type' => 'select',
		'options' => array(
			'standard' => __( 'Standard Font (Open Sans)', 'responsive-brix'),
			'cursive' => __( 'Cursive Font', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Accent Color', 'responsive-brix'),
		'id' => 'accent_color',
		'std' => '#f3595b',
		'type' => 'color' );

	$options[] = array(
		'name' => __('Font Color on Accent Color', 'responsive-brix'),
		'id' => 'accent_font',
		'std' => '#ffffff',
		'type' => 'color' );

	if ( current_theme_supports( 'woocommerce' ) ) :
	$options[] = array(
		'name' => __('Woocommerce Styling', 'responsive-brix'),
		'desc' => sprintf( __('Looks like you are using Woocommerce. Install %sthis plugin%s to change colors and styles for WooCommerce elements like buttons etc.', 'responsive-brix'), '<a href="https://wordpress.org/plugins/woocommerce-colors/" target="_blank">', '</a>' ),
		'type' => 'info');
	endif;

	$options[] = array(
		'name' =>  __('Site Background', 'responsive-brix'),
		'id' => 'background',
		'std' => array(
			'type' => 'custom',
			'color' => '#ffffff', ),
		'type' => 'background', );

	$options[] = array(
		'name' =>  __('Content Box Background', 'responsive-brix'),
		'desc' => __("This background will be used only if <strong>'Boxed'</strong> option is selected in the <strong>'Site Layout'</strong> option in the Layout tab above.", 'responsive-brix'),
		'id' => 'box_background_color',
		'std' => '#ffffff',
		'type' => 'color', );

	$options[] = array(
		'name' => __('Layout', 'responsive-brix'),
		'type' => 'heading');

	$options[] = array(
		'name' => __("Site Layout - Boxed vs Stretched", 'responsive-brix'),
		'desc' => __("For boxed layouts, both backgrounds (site and box) can be set in the 'General > Styling' tab above.<br /><br />For Stretched Layout, only site background is available.", 'responsive-brix'),
		'id' => "site_layout",
		'std' => "stretch",
		'type' => 'radio',
		'options' => array(
			'boxed' => __('Boxed layout', 'responsive-brix'),
			'stretch' => __('Stretched layout (full width)', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Page Header - Full Width', 'responsive-brix'),
		'desc' => __('Page header location (Page Title with meta info like author, categories etc.)', 'responsive-brix') . '<br /><img src="' . $imagepath . 'page-header.png">',
		'id' => 'page_header_full',
		'std' => array(
			'default' => '1',
			'pages' => '1',
			'no-sidebar' => '1', ),
		'type' => 'multicheck',
		'options' => array(
			'default' => __('Default (Archives, Blog etc.)', 'responsive-brix'),
			'posts' => __('For All Posts', 'responsive-brix'),
			'pages' => __('For All Pages', 'responsive-brix'),
			'no-sidebar' => __('Always override for full width pages (with no sidebar)', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __("Max. Site Width (pixels)", 'responsive-brix'),
		'id' => "site_width",
		'std' => "1260",
		'type' => 'radio',
		'options' => array(
			'1260' => __('1260px (wide)', 'responsive-brix'),
			'1080' => __('1080px (standard)', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __("Sidebar Layout (Site-wide)", 'responsive-brix'),
		'desc' => __("Set the default sidebar width and position for your entire site.<br />* Wide Right Sidebar<br />* Narrow Right Sidebar<br />* No Sidebar", 'responsive-brix'),
		'id' => "sidebar",
		'std' => "wide-right",
		'type' => "images",
		'options' => array(
			'wide-right' => $imagepath . 'sidebar-wide-right.png',
			'narrow-right' => $imagepath . 'sidebar-narrow-right.png',
			'none' => $imagepath . 'sidebar-none.png', )
	);

	$options[] = array(
		'name' => __("Sidebar Layout (for Pages)", 'responsive-brix'),
		'desc' => __("Set the default sidebar width and position for pages.<br />* Wide Right Sidebar<br />* Narrow Right Sidebar<br />* No Sidebar", 'responsive-brix'),
		'id' => "sidebar_pages",
		'std' => "wide-right",
		'type' => "images",
		'options' => array(
			'wide-right' => $imagepath . 'sidebar-wide-right.png',
			'narrow-right' => $imagepath . 'sidebar-narrow-right.png',
			'none' => $imagepath . 'sidebar-none.png', )
	);

	$options[] = array(
		'name' => __("Sidebar Layout (for single Posts)", 'responsive-brix'),
		'desc' => __("Set the default sidebar width and position for single posts.<br />* Wide Right Sidebar<br />* Narrow Right Sidebar<br />* No Sidebar", 'responsive-brix'),
		'id' => "sidebar_posts",
		'std' => "wide-right",
		'type' => "images",
		'options' => array(
			'wide-right' => $imagepath . 'sidebar-wide-right.png',
			'narrow-right' => $imagepath . 'sidebar-narrow-right.png',
			'none' => $imagepath . 'sidebar-none.png', )
	);

	if ( current_theme_supports( 'woocommerce' ) ) :
	$options[] = array(
		'name' => __("Sidebar Layout (Woocommerce Shop/Archives)", 'responsive-brix'),
		'desc' => __("Set the default sidebar width and position for WooCommerce shop and archives pages like product categories etc.<br />* Wide Right Sidebar<br />* Narrow Right Sidebar<br />* No Sidebar", 'responsive-brix'),
		'id' => "sidebar_wooshop",
		'std' => "wide-right",
		'type' => "images",
		'options' => array(
			'wide-right' => $imagepath . 'sidebar-wide-right.png',
			'narrow-right' => $imagepath . 'sidebar-narrow-right.png',
			'none' => $imagepath . 'sidebar-none.png', )
	);
	$options[] = array(
		'name' => __("Sidebar Layout (Woocommerce Single Product Page)", 'responsive-brix'),
		'desc' => __("Set the default sidebar width and position for WooCommerce product pages<br />* Wide Right Sidebar<br />* Narrow Right Sidebar<br />* No Sidebar", 'responsive-brix'),
		'id' => "sidebar_wooproduct",
		'std' => "wide-right",
		'type' => "images",
		'options' => array(
			'wide-right' => $imagepath . 'sidebar-wide-right.png',
			'narrow-right' => $imagepath . 'sidebar-narrow-right.png',
			'none' => $imagepath . 'sidebar-none.png', )
	);
	endif;

	$options[] = array(
		'name' => __("Footer Layout", 'responsive-brix'),
		'desc' => sprintf( __('Once you save the settings here, you can add content to footer columns using the %sWidgets Management screen%s.', 'responsive-brix'), '<a href="' . admin_url('/') . 'widgets.php" target="_blank">', '</a>' ),
		'id' => "footer",
		'std' => "3-1",
		'type' => "images",
		'options' => array(
			'1-1' => $imagepath . '1-1.png',
			'2-1' => $imagepath . '2-1.png',
			'2-2' => $imagepath . '2-2.png',
			'2-3' => $imagepath . '2-3.png',
			'3-1' => $imagepath . '3-1.png',
			'3-2' => $imagepath . '3-2.png',
			'3-3' => $imagepath . '3-3.png',
			'3-4' => $imagepath . '3-4.png',
			'4-1' => $imagepath . '4-1.png', )
	);

	$options[] = array(
		'name' => __('Content', 'responsive-brix'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Topbar', 'responsive-brix'),
		'type' => 'subheading');

	$options[] = array(
		'name' => __('Left/Right Topbar', 'responsive-brix'),
		'desc' => sprintf( __('You can add content to Left/Right Topbar using Text Widget in the %sWidget Management Screen%s', 'responsive-brix'), '<a href="' . admin_url('/') . 'widgets.php" target="_blank">', '</a>' ),
		'type' => 'info');

	$options[] = array(
		'name' => __('Hide Search Box', 'responsive-brix'),
		'desc' => __('Check this to hide Search box in Topbar Right Column', 'responsive-brix'),
		'id' => 'topbar_hide_search',
		'type' => 'checkbox');

	$options[] = array(
		'name' => __('Archives (Blog, Cats, Tags)', 'responsive-brix'),
		'type' => 'subheading');

	$options[] = array(
		'name' => __('Post Items Content', 'responsive-brix'),
		'desc' => __('Content to display for each post in the list', 'responsive-brix'),
		'id' => 'archive_post_content',
		'std' => 'excerpt',
		'type' => 'radio',
		'options' => array(
			'excerpt' => __('Post Excerpt', 'responsive-brix'),
			'full-content' => __('Full Post Content', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Meta Information for Post List Items', 'responsive-brix'),
		'desc' => __('Check which meta information to display for each post item in the archive list.', 'responsive-brix'),
		'id' => 'archive_post_meta',
		'std' => array(
			'author' => '1',
			'date' => '1',
			'cats' => '1',
			'comments' => '1', ),
		'type' => 'multicheck',
		'options' => array(
			'author' => __('Author', 'responsive-brix'),
			'date' => __('Date', 'responsive-brix'),
			'cats' => __('Categories', 'responsive-brix'),
			'tags' => __('Tags', 'responsive-brix'),
			'comments' => __('No. of comments', 'responsive-brix') ) );

	$options[] = array(
		'name' => __("Excerpt Length", 'responsive-brix'),
		'desc' => __("Number of words in excerpt. Default is 105. Leave empty if you dont want to change it.", 'responsive-brix'),
		'id' => 'excerpt_length',
		'type' => 'text');

	$options[] = array(
		'name' => __("'Read More' Text", 'responsive-brix'),
		'desc' => __("Replace the default 'Read More' text. Leave empty if you dont want to change it.", 'responsive-brix'),
		'id' => 'read_more',
		'type' => 'text');

	$options[] = array(
		'name' => __('Single (Posts, Pages)', 'responsive-brix'),
		'type' => 'subheading');

	$options[] = array(
		'name' => __('Display Featured Image', 'responsive-brix'),
		'desc' => __('Display featured image at the beginning of post/page content.', 'responsive-brix'),
		'id' => 'post_featured_image',
		'std' => '1',
		'type' => 'checkbox');

	$options[] = array(
		'name' => __('Meta Information on Posts', 'responsive-brix'),
		'desc' => __("Check which meta information to display on an individual 'Post' page", 'responsive-brix'),
		'id' => 'post_meta',
		'std' => array(
			'author' => '1',
			'date' => '1',
			'cats' => '1',
			'tags' => '1',
			'comments' => '1', ),
		'type' => 'multicheck',
		'options' => array(
			'author' => __('Author', 'responsive-brix'),
			'date' => __('Date', 'responsive-brix'),
			'cats' => __('Categories', 'responsive-brix'),
			'tags' => __('Tags', 'responsive-brix'),
			'comments' => __('No. of comments', 'responsive-brix') ) );

	$options[] = array(
		'name' => __('Meta Information on Page', 'responsive-brix'),
		'desc' => __("Check which meta information to display on an individual 'Page' page", 'responsive-brix'),
		'id' => 'page_meta',
		'std' => array(
			'author' => '1',
			'date' => '1',
			'comments' => '1', ),
		'type' => 'multicheck',
		'options' => array(
			'author' => __('Author', 'responsive-brix'),
			'date' => __('Date', 'responsive-brix'),
			'comments' => __('No. of comments', 'responsive-brix') ) );

	$options[] = array(
		'name' => __('Meta Information location', 'responsive-brix'),
		'id' => 'post_meta_location',
		'std' => 'top',
		'type' => 'radio',
		'options' => array(
			'top' => __('Top (below title)', 'responsive-brix'),
			'bottom' => __('Bottom (after content)', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Previous/Next Posts', 'responsive-brix'),
		'desc' => __('Display links to Prev/Next Post links at the end of content.', 'responsive-brix'),
		'id' => 'post_prev_next_links',
		'std' => '1',
		'type' => 'checkbox');

	$options[] = array(
		'name' => __('Contact Form', 'responsive-brix'),
		'desc' => sprintf( __('This theme comes bundled with CSS required to style %sContact-Form-7%s forms. Simply install and activate the plugin to add Contact Forms to your pages.', 'responsive-brix'), '<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">', '</a>'),
		'type' => 'info' );

	if ( current_theme_supports( 'woocommerce' ) ) :
	$options[] = array(
		'name' => __('WooCommerce', 'responsive-brix'),
		'type' => 'subheading');
	$wooproducts = range( 0, 99 );
	for ( $wpr=0; $wpr < 4; $wpr++ )
		unset( $wooproducts[$wpr] );
	$options[] = array(
		'name' => __("Total Products per page", 'responsive-brix'),
		'desc' => __("Total number of products to show on the Shop page", 'responsive-brix'),
		'id' => "wooshop_products",
		'std' => "12",
		'type' => "select",
		'options' => $wooproducts
	);
	$options[] = array(
		'name' => __("Product Columns", 'responsive-brix'),
		'desc' => __("Number of products to show in 1 row on the Shop page", 'responsive-brix'),
		'id' => "wooshop_product_columns",
		'std' => "3",
		'type' => "select",
		'options' => array(
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5' )
	);
	endif;

	if ( current_theme_supports( 'hoot-widgetized-template' ) ) :

	$options[] = array(
		'name' => __('Templates', 'responsive-brix'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Widgetized Template', 'responsive-brix'),
		'type' => 'subheading');

	$options[] = array(
		'name' => __('How to use this template', 'responsive-brix'),
		'desc' => sprintf( __( "<p>'Widgetized Template' is a special Page Template which is often used to create a Customized Front Page.</p><ol><li>Create a %sNew Page%s. In the <strong>'Page Attributes'</strong> option box select the <strong>'Widgetized Template'</strong> in the <strong>'Template'</strong> dropdown.</li><li>Goto %sSetting > Reading%s menu. In the <strong>'Front page displays'</strong> option, select <strong>'A static page'</strong> and select the page you created in previous step.</li></ol>", 'responsive-brix'), '<a href="' . admin_url('/') . 'post-new.php?post_type=page" target="_blank">', '</a>', '<a href="' . admin_url('/') . 'options-reading.php" target="_blank">', '</a>'),
		'type' => 'info' );

	$slider1_label = __('HTML Slider', 'responsive-brix');
	$slider2_label = __('Image Slider', 'responsive-brix');

	$options[] = array(
		'name' => __('Widget Areas Order', 'responsive-brix'),
		'desc' => sprintf( __("Sort different sections of the 'Widgetized Template' in the order you want them to appear.<br />You can disable areas by clicking the 'eye' icon.<br /><br />You can add content to widget areas from the %sWidgets Management screen%s.<br /><br />'Page Content' is the actual content of the page on which this template is applied. This can be used in special situations for creating extra customizable content.", 'responsive-brix'), '<a href="' . admin_url('/') . 'widgets.php" target="_blank">', '</a>' ),
		'id' => 'widgetized_template_sections',
		'std' => array(
			'slider_html' => '1,1',
			'slider_img'  => '2,1',
			'area_a'      => '3,1',
			'area_b'      => '4,1',
			'area_c'      => '5,1',
			'area_d'      => '6,1',
			'content'     => '7,0', ),
		'type' => 'sortlist',
		'settings' => array(
			'hideable' => true,
			),
		'options' => array(
			'slider_html' => $slider1_label,
			'slider_img'  => $slider2_label,
			'area_a'      => __('Widget Area A', 'responsive-brix'),
			'area_b'      => __('Widget Area B', 'responsive-brix'),
			'area_c'      => __('Widget Area C', 'responsive-brix'),
			'area_d'      => __('Widget Area D-left / D-right', 'responsive-brix'),
			'content'     => __('Page Content', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Widget Area D Widths', 'responsive-brix'),
		'desc' => __('Widths for D-Left and D-Right Areas', 'responsive-brix'),
		'id' => 'widgetized_template_area_d_width',
		'std' => '50-50',
		'type' => 'select',
		'options' => array(
			'50-50' => __('50 50', 'responsive-brix'),
			'33-66' => __('33 66', 'responsive-brix'),
			'66-33' => __('66 33', 'responsive-brix'),
			'25-75' => __('25 75', 'responsive-brix'),
			'75-25' => __('75 25', 'responsive-brix'), ) );

	$options[] = array(
		'name' => __('Highlight Widget Areas (Highlgihted Background)', 'responsive-brix'),
		'desc' => __('Check which areas from the above template you want to be highlgihted.', 'responsive-brix'),
		'id' => 'widgetized_highlight_template_area',
		'type' => 'multicheck',
		'options' => array(
			'area_a'      => __('Widget Area A', 'responsive-brix'),
			'area_b'      => __('Widget Area B', 'responsive-brix'),
			'area_c'      => __('Widget Area C', 'responsive-brix'),
			'area_d'      => __('Widget Area D', 'responsive-brix'),
			'content'     => __('Page Content Area', 'responsive-brix'), ) );

	$options[] = array(
		'name' => $slider1_label,
		'type' => 'subheading');

	$options[] = array(
		'name' => __("Slider Width (in Stretched Site Layout)", 'responsive-brix'),
		'desc' => __("Enable this to stretch the slider to Full Screen Width.<br />Note: This option is useful only if the <strong>'Site Layout'</strong> is set to <strong>'Stretched (full width)'</strong> in the 'Layout' tab above.", 'responsive-brix'),
		'id' => "wt_html_slider_width",
		'std' => "boxed",
		'type' => "images",
		'options' => array(
			'boxed' => $imagepath . 'slider-width-boxed.png',
			'stretch' => $imagepath . 'slider-width-stretch.png', )
	);

	$options[] = array(
		'name' => __("Minimum Slider Height (in pixels)", 'responsive-brix'),
		'desc' => __("<strong>(in pixels)</strong> Leave empty to let the slider height adjust automatically.", 'responsive-brix'),
		'id' => "wt_html_slider_min_height",
		'std' => '375',
		//'class' => 'mini',
		'type' => "text",
	);

	$options[] = array(
		'name' => __('Slides', 'responsive-brix'),
		'desc' => __('Premium version of this theme comes with capability to create <strong>Unlimited slides</strong>.', 'responsive-brix'),
		'id' => 'wt_html_slider',
		'std' => array( 'g0' => array(), 'g1' => array(), 'g2' => array(), 'g3' => array() ),
		'type' => 'group',
		'settings' => array(
			'title' => __( 'Slide', 'responsive-brix' ),
			'add_button' => __( 'Add New Slide', 'responsive-brix' ),
			'remove_button' => __( 'Remove Slide', 'responsive-brix' ),
			),
		'fields' => array(
			array(
				'name' => __('To hide this slide, simply leave the Image and Content empty.', 'responsive-brix'),
				'type' => 'info'),
			array(
				'name' => __('Slide Featured Image (Right Column)', 'responsive-brix'),
				'desc' => __('The main showcase image.', 'responsive-brix'),
				'id' => 'image',
				'type' => 'upload'),
			array(
				'name' => __('Content', 'responsive-brix'),
				'desc' => __('You can use the <code>&lt;h3&gt;Lorem Ipsum Dolor&lt;/h3&gt;</code> tag to create styled heading.', 'responsive-brix'),
				'id' => 'content',
				'std' => '<h3>Lorem Ipsum Dolor</h3>'."\n".'<p>This is a sample description text for the slide.</p>',
				'type' => 'textarea',
				'settings' => array( 'rows' => 4 ) ),
			array(
				'name' => __('Button Text', 'responsive-brix'),
				'id' => 'button',
				'type' => 'text'),
			array(
				'name' => __('Button URL', 'responsive-brix'),
				'desc' => __('Leave empty if you do not want to show the button.', 'responsive-brix'),
				'id' => 'url',
				'type' => 'text'),
			array(
				'name' =>  __('Slide Background', 'responsive-brix'),
				'id' => 'background',
				'std' => array(
					'color' => '#ffffff' ),
				'type' => 'background',
				'options' => array(
					'attachment' => false,
					'repeat' => false,
					'position' => false, ) ),
			), );

	$options[] = array(
		'name' => $slider2_label,
		'type' => 'subheading');

	$options[] = array(
		'name' => __("Slider Width (in Stretched Site Layout)", 'responsive-brix'),
		'desc' => __("Enable this to stretch the slider to Full Screen Width.<br />Note: This option is useful only if the <strong>'Site Layout'</strong> is set to <strong>'Stretched (full width)'</strong> in the 'Layout' tab above.", 'responsive-brix'),
		'id' => "wt_img_slider_width",
		'std' => "boxed",
		'type' => "images",
		'options' => array(
			'boxed' => $imagepath . 'slider-width-boxed.png',
			'stretch' => $imagepath . 'slider-width-stretch.png', )
	);

	$options[] = array(
		'name' => __('Slides', 'responsive-brix'),
		'desc' => __('Premium version of this theme comes with capability to create <strong>Unlimited slides</strong>.', 'responsive-brix'),
		'id' => 'wt_img_slider',
		'std' => array( 'g0' => array(), 'g1' => array(), 'g2' => array(), 'g3' => array() ),
		'type' => 'group',
		'settings' => array(
			'title' => __( 'Slide', 'responsive-brix' ),
			'add_button' => __( 'Add New Slide', 'responsive-brix' ),
			'remove_button' => __( 'Remove Slide', 'responsive-brix' ),
			),
		'fields' => array(
			array(
				'name' => __('To hide this slide, simply leave the Image empty.', 'responsive-brix'),
				'type' => 'info'),
			array(
				'name' => __('Slide Image', 'responsive-brix'),
				'desc' => __('The main showcase image.', 'responsive-brix'),
				'id' => 'image',
				'type' => 'upload'),
			array(
				'name' => __('Slide Caption (optional)', 'responsive-brix'),
				'id' => 'caption',
				'type' => 'text'),
			array(
				'name' => __('Slide Link', 'responsive-brix'),
				'desc' => __('Leave empty if you do not want to link the slide.', 'responsive-brix'),
				'id' => 'url',
				'type' => 'text'),
			), );

	endif; // end 'hoot-widgetized-template' supported theme options

	// Add Premium Tab
	$options_premium = include_once( trailingslashit( HOOT_THEMEDIR ) . 'admin/premium.php' );
	if ( is_array( $options_premium ) && !empty( $options_premium ) ) {

		$premium = '';
		foreach ( $options_premium as $option_premium ) {
			$premium .= '<div class="section section-info section-premium-info">';
			if ( !empty( $option_premium['desc'] ) ) {
				$premium .= '<div class="premium-info">';
				$premium .= '<div class="premium-info-text controls">';
				if ( !empty( $option_premium['name'] ) )
					$premium .= '<h4 class="heading">' . $option_premium['name'] . '</h4>';
				$premium .= $option_premium['desc'];
				$premium .= '</div>';
				if ( !empty( $option_premium['img'] ) )
					$premium .= '<div class="premium-info-img explain">' .
								'<img src="' . esc_url( $imagepath . $option_premium['img'] ) . '" />' .
								'</div>';
				$premium .= '<div class="clear"></div>';
				$premium .= '</div>';
			} elseif ( !empty( $option_premium['name'] ) ) {
				$premium .= '<h4 class="heading">' . $option_premium['name'] . '</h4>';
			}
			if ( !empty( $option_premium['std'] ) )
				$premium .= $option_premium['std'];
			$premium .= '</div>';
		}

		$options[] = array(
			'name' => __('Premium Options', 'responsive-brix'),
			'type' => 'heading');
		$options[] = array(
			'std' => $premium,
			'type' => 'html');
	}

	/* Return Hoot Options Array */
	return $options;
}