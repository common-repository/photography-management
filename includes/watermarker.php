<?php

/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 29.11.2015
 * Time: 01:22
 */
class Photography_Management_Watermarker
{
    public function watermark_image($args){

        ini_set("log_errors", 1);
        ini_set("error_log", dirname(__FILE__)."/php-error.log");

        $filename = $args['file'];
        list($w_img, $h_img, $image_type) = getimagesize($filename);
        $watermark_path = dirname(__FILE__).'/../assets/watermark.png';
        list($w_watermark, $h_watermark) = getimagesize($watermark_path);

        $pos_h = get_option('codeneric/phmm/watermark/pos-h', 0.5); //50% is default
        $pos_v = get_option('codeneric/phmm/watermark/pos-v', 0.5); //50% is default
        $scale = get_option('codeneric/phmm/watermark/scale', 1.0); //50% is default

        $ratio = min($w_img / $w_watermark, $h_img / $h_watermark ) * $scale;


//        $w_watermark_new = ceil( $w_img * $fraction_h);
//        $h_watermark_new = ceil( ($w_watermark_new / $w_watermark) * $h_watermark);
        $w_watermark_new = ceil( $w_watermark * $ratio);
        $h_watermark_new = ceil( $h_watermark * $ratio);

        if(function_exists('ini_set')){
           // $memory = ceil((($w_watermark * $h_watermark+ $w_img * $h_img + $w_watermark_new * $h_watermark_new ) * 16 ) / (8 * 1000 * 1000)) + ceil(memory_get_usage(true)/(1000*1000));
           // ini_set("memory_limit",$memory."M");
            ini_set("memory_limit","-1");
           // error_log( "Set memory: " . $memory . "M" );
        }

        switch ($image_type)
        {
            case 1: $dest = imagecreatefromgif($filename); break;
            case 2: $dest = imagecreatefromjpeg($filename);  break;
            case 3: $dest = imagecreatefrompng($filename); break;
            default: return null;  break;
        }




        //try to load image
        $watermark_src = imagecreatefrompng($watermark_path);




        $abs_pos_h = ceil( $pos_h * $w_img - $w_watermark_new/2);
        $abs_pos_v = ceil( $pos_v * $h_img - $h_watermark_new/2);

        $abs_pos_h = $abs_pos_h + $w_watermark_new <= $w_img ? $abs_pos_h : $abs_pos_h - ($abs_pos_h + $w_watermark_new - $w_img);
        $abs_pos_v = $abs_pos_v + $h_watermark_new <= $h_img ? $abs_pos_v : $abs_pos_v - ($abs_pos_v + $h_watermark_new - $h_img);

        $abs_pos_h = $abs_pos_h  >= 0 ? $abs_pos_h : 0;
        $abs_pos_v = $abs_pos_v  >= 0 ? $abs_pos_v : 0;


        $watermark = imagecreatetruecolor($w_watermark_new, $h_watermark_new);
        imagealphablending($watermark, false);
        imagesavealpha($watermark, true);

        imagecopyresampled ( $watermark , $watermark_src , 0 , 0 , 0 , 0 , $w_watermark_new, $h_watermark_new , $w_watermark, $h_watermark );



//        imagecopymerge($dest, $watermark, 10, 9, 0, 0, 181, 180, 75); //have to play with these numbers for it to work for you, etc.
        imagecopy($dest, $watermark, $abs_pos_h, $abs_pos_v, 0, 0, $w_watermark_new, $h_watermark_new); //have to play with these numbers for it to work for you, etc.


//        imagejpeg($dest);
        switch ($image_type)
        {
            case 1: imagegif($dest); break;
            case 2: imagejpeg($dest);  break; // best quality
            case 3: imagepng($dest); break; // no compression
            default: echo ''; break;
        }

        imagedestroy($dest);
        imagedestroy($watermark_src);
        imagedestroy($watermark);
//        exit;
    }
}