<?php
// Return if no boxes to show
if ( empty( $boxes ) || !is_array( $boxes ) )
	return;

// Get border classes
$top_class = hoot_widget_border_class( $border, 0, 'topborder-');
$bottom_class = hoot_widget_border_class( $border, 1, 'bottomborder-');

// Get total columns and set column counter
$columns = ( intval( $columns ) >= 1 && intval( $columns ) <= 5 ) ? intval( $columns ) : 3;
$column = 1;

// Get an array of page ids for custom WP Query
$page_ids = array();
foreach ( $boxes as $key => $box ) {
	$box['page'] = ( isset( $box['page'] ) ) ? intval( $box['page'] ) : '';
	if ( !empty( $box['page'] ) )
		$page_ids[] = $box['page'];
}

// Style-3 exceptions: doesnt work great with icons of 'None' style. So revert to Style-2 for this scenario.
if ( $icon_style == 'none' && $style == 'style3' ) $style = 'style2';
// Style-3 exceptions: doesnt work great with images. So revert to Style-2 for this scenario.
if ( $image && $style == 'style3' ) $style = 'style2';

// Create a custom WP Query
$content_blocks_query = new WP_Query( array( 'post_type' => 'page', 'post__in' => $page_ids, 'posts_per_page' => -1 ) );

// Temporarily remove read more links from excerpts
add_filter( 'hoot_readmore', 'hoot_content_blocks_widget_readmore' );
if ( !function_exists( 'hoot_content_blocks_widget_readmore' ) ) {
	function hoot_content_blocks_widget_readmore(){ return ''; }
}
?>

<div class="content-blocks-widget-wrap <?php echo sanitize_html_class( $top_class ); ?>">
	<div class="content-blocks-widget-box <?php echo sanitize_html_class( $bottom_class ); ?>">
		<div class="content-blocks-widget <?php echo sanitize_html_class( 'content-blocks-widget-' . $style ); ?>">

			<?php foreach ( $boxes as $key => $box ) : ?>
				<div class="content-block-column <?php echo 'column-1-' . $columns; ?> <?php echo sanitize_html_class( 'content-block-' . $style ); ?>">

					<?php $box['page'] = ( isset( $box['page'] ) ) ? intval( $box['page'] ) : ''; ?>
					<?php if ( !empty( $box['page'] ) ) :

						global $post;
						$has_image = $has_icon = false;

						foreach( $content_blocks_query->posts as $post ) :
							if ( intval( $box['page'] ) == $post->ID ) :

								setup_postdata( $post );
								if ( $image && has_post_thumbnail() )
									$has_image = true;
								if ( !$image && !empty( $box['icon'] ) )
									$has_icon = true;

								if ( !empty( $excerpt ) && empty( $box['url'] ) ) {
									$linktag = '<a href="' . get_permalink() . '">';
									$linktagend = '</a>';
								} elseif ( !empty( $box['url'] ) ) {
									$linktag = '<a href="' . esc_url( $box['url'] ) . ' ">';
									$linktagend = '</a>';
								} else {
									$linktag = $linktagend = '';
								} ?>

								<?php
								$block_class = ( !$has_image && !$has_icon ) ? 'no-highlight' : ( ( $style == 'style2' ) ? 'highlight-typo' : '' );
								$block_class = ( $style == 'style3' ) ? 'highlight-typo' : $block_class; ?>
								<div class="content-block <?php echo $block_class; ?>">

									<?php
									if ( $has_image ) : ?>
										<div class="content-block-visual content-block-image">
											<?php
											echo $linktag;
												if ( $style == 'style4' ) {
													switch ( $columns ) {
														case 1: $image_col_width = 2; break;
														case 2: $image_col_width = 4; break;
														default: $image_col_width = 5;
													}
												} else {
													$image_col_width = $columns;
												}
												hoot_post_thumbnail( 'content-block-img', 'column-1-' . $image_col_width );
											echo $linktagend;
											?>
										</div><?php

									elseif ( $has_icon ):
										$contrast_class = ( 'none' == $icon_style ) ? '' : ' contrast-typo ';
										$contrast_class = ( 'style3' == $style ) ? ' enforce-typo ' : $contrast_class; ?>
										<div class="content-block-visual content-block-icon <?php echo 'icon-style-' . esc_attr( $icon_style ); echo $contrast_class; ?>">
											<?php echo $linktag; ?>
												<i class="fa <?php echo sanitize_html_class( $box['icon'] ); ?>"></i>
											<?php echo $linktagend; ?>
										</div><?php
									endif; ?>

									<div class="content-block-content<?php
										if ( $has_image ) echo ' content-block-content-hasimage';
										elseif ( $has_icon ) echo ' content-block-content-hasicon';
										else echo ' no-visual';
										?>">
										<h4><?php 
											echo $linktag;
												the_title();
											echo $linktagend;
										?></h4>
										<div class="content-block-text"><?php
											if ( empty( $excerpt ) )
												the_content();
											else
												the_excerpt();
											if ( $linktag ) {
												$linktext = ( !empty( $box['link'] ) ) ? $box['link'] : hoot_get_option('read_more');
												$linktext = ( empty( $linktext ) ) ? sprintf( __( 'Read More %s', 'responsive-brix' ), '&rarr;' ) : $linktext;
												echo '<p class="more-link">' . $linktag . $linktext . $linktagend . '</p>';
											}
										?></div>
									</div>

								</div><?php

							break;
							endif;
						endforeach;

						wp_reset_postdata(); ?>

					<?php endif; ?>

				</div>
				<?php $column++;
				if ( $column > $columns ) {
					$column = 1;
					echo '<div class="clearfix"></div>';
				} ?>
			<?php endforeach; ?>

			<div class="clearfix"></div>
		</div>
	</div>
</div>

<?php
// Reinstate read more links to excerpts
remove_filter( 'hoot_readmore', 'hoot_content_blocks_widget_readmore' );
?>