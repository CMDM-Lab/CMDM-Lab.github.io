<?php

echo '<div class="searchbody">';

	echo '<form method="get" class="searchform" action="' . esc_url( home_url( '/' ) ) . '" >';

		echo '<label for="s" class="screen-reader-text">' . __( 'Search', 'responsive-brix' ) . '</label>';
		echo '<i class="fa fa-search"></i>';
		echo '<input type="text" class="searchtext" name="s" placeholder="' . __( 'Type Search Term...', 'responsive-brix' ) . '" />';
		echo '<input type="submit" class="submit forcehide" name="submit" value="' . esc_attr( 'Search', 'responsive-brix' ) . '" />';

	echo '</form>';

echo '</div><!-- /searchbody -->';