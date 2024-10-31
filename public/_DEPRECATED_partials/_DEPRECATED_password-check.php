<?php

global $cc_phmm_client_id;

$pass_required = post_password_required($cc_phmm_client_id);
if ( $pass_required ) { //password required, attention!
    if(is_user_logged_in()){ //user is logged in, maybe password check is not necessary

        //global $current_user;
        //get_currentuserinfo();
        $current_user = wp_get_current_user();
        $users_post_id = get_user_meta($current_user->ID, 'phmm_post_id', true);


        if($cc_phmm_client_id !== $users_post_id ){ // well, it is. He is probably trying to view another project!

        }else{
            $pass_required = false;
        }
    }else{

    }

}


//$GLOBALS['cc_phmm_pass_required'] = $pass_required;

require_once(dirname(__FILE__).'/../../includes/permission.php');
$GLOBALS['cc_phmm_pass_required'] = Photography_Management_Base_Permission::current_user_can_access_client($cc_phmm_client_id);