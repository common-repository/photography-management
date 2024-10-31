<?php

//add_action( 'admin_menu', 'codeneric_phmm_premium_page' );

class Photography_Management_Base_Premium
{

    private $plugin_name;
    private $version;
    private $slug;

    private $page_name = 'premium';
    public function __construct( $plugin_name, $version, $slug ) {

        $this->plugin_name =    $plugin_name;
        $this->version     =    $version;
        $this->slug     =       $slug;



    }

    public function add_premium_page()
    {

        add_submenu_page('edit.php?post_type='.$this->slug, 'Photography Management Premium', __('Premium'), 'manage_options', $this->page_name, array($this, 'render_premium_page'));

    }

    public function render_premium_page()
    {



        ?>
        <div id="premium-modal"></div>
        <script>
            jQuery('#cc_phmm_notice_wrap').hide(); //better remove_action than this
        </script>

        <div class="wrap">
            <div class="postbox">
                <?php
                // START: REPLACE FOR DEMO
                ?>
                <div id="cc-phmm-container" class="inside" style="width: 50%;">
                    <div style="background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:block;"></div>
                </div>


                <?php

                // REPLACE WITH
                /*
                    ?>
                        <div class="inside">
                            <h1>This is a demo of PHMM premium</h1>
                        </div>
                    <?php
                */
                // END: REPLACE FOR DEMO
                ?>
            </div>
            <br><br>
            <strong><?php echo __('Join our <a style="color: coral" target="_blank" href="https://www.facebook.com/groups/1529247670736165/">facebook group</a> to get immediate help or get in contact with other photographers using WordPress!',$this->plugin_name); ?></strong>

        </div>


        <?php

      
    }

}