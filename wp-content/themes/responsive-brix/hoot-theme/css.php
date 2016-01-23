<?php
/**
 * Add custom css to frontend.
 *
 * @package hoot
 * @subpackage responsive-brix
 * @since responsive-brix 1.0
 */

// Hook into 'wp_enqueue_scripts' as 'wp_add_inline_style()' requires stylesheet $handle to be already registered.
// Main stylesheet with handle 'style' is registered by the framework via 'wp_enqueue_scripts' hook at priority 0
add_action( 'wp_enqueue_scripts', 'hoot_custom_css', 99 );

/**
 * Custom CSS built from user theme options
 * For proper sanitization, always use functions from hoot/functions/css-styles.php
 *
 * @since 1.0
 * @access public
 */
function hoot_custom_css() {
	$css = '';
	$vars = array();

	$accent_color = hoot_get_option( 'accent_color' );
	$accent_color_dark = hoot_color_increase( $accent_color, 10, 10 );
	$accent_font = hoot_get_option( 'accent_font' );

	$cssrules = array();

	// Hoot Grid
	$cssrules['.grid'] = hoot_css_grid_width();

	// Base Typography and HTML
	$cssrules['a'] = hoot_css_rule( 'color', $accent_color ); // Overridden by hoot_premium_custom_cssrules()
	$cssrules['.invert-typo'] = array(
		hoot_css_rule( 'background', $accent_color ),
		hoot_css_rule( 'color', $accent_font ),
		);
	$cssrules['.invert-typo a, .invert-typo a:hover, .invert-typo h1, .invert-typo h2, .invert-typo h3, .invert-typo h4, .invert-typo h5, .invert-typo h6, .invert-typo .title'] = hoot_css_rule( 'color', $accent_font );
	$cssrules['input[type="submit"], #submit, .button'] = array(
		hoot_css_rule( 'background', $accent_color ),
		hoot_css_rule( 'color', $accent_font ),
		);
	$cssrules['input[type="submit"]:hover, #submit:hover, .button:hover'] = array(
		hoot_css_rule( 'background', $accent_color_dark ),
		hoot_css_rule( 'color', $accent_font ),
		);

	// Override @headingsFontFamily if selected in options
	if ( 'cursive' != hoot_get_option( 'headings_fontface' ) ) {
		$cssrules['h1, h2, h3, h4, h5, h6, .title, .titlefont'] = array(
			hoot_css_rule( 'font-family', '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif' ),
			hoot_css_rule( 'font-weight', '300' ),
			hoot_css_rule( 'color', '#000000' ),
			);
	}

	// Layout
	$content_bg = hoot_get_option( 'background' );
	$cssrules['body'][] = hoot_css_background( $content_bg );
	if ( hoot_get_option( 'site_layout' ) == 'boxed' ) {
		$content_bg = array( 'color' => hoot_get_option( 'box_background_color' ) );
		$cssrules['#page-wrapper'][] = hoot_css_background( $content_bg );
	}
	$vars['content_bg'] = $content_bg;

	// Header (Topbar, Header, Main Nav Menu)
	// Topbar
	$cssrules['.topbar-right-inner input'] = hoot_css_rule( 'background', $content_bg['color'] );
	// Header Layout
	if ( hoot_get_option( 'logo_background_type' ) == 'accent' ) {
		$cssrules['#header:before'] = hoot_css_rule( 'background', $accent_color );
	} else {
		$cssrules['#header:before, #site-logo'] = hoot_css_rule( 'background', 'none' );
		$cssrules['#header, #branding, #header-aside'] = hoot_css_rule( 'background', 'none' );
		$cssrules['#site-logo #site-title, #site-logo #site-description'] = hoot_css_rule( 'color', $accent_color );
	}
	// Logo (with icon)
	$title_icon_size = hoot_get_option( 'site_title_icon_size', NULL );
	if ( intval( $title_icon_size ) )
		$cssrules['.site-logo-with-icon #site-title i'] = hoot_css_rule( 'font-size', $title_icon_size );
	$title_icon = hoot_get_option( 'site_title_icon', NULL );
	if ( $title_icon && intval( $title_icon_size ) )
		$cssrules['.site-logo-with-icon #site-title'] = hoot_css_rule( 'padding-left', $title_icon_size );
	// Mixed Logo (with image)
	$logo_image_width = hoot_get_option( 'logo_image_width', NULL );
	$logo_image_width = ( intval( $logo_image_width ) ) ? intval( $logo_image_width ) : 120;
	$cssrules['.site-logo-with-image .site-logo-mixed-image, .site-logo-with-image .site-logo-mixed-image img'] = hoot_css_rule( 'width', intval( $logo_image_width ) . 'px' ); // Important to have logo img width as img does not follow max-width inside non-fixed tables in Firefox
	// Custom Logo
	$hoot_logo = hoot_get_option( 'logo' );
	if ( 'custom' == $hoot_logo || 'mixedcustom' == $hoot_logo ) {
		$title_custom = apply_filters( 'hoot_logo_custom_text', hoot_get_option( 'logo_custom' ) );
		if ( is_array( $title_custom ) && !empty( $title_custom ) ) {
			$lcount = 1;
			foreach ( $title_custom as $title_line ) {
				if ( !empty( $title_line['size'] ) )
					$cssrules['#site-logo-custom .site-title-line' . $lcount . ',#site-logo-mixedcustom .site-title-line' . $lcount] = hoot_css_rule( 'font-size', $title_line['size'] );
				$lcount++;
			}
		}
	}

	// Light Slider
	$cssrules['.lSSlideOuter .lSPager.lSpg > li:hover a, .lSSlideOuter .lSPager.lSpg > li.active a'] = hoot_css_rule( 'background-color', $accent_color );

	// Allow CSS to be modified
	$cssrules = apply_filters( 'hoot_dynamic_cssrules', $cssrules, $vars );


	/** Print CSS Rules **/

	foreach ( $cssrules as $selector => $rules ) {
		if ( !empty( $selector ) ) {
			$css .= $selector . ' {';
			if ( is_array( $rules ) ) {
				foreach ( $rules as $rule ) {
					$css .= $rule . ' ';
				}
			} else {
				$css .= $rules;
			}
			$css .= ' }' . "\n";
		}
	}

	// @todo add media queries to preceding code

	// Allow CSS to be modified
	$cssrules = apply_filters( 'hoot_dynamic_css', $css, $vars );

	// Print CSS
	if ( !empty( $css ) ) {
		wp_add_inline_style( 'style', $css );
	}

}