<?php
/*
Plugin Name: Orbis Projects
Plugin URI: https://www.pronamic.eu/plugins/orbis-projects/
Description: The Orbis Projects plugin extends your Orbis environment with the option to manage projects.

Version: 1.0.0
Requires at least: 3.5

Author: Pronamic
Author URI: https://www.pronamic.eu/

Text Domain: orbis_projects
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/wp-orbis/wp-orbis-projects
*/

/**
 * Includes
 */
require_once 'includes/projects.php';
require_once 'includes/shortcodes.php';

/**
 * Bootstrap
 */
function orbis_projects_bootstrap() {
	// Classes
	require_once 'classes/orbis-projects-plugin.php';

	// Initialize
	global $orbis_projects_plugin;

	$orbis_projects_plugin = new Orbis_Projects_Plugin( __FILE__ );
}

add_action( 'orbis_bootstrap', 'orbis_projects_bootstrap' );
