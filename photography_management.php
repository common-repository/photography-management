<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://codeneric.com
 * @since             1.0.0
 * @package           Photography_Management
 *
 * @wordpress-plugin
 * Plugin Name:       Photography Management
 * Plugin URI:        phmm.codeneric.com
 * Description:       Provide your clients with links to (optionally password protected) photographs.
 * Version:           3.2.5
 * Author:            Codeneric
 * Author URI:        http://codeneric.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       photography-management
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//if ( ! function_exists( 'get_plugins' ) ) {
//	require_once ABSPATH . 'wp-admin/includes/plugin.php';
//}
//$all_plugins = get_plugins();
//var_dump($all_plugins);
require_once(dirname(__FILE__) .'/admin/shortcode.php'); //TODO(alex): refactor

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/activator.php
 */
function activate_photography_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
	Photography_Management_Base_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/deactivator.php
 */
function deactivate_photography_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
	Photography_Management_Base_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_photography_management' );
register_deactivation_hook( __FILE__, 'deactivate_photography_management' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/base.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_photography_management() {

	require_once dirname( __FILE__ ) . '/includes/permission.php'; //require this s.t. the permission filter gets registered

	global $cc_phmm_config;
	if(!isset($cc_phmm_config)) {
		require_once dirname(__FILE__) . '/config.php';
		$config = Photography_Management_Base_Config::set('production');
		$GLOBALS["cc_phmm_config"] = $config;
	}
	$plugin = new Photography_Management_Base();
	$plugin->run();


}
run_photography_management();
