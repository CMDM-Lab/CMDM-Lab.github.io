<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package SKT IT Consultant
 */

get_header(); ?>

<div class="content-area">
    <div class="container">
   <div class="page_wrapper">        
       <section id="site-main" class="site-main content-part" >       
			<?php if ( have_posts() ) : ?>
                <header class="page-header">
                    <h1 class="page-title">
                        <?php
                            if ( is_category() ) :
                                single_cat_title();

                            elseif ( is_tag() ) :
                                single_tag_title(_e('Tag: ', 'itconsultant'));

                            elseif ( is_author() ) :
                                /* Queue the first post, that way we know
                                 * what author we're dealing with (if that is the case).
                                */
                                the_post();
                                printf( __( 'Author: %s', 'itconsultant' ), '<span class="vcard">' . get_the_author() . '</span>' );
                                /* Since we called the_post() above, we need to
                                 * rewind the loop back to the beginning that way
                                 * we can run the loop properly, in full.
                                 */
                                rewind_posts();

                            elseif ( is_day() ) :
                                printf( __( 'Day: %s', 'itconsultant' ), '<span>' . get_the_date() . '</span>' );
    
                            elseif ( is_month() ) :
                                printf( __( 'Month: %s', 'itconsultant' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );
    
                            elseif ( is_year() ) :
                                printf( __( 'Year: %s', 'itconsultant' ), '<span>' . get_the_date( 'Y' ) . '</span>' );
    
                            elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
                                _e( 'Asides', 'itconsultant' );
    
                            elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
                                _e( 'Images', 'itconsultant');
    
                            elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
                                _e( 'Videos', 'itconsultant' );
    
                            elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :
                                _e( 'Quotes', 'itconsultant' );
    
                            elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
                                _e( 'Links', 'itconsultant' );
    
                            else :
                                _e( 'Archives', 'itconsultant' );
    
                            endif;
                        ?>
                    </h1>
                    <?php
                        // Show an optional term description.
                        $term_description = term_description();
                        if ( ! empty( $term_description ) ) :
                            printf( '<div class="taxonomy-description">%s</div>', $term_description );
                        endif;
                    ?>
                </header><!-- .page-header -->
				<div class="blog-post">
					<?php /* Start the Loop */ ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part( 'content', get_post_format() ); ?>
                    <?php endwhile; ?>
                </div>
                <?php skt_itconsultant_pagination(); ?>
            <?php else : ?>
                <?php get_template_part( 'no-results', 'archive' ); ?>
            <?php endif; ?>
          
        </section>      
	     <?php get_sidebar();?>      
        <div class="clear"></div>
        </div><!--end .page_wrapper-->
    </div>
</div>
	
<?php get_footer(); ?>