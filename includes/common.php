<?php

/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 28.10.2015
 * Time: 19:46
 */
class Photography_Management_Base_Common
{
    public static function get_full_and_thumb_image($attachID, $m = 'medium', $l = 'full', $isPub = false)
    {

        $res = new stdClass();

        if ($isPub === false) {
            $m_img = wp_get_attachment_image_src($attachID, array(160, 120));
        } else {
            $m_img = wp_get_attachment_image_src($attachID, $m);
        }

        $f_img = wp_get_attachment_image_src($attachID, $l);
        $res->thumb = is_array($m_img) ? $m_img[0] : '';
        $res->url = is_array($f_img) ? $f_img[0] : '';
        $res->id = intval($attachID);
        $p = get_post($attachID);
        if (!empty($p))
            $res->caption = get_post($attachID)->post_excerpt;

        $meta = wp_get_attachment_metadata($attachID);
        if(is_array($meta) && isset($meta['file']) ) {
            $res->filename = basename($meta['file']);
        }
        else {
            $res->filename = 'file not found';
        }



        return $res;
    }

    public static function process_favorites($client_id, $project_id, $favs = array()){
//        $projects = get_post_meta($client_id, "projects", true);
//        $projects = is_array($projects) ? $projects : array();
//
//        $project = isset($projects[$project_id]) ? $projects[$project_id] : array();
//        $project['starred'] = $favs;

        $starred_filesnames = array();
        $upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
        $upload_dir = $upload_dir['basedir'] . '/photography_management';
        $filename = "favorites-" . $client_id . "-" . $project_id . ".txt";
        $selected_favs_path = $upload_dir . '/' . $filename;
        $real_path = realpath(dirname($selected_favs_path));
        $ext = pathinfo($selected_favs_path, PATHINFO_EXTENSION);

        if ($real_path !== $upload_dir || $ext !== 'txt')
            return false;


        $myfile = fopen($selected_favs_path, "w");

        if($myfile === false)return false;

        fwrite($myfile, "*** Favorites selected by client ***" . PHP_EOL . PHP_EOL);

        if (empty($project['starred'])) {
            fwrite($myfile, "The client unselected all images." . PHP_EOL);
        }

        foreach ($favs as $ID) {
            fwrite($myfile, basename(get_attached_file($ID)) . PHP_EOL);
            array_push($starred_filesnames, basename(get_attached_file($ID)));
        }

        fclose($myfile);

        $temp = wp_upload_dir();
        return $temp['baseurl'] . '/photography_management/' . $filename;



    }

    public static function star_photo_cb()
    {

        if (!isset($_POST['postID']) || !isset($_POST['projectID'])) {
            status_header(400);
            wp_die();
        }

        require_once(dirname(__FILE__) . '/permission.php');
        $permit = Photography_Management_Base_Permission::current_user_can_access_client($_POST['postID']);
        if (!$permit) {
            status_header(401);
            wp_die();
        }

        $res = new stdClass();


        $projects = get_post_meta(intval($_POST['postID']), "projects", true);

        $projects = is_array($projects) ? $projects : array();

        if (!isset($projects[$_POST['projectID']])) {
            status_header(400);
            wp_die();
        }

        $project = $projects[$_POST['projectID']];
        if (!isset($project['starred'])) {
            $project['starred'] = array();

        }


        $project['starred'] = isset($_POST['photoIDs']) ? $_POST['photoIDs'] : array();


        $starred_filesnames = array();
//        if ( ! empty( $project['starred'] ) ) {
        $upload_dir = wp_upload_dir();//['basedir'].'/photography_management';
        $upload_dir = $upload_dir['basedir'] . '/photography_management';
        $filename = "favorites-" . $_POST['postID'] . "-" . $_POST['projectID'] . ".txt";

        $selected_favs_path = $upload_dir . '/' . $filename;

        $real_path = realpath(dirname($selected_favs_path));

        $ext = pathinfo($selected_favs_path, PATHINFO_EXTENSION);

        if ($real_path !== $upload_dir || $ext !== 'txt')
            wp_die();


        $myfile = fopen($selected_favs_path, "w") or die("Unable to open file!");

        fwrite($myfile, "*** Favorites selected by client ***" . PHP_EOL . PHP_EOL);

        if (empty($project['starred'])) {
            fwrite($myfile, "The client unselected all images." . PHP_EOL);
        }

        foreach ($project['starred'] as $ID) {
            fwrite($myfile, basename(get_attached_file($ID)) . PHP_EOL);
            array_push($starred_filesnames, basename(get_attached_file($ID)));
        }

        fclose($myfile);

        $temp = wp_upload_dir();
        $project['download_favs'] = $temp['baseurl'] . '/photography_management/' . $filename;
//        }


        $projects[$_POST['projectID']] = $project;
        $res->data = $project;
        update_post_meta($_POST['postID'], 'projects', $projects);
//        echo json_encode( $res );

        /** SEND EMAIL */
//        echo json_encode(get_option('cc_phmm_pf'));
//        if(!get_option('cc_phmm_pf'))
//            wp_die();


        $client = get_post_meta(intval($_POST['postID']), "client", true);
        $headers = 'From: PHMM Notifier <phmm-notifier@codeneric.com>' . PHP_EOL . PHP_EOL;

        $body = 'Your client ' . $client['full_name'] . ' has changed his/her favorites' . PHP_EOL . PHP_EOL
            . '------------------------------------------------------------' . PHP_EOL . PHP_EOL
            . 'Client: ' . $client['full_name'] . PHP_EOL
            . 'Project: ' . $project['title'] . PHP_EOL
            . 'Images:' . PHP_EOL;
        if (count($starred_filesnames) == 0)
            $body = $body . 'No images selected' . PHP_EOL;
        else
            foreach ($starred_filesnames as $file) {
                $body = $body . $file . PHP_EOL;
            }
        $body = $body . PHP_EOL
            . '------------------------------------------------------------'
            . PHP_EOL . PHP_EOL
            . 'Regards,' . PHP_EOL . PHP_EOL . 'Photography Management Team' . PHP_EOL . 'www.codeneric.com';

        $body = $body . PHP_EOL . PHP_EOL . PHP_EOL
            . 'Please do not reply to this email, since it is generated automatically. If you have questions, contact us at support@codeneric.com';


        //$admin_mail = get_option('admin_email');

        //$options = get_option('cc_photo_settings');
        //$options['cc_email_recipient'];

        $emails = get_option('admin_email');

        if (has_filter('cc_phmm_register_get_email_recipients')) {
            $emails = apply_filters('cc_phmm_register_get_email_recipients', $emails);
            wp_mail($emails, $client['full_name'] . ' has changed his/her favorite selection of project ' . $project['title'], $body, $headers);

        }

        status_header(200);
        wp_die(json_encode($res));
    }

    public static function get_minithumb_obj($id, $client_id, $project_id)
    {
        //	$id = intval($id);
        $imagedata = wp_get_attachment_metadata($id);

        $postdata = get_post($id);

        $filename = false;
        $img_urls = array();
        $original_file = '';
        $caption = '';
        if (is_array($imagedata) && isset($imagedata['sizes']) && isset($imagedata['sizes']['medium']) && isset($imagedata['sizes']['medium']['file'])) {
            $filename = $imagedata['sizes']['medium']['file'];

            //$caption = $imagedata['image_meta']['caption'];
            $caption = $postdata->post_excerpt;

            $original_file = basename($imagedata['file']);
//			$filename = $imagedata['sizes']['thumbnail']['file'];
            $o_path = get_attached_file($id, true);
            $filename = dirname($o_path) . "/$filename";

            $images = array();

            $images['medium'] = wp_get_attachment_image_src($id, 'medium');
            $images['post-thumbnail'] = wp_get_attachment_image_src($id, 'post-thumbnail');
            $images['large'] = wp_get_attachment_image_src($id, 'large');
            $images['phmm-fullscreen'] = wp_get_attachment_image_src($id, 'phmm-fullscreen');


            $medium_url = '';


            foreach ($images as $key => $val) {
                if (is_array($val)) {
                    if ($val[3]) { //is intermediate size
                        $medium_url = $val[0];
                    } elseif (is_array($images['large'])) { //use 'large' at most
                        $medium_url = $images['large'][0];
                    }

                    $img_urls[$key] = add_query_arg(array('client' => $client_id, 'project' => $project_id, 'attach' => $id), $medium_url);
                }

            }
//
//
//
//            $fullscreen_img      = wp_get_attachment_image_src( $id, 'phmm-fullscreen' );
//
//            if($fullscreen_img === false || !$fullscreen_img[3]){
//                $fullscreen_img      = wp_get_attachment_image_src( $id, 'large' );
//            }


            if (!file_exists($filename))
                return false;
        }


//		$filename = wp_get_attachment_thumb_file( $id );
        //echo $filename;
        if ($filename === false) return false;
        //$filename = $filename[0];
        list($width, $height, $image_type) = getimagesize($filename);
        $b64_path = dirname($filename) . "/$id.b64";
        if (!file_exists($b64_path)) {

            //$file   = @fopen( $filename, 'rb' );

            $newwidth = min(20, intval(20 * ($width / $height)));
            $newheight = min(20, intval(20 * ($height / $width)));

            // Load
            $minithumb = imagecreatetruecolor($newwidth, $newheight);
            $image_type_str = null;
            $source = null;

            switch ($image_type) {
                case 1:
                    $source = imagecreatefromgif($filename);
                    break;
                case 2:
                    $source = imagecreatefromjpeg($filename);
                    $image_type_str = 'jpeg';
                    break;
                case 3:
                    $source = imagecreatefrompng($filename);
                    $image_type_str = 'png';
                    break;
                default:
                    break;
            }
            //			$source = imagecreatefromjpeg($filename);
            if ($source === null || $image_type_str === null) return false;
            // Resize
            imagecopyresized($minithumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

            ob_start();
            imagejpeg($minithumb); // no second parameter, will do output instead of writing to file
            $img = ob_get_clean();
            $b64 = "data:image/$image_type_str;base64," . base64_encode($img);


            imagedestroy($minithumb);
            imagedestroy($source);

            $fp = fopen($b64_path, 'w');
            fwrite($fp, $b64);
            fclose($fp);

            return array('id' => $id, 'b64' => $b64, 'w' => $width, 'h' => $height, 'image_urls' => $img_urls, 'filename' => $original_file, 'caption' => $caption);

        } else {
            $fp = fopen($b64_path, 'r');
            $res = fread($fp, filesize($b64_path));
            fclose($fp);
            return array('id' => $id, 'b64' => $res, 'w' => $width, 'h' => $height, 'image_urls' => $img_urls, 'filename' => $original_file, 'caption' => $caption);
        }
    }

}