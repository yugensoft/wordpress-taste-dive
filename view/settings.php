<div class="wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>
	<form action="options.php" method="post">
		<?php
			settings_fields( 'taste_dive' );
			do_settings_sections( 'taste-dive-settings' );
			submit_button();
		?>
	</form>
</div>