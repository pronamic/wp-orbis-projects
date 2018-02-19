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

		$inputs  = filter_input_array( INPUT_POST );
		$post_id = filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_STRING );

		// add a new invoice
		if ( filter_has_var( INPUT_POST, 'orbis_projects_invoice_add' ) ) {

			global $wpdb;

			$is_final_invoice = ( 1 == filter_input( INPUT_POST, '_orbis_project_is_final_invoice', FILTER_SANITIZE_STRING ) ) ? 1 : 0;

			$hours = orbis_filter_time_input( INPUT_POST, '_orbis_project_invoice_seconds_available', FILTER_SANITIZE_STRING );
			$hours = ( ! $hours ) ? null : $hours;

			// check if it is a final invoice, set others to zero, then store the number to the project and update meta.
			if ( $is_final_invoice ) {
				update_post_meta( $post_id, '_orbis_project_invoice_number', filter_input( INPUT_POST, '_orbis_project_invoice_number', FILTER_SANITIZE_STRING ) );
			}

			$result = $wpdb->insert(
				$wpdb->orbis_projects_invoices,
				array(
					'project_id'     => filter_input( INPUT_POST, '_project_id', FILTER_SANITIZE_STRING ),
					'invoice_number' => filter_input( INPUT_POST, '_orbis_project_invoice_number', FILTER_SANITIZE_STRING ),
					'amount'         => filter_input( INPUT_POST, '_orbis_project_invoice_amount', FILTER_SANITIZE_STRING ),
					'hours'          => $hours,
					'user_id'        => get_current_user_id(),
					'create_date'    => filter_input( INPUT_POST, '_orbis_project_invoice_date', FILTER_SANITIZE_STRING ),
				),
				array(
					'%d',
					'%s',
					'%f',
					'%d',
					'%d',
					'%s',
				)
			);
		}

		if ( $inputs ) {
			// edit an existing invoice
			if ( array_search( __( 'Edit Invoice', 'orbis-projects' ), $inputs, true ) ) {
				global $wpdb;

				$invoice_id = array_search( __( 'Edit Invoice', 'orbis-projects' ), $inputs, true );

				$invoice_number_name = '_orbis_project_invoice_number_edit_' . $invoice_id;
				$invoice_amount_name = '_orbis_project_invoice_amount_edit_' . $invoice_id;
				$invoice_time_name   = '_orbis_project_invoice_seconds_available_edit_' . $invoice_id;
				$invoice_date_name   = '_orbis_project_invoice_date_edit_' . $invoice_id;

				$invoice_final_id = intval( filter_input( INPUT_POST, '_is_final_invoice_edit', FILTER_SANITIZE_STRING ) );
				$is_final_invoice = ( filter_input( INPUT_POST, '_is_final_invoice_edit', FILTER_SANITIZE_STRING ) ) ? 1 : 0;

				$hours = orbis_filter_time_input( INPUT_POST, $invoice_time_name, FILTER_SANITIZE_STRING );
				$hours = ( ! $hours ) ? null : $hours;

				if ( $invoice_final_id === $invoice_id ) {
					update_post_meta( $post_id, '_orbis_project_invoice_number', filter_input( INPUT_POST, $invoice_number_name, FILTER_SANITIZE_STRING ) );
				}

				$result = $wpdb->update(
					$wpdb->orbis_projects_invoices,
					array(
						'invoice_number' => filter_input( INPUT_POST, $invoice_number_name, FILTER_SANITIZE_STRING ),
						'amount'         => filter_input( INPUT_POST, $invoice_amount_name, FILTER_SANITIZE_STRING ),
						'hours'          => $hours,
						'create_date'    => filter_input( INPUT_POST, $invoice_date_name, FILTER_SANITIZE_STRING ),
					),
					array( 'id' => $invoice_id ),
					array(
						'%s',
						'%f',
						'%d',
						'%s',
					)
				);
			}

			// delete an existing invoice
			if ( array_search( __( 'Delete Invoice', 'orbis-projects' ), $inputs, true ) ) {
				global $wpdb;

				$invoice_id       = array_search( __( 'Delete Invoice', 'orbis-projects' ), $inputs, true );
				$invoice_final_id = intval( filter_input( INPUT_POST, '_is_final_invoice_edit', FILTER_SANITIZE_STRING ) );

				if ( $invoice_final_id === $invoice_id ) {
					delete_post_meta( $post_id, '_orbis_project_invoice_number' );
				}

				$result = $wpdb->delete(
					$wpdb->orbis_projects_invoices,
					array( 'id' => $invoice_id )
				);
			}
		}
	}
}
