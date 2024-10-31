<?php
//echo sanitize_title('Phil C ');
//update_option('cc_prem', false);
add_action( 'add_meta_boxes', 'add_project_meta' );
function add_project_meta()
{
	add_meta_box( 'cc_project_meta', 'Projects', 'add_project_meta_cb', 'client', 'normal', 'high' );
}
/*
function cc_get_full_and_thumb_image($attachID){
    $res = new stdClass();


    $m_img = wp_get_attachment_image_src($attachID, 'medium');
    $f_img = wp_get_attachment_image_src($attachID, 'full');
    $res->thumb =  is_array($m_img) ? $m_img[0] : '';
    $res->url =  is_array($f_img) ? $m_img[0] : '';
    return $res;
}*/


function add_project_meta_cb( $post )
{
	global $plugin;
	//wp_enqueue_style( 'metabox-css', plugins_url('metaboxes.css',__FILE__));
	wp_enqueue_script( 'angularjs', plugins_url('../../bower_components/angular/angular.js', __FILE__), array(), '1.3.8', false );
	//wp_enqueue_script( 'angularjs', plugins_url().'/bower_components/angular/angular.js', array() );

    add_thickbox();

	//register the script, first param is ID, must be unique
	wp_register_script( 'meta-project', plugins_url('scripts/meta-project.min.js',__FILE__), array('angularjs') );
	// ur data
	$projects = get_post_meta($post->ID,"projects",true);
    //print_r($projects);
    if(is_array($projects))
        foreach($projects as $index => $project){
            if(isset($project['gallery'])){
                $projects[$index]['gallery'] = array();
                foreach($project['gallery'] as $index_2 => $attachID){
                    $attachment = cc_photo_manage_get_full_and_thumb_image(intval($attachID));
                    $attachment->id = $attachID;
                    //print_r($attachment);
                    $projects[$index]['gallery'][] = $attachment;
                    //print_r($projects);
                    //array_push($gallery, $attachment->thumb);
                }
            }
        }


	// second param is the js varibale name, will be globally exposed
	wp_localize_script( 'meta-project', 'GLOB_projects', $projects );
    wp_localize_script( 'meta-project', 'GLOB_id', get_option('cc_photo_manage_id') );
    wp_localize_script( 'meta-project', 'GLOB_max', get_option('cc_prem')  ? ''.get_option('cc_prem') : '1' );
    wp_localize_script( 'meta-project', 'GLOB_clients_num', ''.wp_count_posts( 'client')->publish );
	//enque the script with given id
	wp_enqueue_script( 'meta-project' );




	//wp_enqueue_script( 'upload', plugins_url('scripts/upload.js',__FILE__), array('meta-project') );



// The script can be enqueued now or later.

	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );


	?>
	<style>
		[ng-cloak] { display: none!important;}

	</style>
	<div ng-app="Project" ng-controller="Main as ctrl" class="clearfix" ng-cloak="">

		<div class="Project" index="{{$index + 1}}" ng-repeat="i in ctrl.projects">
			<div class="cc-form-field">
				<label for="projects[{{$index}}][title]">Project Title</label>
				<input ng-model="i.title" name="projects[{{$index}}][title]" type="text" placeholder="Project Title" value="" required />
			</div>
			<div class="cc-form-field">
				<label for="projects[{{$index}}][title]">Project Link</label>
                <?php if(get_post_status( get_the_ID() ) === 'publish'): ?>
                    <a href="<?php  echo add_query_arg( 'project', '{{$index}}', get_permalink(get_the_ID())); ?>"><?php  echo add_query_arg( 'project', '{{$index}}', get_permalink(get_the_ID())); ?></a>
                <?php endif;
                    if(get_post_status( get_the_ID() ) !== 'publish')echo "<small>(you need to publish this post to get the link)</small>";
                ?>
            </div>
			<div class="cc-form-field">
				<label for="projects[{{$index}}][description]">Project Description</label>
				<textarea ng-model="i.description" name="projects[{{$index}}][description]"  rows="5" ></textarea>

			</div>
<!--			<div class="cc-form-field">-->
<!--				<label for="projects[{{$index}}][title]">available for the client</label>-->
<!--				<input ng-model="i.isAvailable" name="projects[{{$index}}][isAvailable]" value="isAvailable" type="checkbox" checked />-->
<!--			</div>-->
			<div class="uploader Gallery cc-form-field">
				<label for="">Gallery</label>

				<div class="Gallery--wrap">
					<div class="Gallery--images">
						<div class="Gallery--img" ng-repeat="elem in i.gallery track by $index" >
                            <span class="Gallery--img--remove" ng-click="ctrl.removeImage($parent.$index, $index)">x</span>
							<img ng-src="{{ elem.thumb}}" alt=""/>

							<input value="{{elem.id}}"  url-holder="{{elem.id}}" class="hidden gallery_url_text" name="projects[{{$parent.$index}}][gallery][]" type="text" />
						</div>
                        <div style="float: none; vertical-align: text-bottom;" id="cc-image-spinner-{{ $index }}" class="spinner"></div>
					</div>
					<input upload-gallery="{{ $index }}" type="button" value="Upload" class="button-primary upload-button"/>

					<input ng-if="i.gallery.length != 0"  ng-click="ctrl.emptyGallery($index)" type="button" value="Remove All" class="button-secondary remove-button" />
				</div>
			</div>
			<input ng-click="ctrl.removeProject($index)" type="button" value="x delete project" class="delete button" />
		</div>



		 <input type="button" ng-click="ctrl.addProject()" value="+ add project" class="button-primary alignright" />
	</div>

    <a href="#TB_inline?height=400&inlineId=modal-window-id" class="thickbox hidden" title="Go Premium for Free"></a>


    <div  id="modal-window-id" style="display:none;">
			<h3>Get the Premium Version for Free</h3>
			<p>You need the premium Photography Management plugin for unlimited Clients and Projects. You can also get it for free if you leave a short review on <a href="https://wordpress.org/support/view/plugin-reviews/photography-management" target="_blank">wordpress.org</a>:</p>
			<p>1. Register <a href="https://wordpress.org/support/register.php" target="_blank">here</a>
				if you don't already have an account <small>(and remember your username for the 2. step).</small>
			</p>
			<p>2. Type in your <strong>wordpress.org username</strong>: <input type="text" id="cc-username" /> </p>
			<p>3. Click on this <a href="https://wordpress.org/support/view/plugin-reviews/photography-management" target="_blank">link</a> and write a short review (you need to be logged in). </p>
			<p><small>Support: contact@codeneric.com</small></p>
			<input id="cc-go-premium" type="button" value="Check my review and unlock premium! (immediately)" class="button-primary" onclick="checkReview()" />
			<div style="float: none; vertical-align: text-bottom;" id="cc-premium-spinner" class="spinner"></div>
    </div>
    <div id="rss-feed" style="display: none;"></div>

<?php
}
