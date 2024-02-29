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
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		$this->project_post_type = new AdminProjectPostType( $plugin );
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
			\__( 'Orbis Projects Billing', 'orbis-projects' ),
			\__( 'Billing', 'orbis-projects' ),
			'manage_options',
			'orbis_projects_billing',
			[ $this, 'page_billing' ]
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
