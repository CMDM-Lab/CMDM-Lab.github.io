<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package SKT IT Consultant
 */
?>
<div class="sidebar-right">    
    <?php if ( ! dynamic_sidebar( 'sidebar-main' ) ) : ?>     
       <aside id="categories" class="sidebar-area">
            <h3 class="widget_title"><?php _e( 'Categories', 'itconsultant' ); ?></h3>
            <ul>
                <?php wp_list_categories('title_li='); ?>
            </ul>
            <div class="side_shadow"></div>
        </aside> 
            
        <aside id="archives" class="sidebar-area">
            <h3 class="widget_title"><?php _e( 'Archives', 'itconsultant' ); ?></h3>
            <ul>
                <?php wp_get_archives( array( 'type' => 'monthly' ) ); ?>
            </ul>
            <div class="side_shadow"></div>
        </aside>        
    <?php endif; // end sidebar widget area ?>	
</div><!-- sidebar -->