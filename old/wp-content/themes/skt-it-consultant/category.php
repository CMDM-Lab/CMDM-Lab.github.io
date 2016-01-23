<?php
/**
 * The template for displaying all category pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package SKT IT Consultant
 */

get_header(); ?>

<div class="content-area">
    <div class="container">
       <div class="page_wrapper">        
        <section id="site-main" class="site-main content-part" >        
            <header class="page-header">
				<h1 class="page-title"><?php single_cat_title(_e('Category: ', 'itconsultant' )); ?></h1>
            </header><!-- .page-header -->
			<?php if ( have_posts() ) : ?>
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