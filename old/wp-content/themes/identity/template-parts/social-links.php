<?php
/**
 * The social links template.
 *
 * Displays the social menu items.
 *
 * @package Identity
 */

wp_nav_menu( 
	array( 
		'theme_location'	=> 'social',
		'container_class' 	=> 'social-menu-container',
		'menu_class'      	=> 'social-menu-items',
		'link_before'	 	=> '<span class="screen-reader-text">',
		'link_after'		=> '</span>',
		'depth'				=> 1,
	) 
);
