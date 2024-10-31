<?php


add_action( 'add_meta_boxes', 'cc_photo_manage_add_client_meta' );
function cc_photo_manage_add_client_meta()
{
	add_meta_box( 'cc_client_meta', 'Client', 'cc_photo_manage_add_client_meta_cb', 'client', 'normal', 'high' );
}


function cc_photo_manage_add_client_meta_cb( $post )
{




	$client = get_post_meta($post->ID,"client",true);
	$pwd = $post->post_password;


	$usePwd = !empty($pwd);//post_password_required($post->ID);



	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );


	?>
        <div class="cc-form-field" style="display: none;" id="premium-message">
          <h1><a href="#"> <strong onclick="jQuery('a.thickbox').click();">You need to get the free Premium version!</strong></a></h1>
        </div>
		<div class="cc-form-field">
			<label for="client[full_name]">Full Name</label>
			<input id="cc-full-name" name="client[full_name]" type="text" placeholder="Full Name" value="<?php echo isset($client['full_name'])? $client['full_name'] : ''; ?>"  required=""/>
		</div>
		<div class="cc-form-field">
			<label for="client[email]">Email</label>
			<input name="client[email]" type="email" placeholder="Email" value="<?php echo isset($client['email'])? $client['email'] : ''; ?>" required="" />
		</div>
        <div class="cc-form-field">
            <label for="client[email]">Password Protected</label>
            <input type="checkbox" id="cc-password-check" name="visibility" value="password" <?php echo $usePwd==true ? 'checked' : ''; ?> >
            <small id="cc-password-hint" class="<?php echo $usePwd==true ? '' : 'hidden'; ?> "> (Log out of Wordpress to test the password protection.)</small>
        </div>
		<div class="cc-form-field">
			<label id="cc-password-label" for="post_password[email]" class="<?php echo $usePwd ? '': 'hidden'; ?>">Client Password</label>
            <input id="cc-password-textbox" type="text" class="<?php echo $usePwd ? '': 'hidden'; ?>" name="post_password" placeholder="Password" id="post_password" value="<?php echo $pwd; ?>" maxlength="20" >
		</div>
     <script>
        var PW = "<?php echo $pwd; ?>";
        jQuery('#visibility').remove();
        jQuery('#cc-password-check').change(function(){
            if(jQuery(this).is(':checked')){
                jQuery('#cc-password-label').removeClass('hidden');
                jQuery('#cc-password-textbox').removeClass('hidden').val(PW);
                jQuery('#cc-password-hint').removeClass('hidden');
            }else{
                jQuery('#cc-password-label').addClass('hidden');
                jQuery('#cc-password-textbox').addClass('hidden').val('');
                jQuery('#cc-password-hint').addClass('hidden');
            }

        });
        jQuery('#cc-password-textbox').change(function(){
            PW = jQuery(this).val();
        });

        jQuery('#cc-full-name').change(function(){
            //jQuery('[name=post_title]').val(jQuery('#cc-full-name').val());
        });

        jQuery('#publish').click(function(){

            var div = document.createElement('div');
            div.className = 'updated';
            div.innerHTML = 'Zipping images...</br>This could take a while</br><strong>Do not close this window.</strong>';

            var container = document.getElementsByClassName('postbox-container')[0];
            container.appendChild(div);

        });
        jQuery('#titlediv').addClass('hidden');

    </script>


<?php
}
