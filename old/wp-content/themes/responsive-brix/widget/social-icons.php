<?php
// Return if no icons to show
if ( empty( $icons ) || !is_array( $icons ) )
	return;
?>

<div class="social-icons-widget <?php echo 'social-icons-' . esc_attr( $size ); ?>"><?php
	foreach( $icons as $key => $icon ) :
		if ( !empty( $icon['url'] ) && !empty( $icon['icon'] ) ) :
			?><a class="social-icons-icon <?php echo sanitize_html_class( $icon['icon'] ) . '-block'; ?>" href="<?php echo esc_url( $icon['url'] ); ?>" target="_blank">
				<i class="fa <?php echo sanitize_html_class( $icon['icon'] ); ?>"></i>
			</a><?php
		endif;
	endforeach; ?>
</div>