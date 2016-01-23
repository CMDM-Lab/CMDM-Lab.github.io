<?php
$top_class = hoot_widget_border_class( $border, 0, 'topborder-');
$bottom_class = hoot_widget_border_class( $border, 1, 'bottomborder-');
?>

<div class="cta-widget-wrap <?php echo sanitize_html_class( $top_class ); ?>">
	<div class="cta-widget-box <?php echo sanitize_html_class( $bottom_class ); ?>">
		<div class="cta-widget">
			<?php if ( !empty( $headline ) ) { ?>
				<h3 class="cta-headine"><?php echo do_shortcode( $headline ); ?></h3>
			<?php } ?>
			<?php if ( !empty( $description ) ) { ?>
				<div class="cta-description"><?php echo do_shortcode( wpautop( $description ) ); ?></div>
			<?php } ?>
			<?php if ( !empty( $url ) ) { ?>
				<a class="cta-widget-button button button-large border-box" href="<?php echo esc_url( $url ); ?>"><?php echo $button_text; ?></a>
			<?php } ?>
		</div>
	</div>
</div>