<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://codeneric.com
 * @since      1.0.0
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/admin
 * @author     Codeneric <support@codeneric.com>
 */

require_once(dirname(__FILE__).'/../includes/common.php');

class Photography_Management_Base_Admin {


	private $plugin_name;
	private $version;
	private $slug;

	public function __construct( $plugin_name='', $version='', $slug='') {
		global $cc_phmm_config;
		$this->plugin_name =    $cc_phmm_config['plugin_name'];
		$this->version     =    $cc_phmm_config['version'];
		$this->slug     =       $cc_phmm_config['slug'];



	}


	public function register_post_type() {
		register_post_type( $this->slug,
			array(
				'labels'             => array(
					'name'               => __('All clients', $this->plugin_name),
					'singular_name'      => __('Client', $this->plugin_name),
					'menu_name'          => __('PHMM', $this->plugin_name),
	//				'name_admin_bar'     => __('d', $this->plugin_name),
					'all_items'          => __('All clients', $this->plugin_name),
					'add_new'            => __('Add new client', $this->plugin_name),
					'add_new_item'       => __('Add new client', $this->plugin_name),
					'edit_item'          => __('PHMM Client', $this->plugin_name),
//					'new_item'           => __('i', $this->plugin_name),
//					'view_item'          => __('j', $this->plugin_name),
					'search_items'       => __('Search clients', $this->plugin_name),
					'not_found'          => __('No clients found', $this->plugin_name)
					//'not_found_in_trash' => __('m', $this->plugin_name),
//					'parent_item_colon'  => __('n', $this->plugin_name),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'query_var'          => true,
				'can_export'         => true,
				'has_archive'        => false,
				'menu_icon'          => 'dashicons-camera',
				'rewrite'            => array( 'slug' => $this->slug, 'with_front' => false ),
				'supports'           => array(
					'title' => false,
					'editor' => false
				),
				'taxonomies'         => array( '' ),
			)
		);

	}


	public function add_meta_boxes() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/client.php';
		add_meta_box(  $this->plugin_name.'-client', __('Client Information', $this->plugin_name), 'codeneric_phmm_admin_client', $this->slug, 'normal', 'high' );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/project.php';
		add_meta_box(  $this->plugin_name.'-project', __('Projects', $this->plugin_name), 'codeneric_phmm_admin_project', $this->slug, 'normal', 'high' );
	}

	public function save_meta_box_data($post_id) {


		if(get_post_type($post_id) != $this->slug)
			return; // not our business

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;


		$this->save_projects($post_id);
		$this->save_client($post_id);


	}

	public function save_projects($post_id){
		global $meta_keys;
		$PROJECTS = isset($_POST['projects']) ? $_POST['projects'] : array() ;
//		if(!isset($PROJECTS)) $PROJECTS = array();




		$projects = $PROJECTS;
		$upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
		$upload_dir = $upload_dir['basedir'].'/photography_management';


		foreach($projects as $index => $project){
			if(isset($project['starred']) && is_string($project['starred']))
				$PROJECTS[$index]['starred'] = explode(',', $project['starred']);
			$PROJECTS[$index]['starred'] = isset($PROJECTS[$index]['starred']) ? $PROJECTS[$index]['starred'] : array();
			$project = $PROJECTS[$index];



			if(!empty($project['favoritable']))
				$project['favoritable'] = $project['favoritable'] === "true";             // parse string to boolean
			if(!empty($project['commentable']))
				$project['commentable'] = $project['commentable'] === "true";             // parse string to boolean
			if(!empty($project['downloadable']))
				$project['downloadable'] = $project['downloadable'] === "true";           // parse string to boolean
			if(!empty($project['downloadable_favs']))
				$project['downloadable_favs'] = $project['downloadable_favs'] === "true";           // parse string to boolean
			if(!empty($project['disableRightClick']))
				$project['disableRightClick'] = $project['disableRightClick'] === "true";


			if(isset($project['gallery'])){


				if(is_string($project['gallery']))
					$project['gallery'] = explode(',', $project['gallery']);

				$PROJECTS[$index]['starred'] = array_values(array_intersect($project['starred'],$project['gallery'] ));
				//asort($project['gallery']);
				$PROJECTS[$index]['gallery'] = $project['gallery'];

				$PROJECTS[$index]['download_favs']  = Photography_Management_Base_Common::process_favorites($post_id, $index, $PROJECTS[$index]['starred']);
				//asort($_POST['projects'][$index]['gallery']); //sort the data to be pushed, too.




//				$new_gallery = implode(',', $project['gallery']);
//				$old_gallery = $new_gallery . 'diff'; //first old_gallery is different
//				$old_projects = get_post_meta($post_id,'projects', true);
//				if(isset($old_projects) && isset($old_projects[$index])
//					&& isset($old_projects[$index]['gallery'])){
//					$old_gallery = implode(',',$old_projects[$index]['gallery']);
//				}
//
//
//				if($new_gallery != $old_gallery){
//					$attach_path = array();
//					foreach($project['gallery'] as $index_2 => $attachID){
//						$attachment = Photography_Management_Base_Common::get_full_and_thumb_image(intval($attachID));
//						$attachment->id = $attachID;
//						//print_r($attachment);
//						$project['gallery'][$index_2] = $attachment;
//						$attach_path[] = get_attached_file( $attachID);
//					}
//
//					$PROJECTS[$index]['status'] = 'in-zipping-queue';
//
//
//				}
			}
//			else if(file_exists ( $filename = "$upload_dir/$post_id-$index.zip" ))
//				unlink($filename);
		}
		//print_r($_POST['projects']);
		//echo "Gallery len:" . count($_POST['projects'][0]['gallery']);

		update_post_meta( $post_id, 'projects', $PROJECTS );
	}
	public function save_client($post_id){
		$CLIENT = $_POST['client'];
		if ( isset( $CLIENT['show_on_page'] ) && $CLIENT['show_on_page'] === '' ) //none
			unset($CLIENT['show_on_page']);

		$current_meta = get_post_meta( $post_id, 'client', true );
		if ( isset( $current_meta['show_on_page'] ) ) {
			$old_page_content          = get_post_field( 'post_content', $current_meta['show_on_page'] );
			$old_page_stripped_content = str_replace( '[cc_phmm_client id="' . $post_id . '"]', '', $old_page_content );
			$old_page                  = array(
				'ID'           => $current_meta['show_on_page'],
				'post_content' => $old_page_stripped_content
			);
			wp_update_post( $old_page );
		}


		if ( isset( $CLIENT['show_on_page'] ) ) {
			$page_content          = get_post_field( 'post_content', $CLIENT['show_on_page'] );
			$page_content = $page_content.'[cc_phmm_client id="' . $post_id . '"]';
			$old_page                  = array(
				'ID'           => $CLIENT['show_on_page'],
				'post_content' => $page_content
			);
			wp_update_post( $old_page );
		}

		if( !empty( $CLIENT['pwd'] ) ){

			$login_name = '';
			if(isset($CLIENT['login_name'])){
				$login_name = $CLIENT['login_name'];

			} else{
				$login_name = sanitize_text_field($CLIENT['full_name']);
			}
			$email = isset($CLIENT['email']) ? $CLIENT['email'] : '';
			if(!empty($email)){
				$email = sanitize_email($email);
			}


			$userdata = array(
				'user_login'  =>  $login_name,
				//'user_pass'   =>  $CLIENT['pwd'],
				'role'		  =>  'phmm_client',
				'user_email'  =>  $email,
				'display_name'=>  $login_name,
				'nickname'=>  $login_name,
				'user_nicename' => $login_name,
				'show_admin_bar_front'=>false
			);


			if( !isset(  $current_meta['pwd']) || ($CLIENT['pwd'] !== $current_meta['pwd'])) //only update pass if needed
				$userdata['user_pass'] = $CLIENT['pwd'];


			if(isset($current_meta['wp_user_id'])){

				$userdata['ID'] = $current_meta['wp_user_id'];
				$user_id = wp_update_user( $userdata );

			}else{

				$user_id = wp_insert_user( $userdata ) ;
				update_user_meta( $user_id, 'is_phmm_client', true );
				update_user_meta( $user_id, 'phmm_post_id', $post_id );
			}

			if(is_numeric($user_id)){
				$CLIENT['wp_user_id'] = $user_id;
			}

		}

		update_post_meta( $post_id, 'client', $CLIENT );


	}

	private function zip_gallery($path, $attach_path, $client_id, $project_index) {
		//delete_option( 'codeneric_phmm_error_log' );

		$progress = new stdClass();
		$progress->done = 	count($attach_path) ;
		$progress->total = count($attach_path);

		return $progress;

//		$zip_states = get_option('cc_phmm_zip_states', array());
//
//		if(!isset($zip_states[$client_id])){
//			$zip_states[$client_id] = array();
//		}
//		if(!isset($zip_states[$client_id][$project_index])){
//			$to_dos = isset($attach_path) ? $attach_path : array();
//
//			$zip_states[$client_id][$project_index] = array('to_do'=>$to_dos,
//				'locked'=>true,
//				'time' => time()
//			);
//
//			update_option('cc_phmm_zip_states', $zip_states); //TODO: doing some bad locking here
//
//			$projects = get_post_meta($client_id,'projects', true);
//			if(isset($projects) && isset($projects[$project_index])){
//				$projects[$project_index]['status'] = 'zipping';
//				update_post_meta($client_id,'projects', $projects);
//			}
//			if(file_exists($path)){
//				$done = unlink($path);
////				codeneric_phmm_add_to_log('delete old zip: '.($done?'ok':'fail'));
//			}
//
//
//		}else{
//			$old_zip_states = get_option('cc_phmm_zip_states', false);
//			$max_exec = ini_get('max_execution_time');
//			$max_exec = intval($max_exec);
//
//			if($old_zip_states !== false && isset($old_zip_states[$client_id])
//				&& isset($old_zip_states[$client_id][$project_index])
//				&& $old_zip_states[$client_id][$project_index]['locked']
//				&& $old_zip_states[$client_id][$project_index]['time'] + $max_exec > time() ) //prevent deadlock on-fail
//			{
//				$to_dos = $old_zip_states[$client_id][$project_index]['to_do'];
////				$progress = (count($attach_path) -count($to_dos)) .'/'.count($attach_path);
//				$progress = new stdClass();
//				$progress->done = 	(count($attach_path) -count($to_dos));
//				$progress->total = count($attach_path);
//
//				return $progress;
//			}
//
//		}
//
//
////      codeneric_phmm_add_to_log('to-dos:'.json_encode($zip_states));
////		codeneric_phmm_add_to_log('start zipping...');
//		if(file_exists($path)){
////			codeneric_phmm_add_to_log('zip size before: '.filesize($path)/1000000 );
//		}
//
//		$MAX_IMGS_PER_RUN = 4;//rand ( 1 , 1 );
//		$part=array();
//		for($i=0; $i < $MAX_IMGS_PER_RUN && count($zip_states[$client_id][$project_index]['to_do']); $i++){
//			$last_elem = array_shift($zip_states[$client_id][$project_index]['to_do']);
//			array_push($part,$last_elem);
//		}
//
//
////		cc_photo_manage_create_zip($part, $path, false, "$client_id-$project_index");
//		require_once(dirname(__FILE__).'/zip.php');
//		Photography_Management_Base_Zip::add_to_zip($part, $path, false, "$client_id-$project_index");
//
//		if(file_exists($path)){
////			codeneric_phmm_add_to_log('zip size after: '.filesize($path)/1000000 );
//		}
//
//
//		$to_dos = $zip_states[$client_id][$project_index]['to_do'];
////		$progress = (count($attach_path) -count($to_dos)) .'/'.count($attach_path);
//		$progress = new stdClass();
//		$progress->done = 	(count($attach_path) -count($to_dos));
//		$progress->total = count($attach_path);
//
//
//		if(count($zip_states[$client_id][$project_index]['to_do']) === 0){
//			$projects = get_post_meta($client_id,'projects', true);
//			if(isset($projects) && isset($projects[$project_index])){
//				$projects[$project_index]['status'] = 'ok';
//				update_post_meta($client_id,'projects', $projects);
//			}
//			unset($zip_states[$client_id][$project_index]);
////			codeneric_phmm_add_to_log('unset old zip state');
//
//		}else{
//			$zip_states[$client_id][$project_index]['locked'] = false;
//		}
//
//
//		update_option('cc_phmm_zip_states', $zip_states);
////		codeneric_phmm_add_to_log('end zipping, save state.');
//
//		return $progress;

	}





	// also used by premium
	public static function generate_frontend_globals($post) {
		global $cc_phmm_config;
		$config = $cc_phmm_config;
		// INJECT VARS TO JS
		$phmmGlobals = array();

		// is not true if we are in premium-tab, where we still need some vars for modal display
		if(isset($post)) {
			$client = get_post_meta($post->ID,"client",true);
			if($client == '')
				$client = array();


			$pwd = $post->post_password;
			if(isset($client['full_name']))
				$client['permalink'] = get_the_permalink();
			$usePwd = !empty($pwd); //post_password_required($post->ID);

			if(!empty($pwd))
				$client['pwd'] = $pwd;


			//$phmmGlobals['locale'] = get_locale();
			$phmmGlobals['client'] = $client;

			$phmmGlobals['post_id'] = $post->ID;
			$phmmGlobals['projects'] = Photography_Management_Base_Admin::prepareProjectsForFrontend($post->ID);
			$data = get_option('codeneric/phmm/canned-email-sent', array());
			$phmmGlobals['canned_email_sent'] = in_array($post->ID, $data);

			$options = get_option('cc_photo_settings', array());
			$cannedEmail = isset($options['canned_email']) ? $options['canned_email'] : '';
			$cannedEmail = trim($cannedEmail);
			$phmmGlobals['canned_email_empty'] = $cannedEmail === '';
			$phmmGlobals['portal_page'] = isset($options['cc_photo_portal_page']) ? $options['cc_photo_portal_page'] : '';
			$phmmGlobals = apply_filters('codeneric/phmm/statistics/append', $phmmGlobals);

		}


		$phmmGlobals['id'] = get_option( 'cc_photo_manage_id' );

		$phmmGlobals['pages'] = get_pages();

		$phmmGlobals['admin_email'] = get_option('admin_email');

		$phmmGlobals['assets'] = plugin_dir_url( __FILE__ ).'../assets/';

		$adminurl = admin_url( 'edit.php' );
		$phmmGlobals['premium_url'] = add_query_arg( array('post_type' => $config['slug'], 'page' => 'premium'), $adminurl);
		$phmmGlobals['support_url'] = add_query_arg( array('post_type' => $config['slug'], 'page' => 'support'), $adminurl);

//			$config = get_option('cc_phmm_config');

		//wp_localize_script( $this->plugin_name . '-admin', 'cc_wpps_url', $config['wpps_url'] );


		$phmmGlobals['wpps_url'] = str_replace('http://', 'https://', $config['wpps_url']);
		$phmmGlobals['landing_url'] = $config['landing_url'];

		$phmmGlobals['locale'] = get_locale();
		$phmmGlobals['has_premium_ext'] = $config['has_premium_ext'];


//		function isSecure() {
//			return
//				( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' )
//				|| $_SERVER['SERVER_PORT'] == 443;
//		}
		$isSecure =
				( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' )
				|| $_SERVER['SERVER_PORT'] == 443;

		$return_url = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$return_url = $isSecure ? "https://$return_url" : "http://$return_url";
		$return_url = add_query_arg( 'paid', 'yes', $return_url );

		$phmmGlobals['paypal_return'] = $return_url;
		$phmmGlobals['paypal_merchant'] = $config['paypal_merchant'];
		$phmmGlobals['paypal_post_url'] = $config['paypal_post_url'];
		$phmmGlobals['paypal_env'] = $config['paypal_env'];

		$phmmGlobals['stripe_key'] = $config['stripe_key'];



		return $phmmGlobals;
	}
	// also used by premium
	public static function prepareProjectsForFrontend($post_id) {
		$projects = get_post_meta( $post_id, "projects", true );

		if(!is_array($projects)) $projects = array();

		if ( is_array( $projects ) ) {

			foreach ( $projects as $index => $project ) {

				if(!empty($projects[ $index ]['favoritable']))
					$projects[ $index ]['favoritable'] = $projects[ $index ]['favoritable'] === "true";             // parse string to boolean
				if(!empty($projects[ $index ]['downloadable']))
					$projects[ $index ]['downloadable'] = $projects[ $index ]['downloadable'] === "true";           // parse string to boolean

				if(!empty($projects[ $index ]['commentable']))
					$projects[ $index ]['commentable'] = $projects[ $index ]['commentable'] === "true";           // parse string to boolean
				if(!empty($project['downloadable_favs']))
					$projects[ $index ]['downloadable_favs'] = $projects[ $index ]['downloadable_favs'] === "true";          // parse string to boolean
				if(!empty($projects[ $index ]['disableRightClick']))
					$projects[ $index ]['disableRightClick'] = $projects[ $index ]['disableRightClick'] === "true"; // parse string to boolean

				if(!empty($projects[ $index ]['showCaptions']))
					$projects[ $index ]['showCaptions'] = $projects[ $index ]['showCaptions'] === "true"; // parse string to boolean

				if ( isset( $project['thumbnail'] ) ) {
					$thumb     = Photography_Management_Base_Common::get_full_and_thumb_image( intval( $project['thumbnail'] ) );

					$projects[ $index ]['thumbnail'] = $thumb;
				}
				if ( isset( $project['gallery'] ) ) {
					if ( is_string( $project['gallery'] ) ) {
						$project['gallery'] = explode( ',', $project['gallery'] );
					}


					$projects[ $index ]['gallery'] = array();
					foreach ( $project['gallery'] as $index_2 => $attachID ) {
						$attachment     = Photography_Management_Base_Common::get_full_and_thumb_image( intval( $attachID ) );
						$attachment->id = $attachID;
						//print_r($attachment);
						$projects[ $index ]['gallery'][] = $attachment;
						//print_r($projects);
						//array_push($gallery, $attachment->thumb);
					}
				}
				if ( get_post_status( get_the_ID() ) === 'publish' ) {
					$projects[ $index ]['url'] = add_query_arg( 'project', $index, get_permalink( get_the_ID() ) );
				}
			}
	}

	return $projects;
}

	public function enqueue_scripts() {
		global $pagenow, $typenow, $cc_phmm_config;
		$config = $cc_phmm_config;

		// only include if its our slug
		if($typenow == $cc_phmm_config['slug'] ) {


				wp_enqueue_script( 'dashicons' );
				wp_enqueue_media();
				wp_enqueue_script( 'stripe','https://checkout.stripe.com/checkout.js', array(), '2.1.1', false );
				wp_enqueue_script('jquery-ui-accordion', array('jquery'));



				$post = get_post();
				$phmmGlobals = Photography_Management_Base_Admin::generate_frontend_globals($post);


			$scriptname = '';
			// we are in either new post or edit post
			if($pagenow == 'post-new.php' || $pagenow == 'post.php') {
				$scriptname = $cc_phmm_config['plugin_name'] . '-admin';
				wp_register_script( $scriptname, $config['js_admin_entry'], array('jquery','media-upload'), $this->version, true );

			}


			if(isset($_GET['page']) && $_GET['page'] === 'premium') {
				$scriptname = $cc_phmm_config['plugin_name'] . '-premium-page';

				wp_register_script($scriptname, $config['js_admin_premium_page_entry'], array('jquery','media-upload'), $this->version, true );

			}



			// localize all
			wp_localize_script($scriptname, 'PHMM_GLOBALS', $phmmGlobals );
			wp_enqueue_script($scriptname);

		}
		if($pagenow === 'plugins.php'){
			$scriptname = $cc_phmm_config['plugin_name'] . '-plugins';
			$js_url = plugin_dir_url( __FILE__ ).'js/ask-before-deactivation.js';
			wp_register_script( $scriptname, $js_url, array('jquery'), $this->version, true );
			wp_enqueue_script($scriptname);
		}
	}
	public function enqueue_styles() {
		global $pagenow, $typenow, $cc_phmm_config;
		$page = $_GET;

		if($typenow == $this->slug && isset($_GET['page']) && ($_GET['page'] === 'options' ||  $_GET['page'] === 'premium' || $_GET['page'] === 'support') )
			wp_enqueue_style( $cc_phmm_config['plugin_name'].'-admin', plugin_dir_url( __FILE__ ) . 'style/admin.css', array(), $this->version, 'all' );



		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/photography-management-admin.css', array(), $this->version, 'all' );

	}

	public function dequeue_files() {
//        global $wp_scripts;
//    foreach( $wp_scripts->queue as $handle ) :
//        echo $handle . ' | ';
//    endforeach;
//
//    echo "<br> END OF SCRiPTS <br>";
//
//    global $wp_styles;
//    foreach( $wp_styles->queue as $handle ) :
//        echo $handle . ' | ';
//    endforeach;

//		require_once( dirname( __FILE__ ) . '/../../config.php' );
//
//		$wl = codeneric_phmm_get_styles_wl();
//		global $wp_styles;
//
//		foreach( $wp_styles->queue as $handle ){
//			if(!in_array($handle,$wl)){
//				wp_dequeue_style( $handle );
//				//wp_deregister_style($handle);
//				// echo "dequeuing: $handle<br>";
//			}else{
//				//echo "enqueuing: $handle<br>";
//			}
//		}
//
//
//
//
//		$wl = codeneric_phmm_get_scripts_wl();
//		global $wp_scripts;
//		foreach( $wp_scripts->queue as $handle ){
//			if(!in_array($handle,$wl)){
//				wp_dequeue_script( $handle );
//			}
//		}



	}


	public function fill_custom_columns( $column, $post_id ) {
		$client = get_post_meta( $post_id, 'client', true );

		// edit_post_link does not work on all servers apparantely
		$name = isset( $client['full_name'] ) ? $client['full_name'] : "not published";
		$link = get_edit_post_link( $post_id );
		switch ( $column ) {
			case "full_name":
				echo '<a class="post-edit-link" href="'.$link.'">'.$name.'</a>';
				break;
			case "shortcode":
				echo '[cc_phmm_client id="' . $post_id . '"]';
				break;
			case "email":
				echo isset( $client['email'] ) ? $client['email'] : "not published";
				break;
			case "projects":
				$projects = get_post_meta( $post_id, "projects", true );
				if ( ! is_array( $projects ) ) {
					$projects = array();
				}
				$project_titles   = array();
				$client_permalink = get_post_permalink( $post_id );
				foreach ( $projects as $key => $project ) {
					if ( isset( $project['title'] ) ) {
						array_push( $project_titles, '<a href="' . add_query_arg( 'project', $key, $client_permalink ) . '">' . $project['title'] . '</a>' );
					}
				}
				echo implode( ', ', $project_titles );
				break;
		}
	}

	public function define_table_columns( $column_name ) {
		//die($column_name);
		$cols = array(
			'cb'        => '<input type="checkbox" />',
			'full_name'     => __( 'Name' ),
			'projects'      => __( 'Projects' , $this->plugin_name),
			'email'      => __( 'Email' ),
			'shortcode' => __( 'Shortcode' ),
		);

		return $cols;
	}

	public function deleted_client( $postid ){

		// We check if the global post type isn't ours and just return
		if(get_post_type($postid) !== $this->slug) return;
		$client = get_post_meta($postid,'client',true);
		if(isset($client) ){
			if(isset($client['wp_user_id']))
				wp_delete_user($client['wp_user_id']);
			$projects = get_post_meta($postid,'projects',true);
			foreach($projects as $id => $project)
				do_action('codeneric/phmm/delete-project', $project);
		}



		// My custom stuff for deleting my custom post type here
	}

	public function add_support_page(){
		require_once(dirname(__FILE__).'/subpages/support.php');
		$s_p = new Photography_Management_Base_Support($this->plugin_name, $this->version, $this->slug);
		$s_p->add_support_page();
	}

	public function add_settings_page(){
		require_once(dirname(__FILE__).'/subpages/options.php');
		$s_p = new Photography_Management_Base_Options($this->plugin_name, $this->version, $this->slug);
		$s_p->add_settings_page();
	}

	public function settings_init(){
		require_once(dirname(__FILE__).'/subpages/options.php');
		$s_p = new Photography_Management_Base_Options($this->plugin_name, $this->version, $this->slug);
		$s_p->settings_init();

	}

	public function add_premium_page(){
		require_once(dirname(__FILE__).'/subpages/premium.php');
		$s_p = new Photography_Management_Base_Premium($this->plugin_name, $this->version, $this->slug);
		$s_p->add_premium_page();
//		remove_action( 'admin_notices', 'cc_phmm_base_admin_notice_update_to_premium', 9 );

	}

	public function resume_photo_upload_dir( $param ) {
		global $cc_phmm_config;
		if(isset($_SERVER['HTTP_REFERER'])) {
			$comps = parse_url($_SERVER['HTTP_REFERER']);
			if(isset($comps['query'])){
				parse_str($comps['query'],$query_params);
				$new_post = (isset($query_params['post']) && get_post_type($query_params['post']) === $cc_phmm_config['slug']);
				$edit_old_post = (isset($query_params['post_type']) && $query_params['post_type'] === $cc_phmm_config['slug']);
				if($new_post || $edit_old_post){
					add_image_size('phmm-fullscreen', 1600, 1600);
					$mydir = '/photography_management'.$param['subdir'];
					$param['path'] = $param['basedir'] . $mydir;
					$param['url'] = $param['baseurl'] . $mydir;
				}
			}
		}

		return $param;
	}


	public function update_database(){
		require_once(dirname(__FILE__).'/DBUpdater.php');
		PHMM_DBUpdater::updateClients();
	}




//	public function plugins_api_args_filter($args/*, $action*/){
//		var_dump($args);
////		var_dump($action);
//		return $args;
//	}


	public function watermark($args){
		require_once(dirname(__FILE__).'/../includes/watermarker.php');
		$WM = new Photography_Management_Watermarker();
		$WM->watermark_image($args);
	}





	/****************** AJAX HOOKS ***************/

	public function get_project_status($client_id=0){

		$upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
		$upload_dir = $upload_dir['basedir'].'/photography_management';

		$client_id = isset($_POST['client_id']) ? $_POST['client_id'] : $client_id;
		//$project_index = $_POST['$project_index'];
		$client_id = intval($client_id);

		//$zip_arr = get_option('codeneric_phmm_zip_arr');
		$zip_arr = array();
		$projects = get_post_meta($client_id,'projects', true);
		$projects = is_array($projects) ? $projects : array();
		if(isset($projects) && is_array($projects)){
			foreach($projects as $index => $project){
//				$status = isset($project['status']) ? $project['status'] : 'ok';
				$status = 'ok';
				array_push($zip_arr, array('index'=> $index, 'status' => $status ) );
			}
		}

		$res = $zip_arr;




		foreach($projects as $index => $project){
			if($zip_arr[$index]['status'] === 'in-zipping-queue' || $zip_arr[$index]['status'] === 'zipping'){
				$attach_path = array();

				foreach($project['gallery'] as $index_2 => $attachID){
					$temp = get_attached_file( $attachID);
					array_push($attach_path, $temp);
				}

				//cc_photo_manage_create_zip($attach_path, "$upload_dir/$client_id-$index.zip", true);
				$p = $this->zip_gallery("$upload_dir/$client_id-$index.zip", $attach_path, $client_id, $index);
				$res[$index]['progress'] = $p;
				$res[$index]['memory_limit'] = ini_get('memory_limit');
				$res[$index]['status'] = $res[$index]['status'] === 'in-zipping-queue' ? 'zipping' : $zip_arr[$index]['status'];

			}

		}

		if(isset($_POST['client_id'])){

			$json = json_encode($res);
			header( "Content-Type: application/json" );

			wp_die($json);

		}
		return $res;
	}

	public function star_photo_cb() {
		Photography_Management_Base_Common::star_photo_cb();
	}

	private function validate_username_fallback($un){
		$char_white_list = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 ";
		if($un[0] === ' ' || $un[strlen($un) -1] === ' ')return false;
		for($i=0; $i<strlen($un); $i++){
			if(strpos ( $char_white_list , $un[$i]) === false)return false;
		}
		return true;
	}

	public function check_username() {
		if(!isset($_POST['username'])){
			status_header(  400 );
			wp_die();
		}
		$un = $_POST['username'];
		$un = $un . '';
		$valid = strlen($un) > 0;
		if($valid){
			$valid = validate_username( $un );
			if(!$valid) $valid = $this->validate_username_fallback($un); //better check one more time
		}
		$valid = $valid && is_numeric(username_exists( $un )) === false;
		//$valid = isset($un) ;
		status_header( $valid ? 200: 400 );

		wp_die($un);
	}


	public function send_feedback() {

		if (empty($_POST) ||
		    (!isset($_POST['cc_send_feedback_nonce']) && !isset($_POST['cc_transfer_license_nonce'])) ||
		    (!wp_verify_nonce($_POST['cc_send_feedback_nonce'], 'cc_send_feedback')
		     && !wp_verify_nonce($_POST['cc_transfer_license_nonce'], 'cc_send_feedback'))) {
			echo 'You targeted the right function, but sorry, your nonce did not verify.';
			die();
		} else {


			$data = $_POST['support'];
			$to = array('support@codeneric.com');
			$subject = 'PHMM: '.$data['subject'];
			$headers[] = 'From: <'.$data['email'].'>';

			$message = $data['content'];

			global $cc_phmm_config;
			$adminurl = admin_url('edit.php');
			if(isset($_POST['cc_send_feedback_nonce'])){
				if(wp_mail( $to, $subject, $message, $headers))
					wp_redirect(add_query_arg( array('post_type' => $cc_phmm_config['slug'], 'page' => 'support', 'is_send' => "true"), $adminurl));
				else
					wp_redirect(add_query_arg( array('post_type' => $cc_phmm_config['slug'], 'page' => 'support', 'is_send' => "false"), $adminurl));
			}

			if(isset($_POST['cc_transfer_license_nonce'])){
				if(wp_mail( $to, $subject, $message, $headers))
					wp_redirect(add_query_arg( array('post_type' => $cc_phmm_config['slug'], 'page' => 'premium', 'is_send' => "true"), $adminurl));
				else
					wp_redirect(add_query_arg( array('post_type' => $cc_phmm_config['slug'], 'page' => 'premium', 'is_send' => "false"), $adminurl));
			}


			// do your function here

		}
	}

	public function premium_ajax_install(){
		require_once(dirname(__FILE__) .'/install-premium-plugin.php');
		Photography_Management_Base_Premium_Ajax_Installer();
	}

	public function update_premium(){
		$val = $_POST["bool"] == "true";
		update_option('cc_prem', $val);
		delete_option('__temp_site_transiant_54484886');
	}


	public function get_attachment() {
		//todo whole workflow a bit messy
		$attachIDArray = $_POST['attachID'];
		if ( is_array( $attachIDArray ) ) {
			$res = array();
			foreach ( $attachIDArray as $i => $attachID ) {
				$temp = Photography_Management_Base_Common::get_full_and_thumb_image( intval( $attachID ) );
				array_push( $res, $temp );

			}
			header( "Content-Type: application/json" );
			echo json_encode( $res );
			exit;

		} else {
			$attachID = intval( $attachIDArray );
			$res      = Photography_Management_Base_Common::get_full_and_thumb_image( intval( $attachID ) );
			header( "Content-Type: application/json" );
			echo json_encode( $res );
			exit;
		}

	}

	public function check_email() {
		if(!isset($_POST['email']) || !isset($_POST['clientId']) || !is_numeric($_POST['clientId']) ){
			status_header(  400 );
			wp_die();
		}
		global $cc_phmm_config;
		$em = $_POST['email'];
		$client_id = intval($_POST['clientId']);
		$user = get_user_by('email', $em);

		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => $cc_phmm_config['slug'],
			'suppress_filters' => true
		);
		$posts_array = get_posts( $args );
		$no_client_has_this_mail = true;
		$owner = -1;
		foreach($posts_array as $post ){
			$client = get_post_meta($post->ID, 'client', true);
			if($client['email'] === $em) {
				$no_client_has_this_mail = false;
				$owner = $post->ID;
				break;
			}
		}

		if($owner === $client_id){
			$user = false;
			$no_client_has_this_mail = true;
		}
		status_header( ($user === false) && is_email($em) && $no_client_has_this_mail ? 200: 400 );
		wp_die($em);
	}

	public function hide_admin_bar() {
		$options = get_option('cc_photo_settings', array());

		if (!isset($options['hide_admin_bar']))
			$options['hide_admin_bar'] = false;

		if ($options['hide_admin_bar'] &&  ! current_user_can('edit_posts') ) {
			add_filter('show_admin_bar', '__return_false');
		}
	}



}
