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

		\add_filter( 'post_class', [ $this, 'post_class' ] );

		\add_filter( 'orbis_project_sections', [ $this, 'project_sections' ] );
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

	/**
	 * Project sections.
	 * 
	 * @param array $sections Sections.
	 * @return array
	 */
	public function project_sections( $sections ) {
		if ( \current_user_can( 'read_orbis_project_invoice', \get_the_ID() ) ) {
			$sections[] = [
				'id'       => 'invoices',
				'slug'     => __( 'invoices', 'orbis-projects' ),
				'name'     => __( 'Invoices', 'orbis-projects' ),
				'callback' => function () {
					include __DIR__ . '/../templates/project-invoices.php';
				},
			];
		}

		return $sections;
	}
}
