<!DOCTYPE html>
<html lang="en">
<?php $plugins_url = plugins_url();
//echo plugins_url( 'js/jquery.swipebox.js',__FILE__ );
?>
<?php


function my_scripts_method() {
    wp_enqueue_style('bootstrap.min', plugins_url( 'css/bootstrap.min.css',__FILE__ ) );
    wp_enqueue_style('thumbnail-gallery', plugins_url( 'css/thumbnail-gallery.css',__FILE__ ) );
    wp_enqueue_style('swipebox.min', plugins_url( 'js/swipebox/src/css/swipebox.min.css',__FILE__ ) );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script('jquery.swipebox.min', plugins_url( 'js/swipebox/src/js/jquery.swipebox.min.js',__FILE__ ),array('jquery') );
    wp_enqueue_script('bootstrap.min', plugins_url( 'js/bootstrap.min.js',__FILE__ ),array('jquery') );

    wp_enqueue_script('html5shiv', plugins_url( 'js/html5shiv.js',__FILE__ ) );
    wp_enqueue_script('respond.min', plugins_url( 'js/respond.min.js',__FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
?>
<?php
//get_header();
   wp_head();

?>


<body>

    <!-- Navigation -->


    <?php
    $preview = isset($_GET['preview']) ? $_GET['preview'] : false;

    if($preview)echo "<h1 style='text-align: center;'>Preview-mode is currently not supported, but will be in the next version.</h1>";

    if(!isset($_GET['project'])){
        echo "<h1 style='text-align: center;'>This link is currently not in use, click on a project-link to view it.</h1>";
    }

    if(isset($_GET['project']) && !$preview):

        //print_r($project);
        if ( post_password_required() ){
            echo "<style>form p {text-align: center;}</style>";
            echo get_the_password_form();
        }

        if(!post_password_required() ) :
            //TODO: Do not just provide zip, require a secret string
            $post_id = get_post()->ID;
            $project_id = $_GET['project'];
            $project = get_post_meta($post_id,"projects",true);
           // if(count($project) === 0)
            $project = $project[$project_id];
            //print_r($project);
            $attach_path = array();
            if(!isset($project['gallery']))$project['gallery'] = array();
            foreach($project['gallery'] as $index_2 => $attachID){
                $attachment = cc_photo_manage_get_full_and_thumb_image(intval($attachID));
                $attachment->id = $attachID;
                //print_r($attachment);
                $project['gallery'][$index_2] = $attachment;
                $attach_path[] = get_attached_file( $attachID);
            }
            $download_url = wp_upload_dir();//['baseurl']."/photography_management/$post_id-$project_id.zip";
            $download_url = $download_url['baseurl']."/photography_management/$post_id-$project_id.zip";
            ?>


    <!-- Page Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"><?php echo $project['title']; ?></h1>
                <p><?php echo $project['description']; ?></p>
            </div>
            <?php foreach($project['gallery'] as $key=>$elem): ?>
                <div class="col-lg-3 col-md-4 col-xs-6 thumb">
                    <a rel="gallery" href="<?php echo $elem->url; ?>"  class="thumbnail swipebox" >
                        <img class="img-responsive" src="<?php echo $elem->thumb; ?>" alt="">
                    </a>
                </div>
            <?php endforeach; ?>

        </div>

        <hr>
        <div class="btn-group"> <a href="<?php echo count($project['gallery']) > 0 ? $download_url : '#' ?>">
                <button id="download-all" type="button" class="btn btn-primary" >
                    Download All
                </button>
            </a>
        </div>
       <?php wp_footer(); ?>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery(".swipebox").swipebox();
        });
    </script>

        <?php endif; ?>
    <?php endif; ?>
</body>

</html>
