<?php

function cc_photo_manage_register_post_type_client() {
	register_post_type( 'client',
		array(
			'labels' => array(
				'name' => __( 'Clients' ),
				'singular_name' => __( 'Client' ),
                'edit_item' => 'Edit Client',
                'new_item' => 'New Client',
                'add_new_item' => 'Add New Client'
            ),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'can_export' => true,
			'has_archive' => false,
			'menu_icon' => '',
			'rewrite' => array('slug' => 'client', 'with_front' => false),
			'supports' =>  array(
				'title',
				'editor' => false),
			'taxonomies' => array(''),
		)
	);




    $upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
    $upload_dir = $upload_dir['basedir'].'/photography_management';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir);
    }

}

add_action('init','cc_photo_manage_register_post_type_client');

function cc_photo_manage_enqueue_scripts(){
    wp_enqueue_media();
    wp_enqueue_script('media-upload');
    wp_enqueue_script( 'jquery' );
    //wp_enqueue_script( 'jquery.rss', plugins_url('metaboxes/scripts/jquery.rss.js',__FILE__), array('jquery') );
    wp_enqueue_style( 'metabox-css', plugins_url('metaboxes/metaboxes.css',__FILE__));
}

add_action('admin_enqueue_scripts' ,'cc_photo_manage_enqueue_scripts');



function cc_photo_manage_add_menu_icons_styles(){
	?>

	<style>
		#adminmenu .menu-icon-client div.wp-menu-image:before {
			content: "\f306";
		}
	</style>

<?php
}
add_action( 'admin_head', 'cc_photo_manage_add_menu_icons_styles' );

require_once(dirname(__FILE__).'/metaboxes/meta-client.php');
require_once(dirname(__FILE__).'/metaboxes/meta-project.php');
//require_once(dirname(__FILE__).'/metaboxes/meta-gallery.php');


$meta_keys = array('client', 'projects' ); //'gallery_url'



add_action( 'save_post', 'cc_photo_manage_cd_meta_box_save' );
function cc_photo_manage_cd_meta_box_save( $post_id )
{

	//TODO: projects was deleted => delete zip.
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;
	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;


	global $meta_keys;
	// Make sure your data is set before trying to save it


	if(!isset($_POST['post_title']))
		$_POST['post_title'] = sanitize_title($_POST['client']['full_name']);

    if(!isset($_POST['projects'])) $_POST['projects'] = array();

    $projects = $_POST['projects'];
    $upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
    $upload_dir = $upload_dir['basedir'].'/photography_management';

    foreach($projects as $index => $project){
        if(isset($project['gallery'])){
            asort($project['gallery']);
            asort($_POST['projects'][$index]['gallery']); //sort the data to be pushed, too.

            $new_gallery = implode(',', $project['gallery']);
            $old_gallery = $new_gallery . 'diff'; //first old_gallery is different
            $old_projects = get_post_meta($post_id,'projects', true);
            if(isset($old_projects) && isset($old_projects[$index])
                && isset($old_projects[$index]['gallery']))
                $old_gallery = implode(',',$old_projects[$index]['gallery']);
            echo "new:$new_gallery\n old:$old_gallery\n";
            if($new_gallery != $old_gallery){
                $attach_path = array();
                foreach($project['gallery'] as $index_2 => $attachID){
                    $attachment = cc_photo_manage_get_full_and_thumb_image(intval($attachID));
                    $attachment->id = $attachID;
                    //print_r($attachment);
                    $project['gallery'][$index_2] = $attachment;
                    $attach_path[] = get_attached_file( $attachID);
                }
                echo cc_photo_manage_create_zip($attach_path, "$upload_dir/$post_id-$index.zip", true) ? 'ok\n': 'fail\n';

            }
        }else if(file_exists ( $filename = "$upload_dir/$post_id-$index.zip" ))
            echo unlink($filename) ? 'ok' : 'fail-delete';
    }

   // die(0);



	foreach($meta_keys as $value) {
		if( isset( $_POST[$value] ) )
			update_post_meta( $post_id, $value, $_POST[$value] );
	}


}

function cc_photo_manage_change_columns( $cols ) {
  $cols = array(

    'cb'       => '<input type="checkbox" />',

    'full_name'      => __( 'Full Name',      'trans' ),

    'email' => __( 'Email', 'trans' ),

    'projects'     => __( 'Projects', 'trans' ),

  );

  return $cols;

}
add_filter( "manage_client_posts_columns", "cc_photo_manage_change_columns" );

function cc_photo_manage_custom_columns( $column, $post_id ) {
	$client = get_post_meta($post_id,"client",true);

	switch ( $column ) {
		case "full_name":
			edit_post_link($client['full_name']);
			break;
		case "email":
			echo $client['email'];
			break;
		case "projects":
			$projects = get_post_meta($post_id,"projects",true);
            //print_r($projects);
            $project_titles = array();
            $client_permalink =  get_post_permalink($post_id);
            foreach($projects as $key=>$project){
                if(isset($project['title']))
                    array_push($project_titles, '<a href="'.add_query_arg( 'project', $key, $client_permalink).'">'. $project['title'].'</a>');
            }
            echo implode(', ',$project_titles);
			break;
	}
}

add_action( "manage_posts_custom_column", "cc_photo_manage_custom_columns", 10, 2 );

function cc_photo_manage_get_full_and_thumb_image($attachID){
    $res = new stdClass();
    $m_img = wp_get_attachment_image_src($attachID, 'medium');
    $f_img = wp_get_attachment_image_src($attachID, 'full');
    $res->thumb =  is_array($m_img) ? $m_img[0] : '';
    $res->url =  is_array($f_img) ? $f_img[0] : '';
    return $res;
}

function cc_photo_manage_get_attachment(){
    $attachID = intval($_POST['attachID']);
    $res = cc_photo_manage_get_full_and_thumb_image($attachID);
    header( "Content-Type: application/json" );
    echo json_encode($res);
    exit;
}

add_action( 'wp_ajax_nopriv_cc_photo_manage_get_attachment', 'cc_photo_manage_get_attachment' );
add_action( 'wp_ajax_cc_photo_manage_get_attachment', 'cc_photo_manage_get_attachment' );


function cc_photo_manage_prem(){
    update_option('cc_prem',10000);
    echo 200;
    exit;
}

//add_action( 'wp_ajax_nopriv_cc_photo_manage_prem', 'cc_photo_manage_prem' );
add_action( 'wp_ajax_cc_photo_manage_prem', 'cc_photo_manage_prem' );

function cc_photo_manage_create_zip($files = array(),$destination = '',$overwrite = false) {
    //if the zip file already exists and overwrite is false, return false
    if(file_exists($destination) && !$overwrite) { return false; }
    //vars
    $valid_files = array();
    //if files were passed in...
    if(is_array($files)) {
        //cycle through each file
        foreach($files as $file) {
            //make sure the file exists
            if(file_exists($file)) {
                $valid_files[] = $file;
            }
        }
    }
    //if we have good files...
    if(count($valid_files)) {
        //create the archive
        $zip = new ZipArchive();
        if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //add the files
        //$i = 0;
        foreach($valid_files as $file) {
            $zip->addFile($file,basename($file) );
        }
        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

        //close the zip -- done!
        $zip->close();

        //check to make sure the file exists
        return file_exists($destination);
    }
    else
    {
        return false;
    }
}



