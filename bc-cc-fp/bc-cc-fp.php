<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://masterfermin02.github.io
 * @since             1.0.0
 * @package           bc-cc-fp
 *
 * @wordpress-plugin
 * Plugin Name:       BlueCoding Code Challnge - Fermin Perdomo
 * Plugin URI:        https://masterfermin02.github.io
 * Description:       Create a custom list users.
 * Version:           1.0.0
 * Author:            Fermin Perdomo
 * Author URI:        https://masterfermin02.github.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bc-cc-fp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BC_CC_FP_VERSION', '1.0.0' );


require_once plugin_dir_path( dirname( __FILE__ ) ) . 'bc-cc-fp'.DIRECTORY_SEPARATOR.'autoload.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'bc-cc-fp'.DIRECTORY_SEPARATOR.'Bc_cc_fp_list_users.php';

use Bcccfp\Includes\BC_cc_fp_Activator;
use Bcccfp\Includes\Bc_cc_fp_Deactivator;
use Bcccfp\Includes\BC_cc_fp;

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_bc_cc_fp() {
	Bc_cc_fp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_bc_cc_fp() {
    Bc_cc_fp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bc_cc_fp' );
register_deactivation_hook( __FILE__, 'deactivate_bc_cc_fp' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bc_cc_fp() {

	$plugin = new BC_cc_fp([
	    'BC_cc_fp_list_users' => BC_cc_fp_list_users::class
    ]);
	$plugin->run();

}
run_bc_cc_fp();
