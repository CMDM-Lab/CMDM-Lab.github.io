<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package SKT IT Consultant
 */
?>
<div class="clear"></div>
         
</div><!--end .main-wrapper-->

<footer id="footer">
	<div class="container">		
        	<aside class="widget first">
                <h3 class="widget-title"><?php echo get_theme_mod('footcolonetitle', 'John doe'); ?></h3>
                <p><?php echo get_theme_mod('footcolonecontent', ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque augue eros, posuere a condimentum sit amet, rhoncus eu libero. Maecenas in tincidunt turpis, ut rhoncus neque. Nullam sit amet porta odio. Maecenas mattis nulla ac aliquet facilisis. '); ?> </p>              
	        </aside>	
		<?php if ( ! dynamic_sidebar( 'footer-tweet' ) ) : ?>
	        <aside class="widget second">
                <h3 class="widget-title"><?php _e('Latest Tweets','itconsultant'); ?></h3>
              		<p>Use twitter widget for twitter feed.</p>
	        </aside>
		<?php endif; // end sidebar widget area ?>

		
	        <aside class="widget third">
                <h3 class="widget-title"><?php echo get_theme_mod('social_title', 'Connect with us'); ?></h3> 
                <div class="social-icons">
                 <a target="_blank" href="<?php echo esc_url(get_theme_mod('fb_link', '#facebook')); ?>" title="Facebook" ><div class="fb icon"></div><span><?php _e('Facebook','itconsultant'); ?></span></a>
                 <a target="_blank" href="<?php echo esc_url(get_theme_mod('twitt_link', '#twitter')); ?>" title="Twitter" ><div class="twitt icon"></div><span><?php _e('Twitter','itconsultant'); ?></span></a>
                 <a target="_blank" href="<?php echo esc_url(get_theme_mod('gplus_link', '#gplus')); ?>" title="Google Plus" ><div class="gplus icon"></div><span><?php _e('Google +','itconsultant'); ?></span></a>
                 <a target="_blank" href="<?php echo esc_url(get_theme_mod('linked_link', '#linkedin')); ?>" title="Linkedin" ><div class="linkedin icon"></div><span><?php _e('Linkedin','itconsultant'); ?></span></a>
                 </div>
	        </aside>	
       
	        <aside class="widget last">
                <h3 class="widget-title"><?php echo get_theme_mod('contact_info', 'Contact info'); ?></h3>               
                <p><?php echo get_theme_mod('contact_add', 'Office Blvd No. 000-000,<br> Farmville Town, LA 12345'); ?><br />
                <?php _e('Phone:','itconsultant'); ?> <?php echo get_theme_mod('contact_call', '+62 500 800 123'); ?><br />
                <?php _e('Fax:','itconsultant'); ?> <?php echo get_theme_mod('contact_fax', '+62 500 800 112'); ?>
                <p>
                <?php _e('Email:','itconsultant'); ?> <a href="mailto:<?php echo get_theme_mod('contact_mail', 'demo@domain.com'); ?>"><?php echo get_theme_mod('contact_mail', 'demo@domain.com'); ?></a><br />
                <?php _e('Website:','itconsultant'); ?> <a href="http://<?php echo get_theme_mod('contact_web', 'www.yourdomain.com'); ?>"><?php echo get_theme_mod('contact_web', 'www.yourdomain.com'); ?></a>
                </p>
	        </aside>
		
        <div class="clear"></div>
    </div>
</footer>
<div id="copyright">
	<div class="container">
    	<div class="left"><?php echo get_theme_mod('copy_text','IT Consultant &copy; 2015'); ?> | <?php echo itconsultant_credit_link(); ?></div>
    	<div class="right">
		    <?php wp_nav_menu( array('theme_location'  => 'footer', 'container' => '', 'container_class' => '', 'items_wrap' => '<ul>%3$s</ul>', 'depth' => '1' ) ); ?>
		 </div>
        <div class="clear"></div>
    </div>
</div>

<?php wp_footer(); ?>

</body>
</html>