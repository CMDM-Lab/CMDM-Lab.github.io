<?php
/**
 * Theme Contextual Help
 * @package Oprum
 */
add_filter( 'contextual_help', 'oprum_admin_contextual_help', 10 );

function oprum_admin_contextual_help() {

	$screen = get_current_screen();

if ( $screen->id == 'post' ) {

	$screen->add_help_tab( array(
		'id'      => 'oprum-post-fimg',
		'title'   => __( 'Theme Features', 'oprum' ),
		'content' => '<h2>' . __( 'Theme Features', 'oprum' ) . '</h2><p><strong>' . __( 'Use Featured image', 'oprum' ) . '</strong></p><p>' . __( 'Upload the image that will be displayed header on single post.', 'oprum' ) . '</p><p><strong>' . __( 'Use Excerpt', 'oprum' ) . '</strong></p><p>' . __( 'Enter text in Metabox Excerpt to show announcement or the focus of the post.', 'oprum' ) . '</p><p><strong>' . __( 'Format', 'oprum' ) . '</strong></p><p>' . __( '<b>Quote.</b> As title, type the author quotes. Quote place in the main text box without using tag blockquote (b-quote). Featured image and Excerpt are not used.<br /><b>Link.</b> As title, type the name Link. URL place in the main text box without using http (http://). Featured image and Excerpt are not used.', 'oprum' ) . '</p><p><strong>' . __( 'Button', 'oprum' ) . '</strong></p><p>' . __( 'To show the button, use the link class, , for example <code>class="btn blue"</code>. Color options: green, blue, red.', 'oprum' ) . '</p>',
  ) );

}

if ( $screen->id == 'page' ) {

  $screen->add_help_tab( array(
      'id' => 'oprum_page_tab',
      'title' => __( 'Theme Features', 'oprum' ),
	'content' =>  '<h2>' . __( 'Theme Features', 'oprum' ) . '</h2><p><strong>' . __( 'Use Featured image', 'oprum' ) . '</strong></p><p>' . __( 'Upload the image that will be displayed header on page.', 'oprum' ) . '</p><p><strong>' . __( 'Use Excerpt', 'oprum' ) . '</strong></p><p>' . __( 'Enter text in Metabox Excerpt to show announcement or the focus of the page.', 'oprum' ) . '</p><p><strong>' . __( 'Templates', 'oprum' ) . '</strong></p><p>' . __( 'The theme is the templates page without the sidebar, wooCommerce template, and wooTestimonials template. Use metabox Page Attributes > dropdown Template. Note: In the page template FullPage is not showing the page title.', 'oprum' ) . '</p><p><strong>' . __( 'Button', 'oprum' ) . '</strong></p><p>' . __( 'To show the button, use the link class, , for example <code>class="btn blue"</code>. Color options: green, blue, red.', 'oprum' ) . '</p>',
  ) );

}

if ( $screen->id == 'widgets' ) {

	$screen->add_help_tab( array(
		'id'      => 'rectangulum-widgets',
		'title'   => __( 'Theme Features', 'oprum' ),
		'content' =>  '<h2>' . __( 'Custom widgets', 'oprum' ) . '</h2><p>' . __( 'The theme has a custom widgets to display posts format video, gallery, quote or aside in places sidebars (sidebar or footer). If these widgets to Link for archive by default, the link will be in the form of icons format.', 'oprum' ) . '</p><p>' . __( '<b>Note:</b> If you want in the sidebar to display social menu as links social media icons of the menu name should be Social.', 'oprum' ) . '</p>',
	) );
}

if ( $screen->id == 'appearance_page_custom-header' ) {

	$screen->add_help_tab( array(
		'id'      => 'oprum-header',
		'title'   => __( 'Theme Features', 'oprum' ),
		'content' =>  '<h2>' . __( 'Custom widgets', 'oprum' ) . '</h2><p>' . __( 'Background color header set using Customizer. Go to Customize > Colors: Header BG Color', 'oprum' ) . '</p>',
	) );
}

if ( $screen->id == 'nav-menus' ) {

	$screen->add_help_tab( array(
		'id'      => 'oprum-social-menus',
		'title'   => __( 'Social Menu', 'oprum' ),
		'content' =>  '<h2>' . __( 'Custom widgets', 'oprum' ) . '</h2><p>' . __( 'Menu icons social media is displayed in the footer. Included all popular icons of social media, and Feedburner. To create a menu item, use the tab Links (Edit Menus). And select Social Menu as Theme locations.', 'oprum' ) . '</p><p>' . __( 'Example:<br />tab <strong>Links</strong><br /><em>URL</em> http://twitter.com/your<br /><em>Navigation Label</em> Twitter', 'oprum' ) . '</p><p>' . __( '<b>Note:</b> If you want in the sidebar to display social menu as links social media icons of the menu name should be Social.', 'oprum' ) . '</p>',
	) );
}

/**
*else
*/
      return;
}
?>