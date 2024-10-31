<?php

global $cc_phmm_client_id;
$user_can_access_post = apply_filters('codeneric/phmm/check_user_permission', $cc_phmm_client_id);


if ( !$user_can_access_post ) { //password required, attention!

    echo "<style>form p {text-align: center;}</style>";
    LayoutManager::getOpeningTags();
    echo get_the_password_form();
    LayoutManager::getClosingTags();


}
add_filter('show_admin_bar', '__return_false');

if($user_can_access_post) :
        //TODO: Do not just provide zip, require a secret string

    $is_admin = current_user_can( 'manage_options' );
    if(!$is_admin){ //ignore stats for admin
        $hook_args = array('client'=>$cc_phmm_client_id, 'project'=> $_GET['project'], 'type'=> 'project-seen');
        do_action('codeneric/phmm/statistics/event', $hook_args);
    }

    ?>
    <div id="codeneric-phmm-public-container"></div>
    <?php endif; ?>