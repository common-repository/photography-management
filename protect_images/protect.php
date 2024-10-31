<?php

/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 30.10.2015
 * Time: 16:07
 */

$pi_path = dirname(__FILE__);
require_once("$pi_path/security-logic.php");
//var_dump($_GET);

//error_reporting(E_ERROR);
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

//error_reporting(0);

/*********** logic stuff ***************/
$root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
$prevent_other_plugins_from_loading = isset($_GET) && isset($_GET['f']) && ($_GET['f'] === 'zip-all' || $_GET['f'] === 'zip-favs');

if($prevent_other_plugins_from_loading){
    define('WP_INSTALLING', true);
}



require_once( $root_path . '/wp-load.php' );

if($prevent_other_plugins_from_loading) {

    require_once(dirname(dirname(__FILE__)) . '/photography_management.php');
    global $cc_phmm_config;

    $premium_path = dirname(dirname(dirname(__FILE__))) . '/photography-management-premium/photography_management-premium.php';
    if($cc_phmm_config['premium_ext_active'] && file_exists($premium_path))
        require_once($premium_path);
}



$upload_dir = wp_upload_dir();
$upload_dir = $upload_dir['basedir'];



$p_f = new Photography_Management_Base_Protect_Images();
if($p_f->check_prama() === false){
    if(function_exists('http_response_code'))
        http_response_code(400); //TODO: make compatible with older php versions
    exit;
}

if($_GET['f'] !== 'zip-favs' && $_GET['f'] !== 'zip-all' && !file_exists($upload_dir.'/photography_management/'.$_GET['f'])){
    if(function_exists('http_response_code'))
        http_response_code(404); //TODO: make compatible with older php versions
    exit;
}

if($p_f->user_is_permitted() === false){
    if(function_exists('http_response_code'))
        http_response_code(401); //TODO: make compatible with older php versions
    exit;
}



$file_path = $upload_dir.'/photography_management/'.$_GET['f'];
$p_f->provide_file($file_path);
