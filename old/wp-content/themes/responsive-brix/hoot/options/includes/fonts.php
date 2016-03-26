<?php
/**
 * Functions for sending list of fonts available.
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 1.0.0
 */

/**
 * Generates the font (websafe) list
 * Font list should always have the form:
 * {css style} => {font name}
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function hoot_fonts_list() {

	return apply_filters( 'hoot_websafe_fonts', array(
		'Arial, Helvetica, sans-serif'            => 'Arial',
		'Helvetica, sans-serif'                   => 'Helvetica',
		'Verdana, Geneva, sans-serif'             => 'Verdana, Geneva',
		'"Trebuchet MS", Helvetica, sans-serif'   => 'Trebuchet',
		'Georgia, serif'                          => 'Georgia',
		'"Times New Roman", serif'                => 'Times New Roman',
		'Tahoma, Geneva, sans-serif'              => 'Tahoma, Geneva',
		)
	);

}