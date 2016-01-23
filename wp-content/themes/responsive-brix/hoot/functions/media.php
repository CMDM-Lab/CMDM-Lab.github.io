<?php
/**
 * Functions for handling media (i.e., attachments) within themes.
 * This file is loaded via the 'after_setup_theme' hook at priority '2'
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/* Add all image sizes to the image editor to insert into post. */
add_filter( 'image_size_names_choose', 'hoot_image_size_names_choose' );

/* Adds ID3 tags for media display. */
add_filter( 'wp_get_attachment_id3_keys', 'hoot_attachment_id3_keys', 5, 3 );

/* Register custom image sizes. */
add_action( 'init', 'hoot_register_image_sizes', 5 );

/**
 * Adds theme "post-thumbnail" size plus an internationalized version of the image size name to the 
 * "add media" modal.  This allows users to insert the image within their post content editor.
 *
 * @since 1.0.0
 * @access public
 * @param array   $sizes  Selectable image sizes.
 * @return array
 */
function hoot_image_size_names_choose( $wp_sizes ) {
	$sizes = array();
	$sizes = apply_filters( 'hoot_custom_image_sizes', $sizes );

	foreach ( $sizes as $name => $size ) {

		$default_size = array(
			$label = '',
			$width = 0,
			$height = 0,
			$crop = false,
			$show_in_editor = false,
		);
		$size = wp_parse_args( $size, $default_size );

		/* Add image size to Image Editor if its not a Reserved Name and if 'show_in_editor' */
		if ( $name != 'thumb' && $name != 'thumbnail' && $name != 'medium' && $name != 'large' && $name != 'post-thumbnail' ) {
			if ( $size['show_in_editor'] && $size['label'] && !isset( $wp_sizes[ $name ] ) ) {
				$wp_sizes[ $name ] = $size['label'];
			}
		}

	}

	/* Return the image size names. */
	return $wp_sizes;
}

/**
 * Registers custom image sizes for the theme. 
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function hoot_register_image_sizes() {
	$sizes = array();
	$sizes = apply_filters( 'hoot_custom_image_sizes', $sizes );

	foreach ( $sizes as $name => $size ) :

		$default_size = array(
			'label'          => '',
			'width'          => 0,
			'height'         => 0,
			'crop'           => false,
			'show_in_editor' => false,
		);
		$size = wp_parse_args( $size, $default_size );

		/* Add image size if its not a Reserved Name */
		if ( $name != 'thumb' && $name != 'thumbnail' && $name != 'medium' && $name != 'large' && $name != 'post-thumbnail' ) {

			if ( intval( $size['width'] ) != 0 || intval( $size['height'] ) != 0 )
				add_image_size( $name, intval( $size['width'] ), intval( $size['height'] ), $size['crop'] );

		} elseif ( $name == 'post-thumbnail' ){

			/* Sets the 'post-thumbnail' size. */
			set_post_thumbnail_size( $size['width'], $size['height'], $size['crop'] );

		}

	endforeach;

}

/**
 * Get the image size to use in a span/column of the CSS grid
 * @todo Can be made more flexible, but for now this will have to do.
 *        Case 1: $grid can be a container span, for when a spanN is in a grid which itself is a spanN/Column
 *        Case 2: Account for responsive spans i.e. set a minimum span size for smaller spans so that mobile viewports
 *                will show bigger width images for available screen space. Example: span1,2,3 will have image sizes
 *                corresponding to span4, so that in mobile view where all spans have 100% width, images are displayed
 *                more nicely!
 *        Case 3: Maybe find a robust (not hard coded) way to account for span padding as well (curently $swidth
 *                does not take padding into account)
 *
 * @since 1.0.0
 * @access public
 * @param string $span span size or column size
 * @param NULL|bool $crop get only cropped if true, only noncropped if false, either for anything else.
 * @param int $gridadjust Grid's Width Adjustment for various paddings (possible value 80)
 * @return string
 */
function hoot_get_image_size_name( $span, $crop=NULL, $gridadjust=0 ) {
	$default_grid = 1260;

	/* Get the Span/Column factor */
	if ( strpos( $span, 'span-' ) !== false ) {
		$pieces = explode( "span-", $span );
		$factor = $pieces[1];
	} elseif ( strpos( $span, 'column-' ) !== false ) {
		$pieces = explode( "column-", $span );
		$factors = explode( "-", $pieces[1] );
		$factor = ( $factors[0] * 12 ) / $factors[1];
	} else {
		return false;
	}

	/* Responsive Grid: Any span below 3 gets an image size fit for atleast span3 to display nicely on smaller screens */
	$factor = ( intval( $factor ) < 3 ) ? 3 : intval( $factor );

	/* Get the Grid (int)Width from Hoot Options else Default */
	$grid = ( function_exists( 'hoot_get_option' ) ) ? intval( hoot_get_option( 'site_width' ) ) : 0;
	if ( empty( $grid ) )
		$grid = $default_grid;
	$grid -= $gridadjust;

	/* Get width array arranged in ascending order */
	if ( $crop === true )
		$iwidths = hoot_get_image_sizes( 'sort_by_width_crop' );
	elseif ( $crop === false )
		$iwidths = hoot_get_image_sizes( 'sort_by_width_nocrop' );
	else
		$iwidths = hoot_get_image_sizes( 'sort_by_width' );

	/* Get Image size corresponding to span width */
	$swidth = ( $factor / 12 ) * $grid;
	foreach ( $iwidths as $name => $iwidth ) {
		if ( (int)$swidth <= (int)$iwidth )
			return $name;
	}

	/* If this was a crop/no-crop request and we didn't find any image size, then search all available sizes. */
	if ( $crop === true || $crop === false ){
		$iwidths = hoot_get_image_sizes( 'sort_by_width' );
		foreach ( $iwidths as $name => $iwidth ) {
			if ( (int)$swidth <= (int)$iwidth )
				return $name;
		}
	}

	/* Full size image (largest width) */
	return 'full';

}

/**
 * Get all (or one) registered image sizes with width and height
 *
 * @since 1.0.0
 * @access public
 * @param string $return specific image size to return, or 'sort_by_width' to return array sorted by inc. widths,
 *                       or 'sort_by_width_crop' for sorted (by width) only cropped sizes, or 'sort_by_width_nocrop'
 *                       for sorted (by width) only noncropped sizes
 * @return array
 */
function hoot_get_image_sizes( $return = '' ) {
	static $sizes = array(); // cache
	static $sort_by_width = array();
	static $sort_by_width_crop = array();
	static $sort_by_width_nocrop = array();

	if ( empty( $sizes ) ) {
		global $_wp_additional_image_sizes;
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach( $get_intermediate_image_sizes as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ]['width'] = $_wp_additional_image_sizes[ $_size ]['width'];
				$sizes[ $_size ]['height'] = $_wp_additional_image_sizes[ $_size ]['height'];
				$sizes[ $_size ]['crop'] = $_wp_additional_image_sizes[ $_size ]['crop'];
			}
			// Create additional arrays
			if ( isset( $sizes[ $_size ]['width'] ) ){
				$sort_by_width[ $_size ] = $sizes[ $_size ]['width'];
				if ( $sizes[ $_size ]['crop'] )
					$sort_by_width_crop[ $_size ] = $sizes[ $_size ]['width'];
				else
					$sort_by_width_nocrop[ $_size ] = $sizes[ $_size ]['width'];
			}
		}

		asort( $sort_by_width, SORT_NUMERIC );
		asort( $sort_by_width_crop, SORT_NUMERIC );
		asort( $sort_by_width_nocrop, SORT_NUMERIC );
	}

	if ( $return ) {
		if ( 'sort_by_width' == $return ){
			return $sort_by_width;
		} elseif ( 'sort_by_width_crop' == $return ){
			return $sort_by_width_crop;
		} elseif ( 'sort_by_width_nocrop' == $return ){
			return $sort_by_width_nocrop;
		} elseif ( isset( $sizes[ $return ] ) ) {
			return $sizes[ $return ];
		} else {
			return false;
		}
	}
	return $sizes;
}

/**
 * Creates custom labels for ID3 tags that are used on the front end of the site when displaying 
 * media within the theme, typically on attachment pages.
 *
 * @since 1.0.0
 * @access public
 * @param array   $fields
 * @param object  $attachment
 * @param string  $context
 * @return array
 */
function hoot_attachment_id3_keys( $fields, $attachment, $context ) {

	if ( 'display' === $context ) {

		$fields['filesize']		 = __( 'File Size', 'responsive-brix' );
		$fields['mime_type']		= __( 'Mime Type', 'responsive-brix' );
		$fields['length_formatted'] = __( 'Run Time',  'responsive-brix' );
	}

	if ( hoot_attachment_is_audio( $attachment->ID ) ) {

		$fields['genre']		= __( 'Genre',	'responsive-brix' );
		$fields['year']		 = __( 'Year',	 'responsive-brix' );
		$fields['composer']	 = __( 'Composer', 'responsive-brix' );
		$fields['track_number'] = __( 'Track',	'responsive-brix' );

		if ( 'display' === $context )
			$fields['unsynchronised_lyric'] = __( 'Lyrics', 'responsive-brix' );
	}

	return $fields;
}