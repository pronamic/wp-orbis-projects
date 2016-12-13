<?php

class Orbis_Project {
	public function __construct( $post = null ) {
		$this->post = get_post( $post );
	}

	//////////////////////////////////////////////////

	public function register_invoice( $invoice_number, $amount ) {
		global $wpdb;

		// Insert subscription invoice
		$result = $wpdb->insert(
			$wpdb->orbis_projects_invoices,
			array(
				'project_id'     => $this->post->ID,
				'invoice_number' => $invoice_number,
				'amount'         => $amount,
				'user_id'        => get_current_user_id(),
				'create_date'    => date( 'Y-m-d H:i:s' ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);

		// Update Project
		update_post_meta( $this->post->ID, '_orbis_project_invoice_number', $invoice_number );

		$wpdb->update(
			$wpdb->orbis_projects,
			// Data
			array(
				'invoice_number' => $invoice_number,
			),
			// Where
			array(
				'post_id' => $this->post->ID,
			),
			// Format
			array(
				'%s',
			),
			// Where format
			array(
				'%d',
			)
		);

		return $result;
	}
}
