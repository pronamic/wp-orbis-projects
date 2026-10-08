<?php
/**
 * Template controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

/**
 * Template controller class
 */
class TemplateController {
	/**
	 * Construct.
	 */
	public function __construct() {
		\add_filter( 'template_include', $this->template_include( ... ) );

		\add_filter( 'orbis_organization_sections', $this->organization_sections( ... ) );
	}

	/**
	 * Template include.
	 *
	 * Uses the single and archive project templates of this plugin, unless the theme has one.
	 *
	 * @param string $template Template.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( \is_singular( 'orbis_project' ) && '' === \locate_template( 'single-orbis_project.php' ) ) {
			return __DIR__ . '/../templates/single-orbis_project.php';
		}

		if ( \is_post_type_archive( 'orbis_project' ) && '' === \locate_template( 'archive-orbis_project.php' ) ) {
			return __DIR__ . '/../templates/archive-orbis_project.php';
		}

		return $template;
	}

	/**
	 * Organization sections.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public function organization_sections( $sections ) {
		$sections[] = [
			'id'       => 'projects',
			'name'     => \__( 'Projects', 'orbis-projects' ),
			'callback' => function (): void {
				include __DIR__ . '/../templates/organization-projects.php';
			},
		];

		return $sections;
	}
}
