<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package SKT IT Consultant
 */

get_header(); ?>
<div class="content-area page-layout">
    <div class="container">
       <div class="page_wrapper">      
        <section class="site-main" id="sitefull">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'page' ); ?>				
			<?php endwhile; // end of the loop. ?>
        </section>
      </div><!--end .page_wrapper-->       
    </div>
</div>	
<?php get_footer(); ?>