<?php


require_once(dirname(__FILE__) . '/LayoutManager.php');
require_once( dirname( __FILE__ ) . '/password-check.php' );

global $cc_phmm_client_id;

require_once(dirname(__FILE__).'/../../includes/permission.php');
$user_can_access_post = Photography_Management_Base_Permission::current_user_can_access_client($cc_phmm_client_id);

//if ( current_user_can( 'manage_options' ) ) {
//	echo "<h3>You seem to be the Administrator, please open this page in another browser or logout and revisit it to see the login form.</h3>";
//	die();
//}

if ( $user_can_access_post === false ) { //password required, attention!

	echo "<style>form p {text-align: center;}</style>";
	LayoutManager::getOpeningTags();
	echo get_the_password_form();
	LayoutManager::getClosingTags();


}
add_filter('show_admin_bar', '__return_false');

if($user_can_access_post === true){


	$projects = get_post_meta( $cc_phmm_client_id, "projects", true );

//	if ( current_user_can( 'manage_options' ) && post_password_required($cc_phmm_client_id) ) {
//		echo "<h3>You seem to be the Administrator, please open this page in another browser or logout and revisit it to see the login form.</h3>";
//		die();
//	}

//	if(empty($projects))
//		die('Wrong ID supplied for shortcode.');


	?>

	<div id="codeneric-phmm-public-container"></div>


<!--	<div class="cc-phmm-header">-->
<!--		<h1 class="cc-phmm-title">--><?php //echo get_the_title($cc_phmm_client_id); ?><!--</h1>-->
<!---->
<!---->
<!---->
<!--		<p class="cc-phmm-project-desc">-->
<!--			--><?php
//			if ( count( $projects ) === 0 ) {
//				echo 'No projects created yet';
//			} else if ( count( $projects ) === 1 ) {
//				echo '<strong>One</strong> project';
//			} else {
//				echo '<strong>' . count( $projects ) . '</strong> projects';
//			}
//			?>
<!--		</p>-->
<!--	</div>-->
<!--	<div class="cc-phmm-projects-overview">-->
<!--		<div class="cc-phmm-preview-wrap">-->
<!---->
<!--			--><?php
//			require_once(dirname(__FILE__).'/../../includes/common.php');
//			global $cc_phmm_client_id;
//			$ID=intval($cc_phmm_client_id);
//
//			function cc_photo_manage_get_secure_full_and_thumb_image($a, $project_id){
//
////				return Photography_Management_Base_Common::get_full_and_thumb_image($a);
//				global $cc_phmm_client_id;
//				$attach = Photography_Management_Base_Common::get_full_and_thumb_image($a);
////				$attach->url = $attach->url . "?client=$cc_phmm_client_id&project=$project_id&attach=$a";
////				$attach->thumb = $attach->thumb . "?client=$cc_phmm_client_id&project=$project_id&attach=$a";
//				$attach->url = add_query_arg( array('client'=> $cc_phmm_client_id, 'project'=>$project_id, 'attach'=>$a ), $attach->url );
//				$attach->thumb = add_query_arg( array('client'=> $cc_phmm_client_id, 'project'=>$project_id, 'attach'=>$a ), $attach->thumb );
//				return $attach;
//			}
//			if(isset($projects))
//			foreach ( $projects as $id => $project ):
//				$thumbURL = false;
//
//				if(has_filter('cc_phmm_get_project_thumbnail'))
//					$thumbURL = apply_filters('cc_phmm_get_project_thumbnail',$id, $project);
//
//				if($thumbURL === false) {
//					if ( isset( $project['gallery'] ) && ! empty( $project['gallery'][0] ) ) {
//						$attachment = cc_photo_manage_get_secure_full_and_thumb_image( intval( $project['gallery'][0] ), $id );
//						$thumbURL   = $attachment->thumb;
//					} else {
//						$thumbURL = plugins_url( 'placeholder.png', __FILE__ );
//					}
//				}
//
//				?>
<!---->
<!---->
<!--				<a href="--><?php //echo add_query_arg( array('project'=> $id, 'postId'=>$cc_phmm_client_id ), get_permalink() ) ?><!--" class="cc-phmm-preview">-->
<!--					<img src="--><?php //echo $thumbURL; ?><!--" alt=""/>-->
<!--					--><?php //echo $project['title']; ?>
<!--				</a>-->
<!---->
<!---->
<!--			--><?php //endforeach; ?>
<!--		</div>-->
<!--	</div>-->

<?php }