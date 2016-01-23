<?php
/*
Template Name: Widgetized Template
*/

get_header(); // Loads the header.php template. ?>

<main <?php hoot_attr( 'page_template_content' ); ?>>

	<?php
	// Get Sections List
	$sections = hoot_get_option( 'widgetized_template_sections' );
	$sortlist = hoot_map_sortlist( $sections );
	extract( $sortlist, EXTR_PREFIX_ALL, 'sections' );

	// Get Highlight
	$area_highlight = hoot_get_option( 'widgetized_highlight_template_area' );

	// Display Each Section according to ther sort order.
	foreach ( $sections_order as $key => $order ) :
		if ( !empty( $sections_display[ $key ] ) ):
			$highlight_class = ( is_array( $area_highlight ) && !empty( $area_highlight[ $key ] ) ) ? 'area-highlight' : '';
			switch( $key ):

				// Display Widget Area A/B/C
				case 'area_a': case 'area_b': case 'area_c':

					if ( ! is_active_sidebar( 'widgetized-template-' . $key ) )
						continue; ?>

					<div id="widgetized-template-<?php echo sanitize_html_class( $key ); ?>" class="widgetized-template-area <?php echo $highlight_class; ?>">
						<div class="grid">
							<div class="grid-row">
								<div class="grid-span-12">
									<?php dynamic_sidebar( 'widgetized-template-' . $key ); ?>
								</div>
							</div>
						</div>
					</div>

					<?php break;

				// Display Widget Area D
				case 'area_d':

					$area_d_left = is_active_sidebar( 'widgetized-template-area_d_1' );
					$area_d_right = is_active_sidebar( 'widgetized-template-area_d_2' );

					if ( !$area_d_left && !$area_d_right ) { // None has widgets
						continue;
					} elseif ( $area_d_left && $area_d_right ) { // Both has widgets
						$structure = hoot_2_col_width_to_span( hoot_get_option( 'widgetized_template_area_d_width' ) );
						$area_d_left_span = $structure[0];
						$area_d_right_span = $structure[1];
					} else { // Only 1 has widgets.
						$area_d_left_span = $area_d_right_span = 'grid-span-12';
					} ?>

					<div id="widgetized-template-<?php echo sanitize_html_class( $key ); ?>" class="widgetized-template-area <?php echo $highlight_class; ?>">
						<div class="grid">
							<div class="grid-row"><?php

								if ( $area_d_left ): ?>
									<div id="widgetized-template-area_d_left" class="<?php echo $area_d_left_span; ?>">
										<?php dynamic_sidebar( 'widgetized-template-area_d_1' ); ?>
									</div><?php
								endif;

								if ( $area_d_right ): ?>
									<div id="widgetized-template-area_d_right" class="<?php echo $area_d_right_span; ?>">
										<?php dynamic_sidebar( 'widgetized-template-area_d_2' ); ?>
									</div><?php
								endif; ?>

							</div>
						</div>
					</div>

					<?php break;

				// Display Page Content
				case 'content':
					wp_reset_query(); ?>

					<div id="widgetized-template-page-content" class="widgetized-template-area <?php echo $highlight_class; ?>">
						<div class="grid">
							<div class="grid-row">
								<div class="entry-content grid-span-12">
									<?php the_content(); ?>
								</div>
							</div>
						</div>
					</div>

					<?php break;

				// Display HTML Slider
				case 'slider_html': 
					$slider_width = hoot_get_option( 'wt_html_slider_width' );
					$slider_grid = ( 'stretch' == $slider_width ) ? 'grid-stretch' : 'grid'; ?>

					<div id="widgetized-template-html-slider" class="widgetized-template-area <?php echo $highlight_class; ?>">
						<div class="widgetized-template-slider <?php echo $slider_grid; ?>">
							<div class="grid-row">
								<div class="grid-span-12">
									<?php
									global $hoot_theme;

									/* Reset any previous slider */
									$hoot_theme->slider = array();
									$hoot_theme->sliderSettings = array( 'class' => 'wt-slider', 'min_height' => hoot_get_option( 'wt_html_slider_min_height' ) );

									/* Create slider object */
									$slides = hoot_get_option( 'wt_html_slider' );
									foreach ( $slides as $slide ) {
										if ( !empty( $slide['image'] ) || !empty( $slide['content'] ) || !empty( $slide['url'] ) ) {
											$hoot_theme->slider[] = $slide;
										}
									}

									/* Display Slider Template */
									get_template_part( 'template-parts/slider-html' );
									?>
								</div>
							</div>
						</div>
					</div>

					<?php break;

				// Display Image Slider
				case 'slider_img': 
					$slider_width = hoot_get_option( 'wt_img_slider_width' );
					$slider_grid = ( 'stretch' == $slider_width ) ? 'grid-stretch' : 'grid'; ?>

					<div id="widgetized-template-img-slider" class="widgetized-template-area <?php echo $highlight_class; ?>">
						<div class="widgetized-template-slider <?php echo $slider_grid; ?>">
							<div class="grid-row">
								<div class="grid-span-12">
									<?php
									global $hoot_theme;

									/* Reset any previous slider */
									$hoot_theme->slider = array();
									$hoot_theme->sliderSettings = array( 'class' => 'wt-slider' );

									/* Create slider object */
									$slides = hoot_get_option( 'wt_img_slider' );
									foreach ( $slides as $slide ) {
										if ( !empty( $slide['image'] ) ) {
											$hoot_theme->slider[] = $slide;
										}
									}

									/* Display Slider Template */
									get_template_part( 'template-parts/slider-image' );
									?>
								</div>
							</div>
						</div>
					</div>

					<?php break;

			endswitch;
		endif;
	endforeach;
	?>

</main><!-- #content -->

<?php get_footer(); // Loads the footer.php template. ?>