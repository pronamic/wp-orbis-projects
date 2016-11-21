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
