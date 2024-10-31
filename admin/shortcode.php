<?php

//TODO(alex): refactor this shit"

function codeneric_phmm_shortcode_client( $atts ){
	$att = shortcode_atts( array(
		'id' => 0
	), $atts );

	$temp_post = get_post();
	$id = isset($att['id']) && $att['id'] !== 0 ? $att['id'] : $temp_post->ID;
	ob_start();

//	require_once(dirname(__FILE__) . '../public/LayoutManager.php');


	$user_can_access_post = apply_filters('codeneric/phmm/check_user_permission', $id);


	if ( !$user_can_access_post ) { //password required, attention!

		echo "<style>form p {text-align: center;}</style>";
//		LayoutManager::getOpeningTags();
		echo get_the_password_form();
//		LayoutManager::getClosingTags();


	}
	else {
//		if(isset($_GET['project'])) {
//			$is_admin = current_user_can( 'manage_options' );
//			if(!$is_admin){ //ignore stats for admin
//				$hook_args = array('client'=>get_the_ID(), 'project'=> $_GET['project'], 'type'=> 'project-seen');
//				do_action('codeneric/phmm/statistics/event', $hook_args);
//			}
//		}


		//include_once('client-overview.php');




		do_action('codeneric_shortcode_enqueue_scripts', $id); // enqueue the srtyle

		?>
		<div id="codeneric-phmm-public-container"></div>
		<?php
	}






//	include_once(dirname(__FILE__) .'/../public/front-dependencies.php');
//	frontend_enqueues();
//	//echo dirname(__FILE__) .'/../download-area/client-overview.php';
//	if(!isset($_GET['project']))
//		include_once(dirname(__FILE__) .'/../public/partials/client-overview.php');
//
//	if(isset($_GET['project']))
//		include_once(dirname(__FILE__) .'/../public/partials/project-overview.php');





	//require( plugins_url('/../download-area/client-overview.php', __FILE__));
	//include_once(plugins_url( '../download-area/client-overview.php', __FILE__ ));

// save and return the content that has been output

	$content = ob_get_clean();
	return $content;
}
add_shortcode( 'cc_phmm_client', 'codeneric_phmm_shortcode_client' );


add_action('wp_login_failed', 'my_front_end_login_fail');
function my_front_end_login_fail($username){
	// Get the reffering page, where did the post submission come from?
	$referrer = $_SERVER['HTTP_REFERER'];

	// if there's a valid referrer, and it's not the default log-in screen
	if(!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin')){
		// let's append some information (login=failed) to the URL for the theme to use

		wp_redirect(add_query_arg ('login','failed',$referrer));
		exit;
	}
}

function codeneric_phmm_shortcode_portal( $atts ){



	ob_start();


	if(!is_user_logged_in()){
		if(isset($_GET['login']) && $_GET['login'] == 'failed' )
			echo '<div style="text-align: center; padding: 0.5em 1em; margin-bottom: 1em; border: 1px solid rgba(255, 0, 0, 0.5); font-weight: normal;">Username and/or password incorrect</div>';
		wp_login_form( );
	}else{
//		global $current_user;
//		get_currentuserinfo();
		$current_user = wp_get_current_user();
		$post_id = get_user_meta($current_user->ID, 'phmm_post_id', true);

		if ( current_user_can( 'manage_options' )  ) {
			echo "<h3>You seem to be the Administrator, please open this page in another browser or logout and revisit it to see the login form.</h3>";
			die();
		}

		$id = $post_id;

		do_action('codeneric_shortcode_enqueue_scripts', $id); // enqueue the script

		?>
		<div id="codeneric-phmm-public-container"></div>
		<?php


//		include_once(dirname(__FILE__) .'/../public/front-dependencies.php');
//		frontend_enqueues();
//		//echo dirname(__FILE__) .'/../download-area/client-overview.php';
//		if(!isset($_GET['project']))
//			include_once(dirname(__FILE__) .'/../public/partials/client-overview.php');
//
//		if(isset($_GET['project']))
//			include_once(dirname(__FILE__) .'/../public/partials/project-overview.php');
	}


	//require( plugins_url('/../download-area/client-overview.php', __FILE__));
	//include_once(plugins_url( '../download-area/client-overview.php', __FILE__ ));

// save and return the content that has been output

	$content = ob_get_clean();
	return $content;
}
add_shortcode( 'cc_phmm_portal', 'codeneric_phmm_shortcode_portal' );