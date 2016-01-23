<?php
/**
 * Admin for Hoot Options framework
 *
 * @package hoot
 * @subpackage options-framework
 * @since hoot 1.0.0
 */

class Hoot_Options_Admin {

	/**
	 * Page hook for the options screen
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $options_screen = null;

	/**
	 * Hook in the scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Get options to load
		$options = Hoot_Options::_hootoptions_options();

		// Checks if options are available
		if ( !empty( $options ) ) {

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_custom_options_page' ) );
			add_action( 'admin_menu', array( $this, 'reorder_custom_options_page' ), 9999 );

			// Add the required scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Settings need to be registered after admin_init
			add_action( 'admin_init', array( $this, 'settings_init' ) );

		}

	}

	/**
	 * Registers the settings
	 *
	 * @since 1.0.0
	 */
	function settings_init() {

		// Get the option name
		$name = Hoot_Options::_get_option_name();

		// Registers the settings fields and callback
		register_setting( $name, $name, array ( $this, 'validate_options' ) );

		// Displays notice after options save
		add_action( 'hootoptions_after_validate', array( $this, 'save_options_notice' ) );

	}

	/*
	 * Define menu options
	 *
	 * Examples usage:
	 *
	 * add_filter( 'hootoptions_menu', function( $menu ) {
	 *	$menu['page_title'] = 'The Options';
	 *	$menu['menu_title'] = 'The Options';
	 *	return $menu;
	 * });
	 *
	 * @since 1.0.0
	 *
	 */
	static function menu_settings() {

		$menu = array(

			// Modes: submenu, menu
			'mode' => 'submenu',

			// Submenu default settings
			'page_title' => __( 'Theme Options', 'responsive-brix' ),
			'menu_title' => __( 'Theme Options', 'responsive-brix' ),
			'capability' => 'edit_theme_options',
			'menu_slug' => hootoptions_option_name(),
			'parent_slug' => 'themes.php',

			// Menu default settings
			'icon_url' => 'dashicons-admin-generic',
			'position' => '61'

		);

		return apply_filters( 'hootoptions_menu', $menu );
	}

	/**
	 * Add a subpage called "Theme Options" to the appearance menu.
	 *
	 * @since 1.0.0
	 */
	function add_custom_options_page() {

		$menu = $this->menu_settings();

		// If you want a top level menu, see this Gist:
		// https://gist.github.com/devinsays/884d6abe92857a329d99

		$this->options_screen = add_theme_page(
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			array( $this, 'options_page' )
		);

	}

	/**
	 * Reorder subpage called "Theme Options" in the appearance menu.
	 *
	 * @since 1.0.0
	 */
	function reorder_custom_options_page() {
		global $submenu;
		$menu = $this->menu_settings();
		$index = '';

		if ( !isset( $submenu['themes.php'] ) ) {
			// probably current user doesn't have this item in menu
			return;
		}

		foreach ( $submenu['themes.php'] as $key => $sm ) {
			if ( $sm[2] == $menu['menu_slug'] ) {
				$index = $key;
				break;
			}
		}

		if ( ! empty( $index ) ) {
			//$item = $submenu['themes.php'][ $index ];
			//unset( $submenu['themes.php'][ $index ] );
			//array_splice( $submenu['themes.php'], 1, 0, array($item) );

			/* array_splice does not preserve numeric keys, so instead we do our own rearranging. */
			$smthemes = array();
			foreach ( $submenu['themes.php'] as $key => $sm ) {
				if ( $key != $index ) {
					$setkey = $key;
					for ( $i = $key; $i < 1000; $i++ ) { 
						if( !isset( $smthemes[$i] ) ) {
							$setkey = $i;
							break;
						}
					}
					$smthemes[ $setkey ] = $sm;
					if ( $sm[2] == 'themes.php' ) {
						$smthemes[ $setkey + 1 ] = $submenu['themes.php'][ $index ];
					}
				}
			}
			$submenu['themes.php'] = $smthemes;
		}

	}

	/**
	 * Loads the required stylesheets if options page
	 *
	 * @since 1.0.0
	 */
	function enqueue_admin_styles( $hook ) {
		if ( $this->options_screen == $hook )
			$this->enqueue_admin_options_styles();
	}

	/**
	 * Loads the required stylesheets
	 *
	 * @since 1.1.0
	 */
	static function enqueue_admin_options_styles() {
		// Get the minified suffix
		$suffix = hoot_get_min_suffix();

		wp_enqueue_style( 'hootoptions', trailingslashit( HOOTOPTIONS_URI ) . "css/hootoptions{$suffix}.css", array(),  Hoot_Options::VERSION );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Loads the required javascript if options page
	 *
	 * @since 1.0.0
	 */
	function enqueue_admin_scripts( $hook ) {
		if ( $this->options_screen == $hook )
			$this->enqueue_admin_options_scripts();
	}

	/**
	 * Loads the required javascript
	 *
	 * @since 1.1.0
	 */
	static function enqueue_admin_options_scripts() {
		// Get the minified suffix
		$suffix = hoot_get_min_suffix();

		// Enqueue custom option panel JS
		wp_enqueue_script( 'hoot-options-custom', trailingslashit( HOOTOPTIONS_URI ) . "js/options-custom{$suffix}.js", array( 'jquery','wp-color-picker','hoot-options-media-uploader' ), Hoot_Options::VERSION );
	}

	/**
	 * Builds out the options panel.
	 *
	 * If we were using the Settings API as it was intended we would use
	 * do_settings_sections here.  But as we don't want the settings wrapped in a table,
	 * we'll call our own custom hootoptions_fields.  See hoot-options-interface.php
	 * for specifics on how each individual field is generated.
	 *
	 * Nonces are provided using the settings_fields()
	 *
	 * @since 1.0.0
	 */
	function options_page() {
		$name = Hoot_Options::_get_option_name(); ?>

		<div class="hootoptions-intro-box">
			<div class="hootoptions-intro">
				<a class="hootoptions-intro-img" href="<?php echo esc_url( THEME_AUTHOR_URI ); ?>" /><img src="<?php echo trailingslashit( HOOT_IMAGES ) . 'logo.png'; ?>"></a>
				<div class="hootoptions-intro-message">
					<p><?php echo Hoot_Options_Interface::hootoptions_intro(); ?></p>
				</div>
			</div>
		</div>

		<div id="hootoptions-wrap" class="hootoptions wrap">

		<?php $menu = $this->menu_settings(); ?>
		<h2><?php echo esc_html( $menu['page_title'] ); ?></h2>

		<?php settings_errors( 'hoot-options' ); ?>

		<h2 class="nav-tab-wrapper">
			<?php echo Hoot_Options_Interface::hootoptions_tabs(); ?>
		</h2>

		<div id="hootoptions-box" class="metabox-holder">
			<div id="hootoptions" class="postbox">
				<form action="options.php" method="post">
				<?php settings_fields( $name ); ?>
				<?php Hoot_Options_Interface::hootoptions_fields(); /* Settings */ ?>
				<div id="hootoptions-submit">
					<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'responsive-brix' ); ?>" />
					<input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'responsive-brix' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'responsive-brix' ) ); ?>' );" />
					<div class="clear"></div>
				</div>
				</form>
			</div> <!-- / #container -->
		</div>
		<?php do_action( 'hootoptions_after' ); ?>
		</div> <!-- / .wrap -->

	<?php
	}

	/**
	 * Validate Options.
	 *
	 * This runs after the submit/reset button has been clicked and
	 * validates the inputs.
	 *
	 * @uses $_POST['reset'] to restore default options
	 *
	 * @since 1.0.0
	 * @param array $input Array of values inputted by user
	 */
	function validate_options( $input ) {

		/*
		 * Restore Defaults.
		 *
		 * In the event that the user clicked the "Restore Defaults"
		 * button, the options defined in the theme's options.php
		 * file will be added to the option for the active theme.
		 */

		if ( isset( $_POST['reset'] ) ) {
			add_settings_error( 'hoot-options', 'restore_defaults', __( 'Default options restored.', 'responsive-brix' ), 'updated fade' );
			return $this->get_default_values();
		}

		/*
		 * Update Settings
		 *
		 * This used to check for $_POST['update'], but has been updated
		 * to be compatible with the theme customizer introduced in WordPress 3.4
		 */

		$clean = array();
		$options = Hoot_Options::_hootoptions_options();
		foreach ( $options as $option ) {
			$validated = $this->validate_option( $input, $option );
			if ( is_array( $validated ) )
				$clean = array_merge( $clean, $validated);
		}

		// Hook to run after validation
		do_action( 'hootoptions_after_validate', $clean );

		return $clean;
	}

	/**
	 * Validate Single Options.
	 *
	 * @since 1.1.0
	 * @param array $input Array of values inputted by user
	 * @param array $option Single option array
	 * @return NULL|array Clean array of ( 'id' => 'validated value' )
	 */
	static function validate_option( $input, $option ) {

		if ( ! isset( $option['id'] ) ) {
			return;
		}

		if ( ! isset( $option['type'] ) || $option['type'] == 'heading' || $option['type'] == 'subheading' || $option['type'] == 'info' || $option['type'] == 'html' || $option['type'] == 'import' || $option['type'] == 'export' ) {
			return;
		}

		$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

		// Set checkbox to false if it wasn't sent in the $_POST
		if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
			$input[$id] = false;
		}

		// Set each item in the multicheck to false if it wasn't sent in the $_POST
		if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
			foreach ( $option['options'] as $key => $value ) {
				$input[$id][$key] = false;
			}
		}

		// For a value to be submitted to database it must pass through a sanitization filter
		if ( has_filter( 'hoot_of_sanitize_' . $option['type'] ) ) {
			$clean[$id] = apply_filters( 'hoot_of_sanitize_' . $option['type'], $input[$id], $option );
			return $clean;
		}

		return;

	}

	/**
	 * Display message when options have been saved
	 *
	 * @since 1.0.0
	 */
	function save_options_notice() {
		$errors = get_settings_errors( 'hoot-options' );
		// Use $set to avoid double 'success' messages on first time save to database
		$set = false;
		foreach ( $errors as $error ) {
			if ( $error['setting'] == 'hoot-options' && $error['code'] == 'save_options' )
				$set = true;
		}
		if ( !$set )
			add_settings_error( 'hoot-options', 'save_options', __( 'Options saved.', 'responsive-brix' ), 'updated fade' );
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 * 
	 * @since 1.0.0
	 * @return array Re-keyed options configuration array.
	 */
	function get_default_values() {
		$output = array();
		$config = Hoot_Options::_hootoptions_options();
		foreach ( (array) $config as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}
			if ( ! isset( $option['std'] ) ) {
				continue;
			}
			if ( ! isset( $option['type'] ) ) {
				continue;
			}
			if ( has_filter( 'hoot_of_sanitize_' . $option['type'] ) ) {
				$output[ $option['id'] ] = apply_filters( 'hoot_of_sanitize_' . $option['type'], $option['std'], $option );
			}
		}
		return $output;
	}

}