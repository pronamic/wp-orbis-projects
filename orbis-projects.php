<?php
/*
Plugin Name: Orbis Projects
Plugin URI: https://www.pronamic.eu/plugins/orbis-projects/
Description: The Orbis Projects plugin extends your Orbis environment with the option to manage projects.

Version: 1.0.0
Requires at least: 3.5

Author: Pronamic
Author URI: https://www.pronamic.eu/

Text Domain: orbis-projects
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/wp-orbis/wp-orbis-projects
*/

/**
 * Autoload
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Bootstrap
 */
function orbis_projects_bootstrap() {
	global $orbis_projects_plugin;

	$orbis_projects_plugin = new Orbis_Projects_Plugin( __FILE__ );
}

add_action( 'plugins_loaded', 'orbis_projects_bootstrap' );
