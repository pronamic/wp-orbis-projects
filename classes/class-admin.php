<?php

class Orbis_Projects_Admin {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'save_post', array( $this, 'save_post' ) );

		// Project post type
		$this->project_post_type = new Orbis_Projects_AdminProjectPostType( $plugin );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'orbis-autocomplete' );
		wp_enqueue_style( 'select2' );
	}

	/**
	 * Save post.
	 */
	public function save_post( $post_id ) {

		if ( filter_has_var( INPUT_POST, 'orbis_projects_invoice_add' ) ) {

			global $wpdb;

			$result = $wpdb->insert(
				$wpdb->orbis_projects_invoices,
				array(
					'project_id'	 	=> filter_input( INPUT_POST, '_project_id', FILTER_SANITIZE_STRING ),
					'invoice_number' 	=> filter_input( INPUT_POST, '_orbis_project_invoice_number', FILTER_SANITIZE_STRING ),
					'amount'  			=> filter_input( INPUT_POST, '_orbis_project_invoice_amount', FILTER_SANITIZE_STRING ),
					'user_id'    		=> get_current_user_id(),
					'hours'    			=> orbis_filter_time_input( INPUT_POST, '_orbis_project_invoice_seconds_available', FILTER_SANITIZE_STRING ),
					'create_date'    	=> current_time( 'mysql' ),
				)
			);
		}
	}
}
