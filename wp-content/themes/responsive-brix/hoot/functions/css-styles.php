<?php
/**
 * Functions for building CSS styles to be printed.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/**
 * Create general CSS style
 *
 * @since 1.0.0
 * @access public
 * @param string $style name of the css property
 * @param string $value value of the css property
 * @param bool $echo
 * @param bool $important
 * @return void|string
 */
function hoot_css_rule( $style, $value, $echo = false, $important = false ) {
	if ( empty( $style ) || empty( $value ) )
		return '';

	$important = ( $important ) ? ' !important' : '';

	// Load Sanitization functions if not loaded already (for frontend)
	if ( !function_exists( 'hoot_of_sanitize_enum' ) )
		require trailingslashit( HOOTOPTIONS_DIR ) . 'includes/sanitization.php';

	// Sanitize CSS values
	// @todo box-shadow -moz-box-shadow -webkit-box-shadow
	switch( $style ):

		case 'color':
		case 'background-color':
		case 'border-color':
		case 'border-right-color':
		case 'border-bottom-color':
		case 'border-top-color':
		case 'border-left-color':
			if ( 'none' == $value || 'transparent' == $value )
				$value = 'transparent';
			else
				// sanitize color. hoot_of_sanitize_hex() will return null if $value is not a formatted hex color
				$value = hoot_of_sanitize_hex( $value );
			break;

		case 'background':
			if ( is_array( $value ) ) {
				// use the hoot_css_background function for multiple background properties
				hoot_css_background( $value );
				return;
			} elseif ( 'none' == $value || 'transparent' == $value ) {
				$value = 'none';
			} else {
				// sanitize for background color. hoot_of_sanitize_hex() will return null if $value is not a formatted hex color
				$value = hoot_of_sanitize_hex( $value );
			}
			break;

		case 'background-image':
			$value = 'url("' . esc_url( $value ) . '")';
			break;

		case 'background-repeat':
			$recognized = hoot_of_recognized_background_repeat();
			$value = array_key_exists( $value, $recognized ) ? $value : '';
			break;

		case 'background-position':
			$recognized = hoot_of_recognized_background_position();
			$value = array_key_exists( $value, $recognized ) ? $value : '';
			break;

		case 'background-attachment':
			$recognized = hoot_of_recognized_background_attachment();
			$value = array_key_exists( $value, $recognized ) ? $value : '';
			break;

		case 'font':
			if ( is_array( $value ) ) {
				// use the hoot_css_typography function for multiple font properties
				hoot_css_typography( $value );
				return;
			} else {
				// Recognized font-families in hoot/options/includes/fonts{-google}.php
				$recognized = hoot_of_recognized_font_faces();
				$value = stripslashes( $value );
				$value = array_key_exists( $value, $recognized ) ? $value : '';
			}
			break;

		case 'font-family':
			// Recognized font-families in hoot/options/includes/fonts{-google}.php
			$recognized = hoot_of_recognized_font_faces();
			$value = stripslashes( $value );
			$value = array_key_exists( $value, $recognized ) ? $value : '';
			break;

		case 'font-style':
			$recognized = array( 'inherit', 'initial', 'italic', 'normal', 'oblique' );
			$value = in_array( $value, $recognized ) ? $value : '';
			break;

		case 'font-weight':
			$value_check = intval( $value );
			if ( !empty( $value_check ) ) {
				// for numerical weights like 300, 600 etc.
				$value = $value_check;
			} else {
				// for strings like 'bold', 'light', 'lighter' etc.
				$recognized = array( 'bold', 'bolder', 'inherit', 'initial', 'lighter', 'normal' );
				$value = in_array( $value, $recognized ) ? $value : '';
			}
			break;

		case 'text-decoration':
			$recognized = array( 'blink', 'inherit', 'initial', 'line-through', 'overline', 'underline' );
			$value = in_array( $value, $recognized ) ? $value : '';
			break;

		case 'text-transform':
			$recognized = array( 'capitalize', 'inherit', 'initial', 'lowercase', 'none', 'uppercase' );
			$value = in_array( $value, $recognized ) ? $value : '';
			break;

		case 'font-size':
		case 'padding':
		case 'padding-right':
		case 'padding-bottom':
		case 'padding-left':
		case 'padding-top':
		case 'margin':
		case 'margin-right':
		case 'margin-bottom':
		case 'margin-left':
		case 'margin-top':
			$value_check = preg_replace('/px|em|rem/','', $value);
			$value_check = intval( $value_check );
			$value = ( !empty( $value_check ) || '0' === $value_check || 0 === $value_check ) ? $value : '';
			break;

		case 'opacity':
			$value_check = intval( $value );
			$value = ( !empty( $value_check ) || '0' === $value_check || 0 === $value_check ) ? $value : '';
			break;

	endswitch;

	// Return if $value is empty (failed sanitization checks)
	if ( empty( $value ) )
		return '';

	$output = " $style: $value$important; ";
	if ( true === $echo || 'true' === $echo )
		echo $output;
	else
		return $output;
}

/**
 * Create CSS styles from a background array.
 *
 * @since 1.0.0
 * @access public
 * @param array $background
 * @return string
 */
function hoot_css_background( $background ) {
	$background_defaults = array(
		'color' => '',
		'type' => 'predefined',
		'pattern' => 0,
		'image' => '',
		'repeat' => '',
		'position' => '',
		'attachment' => '',
	);
	$background = wp_parse_args( $background, $background_defaults );
	extract( $background, EXTR_SKIP );

	// Load Sanitization functions if not loaded already (for frontend)
	if ( !function_exists( 'hoot_of_sanitize_enum' ) )
		require trailingslashit( HOOTOPTIONS_DIR ) . 'includes/sanitization.php';

	$output = '';

	if ( !empty( $color ) ) {
		$output .= hoot_css_rule( 'background-color', $color );
	}

	if ( 'predefined' == $type ) {
		if ( !empty( $pattern ) ) { // skip if pattern = 0 (i.e. user has selected 'No Pattern')
			$recognized = hoot_of_recognized_background_pattern();
			if ( array_key_exists( $pattern, $recognized ) ) {
				$background_image = trailingslashit( HOOT_IMAGES ) . 'patterns/' . $pattern;
				$output .= hoot_css_rule( 'background-image', $background_image );
				$output .= hoot_css_rule( 'background-repeat', 'repeat' );
			}
		}
	}

	if ( 'custom' == $type ) {
		if ( !empty( $image ) ) {
			$output .= hoot_css_rule( 'background-image', $image );
			if ( !empty( $repeat ) ) {
				$output .= hoot_css_rule( 'background-repeat', $repeat );
			}
			if ( !empty( $position ) ) {
				$output .= hoot_css_rule( 'background-position', $position );
			}
			if ( !empty( $attachment ) ) {
				$output .= hoot_css_rule( 'background-attachment', $attachment );
			}
		}
	}

	return $output;
}

/**
 * Create CSS styles from a typography array.
 *
 * @since 1.1.1
 * @access public
 * @param array $typography
 * @param bool $reset Reset earlier css rules from stylesheets etc.
 * @return string
 */
function hoot_css_typography( $typography, $reset = false ) {
	$typography_defaults = array(
		'size' => '',
		'face' => '',
		'style' => '',
		'color' => '',
	);
	$typography = wp_parse_args( $typography, $typography_defaults );
	extract( $typography, EXTR_SKIP );

	$output = '';

	if ( !empty( $color ) ) {
		$output .= hoot_css_rule( 'color', $color );
	}

	if ( !empty( $size ) ) {
		$output .= hoot_css_rule( 'font-size', $size );
	}

	if ( !empty( $face ) ) {
		$output .= hoot_css_rule( 'font-family', $face );
	}

	if ( !empty( $style ) ) {
		switch ( $style ) {
			case 'italic':
				$output .= hoot_css_rule( 'font-style', 'italic' );
				if ( $reset ) $output .= hoot_css_rule( 'text-transform', 'none' );
				if ( $reset ) $output .= hoot_css_rule( 'font-weight', 'normal' );
				break;
			case 'bold':
				$output .= hoot_css_rule( 'font-weight', 'bold' );
				if ( $reset ) $output .= hoot_css_rule( 'text-transform', 'none' );
				if ( $reset ) $output .= hoot_css_rule( 'font-style', 'normal' );
				break;
			case 'bold italic':
				$output .= hoot_css_rule( 'font-weight', 'bold' );
				$output .= hoot_css_rule( 'font-style', 'italic' );
				if ( $reset ) $output .= hoot_css_rule( 'text-transform', 'none' );
				break;
			case 'lighter':
				$output .= hoot_css_rule( 'font-weight', 'lighter' );
				if ( $reset ) $output .= hoot_css_rule( 'text-transform', 'none' );
				if ( $reset ) $output .= hoot_css_rule( 'font-style', 'normal' );
				break;
			case 'lighter italic':
				$output .= hoot_css_rule( 'font-weight', 'lighter' );
				$output .= hoot_css_rule( 'font-style', 'italic' );
				if ( $reset ) $output .= hoot_css_rule( 'text-transform', 'none' );
				break;
			case 'uppercase':
				$output .= hoot_css_rule( 'text-transform', 'uppercase' );
				if ( $reset ) $output .= hoot_css_rule( 'font-weight', 'normal' );
				if ( $reset ) $output .= hoot_css_rule( 'font-style', 'normal' );
				break;
			case 'uppercase italic':
				$output .= hoot_css_rule( 'text-transform', 'uppercase' );
				$output .= hoot_css_rule( 'font-style', 'italic' );
				if ( $reset ) $output .= hoot_css_rule( 'font-weight', 'normal' );
				break;
			case 'uppercase bold':
				$output .= hoot_css_rule( 'text-transform', 'uppercase' );
				$output .= hoot_css_rule( 'font-weight', 'bold' );
				if ( $reset ) $output .= hoot_css_rule( 'font-style', 'normal' );
				break;
			case 'uppercase bold italic':
				$output .= hoot_css_rule( 'text-transform', 'uppercase' );
				$output .= hoot_css_rule( 'font-weight', 'bold' );
				$output .= hoot_css_rule( 'font-style', 'italic' );
				break;
			case 'uppercase lighter':
				$output .= hoot_css_rule( 'text-transform', 'uppercase' );
				$output .= hoot_css_rule( 'font-weight', 'lighter' );
				if ( $reset ) $output .= hoot_css_rule( 'font-style', 'normal' );
				break;
			case 'uppercase lighter italic':
				$output .= hoot_css_rule( 'text-transform', 'uppercase' );
				$output .= hoot_css_rule( 'font-weight', 'lighter' );
				$output .= hoot_css_rule( 'font-style', 'italic' );
				break;
			case 'none': default:
				if ( $reset ) $output .= hoot_css_rule( 'text-transform', 'none' );
				if ( $reset ) $output .= hoot_css_rule( 'font-weight', 'normal' );
				if ( $reset ) $output .= hoot_css_rule( 'font-style', 'normal' );
				break;
		}
	}

	return $output;
}

/**
 * Create CSS style from grid width.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function hoot_css_grid_width() {
	$output = '';

	$width = intval( hoot_get_option( 'site_width' ) );
	$width = !empty( $width ) ? $width : 1260;

	$output .= hoot_css_rule( 'max-width', $width . 'px' );

	return $output;
}