<?php

$options = get_option('cc_photo_settings');
$GLOBALS['cc_phmm_options'] = $options;

function frontend_enqueues() {
	$options = get_option('cc_photo_settings');
	global $cc_phmm_client_id;

	$projects = get_post_meta($cc_phmm_client_id,"projects",true);
//	$project =  isset($projects[0]) ? $projects[0] : null;
	//wp_enqueue_style('cc_photography', plugins_url( 'style/cc_photography.css',__FILE__ ), array('dashicons') );
	
	wp_enqueue_script( 'jquery' );


	if(isset($_GET['project'])) {

		//wp_enqueue_script('phmm', plugins_url( 'js/phmm.js',__FILE__ ),array('jquery') );



		if(isset($options['cc_photo_image_box'])) {
			wp_enqueue_style('swipebox.min', plugins_url('js/swipebox/src/css/swipebox.min.css', __FILE__));
			wp_enqueue_script('jquery.swipebox.min', plugins_url( 'js/swipebox/src/js/jquery.swipebox.min.js',__FILE__ ),array('jquery') );
		}
	}
}


