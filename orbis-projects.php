<?php
/**
 * Orbis Projects
 *
 * @package   Pronamic\Orbis\Projects
 * @author    Pronamic
 * @copyright 2024 Pronamic
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Orbis Projects
 * Plugin URI:        https://wp.pronamic.directory/plugins/orbis-projects/
 * Description:       The Orbis Projects plugin extends your Orbis environment with the option to manage projects.
 * Version:           1.0.0
 * Requires at least: 6.9
 * Tested up to:      7.1
 * Requires PHP:      7.2
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       orbis-projects
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://wp.pronamic.directory/plugins/orbis-projects/
 * GitHub URI:        https://github.com/pronamic/wp-orbis-projects
 */

namespace Pronamic\Orbis\Projects;

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

/**
 * Bootstrap.
 */
\add_action(
	'plugins_loaded',
	function (): void {
		\load_plugin_textdomain( 'orbis-projects', false, \dirname( \plugin_basename( __FILE__ ) ) . '/languages' );

		global $orbis_projects_plugin;

		$orbis_projects_plugin = new Plugin( __FILE__ );
	}
);
