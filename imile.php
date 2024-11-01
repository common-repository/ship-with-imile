<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.imile.com
 * @since             1.0.1
 * @package           Imile
 *
 * @wordpress-plugin
 * Plugin Name:       Ship With iMile
 * Plugin URI:        https://www.imile.com
 * Description:       iMile Delivery - 
Our teams come from
logistics and e-commerce backgrounds,
constantly working towards the same goal
 * Version:           1.0.1
 * Author:            iMile
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       imile
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'IMILE_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-imile-activator.php
 */
function activate_imile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-imile-activator.php';
	Imile_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-imile-deactivator.php
 */
function deactivate_imile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-imile-deactivator.php';
	Imile_Deactivator::deactivate();
}

/**
 * Register the patch Class.
 *
 * @since    1.0.0
 */
function imile_version_patch_activation() {
	$patch[] = 'WC_Imile_Patch_Gateway'; 
	return $patch;
}

register_activation_hook( __FILE__, 'activate_imile' );
register_deactivation_hook( __FILE__, 'deactivate_imile' );
register_activation_hook(__FILE__, 'imile_version_patch_activation');


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-imile.php';
require plugin_dir_path( __FILE__ ) . 'includes/imile-api.php';

/*----- Preventing Direct Access -----*/
defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_shipping_init','imile_include_shipping_method' );

/**
 * Include your shipping file.
 */
function imile_include_shipping_method() {
  require_once 'includes/imile-class-shipping-method.php';
}

add_filter( 'woocommerce_shipping_methods', 'imile_woocommerce_shipping_methods' );

function imile_woocommerce_shipping_methods($methods){
   	$methods['imile_shipping_method'] = 'imile_Shipping_Method';
	return $methods;
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.1
 */
function run_imile() {

	$plugin = new Imile();
	$plugin->run();

}
run_imile();
