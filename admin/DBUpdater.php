<?php
/**
 * Created by PhpStorm.
 * User: Denis
 * Date: 26.03.2015
 * Time: 15:35
 */

//require_once(dirname(__FILE__) .'/config.php');


class PHMM_DBUpdater {
//    static $versions = array('1.0.0','1.0.1','1.1.0','1.1.1','1.1.2','1.2.0','1.2.1','1.2.2','2.0.0','2.1.0',
//                             '2.1.1','2.1.2','2.2.0','2.2.1','2.2.2','2.2.3','2.3.0','2.3.1','2.3.2','2.3.3',
//                             '2.3.4','2.3.5','2.3.6','2.4.0','2.4.1','2.4.2','2.5.0','2.6.0','2.6.1');
    static $currVersion;




    static function version_to_func_name($newVersion){
        $tempNew = str_replace(".", "_", $newVersion);

        return "update_to_$tempNew";
    }

    static function updateClients(){
        global $cc_phmm_config;
        PHMM_DBUpdater::$currVersion = $cc_phmm_config['version'];
        $oldVersion = get_option( 'cc_photo_manage_curr_version' );

        if ( get_option( 'cc_photo_manage_id' ) === false ) {
            $uniqid = uniqid('', true);
            $uniqid = str_replace('.','',$uniqid);
            update_option( 'cc_photo_manage_id', $uniqid );
        }

        $funcContainer = new PHMM_FunctionContainer();
        $funcContainer->legacy();

        if($oldVersion === PHMM_DBUpdater::$currVersion)return;

//        update_option('codeneric_register_status', 42);
        flush_rewrite_rules();

        if($oldVersion === false){ //first installation
            $oldVersion = '1.0.1';
        }


        update_option( 'cc_photo_manage_curr_version',PHMM_DBUpdater::$currVersion );
//        $oldVersionIndex = array_search($oldVersion, PHMM_DBUpdater::$versions);
//        $funcContainer = new PHMM_FunctionContainer();

        $functions = get_class_methods('PHMM_FunctionContainer'); //get all functions
        $funcName = PHMM_DBUpdater::version_to_func_name($oldVersion); //get the function which was (potentially) executed last time

        if(!function_exists('cc_phmm_filter_functions')){
            function cc_phmm_filter_functions($v){return strpos($v,'update_to_') === 0;}
        }

        $update_funcs = array_filter($functions, 'cc_phmm_filter_functions'); //we want only update_to_ functions!
        $update_funcs[] = $funcName; //insert last executed function name to make sure it is in the array
        $update_funcs = array_unique($update_funcs); //remove our entry if it was already contained
        natsort($update_funcs); //sort the functions s.t. they are executed in the correct order!
        $update_funcs = array_values($update_funcs); //natsort sorts the keys too. we do not want this, cut them off.


        $oldVersionIndex = array_search($funcName, $update_funcs); //now lookup the current index, s.t. we can apply all remaining functions

//        for($i = $oldVersionIndex+1; $i< count(PHMM_DBUpdater::$versions); $i++ ){
//            $funcName = PHMM_DBUpdater::version_to_func_name(PHMM_DBUpdater::$versions[$i]);
//
//            if(method_exists ($funcContainer, $funcName)){ //we have to perform some operations on the database
//                $funcContainer->$funcName();
//            }
//        }
        for($i = $oldVersionIndex+1; $i< count($update_funcs); $i++ ){ //start from $oldVersionIndex+1 because we already applied $oldVersionIndex in the last version
            $funcName = $update_funcs[$i]; //helper

            if(method_exists ($funcContainer, $funcName)){ //we have to perform some operations on the database
                $funcContainer->$funcName();
            }
        }

        /////////// AFTER INSTALL/UPGRADE
        do_action('codeneric/phmm/base-plugin-updated');

    }


}

class PHMM_FunctionContainer{
    function update_to_1_1_0(){ //update from 1.0.1 to 1.1.0
        $options = get_option('cc_photo_settings', array());
//        $options = array('cc_photo_image_box'=> 1, 'cc_photo_download_text'=> 'Download all' );
        $options['cc_photo_image_box']= 1;
        $options['cc_photo_download_text']=  'Download all';
        update_option( 'cc_photo_settings', $options );

        $posts_array = get_posts( "post_type=client" );
        foreach($posts_array as $client){
            $projects = get_post_meta($client->ID,"projects",true);
            foreach($projects as &$project)
                $project['downloadable'] = true;
            //print_r($projects);
            update_post_meta($client->ID,"projects", $projects);
        }
    }



//    function update_to_2_2_0(){
//
//        $config = codeneric_phmm_get_config();
//
//        $bugfix = get_option('cc_prem');
//
//
//        if($bugfix !== false){ //update wp_version and plugin_version
//
//            $blog_id = get_option('cc_photo_manage_id');
//            $res = wp_remote_get( $config['wpps_url']."/paid/?plugin_id=$blog_id&bug_211=check" );
//
//            if(isset($res) && is_array($res) && isset($res['response']) && isset($res['response']['code'])){
//                $code = $res['response']['code'];
//                if($code === 402 || $code === 202){
//                    delete_option('cc_prem');
//                }
//            }
//        }
//    }

    function update_to_2_2_2(){

        if(get_option('codeneric_phmm_error_log') === false)
            update_option('codeneric_phmm_error_log', array());
    }

    function update_to_2_3_0(){

        add_role( "phmm_client",  __( 'PhMm Client' ),
            array(
                'read'         => true,  // true allows this capability
                'edit_posts'   => false,
                'delete_posts' => false, // Use false to explicitly deny
            ) );
    }

//    function update_to_2_4_0(){
//        $pf = get_option( 'cc_phmm_pf' );
//        $p = get_option( 'cc_prem' );
//        update_option( 'cc_phmm_pf', ($pf !== false) || ($p !== false));
//    }
    function update_to_2_5_0(){
        require_once(dirname(__FILE__) . '/admin.php');
        $plugin_admin = new Photography_Management_Base_Admin();
        $plugin_admin->register_post_type();
        flush_rewrite_rules();

    }

    function update_to_2_7_0(){
        require_once(dirname(__FILE__) . '/../protect_images/generate_htaccess.php');
        $htaccess_suc = Photography_Management_Base_Generate_Htaccess();
        if($htaccess_suc){
            $upload_dir = wp_upload_dir();
            $upload_dir = $upload_dir['basedir'].'/photography_management';
            //protect php
            if( is_link("$upload_dir/protect.php"))
                unlink("$upload_dir/protect.php"); //the htaccess redirects to the actual php-file

            //htaccess
            if( is_link("$upload_dir/.htaccess")) {
                unlink("$upload_dir/.htaccess");
            }
            if(! file_exists("$upload_dir/.htaccess"))
                copy(dirname(__FILE__) . '/../protect_images/apache_htaccess', "$upload_dir/.htaccess");
        }
    }

    function legacy(){
//        $pf = get_option( 'cc_phmm_pf' );
        $p = get_option( 'cc_prem' );
        global $cc_phmm_config;
        if($p && !$cc_phmm_config['has_premium_ext']){
            function cc_phmm_base_admin_notice_update_to_premium() {
                global $cc_phmm_config;
                $class = "update-nag";
//                $d_url =  $cc_phmm_config['wpps_url'] . '/premium/phmm?plugin_id=' . get_option('cc_photo_manage_id');
                $prem_url = admin_url('edit.php');
                $prem_url = add_query_arg(array('post_type' => 'client', 'page' => 'premium'), $prem_url);
                $p_url =  admin_url('plugins.php');
                $message = "Please <a id=\"cc_phmm_install_notice\" href=\"$prem_url\" data-plugins-url=\"$p_url\" >install</a> the Photography Management Premium extension!";
                wp_enqueue_script('cc_phmm_admin_notice', plugin_dir_url( __FILE__ ).'/partials/admin_notice.js');
                $spinner = '<div id="cc_phmm_notice_spinner" style="background:url(\'images/spinner.gif\') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:none;"></div>';
                echo"<div id=\"cc_phmm_notice_wrap\" class=\"$class\"> <p>$spinner $message</p></div>";
            }
            add_action( 'admin_notices', 'cc_phmm_base_admin_notice_update_to_premium' );

        }
        elseif($cc_phmm_config['has_premium_ext'] && !$cc_phmm_config['premium_ext_active']){
            function cc_phmm_base_admin_notice_update_to_premium() {
                global $cc_phmm_config;
                $class = "update-nag";
                $d_url =  admin_url('plugins.php');
                $message = "Please <a href=\"$d_url\" >activate</a> the Photography Management Premium extension!";
                $script = "";//"<script> function cc_phmm_handle_download_click(e){e.preventDefault();alert('alles ok');} jQuery('#cc_phmm_download_notice').on('click',cc_phmm_handle_download_click); </script>";
                echo"<div id=\"cc_phmm_notice_wrap\" class=\"$class\"> <p>$message</p></div>$script";
            }
            add_action( 'admin_notices', 'cc_phmm_base_admin_notice_update_to_premium' );

        }

    }


}