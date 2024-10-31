<?php

/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 28.10.2015
 * Time: 18:57
 */
class Photography_Management_Base_FS
{
    public static function rm_dir($dir){
        if ( is_dir( $dir ) ) {
            $objects = scandir( $dir );
            foreach ( $objects as $object ) {
                if ( $object != "." && $object != ".." ) {
                    if ( filetype( $dir . "/" . $object ) == "dir" ) {
                        Photography_Management_Base_FS::rm_dir( $dir . "/" . $object );
                    } else {
                        unlink( $dir . "/" . $object );
                    }
                }
            }
            reset( $objects );

            return rmdir( $dir );
        }
    }
}