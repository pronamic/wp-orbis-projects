<?php

class Orbis_Projects_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_projects' );
		$this->set_db_version( '1.0.0' );

		// Actions
		add_action( 'p2p_init', array( $this, 'p2p_init' ) );

		// Includes
		$this->plugin_include( 'includes/project.php' );
		$this->plugin_include( 'includes/project-template.php' );
		$this->plugin_include( 'includes/post.php' );

		// Tables
		orbis_register_table( 'orbis_projects' );
		orbis_register_table( 'orbis_projects_invoices' );
	}

	public function loaded() {
		$this->load_textdomain( 'orbis_projects', '/languages/' );
	}

	/**
	 * Install
	 *
	 * @mysql UPDATE wp_options SET option_value = 0 WHERE option_name = 'orbis_db_version';
	 *
	 * @see Orbis_Plugin::install()
	 */
	public function install() {
		// Tables
		orbis_install_table( 'orbis_projects', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED DEFAULT NULL,
			name VARCHAR(128) NOT NULL,
			principal_id BIGINT(16) UNSIGNED DEFAULT NULL,
			start_date DATE NOT NULL DEFAULT "0000-00-00",
			number_seconds INT(16) NOT NULL DEFAULT 0,
			invoicable BOOLEAN NOT NULL DEFAULT TRUE,
			invoiced BOOLEAN NOT NULL DEFAULT FALSE,
			invoice_number VARCHAR(128) DEFAULT NULL,
			finished BOOLEAN NOT NULL DEFAULT FALSE,
			PRIMARY KEY  (id),
			KEY principal_id (principal_id)
		' );

		orbis_install_table( 'orbis_projects_invoices', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			project_id BIGINT(16) UNSIGNED NOT NULL,
			invoice_number VARCHAR(8) NOT NULL,
			amount FLOAT NOT NULL,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			create_date DATETIME DEFAULT NULL,
			PRIMARY KEY  (id)
		' );

		// Install
		parent::install();
	}

	//////////////////////////////////////////////////

	/**
	 * Posts to posts initialize
	 */
	public function p2p_init() {
		p2p_register_connection_type( array(
			'name'        => 'orbis_projects_to_persons',
			'from'        => 'orbis_project',
			'to'          => 'orbis_person',
			'title'       => array(
				'from' => __( 'Involved Persons', 'orbis_projects' ),
				'to'   => __( 'Projects', 'orbis_projects' ),
			),
			'from_labels' => array(
				'singular_name' => __( 'Project', 'orbis_projects' ),
				'search_items'  => __( 'Search project', 'orbis_projects' ),
				'not_found'     => __( 'No projects found.', 'orbis_projects' ),
				'create'        => __( 'Add Project', 'orbis_projects' ),
				'new_item'      => __( 'New Project', 'orbis_projects' ),
				'add_new_item'  => __( 'Add New Project', 'orbis_projects' ),
			),
			'to_labels'   => array(
				'singular_name' => __( 'Person', 'orbis_projects' ),
				'search_items'  => __( 'Search person', 'orbis_projects' ),
				'not_found'     => __( 'No persons found.', 'orbis_projects' ),
				'create'        => __( 'Add Person', 'orbis_projects' ),
				'new_item'      => __( 'New Person', 'orbis_projects' ),
				'add_new_item'  => __( 'Add New Person', 'orbis_projects' ),
			),
		) );
	}
}
