<?php
$site_info = hoot_get_option( 'site_info' );
$site_info = str_replace( "<!--year-->" , date_i18n( 'Y' ) , $site_info );
if ( !empty( $site_info ) ) :
?>
	<div id="post-footer" class="grid-stretch highlight-typo linkstyle">
		<div class="grid">
			<div class="grid-row">
				<div class="grid-span-12">
					<p class="credit small">
						<?php
						if ( trim( $site_info ) == '<!--default-->' ) {
							printf(
								/* Translators: 1 is Theme name/link, 2 is WordPress name/link, 3 is site name/link */
								__( 'Designed using %1$s. Powered by %2$s.', 'responsive-brix' ),
								hoot_get_wp_theme_link( apply_filters( 'hoot_footer_wp_theme_link', 'https://wordpress.org/themes/responsive-brix/' ) ), hoot_get_wp_link(), hoot_get_site_link()
							);
						} else {
							echo $site_info;
						} ?>
					</p><!-- .credit -->
				</div>
			</div>
		</div>
	</div>
<?php
endif;
?>