/**
 * Created by denis_000 on 03.11.2015.
 */
function cc_phmm_handle_download_click(e) {
    e.preventDefault();
    jQuery('#cc_phmm_notice_spinner').css('display', 'inline-block');
    //alert('alles ok');
    jQuery.post(ajaxurl, {action: 'codeneric_phmm_install_premium'}, function (r) {
        console.log('codeneric_phmm_install_premium', r);
        if (r.status === 'ok') { // install was successful
            //dont redirect to premium page
            jQuery('#cc_phmm_notice_spinner').hide();
            var plugins_url = jQuery('#cc_phmm_install_notice').attr('data-plugins-url');
            jQuery('#cc_phmm_notice_wrap').html('<p>Installation was successful! Please go to your <a href="' + plugins_url + '">plugins page</a> and activate the <strong>Photography Management Premium </strong> plugin.</p>');
            //jQuery('#cc_phmm_notice_wrap').html('<p>The installation was successful!</p>');
            jQuery('#cc_phmm_notice_wrap').attr('class', 'updated');
        } else {
            location.href = jQuery('#cc_phmm_install_notice').attr('href');
        }
    });
}
jQuery('#cc_phmm_notice_spinner').hide();
jQuery('#cc_phmm_install_notice').on('click', cc_phmm_handle_download_click);