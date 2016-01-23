<?php
global $hoot_theme;

if ( !isset( $hoot_theme->slider ) || empty( $hoot_theme->slider ) )
	return;

// Ok, so we have a slider to show. Now, lets display the slider.

/* Create Data attributes for javascript settings for this slider */
$atts = $class = $gridstyle = '';
if ( isset( $hoot_theme->sliderSettings ) && is_array( $hoot_theme->sliderSettings ) ) {
	if ( isset( $hoot_theme->sliderSettings['class'] ) )
		$class .= ' ' . sanitize_html_class( $hoot_theme->sliderSettings['class'] );
	if ( isset( $hoot_theme->sliderSettings['id'] ) )
		$atts .= ' id="' . sanitize_html_class( $hoot_theme->sliderSettings['id'] ) . '"';
	foreach ( $hoot_theme->sliderSettings as $setting => $value )
		$atts .= ' data-' . $setting . '="' . esc_attr( $value ) . '"';
	if ( isset( $hoot_theme->sliderSettings['min_height'] ) ) {
		$hoot_theme->sliderSettings['min_height'] = intval( $hoot_theme->sliderSettings['min_height'] );
		if ( !empty( $hoot_theme->sliderSettings['min_height'] ) )
			$gridstyle .= hoot_css_rule( 'height', $hoot_theme->sliderSettings['min_height'] . 'px;' ); // use height instead of min-height (firefox) http://stackoverflow.com/questions/7790222/
	}
}

/* Start Slider Template */
$slide_count = 1; ?>
<ul class="lightSlider<?php echo $class; ?>"<?php echo $atts; ?>><?php
	foreach ( $hoot_theme->slider as $slide ) :
		if ( !empty( $slide['image'] ) || !empty( $slide['content'] ) ) :

			$slide_bg = hoot_css_background( $slide['background'] );
			$is_custom_bg = ( isset( $slide['background']['type'] ) && 'custom' == $slide['background']['type'] ) ? ' is-custom-bg ' : '';
			$column = ( !empty( $slide['image'] ) && !empty( $slide['content'] ) ) ? ' column-1-2 ' : ' column-1-1 ';
			$slide['button'] = empty( $slide['button'] ) ? __('Know More', 'responsive-brix') : $slide['button'];

			?><li class="lightSlide hootslider-html-slide hootslider-html-slide-<?php echo $slide_count; $slide_count++; ?> <?php echo $is_custom_bg; ?>" style="<?php echo esc_attr( $slide_bg ); ?>">
				<div class="grid"<?php if ( !empty( $gridstyle ) ) echo ' style="' . $gridstyle . '"'; ?>>

					<?php if ( !empty( $slide['content'] ) || !empty( $slide['url'] ) ) { ?>
						<div class="<?php echo $column; ?> hootslider-html-slide-column hootslider-html-slide-left">
							<?php if ( !empty( $slide['content'] ) ) { ?>
								<div class="hootslider-html-slide-content linkstyle">
									<?php echo wp_kses_post( wpautop( $slide['content'] ) ); ?>
								</div>
							<?php } ?>
							<?php if ( !empty( $slide['url'] ) ) { ?>
								<div class="hootslider-html-slide-link"><a class="hootslider-html-slide-button button" href="<?php echo esc_url( $slide['url'] ); ?>"><?php echo $slide['button']; ?></a></div>
							<?php } ?>
						</div>
					<?php } ?>

					<?php if ( !empty( $slide['image'] ) ) { ?>
						<div class="<?php echo $column; ?> hootslider-html-slide-column hootslider-html-slide-right">
							<img class="hootslider-html-slide-img" src="<?php echo esc_url( $slide['image'] ); ?>">
						</div>
					<?php } ?>

					<div class="clearfix"></div>
				</div>
			</li><?php

		endif;
	endforeach; ?>
</ul>