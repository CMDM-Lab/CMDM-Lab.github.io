<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Identity
 */

$header = identity_header_image();

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body id="body" <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'identity' ); ?></a>

	<header id="masthead" class="site-header" role="banner">
		<div class="header-hidden">
			<nav id="mobile-navigation" class="main-navigation" role="navigation" aria-label="<?php _e( 'Mobile Menu Navigation', 'identity' ); ?>">
				<div class="menu-title"><h1><?php _e( 'Menu', 'identity' ); ?></h1></div>
				<?php if ( has_nav_menu( 'primary' ) ) { get_template_part( 'template-parts/navigation' ); } ?>

				<div id="mobile-search" class="search-container">
					<form method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
						<input type="search" placeholder="<?php _e('Enter your search query &#8230', 'identity'); ?>" name="s" id="s" /> 
						<button class="search-submit"><span class="screen-reader-text">Search</span><span class="genericon genericon-search"></span></button>
					</form>
				</div><!-- #mobile-search -->
			</nav><!-- #mobile-navigation -->

			<div id="desktop-search" class="search-container">
				<form method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
					<input type="search" placeholder="<?php _e('Enter your search query &#8230', 'identity'); ?>" name="s" id="s" class="search-input" /> 
					<button class="search-submit"><span class="screen-reader-text">Search</span><span class="genericon genericon-search" aria-hidden="true"></span></button>
				</form>
			</div><!-- #desktop-search -->
		</div><!-- .header-hidden -->

		<div class="header-shown">
			<?php if ( ! empty( $header ) ) { ?>
				<div class="header-background" style="background-image:url(<?php echo esc_url( $header ); ?>)"></div>
				<div class="header-background-overlay"></div>
			<?php } ?>
			<div id="masthead-inner" class="header-top">
				<div class="site-branding">
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				</div><!-- .site-branding -->

				<div class="menu-toggle-container">
					<button class="menu-toggle" aria-controls="menu" aria-expanded="false">
						<span class="screen-reader-text"><?php _e( 'Menu Toggle', 'identity' ); ?></span>
						<span class="lines" aria-hidden="true"></span>
					</button>
				</div><!-- .menu-toggle-container -->

				<nav id="desktop-navigation" class="main-navigation" role="navigation" aria-label="<?php _e( 'Desktop Menu Navigation', 'identity' ); ?>">
					<?php if ( has_nav_menu( 'primary' ) ) { get_template_part( 'template-parts/navigation' ); } ?>
					<button class="search-toggle">
						<span class="screen-reader-text"><?php _e('Search Toggle', 'identity'); ?></span>
						<span class="genericon genericon-search" aria-hidden="true"></span>
					</button>
				</nav><!-- #desktop-navigation -->
			</div><!-- .header-top -->

			<?php if ( identity_show_header_content() ) : ?>
				<div class="header-content">
					<?php 
					/**
					 * Display the header content.
					 * 1. Selected page.
					 * 2. User biography.
					 * 3. Site description (default).
					 **/
					$header_content = get_theme_mod( 'identity_header_content', 'default' );
					$user_biography = get_theme_mod( 'identity_user_biography', '0' );
					$page_selection = get_theme_mod( 'identity_page_selection', '0' );

					if ( 'page' == $header_content ) : ?>
						<div class="featured-page">
							<?php $the_query = new WP_Query( "page_id={$page_selection}"  );
								while ( $the_query->have_posts() ) :
									$the_query->the_post();
										the_title( '<h1 class="header-page-title">', '</h1>' );
										the_content();
								endwhile;
							wp_reset_postdata(); ?>
						</div>
						<?php if ( has_nav_menu( 'social' ) ) { get_template_part( 'template-parts/social-links' ); } ?>

					<?php elseif ( 'biography' == $header_content ) : ?>

						<div class="author-profile">
							<div class="author-avatar"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo get_avatar( $user_biography, 224, '', 'Gravatar logo or image of the author.' ); ?></a></div>
							<div class="author-info">
								<h1 class="author-name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo get_the_author_meta( 'display_name', $user_biography ); ?></a></h1>
								<h2 class="author-description"><?php echo get_the_author_meta( 'description', $user_biography ); ?></h2>
								<?php if ( has_nav_menu( 'social' ) ) { get_template_part( 'template-parts/social-links' ); } ?>
							</div>
						</div>

					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php identity_the_site_logo(); ?></a>
						<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
						<span class="social-default"><?php if ( has_nav_menu( 'social' ) ) { get_template_part( 'template-parts/social-links' ); } ?></span>
					<?php endif; ?>
				</div><!-- .header-content -->
			<?php endif; ?>
		</div><!-- .header-shown -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
