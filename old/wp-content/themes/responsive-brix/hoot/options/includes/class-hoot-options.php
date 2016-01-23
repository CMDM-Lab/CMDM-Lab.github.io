<?php
/**
 * Main Hoot Options framework class
 *
 * @package hoot
 * @subpackage options-framework
 * @since hoot 1.0.0
 */

class Hoot_Options {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	const VERSION = HOOT_VERSION;

	/**
	 * Gets option name
	 *
	 * @since 1.0.0
	 */
	static function _get_option_name() {
		return hootoptions_option_name();
	}

	/**
	 * Wrapper for hoot_get_theme_options_array()
	 *
	 * @return array
	 */
	static function _hootoptions_options() {
		return hoot_get_theme_options_array();
	}

}