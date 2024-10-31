<?php

/**
 * Fired during plugin activation
 *
 * @link       http://codeneric.com
 * @since      1.0.0
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/includes
 * @author     Codeneric <support@codeneric.com>
 */



class Photography_Management_Base_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once(dirname(__FILE__) . '/../admin/admin.php');
		$plugin_admin = new Photography_Management_Base_Admin();
		$plugin_admin->register_post_type();
		flush_rewrite_rules();

		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'].'/photography_management';
		if(!file_exists($upload_dir))
			mkdir($upload_dir);

		require_once(dirname(__FILE__) . '/../protect_images/generate_htaccess.php');
		$htaccess_suc = Photography_Management_Base_Generate_Htaccess();
		if($htaccess_suc){
			//protect php
			if( is_link("$upload_dir/protect.php"))
				unlink("$upload_dir/protect.php"); //we do not need this anymore, redirect straight to original php-file
			//htaccess
			if( is_link("$upload_dir/.htaccess")) {
				unlink("$upload_dir/.htaccess");
			}
            if( file_exists("$upload_dir/.htaccess"))
                unlink("$upload_dir/.htaccess");

			if(! file_exists("$upload_dir/.htaccess"))
				copy(dirname(__FILE__) . '/../protect_images/apache_htaccess', "$upload_dir/.htaccess");
		}



		if ( get_option( 'cc_photo_manage_id' ) === false ) {
			$uniqid = uniqid('', true);
			$uniqid = str_replace('.','',$uniqid);
			update_option( 'cc_photo_manage_id', $uniqid );
		}





	}

}
