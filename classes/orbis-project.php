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

		// Meta
		update_post_meta( $this->post->ID, '_orbis_project_invoice_number', $invoice_number );

		return $result;
	}
}
