<footer <?php hoot_attr( 'footer', '', 'footer grid-stretch highlight-typo' ); ?>>
	<div class="grid">
		<div class="grid-row">
			<?php
			$columns = hoot_get_option_footer();
			$alphas = range('a', 'e');
			$structure = hoot_footer_structure();

			for ( $i=0; $i < $columns; $i++ ) { ?>
				<div class="<?php echo 'grid-span-' . $structure[ $i ] ; ?>">
					<?php dynamic_sidebar( 'footer-' . $alphas[ $i ] ); ?>
				</div><?php
			} ?>
		</div>
	</div>
</footer><!-- #footer -->