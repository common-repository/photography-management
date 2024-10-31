<?php


class Photography_Management_Base_Support{

    private $plugin_name;
    private $version;
    private $slug;

    private $page_name = 'support';
    public function __construct( $plugin_name, $version, $slug ) {

        $this->plugin_name =    $plugin_name;
        $this->version     =    $version;
        $this->slug     =       $slug;



    }

    public function add_support_page(  ) {

        add_submenu_page( 'edit.php?post_type='.$this->slug, 'PHMM '.__('Support',$this->plugin_name),  __('Support', $this->plugin_name), 'manage_options', $this->page_name, array($this, 'render_support_page') );

    }

    public function render_support_page() {


        ?>

        <div class="wrap">

            <?php if(isset($_GET['is_send']) && $_GET['is_send'] == 'true'): ?>
                <div class="updated highlight">
                    <h4>The feedback has been sent successfully. Thank you!</h4>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['is_send']) && $_GET['is_send'] == 'false'): ?>
                <div class="error highlight">
                    <h4>Ups, something went wrong. Please try to resubmit your feedback.</h4>
                    <p>You can also contact us directly: <a href="mailto:support@codeneric.com">support@codeneric.com</a></p>
                </div>
            <?php endif; ?>
            <form name="feedback_form" id="feedback_form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" >
                <h2>PHMM <?php echo __('Support',$this->plugin_name); ?></h2>
                <div class="postbox">
                    <div class="inside" style="width: 50%;">
                        <strong><?php echo __('If you encounter a problem, please <a style="color: coral" target="_blank" href="//phmm.codeneric.com">visit the official photography management website</a> for the FAQ first.',$this->plugin_name); ?></strong>
                        <br><br>
                        <strong><?php echo __('Join our <a style="color: coral" target="_blank" href="https://www.facebook.com/groups/1529247670736165/">facebook group</a> to get immediate help or get in contact with other photographers using WordPress!',$this->plugin_name); ?></strong>

                        <p><?php echo __('If you notice any bugs, have any questions regarding the plugin or want to suggest new feautures, please do so in the form.',$this->plugin_name); ?></p>
                        <h4><?php echo __('We will answer you as soon as possible!',$this->plugin_name); ?></h4>
                        <div class="cc-form-field">
                            <label for="support[email]"><?php echo __('Email'); ?></label>
                            <input   type="email" name="support[email]" value="<?php  echo get_option('admin_email'); ?>" required/>
                        </div>
                        <div class="cc-form-field">
                            <label for="support[subject]"><?php echo __('Subject'); ?></label>
                            <input type="text" name="support[subject]" required/>
                        </div>
                        <div class="cc-form-field">
                            <label><?php echo __('Message',$this->plugin_name); ?></label>
                            <textarea  name="support[content]"  cols="30" rows="10" required></textarea>
                        </div>
                        <input type="submit" name="publish" class="button button-primary" value="<?php echo __('Send',$this->plugin_name); ?>" accesskey="s">

                        <?php wp_nonce_field('cc_send_feedback','cc_send_feedback_nonce'); ?>
                        <input name="action" value="cc_send_feedback" type="hidden">



                        <!--                    <a target="_blank" href="//wordpress.org/support/plugin/photography-management">Photography Management Plugin Support Site</a>-->

                    </div>
                </div>
            </form>
        </div>


        <?php
    }
}

