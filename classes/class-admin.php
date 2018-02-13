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

		$inputs = filter_input_array( INPUT_POST );

		// add a new invoice
		if ( filter_has_var( INPUT_POST, 'orbis_projects_invoice_add' ) ) {

			global $wpdb;

			$result = $wpdb->insert(
				$wpdb->orbis_projects_invoices,
				array(
					'project_id'	 	=> filter_input( INPUT_POST, '_project_id', FILTER_SANITIZE_STRING ),
					'invoice_number' 	=> filter_input( INPUT_POST, '_orbis_project_invoice_number_', FILTER_SANITIZE_STRING ),
					'amount'  			=> filter_input( INPUT_POST, '_orbis_project_invoice_amount', FILTER_SANITIZE_STRING ),
					'user_id'    		=> get_current_user_id(),
					'hours'    			=> orbis_filter_time_input( INPUT_POST, '_orbis_project_invoice_seconds_available', FILTER_SANITIZE_STRING ),
					'create_date'    	=> current_time( 'mysql' ),
				)
			);
		}

		// edit an existing invoice
		if ( array_search( 'Edit Invoice', $inputs ) ) {
			global $wpdb;

			$invoice_id = array_search( 'Edit Invoice', $inputs );

			$invoice_number_name 	= '_orbis_project_invoice_number_edit_'.$invoice_id;
			$invoice_amount_name 	= '_orbis_project_invoice_amount_edit_'.$invoice_id;
			$invoice_time_name 		= '_orbis_project_invoice_seconds_available_edit_'.$invoice_id;

			$result = $wpdb->update(
				$wpdb->orbis_projects_invoices,
				array(
					'invoice_number'	=> filter_input( INPUT_POST, $invoice_number_name, FILTER_SANITIZE_STRING ),
					'amount'  			=> filter_input( INPUT_POST, $invoice_amount_name, FILTER_SANITIZE_STRING ),
					'hours'    			=> orbis_filter_time_input( INPUT_POST, $invoice_time_name, FILTER_SANITIZE_STRING ),
				),
				array( 'id' => $invoice_id)
			);
		}

		// delete an existing invoice
		if ( array_search( 'Delete Invoice', $inputs ) ) {
			global $wpdb;

			$invoice_id = array_search( 'Delete Invoice', $inputs );

			$result = $wpdb->delete(
				$wpdb->orbis_projects_invoices,
				array( 'id' => $invoice_id)
			);
		}
	}
}
