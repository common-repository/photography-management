<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://codeneric.com
 * @since      1.0.0
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/admin/partials
 */
function codeneric_phmm_admin_client( $post ) {


	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );

	?>


	<div id="cc_phmm_client">
		<div
			style="background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:block;"></div>
	</div>


	<?php

}