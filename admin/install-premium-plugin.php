<?php
/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 03.11.2015
 * Time: 11:59
 */


include_once (ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

class Installer_Upgrader_Skins extends WP_Upgrader_Skin{

    function __construct($args = array()){
        $defaults = array( 'url' => '', 'nonce' => '', 'title' => '', 'context' => false );
        $this->options = wp_parse_args($args, $defaults);
    }

    function header(){

    }

    function footer(){

    }

    function error($error){
        $this->installer_error = $error;
    }

    function add_strings(){

    }

    function feedback($string){

    }

    function before(){

    }

    function after(){

    }

}


function Photography_Management_Base_Premium_Ajax_Installer()
{

    header('Content-Type: application/json');
    $status = 'error_during_installation';
    die(json_encode(array('status' => $status, 'data' => 0)));

    //include(ABSPATH . 'wp-admin/admin-footer.php');

}
