<?php
/**
 * Theme
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

class Theme {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Hooks
		add_filter( 'post_class', [ $this, 'post_class' ] );
	}

	/**
	 * Post class
	 *
	 * @param array $classes
	 */
	public function post_class( $classes ) {
		global $orbis_project;

		if ( is_object( $orbis_project ) ) {
			$classes[] = $orbis_project->is_finished() ? 'orbis-status-finished' : 'orbis-status-open';
		}

		return $classes;
	}
}
