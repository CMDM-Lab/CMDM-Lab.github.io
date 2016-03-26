<?php
/**
 * The navigation menu.
 *
 * Displays both the mobile and desktop navigation menu.
 *
 * @package Identity
 */

wp_nav_menu( 
	array( 
		'theme_location'	=> 'primary',
		'container_class'	=> 'menu-container',
		'link_before'	 	=> '<span class="link-align">',
		'link_after'		=> '</span>',
		'depth'				=> 3,
	) 
);
