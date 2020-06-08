<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://yourtraffix.com
 * @since             1.0.0
 * @package           Yourtraffix
 *
 * @wordpress-plugin
 * Plugin Name:       Yourtraffix
 * Plugin URI:        http://yourtraffix.com
 * Description:       YourTraffix Social Advertising Network enables your posts to appear on others websites.
 * Version:           0.0.30
 * Author:            Yourtraffix
 * Author URI:        http://yourtraffix.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       yourtraffix
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('YOURTRAFFIX_VERSION', '1.0.0');
define('PLUGIN_DIR',  plugins_url('', (__FILE__)));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-yourtraffix-activator.php
 */
function activate_yourtraffix()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-yourtraffix-activator.php';
	Yourtraffix_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-yourtraffix-deactivator.php
 */
function deactivate_yourtraffix()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-yourtraffix-deactivator.php';
	Yourtraffix_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_yourtraffix');
register_deactivation_hook(__FILE__, 'deactivate_yourtraffix');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-yourtraffix.php';


if (!class_exists('Smashing_Updater')) {
	include_once(plugin_dir_path(__FILE__) . 'updater.php');
}

$updater = new Smashing_Updater(__FILE__);
$updater->set_username('yourtraffix');
$updater->set_repository('yt-plugin');
//$updater->authorize( 'abcdefghijk1234567890' ); // Your auth code goes here for private repos
$updater->initialize();

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_yourtraffix()
{

	$plugin = new Yourtraffix();
	$plugin->run();
}
run_yourtraffix();
