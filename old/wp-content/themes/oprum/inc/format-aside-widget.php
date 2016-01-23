<?php
/**
 * Post Format Aside Widget
 * @package Oprum
 */
add_action('widgets_init', create_function('', 'register_widget("Oprum_Post_Format_Aside_Widget");'));

class Oprum_Post_Format_Aside_Widget extends WP_Widget {
    function __construct() {
        parent::WP_Widget('post_format_aside_widget', 'Post Format Aside', array('description'=>__('Post Format Aside', 'oprum') ));
    }
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

	$title  = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	$aside  = empty($instance['aside']) ? ' ' : apply_filters('widget_aside', $instance['aside']);

	/**
	 * Output the HTML for this widget.
	 */

        echo $before_widget;

        if (!empty($title)) { echo $before_title . $title . $after_title; };

        echo '<div id="aside-post-format-widget">';
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
					'terms'    => array( "post-format-aside" ),
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
	if ( has_post_format( 'aside' ) ) :
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
        echo '<p><a href="' . get_post_format_link('aside') . '" class="widget-format-link"><span class="screen-reader-text">' . $aside . '</span></a></p>';
        echo '</div>';
        echo $after_widget;
    }
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title']  = strip_tags($new_instance['title']);
        return $instance;
    }
    function form($instance) {
        $defaults = array(
            'title' => __('Post Format', 'oprum'), 
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = strip_tags($instance['title']); ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'oprum'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

<?php }
}