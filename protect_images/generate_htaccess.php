<?php
/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 12.11.2015
 * Time: 03:33
 */

function Photography_Management_Base_Generate_Htaccess(){
//    $upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
//    $upload_dir = $upload_dir['baseurl'];
    $protect_url = plugins_url( 'protect.php', __FILE__ );
    $htaccess = "RewriteEngine On" . PHP_EOL .
                "RewriteCond %{REQUEST_URI} !protect.php" .PHP_EOL.
                "RewriteCond %{QUERY_STRING} ^(.*)" . PHP_EOL .
                "RewriteRule ^(.+)$ $protect_url?%1&f=$1 [L,NC]";
//                "RewriteRule ^(.+)$ $upload_dir/photography_management/protect.php?%1&f=$1 [L,NC]";
    if(!is_writable( dirname(__FILE__).'/apache_htaccess'))return false;
    $myfile = fopen( dirname(__FILE__).'/apache_htaccess', "w" );
    if($myfile === false)return false;
    if(fwrite( $myfile, $htaccess ) === false)return false;
    return fclose($myfile);
}