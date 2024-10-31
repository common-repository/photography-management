<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://codeneric.com
 * @since      1.0.0
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/public
 * @author     Codeneric <support@codeneric.com>
 */
class Photography_Management_Base_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;



	}

	public function generate_frontend_variables($cc_phmm_client_id) {
		global $cc_phmm_config;
		$options = get_option('cc_photo_settings', array());
//		if(!is_singular( $cc_phmm_config['slug'])) //todo return if not on single-client. CAUTION: client portal needs adjustment
//			return;

//		$cc_phmm_client_id = get_the_ID();;

		$preloadCount = 10;

		$obj = new stdClass();
		$obj->client = new stdClass(); // worky
		$obj->client->name = get_the_title($cc_phmm_client_id); // worky
		$obj->client->ID = $cc_phmm_client_id;

		$obj->projects = get_post_meta( $cc_phmm_client_id, "projects", true ); // worky
        $obj->projects = isset($obj->projects) ? $obj->projects : array();




		$obj->ajax_url = admin_url( 'admin-ajax.php' );
		$obj->locale = get_locale();
		$obj->preloadCount = $preloadCount;

		$download_url = wp_upload_dir();
		$obj->download_url_base = $download_url['baseurl']."/photography_management/";


		foreach ($obj->projects as $index => $project) {
			$project['ID'] = $index; // todo Assuming index is id;

			$obj->projects[$index]['ID'] = $project['ID'];


			// thumbnail was set as premium
			if(isset($project['thumbnail'])) {
				$obj->projects[$index]['thumbnail'] = $this->get_mini_thumbs(array($project['thumbnail']), $cc_phmm_client_id, $project['ID']);
				$obj->projects[$index]['thumbnail'] = $obj->projects[$index]['thumbnail'][0];
			}
			//take first gallery entry
			elseif (isset($project['gallery']) && count($project['gallery']) > 0) {
				$obj->projects[$index]['thumbnail'] = $this->get_mini_thumbs(array($project['gallery'][0]), $cc_phmm_client_id, $project['ID']);
				$obj->projects[$index]['thumbnail'] = $obj->projects[$index]['thumbnail'][0];
			}

			// otherwise placeholder?

		}






		if( !isset($options['cc_photo_image_box']))
			$obj->disableSlider = true;


		//$obj->isProjectView = isset($_GET['project']);

		if(isset($_GET['project'])) {
			$obj->currentProject = $_GET['project'];

			$initialImgs = array_slice($obj->projects[$_GET['project']]['gallery'], 0, $preloadCount);

			$obj->projects[$_GET['project']]['preloadedGallery'] = $this->get_mini_thumbs($initialImgs, $cc_phmm_client_id, $_GET['project']);
		}


		$post = get_post($cc_phmm_client_id);
		$isPortal = isset($options['cc_photo_portal_page']) && $options['cc_photo_portal_page'] == get_the_ID();


		if(!empty($post->post_password)) {

			if($isPortal) // current page is portal
				$obj->logout_url = wp_logout_url(get_permalink(get_the_ID()));
			else // shortcode or singular
				$obj->logout_url = $this->posts_logout_url();
		}


		if(($isPortal || is_singular($cc_phmm_config['slug'])) && isset($_GET['project']))
			$obj->canGoBack = true;






		return json_decode(json_encode($obj), true); // convert stdObj to array

	}

	function posts_logout_url() {
		return wp_nonce_url( add_query_arg( array( 'action' => 'codeneric_phmm_posts_logout' ), site_url( 'wp-login.php', 'login' ) ), 'codeneric_phmm_posts_logout' );
	}
	public function posts_logout() {
		if ( isset( $_REQUEST['action'] ) and ( 'codeneric_phmm_posts_logout' == $_REQUEST['action'] ) ) {
			check_admin_referer( 'codeneric_phmm_posts_logout' );
			setcookie( 'wp-postpass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH );
			wp_logout(); // destroy user session
			wp_redirect( wp_get_referer() );
			die();
		}
}


public function enqueue_styles() {

//		wp_enqueue_style( $this->plugin_name.'-public', plugin_dir_url( __FILE__ ) . 'style/public.css');

	}



	public function remove_protected_string() {
		return __('%s');
	}
	public function enqueue_scripts($id) {
		global $cc_phmm_config;

		$cc_phmm_client_id =  empty($id) ? get_the_ID() :$id;
		$options = get_option('cc_photo_settings', array());




		if(empty($id)) { // could be a normal page
			if(!is_singular($cc_phmm_config['slug'])) return;
		}




		$user_can_access_post = apply_filters('codeneric/phmm/check_user_permission', $cc_phmm_client_id);


		if(!$user_can_access_post)
			return; // prevent react from failing because of missing DOM

		$correctJSFile = $cc_phmm_config['premium_ext_active']
			?  $cc_phmm_config['js_public_entry_premium']
			:  $cc_phmm_config['js_public_entry'];



		if(isset($options['custom_css'])) {
			wp_enqueue_style(
				'cc_phmm_custom_css',
				plugin_dir_url(__FILE__) . '/custom.css'
			);
			$custom_css = $options['custom_css'];
			wp_add_inline_style('cc_phmm_custom_css', $custom_css);
		}

		wp_register_script( $this->plugin_name.'-public', $correctJSFile, array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name.'-public', '__CC_PHMM_VARS__', $this->generate_frontend_variables($cc_phmm_client_id) );
		wp_enqueue_script($this->plugin_name.'-public');



	}

	public function star_photo_cb() {
		Photography_Management_Base_Common::star_photo_cb();
	}

	public function get_custom_post_type_template($single_template) {
		global $post, $cc_phmm_config;

		if (!(get_post_type($post) === $cc_phmm_config['slug'])) {
			return $single_template;

		}
		if(is_single())

			return dirname( __FILE__ ) . '/single-client.php';

	}

	public function photon_exceptions( $val, $src, $tag ) {
		if ( strpos($src, 'uploads/photography_management') !== false ) { //pipe through protect.php
			return true;
		}
		return $val;
	}

	public function photon_exceptions_2($skip,$b) {
		if(isset($b) && isset($b['attachment_id'])){
			$fullsize_path = get_attached_file( $b['attachment_id']);
			return strpos($fullsize_path, 'uploads/photography_management') !== false;

		}
		return $skip;
	}

	public function get_mini_thumbs($attach_ids = array(), $client_id='0', $project_id='0'){

//		if(!isset($client_id) || !isset($project_id)){
//			throw new Exception('get_mini_thumbs expects three parameters.');
//		}
		require_once(dirname(__FILE__).'/../includes/common.php');
		if(isset($_POST['attach_ids']) && isset($_POST['client_id']) && isset($_POST['project_id'])) {
			$attach_ids = $_POST['attach_ids'];
			$client_id = $_POST['client_id'];
			$project_id = $_POST['project_id'];

			$buffer = 1024 * 8;
			header('Content-Description: File Transfer');
//		header( 'Content-Type: application/octet-stream' );
			header('Content-Type: application/json');
			//header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"' );
			echo '[ ';
			$first = true;
			foreach ($attach_ids as $id) {

				$res = Photography_Management_Base_Common::get_minithumb_obj($id, $client_id, $project_id);
				if ($res === false)
					continue;
				if (!$first) echo " , ";
				$first = false;
				$json = json_encode($res);
				echo $json;

				flush();

			}

			echo ' ]';

			exit;
		}else{
			$final_res = array();
//			$first = true;
			foreach ($attach_ids as $id) {

				$res = Photography_Management_Base_Common::get_minithumb_obj($id, $client_id, $project_id);
				if ($res === false)
					continue;
				array_push($final_res, $res);
//				$final_res[$id] = $res;

			}

			return $final_res;

		}

	}



}
