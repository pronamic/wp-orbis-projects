<?php
/**
 * Shortcodes
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

class Shortcodes {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_shortcode( 'orbis_projects_active', [ $this, 'shortcode_projects_active' ] );
		add_shortcode( 'orbis_projects_without_agreement', [ $this, 'shortcode_projects_without_agreement' ] );
	}

	/**
	 * Projects active shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public function shortcode_projects_active() {
		$return = '';

		ob_start();

		include __DIR__ . '/../templates/projects.php';

		$return = ob_get_contents();

		ob_end_clean();

		return $return;
	}

	/**
	 * Projects without agreement
	 *
	 * @param array $atts
	 * @return string
	 */
	public function shortcode_projects_without_agreement() {
		$return = '';

		ob_start();

		include __DIR__ . '/../templates/projects-without-agreement.php';

		$return = ob_get_contents();

		ob_end_clean();

		return $return;
	}
}
