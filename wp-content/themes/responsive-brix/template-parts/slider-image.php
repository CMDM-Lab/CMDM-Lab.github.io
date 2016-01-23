<?php
global $hoot_theme;

if ( !isset( $hoot_theme->slider ) || empty( $hoot_theme->slider ) )
	return;

// Ok, so we have a slider to show. Now, lets display the slider.

/* Create Data attributes for javascript settings for this slider */
$atts = $class = '';
if ( isset( $hoot_theme->sliderSettings ) && is_array( $hoot_theme->sliderSettings ) ) {
	if ( isset( $hoot_theme->sliderSettings['class'] ) )
		$class .= ' ' . sanitize_html_class( $hoot_theme->sliderSettings['class'] );
	if ( isset( $hoot_theme->sliderSettings['id'] ) )
		$atts .= ' id="' . sanitize_html_class( $hoot_theme->sliderSettings['id'] ) . '"';
	foreach ( $hoot_theme->sliderSettings as $setting => $value )
		$atts .= ' data-' . $setting . '="' . esc_attr( $value ) . '"';
}

/* Start Slider Template */
$slide_count = 1; ?>
<ul class="lightSlider<?php echo $class; ?>"<?php echo $atts; ?>><?php
	foreach ( $hoot_theme->slider as $slide ) :
		if ( !empty( $slide['image'] ) ) :

			?><li class="lightSlide hootslider-image-slide hootslider-image-slide-<?php echo $slide_count; $slide_count++; ?>">
				<?php if ( !empty( $slide['url'] ) ) echo '<a href="' . esc_url( $slide['url'] ) . '">'; ?>
					<img src="<?php echo esc_url( $slide['image'] ); ?>" />
				<?php if ( !empty( $slide['url'] ) ) echo '</a>'; ?>
				<?php if ( !empty( $slide['caption'] ) ) : ?>
					<h6 class="invert-typo hootslider-image-slide-caption"><?php echo esc_html( $slide['caption'] ); ?></h6>
				<?php endif; ?>
			</li><?php

		endif;
	endforeach; ?>
</ul>