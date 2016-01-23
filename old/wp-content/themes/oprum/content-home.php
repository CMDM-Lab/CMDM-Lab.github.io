<?php
/**
 * @package Oprum
 */
$options = get_option('oprum_theme_settings');
?>

<!-- Homepage Tagline -->

<div id="home-tagline" style="background: <?php echo get_theme_mod( 'home_tagline_bgcolor', '#e8e09d' ); ?> url(<?php echo get_theme_mod( 'home_tagline_bgimg', get_template_directory_uri().'/img/tagline.jpg' ); ?>); background-position: 50% 50%; background-size:100%;">

<?php $placement = get_theme_mod( 'home_tagline', '<h1>Home Tagline</h1>' ); if (!empty($placement)) : ?>
<div class="tagline-txt">
	<?php echo do_shortcode( get_theme_mod( 'home_tagline', '<h1>Home Tagline</h1>' ) ); ?>
</div>
<?php endif; ?>

</div><!--#home-tagline-->

<!-- Head Text -->
<?php $placement = get_theme_mod( 'sub_tagline', 'Multi-purpose theme for <a href="#">creativity and business</a>' ); if (!empty($placement)) : ?>
	<div id="home-txt">
		<span><?php echo get_theme_mod( 'sub_tagline', 'Multi-purpose theme for <a href="#">creativity and business</a>' ); ?></span>
	</div>
<?php endif; ?>

<!-- Featured Page -->
<?php
$pages = array();
$mod = get_theme_mod( 'featured_page' );

	if ( 'page-none-selected' != $mod ) {
		$pages[] = $mod;

$args = array(
	'post_type' => 'page',
	'post__in' => $pages,
	'orderby' => 'post__in'
);
$query = new WP_Query( $args );
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
?>
<section id="pagefeature">
	<div class="home-section clearfix">
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>	
	</div>
<?php
		endwhile;
	endif; 
	wp_reset_postdata();
?>
</section><!--#pagefeature-->

<?php } //'page-none-selected' ?>

 <!-- Sticky Post-->

    <?php
        global $post;
        $args = array(
            'post__in'  => get_option( 'sticky_posts' )
        );
        $sticky_posts = get_posts($args);
    ?>
<?php if( get_option( 'sticky_posts' ) ) { ?>
<?php
global $count; //sticky posts number for left-right align
?>
    <section id="home-sticky">
<div class="home-section">
        <?php
        foreach($sticky_posts as $post) : setup_postdata($post);
        ?>
<?php $count ++; // left-right align
?>

	<div class="col"<?php if ( $count == 2 ) : echo 'style="float: right;"'; else : echo 'style="margin-left: 0;"'; endif; ?>>
		<div class="post-thumb" style="background: #FFF url(<?php $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'oprum-medium' ); echo $thumbnail[0]; ?>) no-repeat; background-position: 50% 50%; background-size: 100%;">
		</div>
	</div><!--/col-->

        <div class="col"<?php if ( $count == 2 ) : echo 'style="margin-left: 0;"'; else : echo 'style="float: right;"'; endif; ?>>

           	 <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>

	<?php echo mb_substr( strip_tags( get_the_content() ), 0, 600 ) . '...'; //replacement excerpt ?>

        </div><!--/col-->

<?php if ( $count == 2 ) : $count = 0; endif; ?>

<div class="clearfix"></div>        
        <?php
	endforeach;
	wp_reset_postdata();
	?>
</div><!-- .home-section -->
    </section><!-- /sticky -->

<?php } //if($sticky_posts) ?>


 <!-- Recent Blog Posts -->

<?php
$numberposts = get_theme_mod( 'number_homeposts' );
if ( $numberposts != 0 || !empty($numberposts) ) { //dont show blog posts
?>

    <?php
        global $post;

        $args = array(
            'post_type' => 'post',
            'post__not_in' => get_option( 'sticky_posts' ),
            'numberposts' => $numberposts
        );
        $blog_posts = get_posts($args);
    ?>
    <?php if ( $blog_posts ) { ?>
  
        <section id="home-posts">
	<div class="recent-home-posts">
<h3 class="widget-title"><span><?php echo get_theme_mod( 'recentposts_title', 'Recent Posts' ); ?></span></h3>
	</div>

<div class="row clearfix">
<div class="grid4">
            <?php
            foreach($blog_posts as $post) : setup_postdata($post);
            ?>            

<div class="col">
<?php if ( has_post_thumbnail() && !has_post_format() ) : ?>
	<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
		<div class="img-home-post">
		<?php the_post_thumbnail( 'oprum-medium' ); ?>
		</div>
	</a>
<?php endif; //has_post_thumbnail ?>
<?php //endif; //has_post_format ?>

                <div class="home-posts-description">
<?php if ( has_post_format(array('aside', 'quote', 'link', 'chat', 'image', 'video', 'audio', 'gallery','status')) ) : ?>
		<?php if ( has_post_format( array('aside', 'quote', 'link', 'status') ) ) { ?>
	<div class="metka genericon genericon-<?php echo get_post_format(); ?>"></div>
		<?php } ?>   
	<?php the_content(); ?>                   
<?php  else : ?>
                <h3><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php echo the_title(); ?></a></h3>
	<?php the_excerpt(); ?>
<?php endif; //has_post_format ?>
                </div><!-- /home-entry-description -->
<span class="entry-meta"><?php the_date(); ?></span>
            </div><!--/col-->
            <!-- /home-entry-->
            
<?php endforeach; wp_reset_postdata(); //$blog_posts as $post ?>
</div>
</div><!--//row-->
        </section><!-- /home-posts -->
	<?php } // if ( $blog_posts ) ?>

<?php } //$numberposts ?>

<!--Home Widget-->

<section id="home-widget">
<div class="home-section clearfix">
	<div class="grid2">
		<?php if ( ! dynamic_sidebar( 'home-bottom' ) ) : ?>
		<?php endif; // end widget area ?>
	</div>
</div>
</section>
        <!-- /home-widget-->