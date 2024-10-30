<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<form method="post" action="options.php">
		<?php
		settings_fields( $option );
		do_settings_sections( $page );
		submit_button();
		?>
	</form>
</div>