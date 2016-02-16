<?php
/**
 * Post Format Quote Widget
 * @package Oprum
 */
add_action('widgets_init', create_function('', 'register_widget("Oprum_Post_Format_Quote_Widget");'));

class Oprum_Post_Format_Quote_Widget extends WP_Widget {
    function __construct() {
        parent::WP_Widget('post_format_quote_widget', 'Post Format Quote', array('description'=>__('Post Format Quote', 'oprum') ));
    }
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

	$title  = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	$quote  = empty($instance['quote']) ? ' ' : apply_filters('widget_quote', $instance['quote']);

	/**
	 * Output the HTML for this widget.
	 */

        echo $before_widget;

        if (!empty($title)) { echo $before_title . $title . $after_title; };

        echo '<div id="quote-post-format-widget">';
?>
<?php
		$custom = new WP_Query( array(
			'order'          => 'DESC',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'post_status'    => 'publish',
			'post__not_in'   => get_option( 'sticky_posts' ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'post_format',
					'terms'    => array( "post-format-quote" ),
					'field'    => 'slug',
					'operator' => 'IN',
				),
			),
		) );

		if ( $custom->have_posts() ) :
			while ( $custom->have_posts() ) :
			$custom->the_post();
			$tmp_more = $GLOBALS['more'];
			$GLOBALS['more'] = 0;
?>

	<article <?php post_class(); ?>>
<?php
	if ( has_post_format( 'quote' ) ) :
		the_content();
	endif;

printf( '<span class="entry-meta"><time class="entry-date" datetime="%2$s">%3$s</time></span>',
	esc_url( get_permalink() ),
	esc_attr( get_the_date( 'c' ) ),
	esc_html( get_the_date() )
	);
?>
	</article>
		<?php endwhile; 
			endif;
			// Reset the post globals as this query will have stomped on it.
			wp_reset_postdata(); ?>

<?php
        echo '<p><a href="' . get_post_format_link('quote') . '" class="widget-format-link"><span class="screen-reader-text">' . $quote . '</span></a></p>';
        echo '</div>';
        echo $after_widget;
    }
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title']  = strip_tags($new_instance['title']);
        //$instance['quote']  = strip_tags($new_instance['quote']);
        return $instance;
    }
    function form($instance) {
        $defaults = array(
            'title' => __('Post Format', 'oprum'), 
            'quote' => __('Quote', 'oprum'), 
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = strip_tags($instance['title']);
        //$quote = strip_tags($instance['quote']); ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'oprum'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

<?php }
}