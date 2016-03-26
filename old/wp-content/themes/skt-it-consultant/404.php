<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package SKT IT Consultant
 */

get_header(); ?>

<div class="content-area">
    <div class="container">
    <div class="page_wrapper">        
        <section class="site-main" id="sitefull">       
            <header class="page-header">
                <h1 class="title-404"><?php _e( '<strong>404</strong> Not Found', 'itconsultant' ); ?></h1>
            </header><!-- .page-header -->           
                <p class="text-404"><?php _e( 'Looks like you have taken a wrong turn.....<br />Don\'t worry... it happens to the best of us.', 'itconsultant' ); ?></p>
                         
        </section>
        <div class="clear"></div>
        </div><!--end .page_wrapper-->
    </div>
</div>

<?php get_footer(); ?>