<?php

class Orbis_Projects_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_projects' );
		$this->set_db_version( '1.1.0' );

		// Tables
		orbis_register_table( 'orbis_projects' );
		orbis_register_table( 'orbis_projects_invoices' );

		// Actions
		add_action( 'the_post', array( $this, 'the_post' ) );

		add_action( 'p2p_init', array( $this, 'p2p_init' ) );

		add_action( 'wp_ajax_project_id_suggest', array( $this, 'ajax_projects_suggest_project_id' ) );

		// Load text domain
		$this->load_textdomain( 'orbis-projects', '/languages/' );

		// Content Types
		$this->content_types = new Orbis_Projects_ContentTypes();

		// Query Processor
		$this->query_processor = new Orbis_Projects_QueryProcessor();

		// Shortcodes
		$this->shortcodes = new Orbis_Projects_Shortcodes( $this );

		// Commenter
		$this->commenter = new Orbis_Projects_Commenter( $this );

		// Admin
		if ( is_admin() ) {
			$this->admin = new Orbis_Projects_Admin( $this );
		} else {
			$this->theme = new Orbis_Projects_Theme( $this );
		}
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

	/**
	 * The post.
	 *
	 * @param mixed $post
	 */
	public function the_post( $post ) {
		unset( $GLOBALS['orbis_project'] );

		if ( 'orbis_project' !== get_post_type( $post ) ) {
			return;
		}

		$GLOBALS['orbis_project'] = new Orbis_Project( $post );
	}

	/**
	 * Posts to posts initialize
	 */
	public function p2p_init() {
		p2p_register_connection_type( array(
			'name'        => 'orbis_projects_to_persons',
			'from'        => 'orbis_project',
			'to'          => 'orbis_person',
			'title'       => array(
				'from' => __( 'Involved Persons', 'orbis-projects' ),
				'to'   => __( 'Projects', 'orbis-projects' ),
			),
			'from_labels' => array(
				'singular_name' => __( 'Project', 'orbis-projects' ),
				'search_items'  => __( 'Search project', 'orbis-projects' ),
				'not_found'     => __( 'No projects found.', 'orbis-projects' ),
				'create'        => __( 'Add Project', 'orbis-projects' ),
				'new_item'      => __( 'New Project', 'orbis-projects' ),
				'add_new_item'  => __( 'Add New Project', 'orbis-projects' ),
			),
			'to_labels'   => array(
				'singular_name' => __( 'Person', 'orbis-projects' ),
				'search_items'  => __( 'Search person', 'orbis-projects' ),
				'not_found'     => __( 'No persons found.', 'orbis-projects' ),
				'create'        => __( 'Add Person', 'orbis-projects' ),
				'new_item'      => __( 'New Person', 'orbis-projects' ),
				'add_new_item'  => __( 'Add New Person', 'orbis-projects' ),
			),
		) );
	}

	/**
	 * AJAX projects suggest project ID.
	 */
	public function ajax_projects_suggest_project_id() {
		global $wpdb;

		$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

		$extra_select = '';
		$extra_join   = '';

		if ( isset( $wpdb->orbis_timesheets ) ) {
			$extra_select .= ',
				SUM( entry.number_seconds ) AS project_logged_time
			';

			$extra_join = "
				LEFT JOIN
					$wpdb->orbis_timesheets AS entry
						ON entry.project_id = project.id
			";
		}

		$query = "
			SELECT
				project.id AS project_id,
				principal.name AS principal_name,
				project.name AS project_name,
				project.number_seconds AS project_time
				$extra_select
			FROM
				$wpdb->orbis_projects AS project
					LEFT JOIN
				$wpdb->orbis_companies AS principal
						ON project.principal_id = principal.id
				$extra_join
			WHERE
				project.finished = 0
					AND
				(
					project.name LIKE %s
						OR
					principal.name LIKE %s
				)
			GROUP BY
				project.id
			ORDER BY
				project.id
		";

		$like = '%' . $wpdb->esc_like( $term ) . '%';

		$query = $wpdb->prepare( $query, $like, $like ); // unprepared SQL

		$projects = $wpdb->get_results( $query ); // unprepared SQL

		$data = array();

		foreach ( $projects as $project ) {
			$result = new stdClass();
			$result->id   = $project->project_id;

			$text = sprintf(
				'%s. %s - %s ( %s )',
				$project->project_id,
				$project->principal_name,
				$project->project_name,
				orbis_time( $project->project_time )
			);

			if ( isset( $project->project_logged_time ) ) {
				$text = sprintf(
					'%s. %s - %s ( %s / %s )',
					$project->project_id,
					$project->principal_name,
					$project->project_name,
					orbis_time( $project->project_logged_time ),
					orbis_time( $project->project_time )
				);
			}

			$result->text = $text;

			$data[] = $result;
		}

		echo wp_json_encode( $data );

		die();
	}
}
