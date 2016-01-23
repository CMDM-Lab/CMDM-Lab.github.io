<?php
/**
 * Media Uploader class for Hoot Options
 *
 * @package hoot
 * @subpackage options-framework
 * @since hoot 1.0.0
 */

class Hoot_Options_Media_Uploader {

	/**
	 * Initialize the media uploader class
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'hootoptions_media_scripts' ) );
	}

	/**
	 * Media Uploader Using the WordPress Media Library.
	 *
	 * Parameters:
	 * string $_id - A token to identify this field (the name).
	 * string $_value - The value of the field, if present.
	 * string $_desc - An optional description of the field.
	 *
	 * @since 1.0.0
	 */

	static function hootoptions_uploader( $_id, $_value, $_desc = '', $_name = '' ) {

		// Gets the unique option id
		$option_name = Hoot_Options::_get_option_name();

		$output = '';
		$id = '';
		$class = '';
		$int = '';
		$value = '';
		$name = '';

		$id = strip_tags( strtolower( $_id ) );

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
		}

		if ($_name != '') {
			$name = $_name;
		}
		else {
			$name = $option_name.'['.$id.']';
		}

		if ( $value ) {
			$class = ' has-file';
		}
		$output .= '<input id="' . esc_attr( $id ) . '" class="upload' . $class . '" type="text" name="' . esc_attr( $name ) . '" value="' . $value . '" placeholder="' . __('No file chosen', 'responsive-brix') .'" />' . "\n";
		if ( function_exists( 'wp_enqueue_media' ) ) {
			if ( ( $value == '' ) ) {
				$output .= '<input id="upload-' . esc_attr( $id ) . '" class="upload-button button" type="button" value="' . __( 'Upload', 'responsive-brix' ) . '" />' . "\n";
			} else {
				$output .= '<input id="remove-' . esc_attr( $id ) . '" class="remove-file button" type="button" value="' . __( 'Remove', 'responsive-brix' ) . '" />' . "\n";
			}
		} else {
			$output .= '<p><i>' . __( 'Upgrade your version of WordPress for full media support.', 'responsive-brix' ) . '</i></p>';
		}

		if ( $_desc != '' ) {
			$output .= '<span class="hoot-of-metabox-desc">' . $_desc . '</span>' . "\n";
		}

		$output .= '<div class="screenshot" id="' . $id . '-image">' . "\n";

		if ( $value != '' ) {
			$remove = '<a class="remove-image">Remove</a>';
			$image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
			if ( $image ) {
				$output .= '<img src="' . $value . '" alt="" />' . $remove;
			} else {
				$parts = explode( "/", $value );
				for( $i = 0; $i < sizeof( $parts ); ++$i ) {
					$title = $parts[$i];
				}

				// No output preview if it's not an image.
				$output .= '';

				// Standard generic output if it's not an image.
				$title = __( 'View File', 'responsive-brix' );
				$output .= '<div class="no-image"><span class="file_link"><a href="' . $value . '" target="_blank" rel="external">'.$title.'</a></span></div>';
			}
		}
		$output .= '</div>' . "\n";
		return $output;
	}

	/**
	 * Enqueue scripts for file uploader if options page
	 *
	 * @since 1.0.0
	 */
	function hootoptions_media_scripts( $hook ) {
		$options_page = 'appearance_page_' . hootoptions_option_name();
		if ( $options_page == $hook )
			$this->enqueue_admin_options_media_scripts();
	}

	/**
	 * Enqueue scripts for file uploader
	 *
	 * @since 1.1.0
	 */
	static function enqueue_admin_options_media_scripts() {
		if ( function_exists( 'wp_enqueue_media' ) )
			wp_enqueue_media();

		/* Get the minified suffix */
		$suffix = hoot_get_min_suffix();

		wp_register_script( 'hoot-options-media-uploader', trailingslashit( HOOTOPTIONS_URI ) ."js/media-uploader{$suffix}.js", array( 'jquery' ), Hoot_Options::VERSION );
		wp_enqueue_script( 'hoot-options-media-uploader' );
		wp_localize_script( 'hoot-options-media-uploader', 'hootoptions_l10n', array(
			'upload' => __( 'Upload', 'responsive-brix' ),
			'remove' => __( 'Remove', 'responsive-brix' )
		) );
	}

}