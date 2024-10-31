<?php
/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 25.04.2016
 * Time: 19:59
 */

class Photography_Management_Base_Protect_Images
{

    public $config;
    public function __construct( ) {

        global $cc_phmm_config;
        $this->config = $cc_phmm_config;
    }

//    public function provide_file($file){
//        if (file_exists($file)) {
//            header('Content-Description: File Transfer');
//            header('Content-Type: application/octet-stream');
//            header('Content-Disposition: attachment; filename="'.basename($file).'"');
//            header('Expires: 0');
//            header('Cache-Control: must-revalidate');
//            header('Pragma: public');
//            header('Content-Length: ' . filesize($file));
//            readfile($file);
//            exit;
//        }
//    }
    public function provide_file($filename){


        if($_GET['f'] === 'zip-all' || $_GET['f'] === 'zip-favs'){

            //ini_set('memory_limit', '3M');

            $current_user = wp_get_current_user();
            $user_id = $current_user !== false ? $current_user->ID : 0;

            $hook_args = array('client'=>$_GET['client'], 'project'=> $_GET['project'], 'user'=> $user_id, 'type'=> 'zip-download');
            do_action('codeneric/phmm/statistics/event', $hook_args);

            $dir = dirname( __FILE__ ); //.../protect-images
            require_once($dir.'/ZipStream/ZipStream.php');
            $gallery = $this->get_gallery_from_url();
            $files = array();
            if($_GET['f'] === 'zip-favs'){
                $files = apply_filters('codeneric/phmm/zip/only-favs', $files, $hook_args);
            }elseif($_GET['f'] === 'zip-all') {
                foreach ($gallery as $attach_id)
                    $files[] = get_attached_file($attach_id);
            }

            # create a new zipstream object
            $zip = new Photography_Management_Base_ZipStream('gallery_'.$_GET['project'].'.zip', array('large_file_size'=> 1 ));

            foreach($files as $file ){
                $zip->addFileFromPath(basename($file), $file);
            }
            $zip->finish();
            exit;
        }


        $options = get_option('cc_photo_settings', array());
        if( isset($options['watermark']) && getimagesize($filename) !== false && has_action('codeneric/phmm/watermark')){ //image requested

            $args = array('file'=>$filename);
            do_action('codeneric/phmm/watermark', $args);
            exit;
        }


        $file = @fopen($filename, 'rb');
        $buffer = 1024 * 8;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');

        while (!feof($file)) {
            echo fread($file, $buffer);
            flush();
        }
        fclose($file);

        exit;
    }

    private function get_client_from_url(){
        $client_post = get_post_meta( $_GET['client'], 'client', true );

        return $client_post;
    }

    private function get_project_from_url(){
        $projects = get_post_meta( $_GET['client'], 'projects', true );
        $project = isset($projects) && isset($projects[$_GET['project']]) ? $projects[$_GET['project']] : null;

        return $project;
    }

    private function get_gallery_from_url(){
        $project = $this->get_project_from_url();
        $gallery = isset($project) && isset($project['gallery']) ? $project['gallery'] : array();

        return $gallery;
    }

    public function user_can_access_post(){
        $is_admin = current_user_can( 'manage_options' );
        if($is_admin) return true;
        if(!post_password_required($_GET['client'])){
            return true; //post is either not protected or user has permission via post-password-form
        }
        $owner = $this->get_client_from_url();
        if(empty($owner))return false; //client does not exist
        if(empty($owner['wp_user_id']))
            return false; //old post, has no wp_user assigned yet

        $current_user = wp_get_current_user();

        if($current_user === false)return false; //owner exists, but user is not logged in

        return $current_user->ID === $owner['wp_user_id']; //currently only owner and admins allowed!
    }

    private function file_is_zip(){
        $ext = pathinfo($_GET['f'], PATHINFO_EXTENSION);
        return $ext === 'zip';
//        return $_GET['f'] === 'zip-all';
    }
//    private function get_attachment_id_from_src ($image_src) {
//
//        global $wpdb;
//        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
//        $id = $wpdb->get_var($query);
//        return $id;
//
//    }

    private function file_belongs_to_attachment($url){
        $attach_url = wp_get_attachment_url( $_GET['attach'] );
        if($attach_url === $url)return true;
        $sizes = get_intermediate_image_sizes();
        $sizes = is_array($sizes) ? $sizes : array();
        $sizes[] = 'phmm-fullscreen';
        foreach($sizes as $size){
            $data = wp_get_attachment_image_src( $_GET['attach'], $size );
//            if($data === false)return false;
            if(is_array($data) && $data[0] === $url )
                return true;
        }
        return false;

    }

    public function user_can_access_file(){
        //at this point, the user is permitted to access the specified client and project!

        //special case: user is admin, she can do everything!
        $is_admin = current_user_can( 'manage_options' );
        if($is_admin) return true;

        //case: user wants zip, check if he is permitted
        if($_GET['f'] === 'zip-all' || $_GET['f'] === 'zip-favs') return $this->user_can_access_post();
        if($this->file_is_zip()){
            $zip_name = pathinfo($_GET['f'],PATHINFO_FILENAME );
            $zip_data = explode('-', $zip_name);
            if(count($zip_data) !== 2)return false; //PHMM does not provide other zip files than the generated ones
            return $zip_data[0] === $_GET['client'] && $zip_data[1] === $_GET['project'] && $this->user_can_access_post();
        }

        //case: user wants other file, check if the file is part of the client-post!
        $projects = get_post_meta( $_GET['client'], 'projects', true );
        $project = isset($projects) && isset($projects[$_GET['project']]) ? $projects[$_GET['project']] : null;
        if($project === null)return false;

        //requested file has to be an attachment, check this!
        //debug:OK
        $upload_url = wp_upload_dir();
        $upload_url = $upload_url['baseurl'];
//        $attach = $this->get_attachment_id_from_src("$upload_url/photography_management/".$_GET['f']);
//        if($attach === null)return false;
        $attach_url = "$upload_url/photography_management/".$_GET['f'];
        if(!$this->file_belongs_to_attachment($attach_url))
            return false;

        //from now on we know that requested file is really the specified attachment ($_GET['attach'])

        //special case: user requests project-thumbnail
        if(isset($project['thumbnail']) && $project['thumbnail'] === $_GET['attach']){
            return true; //user is permitted to load the project-thumbnail
        }

        if(empty($project['gallery']))return false;
        foreach($project['gallery'] as $key => $id){
            if($id === $_GET['attach'])
                return true;
        }
        return false; //can not find requested file in the specified project -> not permitted

    }

    private function is_exposed_cover_image(){
        $options = get_option('cc_photo_settings', array());

        if(!empty($options['expose_cover_images'])){ // we have to check if the requested file is a cover image
            $project = $this->get_project_from_url();
            if(!isset($project))return false; //project does not exist, bail out
            if(isset($project['thumbnail']) && $project['thumbnail'] === $_GET['attach']){
                return true; // the requested file is the cover image
            }elseif(!isset($project['thumbnail']) && isset($project['gallery']) && isset($project['gallery'][0]) && $project['gallery'][0] === $_GET['attach']){
                return true; //the requested file is the default cover image
            }
            return false; //non of the 2 whitelisted situation is applicable
        }else{
            return false; //user does not want to expose cover images
        }
    }

    public function user_is_permitted(){
        $user_can_access_post = $this->user_can_access_post();
        $user_can_access_file = $this->user_can_access_file();
        $is_exposed_cover_image = $this->is_exposed_cover_image();
        return ($user_can_access_post && $user_can_access_file) || $is_exposed_cover_image;
    }

//    public function get_project_from_url(){
//        $div = explode('/', $_GET['f']);
//
//        $client_meta_project = get_post_meta( $div[0], 'projects', true );
//
//        return $client_meta_project[$div[1]];
//
//    }

    public function check_prama(){
//        $div = explode('/', $_GET['f']);
//        if(count($div) !== 3) return false;
//
//        return true;
        $is_admin = current_user_can( 'manage_options' );
        if($is_admin)
            return isset($_GET['f']); //admin can request without any further information

        return isset($_GET['f']) && isset($_GET['client']) && isset($_GET['project']) && isset($_GET['attach']); //everyone else needs to define 'client' and 'project'

    }

}