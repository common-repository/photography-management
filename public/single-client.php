<!DOCTYPE html>
<html lang="en">
<?php

get_header();

?>


<body>

    <?php
    require_once(dirname(__FILE__) . '/LayoutManager.php');
    $user_can_access_post = apply_filters('codeneric/phmm/check_user_permission', get_the_ID());

    if ( !$user_can_access_post ) { //password required, attention!

        echo "<style>form p {text-align: center;}</style>";
        LayoutManager::getOpeningTags();
             echo get_the_password_form();
        LayoutManager::getClosingTags();


    }
    else {
        if(isset($_GET['project'])) {
            $is_admin = current_user_can( 'manage_options' );
            if(!$is_admin){ //ignore stats for admin
                $hook_args = array('client'=>get_the_ID(), 'project'=> $_GET['project'], 'type'=> 'project-seen');
                do_action('codeneric/phmm/statistics/event', $hook_args);
            }
        }

        ?>
            <div id="codeneric-phmm-public-container"></div>
        <?php
    }





//    if(!isset($_GET['project'])) {
//        LayoutManager::getOpeningTags();
//        include_once(dirname(__FILE__).'/partials/client-overview.php');
//        LayoutManager::getClosingTags();
//        get_footer();
//    }
//        //include_once('client-overview.php');
////    if(isset($_GET['project']) && !$preview) {
//    if(isset($_GET['project'])) {
//        LayoutManager::getOpeningTags();
//        include_once(dirname(__FILE__).'/partials/project-overview.php');
//        LayoutManager::getClosingTags();
//        get_footer();
//    }

     ?>

</body>



</html>
