<?php

/**
 * Created by PhpStorm.
 * User: denis_000
 * Date: 28.10.2015
 * Time: 17:51
 */

require_once('fs.php');

class Photography_Management_Base_Zip
{

    public static function add_to_zip( $files = array(), $destination = '', $overwrite = false, $zip_arr_name = '' ) {

        $valid_files = array();
        //if files were passed in...
        if ( is_array( $files ) ) {
            //cycle through each file
            foreach ( $files as $file ) {
                //make sure the file exists
                if ( file_exists( $file ) ) {
                    $valid_files[] = $file;
                }
            }
        }else{
//            codeneric_phmm_add_to_log( 'Files is not array!' );
        }

        //if we have good files...
        if ( count( $valid_files ) && class_exists( 'ZipArchive' ) ) {
            //create the archive




            $zip = new ZipArchive();

//            codeneric_phmm_add_to_log( 'Start zipping with ZipArchive...' );

            if ( $zip->open( $destination, ZIPARCHIVE::CREATE ) !== true ) {
//                codeneric_phmm_add_to_log( 'Failed to open archive.' );
                error_log ( 'Failed to open archive.' );
                return false;
            }
            //add the files

            foreach ( $valid_files as $i => $file ) {
                $zip->addFile( $file, basename( $file ) );
            }

            //close the zip -- done!
            //codeneric_phmm_add_to_log( 'About to close zip...' );

            if ( $zip->close() !== true ) {
//                codeneric_phmm_add_to_log( 'Failed to close archive.' );
            }

//            codeneric_phmm_add_to_log( 'Done zip closing.' );

            //check to make sure the file exists

            return file_exists( $destination );
        } elseif ( count( $valid_files ) && function_exists( 'exec' ) ) {
//            codeneric_phmm_add_to_log( 'Start zipping with Exec...' );
            $full_temp_folder_path = $destination . "_temp_folder";

            if ( ! mkdir( $full_temp_folder_path ) ) {
//                codeneric_phmm_add_to_log( 'Failed to make directory.' );

                return false;
            }

            foreach ( $valid_files as $i => $file ) {
                copy( $file, $full_temp_folder_path . "/" . basename( $file ) );

            }

            $temp = exec( "(cd $full_temp_folder_path ; zip -q -9 -r $destination *)" );

//            return codeneric_phmm_rrmdir( $full_temp_folder_path );
            return Photography_Management_Base_FS::rm_dir( $full_temp_folder_path );

        } else {
//            codeneric_phmm_add_to_log( 'Neither ZipArchive nor Exec are available, zipping failed!' );

            return false;
        }
    }

}