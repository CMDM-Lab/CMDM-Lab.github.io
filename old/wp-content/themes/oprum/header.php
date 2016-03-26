<?php
/**
 * The Header Theme
 * @package Oprum
 */
$options = get_option('oprum_theme_settings');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.min.js" type="text/javascript"></script>
		<![endif]-->
	<script>(function(){document.documentElement.className='js'})();</script>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="wrap-header" class="wrap hfeed site">
	<?php do_action( 'before' ); ?>
<?php
	$home_image = get_header_image();
	$header_text_color = get_header_textcolor();
	$logo = get_theme_mod( 'logo_upload' );
?>
	<header id="masthead" class="site-header" role="banner">
<div class="site-branding clearfix" style="background: <?php echo esc_attr( get_theme_mod( 'oprum_headerbg_color', '#f16272' ) ); ?><?php if( !empty($home_image) ) { ?> url(<?php echo esc_url( $home_image );?>) no-repeat 50% 0<?php } ?>;">

	<div id="logo">
<?php if ( !is_home() ) : ?>
<?php if ( !empty($logo) ) : ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" style="color:#<?php echo esc_attr( $header_text_color ); ?>">
	<img src="<?php echo esc_url( get_theme_mod( 'logo_upload' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="logo-img" />
	</a>
<?php endif; //!empty ?>
	<div class="title-group">
	<h1 class="site-title">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" style="color:#<?php echo esc_attr( $header_text_color ); ?>">
	<?php bloginfo( 'name' ); ?>
	</a>
	</h1>
	<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
	</div>
<?php else : ?>
<?php if ( !empty($logo) ) : ?>
	<img src="<?php echo esc_url( get_theme_mod( 'logo_upload' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="logo-img" />
<?php endif; //!empty ?>
	<div class="title-group">
	<h1 class="site-title" style="color:#<?php echo esc_attr( $header_text_color ); ?>"><?php bloginfo( 'name' ); ?></h1>
	<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
	</div>
<?php endif; //!is_front_page() ?>
	</div><!--#logo-->

</div><!--site-branding-->

<nav id="site-navigation" class="main-navigation" role="navigation">
<h1 class="menu-toggle"><?php _e( 'Menu', 'oprum' ); ?></h1>			
<!-- navigation -->
<?php 
	if ( has_nav_menu('primary') ) {
wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu',  'container'       => 'div', 'container_class' => 'menu-main', ) );
	}else {
?>
<div class="menu-main">
	<ul class="nav-menu">
<?php wp_list_pages('depth=1&title_li='); ?>
	</ul>
</div>
<?php
	} // has_nav_menu
?>
</nav><!-- #site-navigation -->

	</header><!-- #masthead -->
	</div><!-- #wrap-header -->

<div id="wrap-content" class="wrap">
	<div id="content" class="site-content">