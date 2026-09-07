<?php
/**
 * Admin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

class Admin {
	/**
	 * Project template post type.
	 *
	 * @var AdminProjectTemplatePostType
	 */
	public $project_template_post_type;

	/**
	 * Construct.
	 *
	 * @param \Pronamic\Orbis\Projects\Plugin $plugin
	 */
	public function __construct(
		/**
		 * Plugin.
		 *
		 * @var \Pronamic\Orbis\Projects\Plugin
		 */
		public $plugin
	) {
		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );

		add_action( 'admin_menu', $this->admin_menu( ... ) );

		$this->project_template_post_type = new AdminProjectTemplatePostType();
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'orbis-autocomplete' );
		wp_enqueue_style( 'select2' );
	}

	/**
	 * Admin menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		\add_submenu_page(
			'edit.php?post_type=orbis_project',
			\__( 'Project Templates', 'orbis-projects' ),
			\__( 'Templates', 'orbis-projects' ),
			'edit_posts',
			'edit.php?post_type=orbis_project_tmpl'
		);

		\add_submenu_page(
			'edit.php?post_type=orbis_project',
			\__( 'Orbis Projects Billing', 'orbis-projects' ),
			\__( 'Billing', 'orbis-projects' ),
			'manage_options',
			'orbis_projects_billing',
			$this->page_billing( ... )
		);
	}

	/**
	 * Page billing.
	 *
	 * @return void
	 */
	public function page_billing() {
		include __DIR__ . '/../admin/page-billing.php';
	}
}
