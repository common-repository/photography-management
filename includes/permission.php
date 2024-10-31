<?php

/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 07.11.2015
 * Time: 17:26
 */
class Photography_Management_Base_Permission
{
    public static function current_user_can_access_client($client_id){
        if(empty($client_id))return false;
        if(post_password_required($client_id)){ //check if user is logged in
            $is_admin = current_user_can( 'manage_options' );
            if($is_admin) return true;

            $client_post = get_post_meta( $client_id, 'client', true );
            if(empty($client_post))return false; //client does not exist
            if(empty($client_post['wp_user_id'])){
//                throw new Exception('Client-post password is required, but the post has no owner (wp_user_id is empty).'); //post password is required, but the post has no owner (wp_user_id), something went terribly wrong!
                // Here we are...this post requires a password, but has no owner, i.e. no
                // automatically generated wordpress user (PhMm Client) is assigned to this
                // client-post.
                return false;
            }
            $current_user = wp_get_current_user();
            if($current_user === false)return false; //simple...user is not logged in -> dismiss
            return $current_user->ID === $client_post['wp_user_id'];

        }else{
            return true;
        }
    }
}

add_filter( 'codeneric/phmm/check_user_permission', array( 'Photography_Management_Base_Permission', 'current_user_can_access_client' ), 10, 1 );