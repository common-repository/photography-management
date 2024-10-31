<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Photography_Management_Base
 * @subpackage Photography_Management_Base/admin
 * @author     Codeneric <support@codeneric.com>
 */
class Photography_Management_Base_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */

	private $slug;

	private $default_settings = array(
		'block_adblock' => 0
	);

	private $plugin_options_key = 'codeneric_phmm_options';
	private $settings_key = 'codeneric_ad_general_settings';
	public  $settings;

	public function __construct( $plugin_name, $version, $slug ) {

		$this->plugin_name =    $plugin_name;
		$this->version     =    $version;
		$this->slug     =       $slug;

	}

//
//
//	public function register_general_settings() {
//
//		register_setting( $this->$settings_key, $this->$settings_key );
//		$section_key = 'section_general';
//
//		add_settings_section( $section_key, __('General Settings'), array( &$this, 'section_general_desc' ), $this->$settings_key );
//
//
//		add_settings_field( 'block_adblock', __('Block AdBlock'), array( &$this, 'field_block_adbock' ), $this->$settings_key, $section_key );
//
//		add_settings_field( 'block_timezone', __('Your Timezone'), array( &$this, 'field_block_timezone' ), $this->$settings_key, $section_key );
//
//	}
//
//	public function load_settings() {
//		$this->general_settings = (array)  get_option( $this->$settings_key );
//		$this->general_settings = array_merge( $this->default_settings, $this->$settings );
//
//	}

}
