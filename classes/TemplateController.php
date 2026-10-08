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
		\add_filter( 'query_vars', $this->query_vars( ... ) );

		\add_action( 'init', $this->init( ... ) );

		\add_filter( 'template_include', $this->template_include( ... ) );

		\add_filter( 'get_the_archive_title', $this->get_the_archive_title( ... ) );

		\add_filter( 'orbis_organization_sections', $this->organization_sections( ... ) );

		\add_action( 'orbis_after_main_content', $this->maybe_include_person_projects( ... ) );

		\add_action( 'get_template_part_templates/filter_advanced', $this->search_form_advanced( ... ), 10, 2 );
	}

	/**
	 * Query vars.
	 *
	 * @param string[] $query_vars Query vars.
	 * @return string[]
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'orbis_projects_route';

		return $query_vars;
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function init() {
		\add_rewrite_rule(
			'projecten/facturen/?$',
			[
				'orbis_projects_route' => 'invoices',
			],
			'top'
		);

		\add_rewrite_rule(
			'projecten/over-budget/?$',
			[
				'orbis_projects_route' => 'over_budget',
			],
			'top'
		);
	}

	/**
	 * Get the archive title.
	 *
	 * @param string $title Title.
	 * @return string
	 */
	public function get_the_archive_title( $title ) {
		return match ( \get_query_var( 'orbis_projects_route', null ) ) {
			'invoices'    => \__( 'Project Invoices', 'orbis-projects' ),
			'over_budget' => \__( 'Projects Over Budget', 'orbis-projects' ),
			default       => $title,
		};
	}

	/**
	 * Search form advanced.
	 *
	 * The search form of the Orbis theme requests the `templates/filter_advanced`
	 * template part with the post type as name, this adds the advanced project
	 * search fields, unless the theme has its own.
	 *
	 * @param string      $slug Slug.
	 * @param string|null $name Name.
	 * @return void
	 */
	public function search_form_advanced( $slug, $name ) {
		if ( 'orbis_project' !== $name ) {
			return;
		}

		if ( '' !== \locate_template( 'templates/filter_advanced-orbis_project.php' ) ) {
			return;
		}

		include __DIR__ . '/../templates/search-form-advanced.php';
	}

	/**
	 * Template include.
	 *
	 * Uses the report templates of this plugin for the report routes and the
	 * single and archive project templates, unless the theme has one.
	 *
	 * @param string $template Template.
	 * @return string
	 */
	public function template_include( $template ) {
		$route = \get_query_var( 'orbis_projects_route', null );

		if ( null !== $route ) {
			return match ( $route ) {
				'invoices'    => __DIR__ . '/../templates/report-invoices.php',
				'over_budget' => __DIR__ . '/../templates/report-over-budget.php',
				default       => $template,
			};
		}

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
		if ( ! \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class ) ) {
			return $sections;
		}

		$sections[] = [
			'id'       => 'projects',
			'name'     => \__( 'Projects', 'orbis-projects' ),
			'callback' => function (): void {
				include __DIR__ . '/../templates/customer-projects.php';
			},
		];

		return $sections;
	}

	/**
	 * Maybe include person projects.
	 *
	 * The person template has no sections filter, so the projects are
	 * shown in a card after the main content.
	 *
	 * @return void
	 */
	public function maybe_include_person_projects() {
		if ( ! \is_singular( 'orbis_person' ) ) {
			return;
		}

		if ( ! \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class ) ) {
			return;
		}

		?>
		<div class="card mb-3">
			<div class="card-header"><?php \esc_html_e( 'Projects', 'orbis-projects' ); ?></div>

			<?php include __DIR__ . '/../templates/customer-projects.php'; ?>
		</div>
		<?php
	}
}
