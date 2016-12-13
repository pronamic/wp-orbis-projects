<?php

class Orbis_Project {
	public function __construct( $post = null ) {
		$this->post = get_post( $post );
	}

	/**
	 * Has principal.
	 *
	 * @return boolean
	 */
	public function has_principal() {
		return isset( $this->post->principal_id );
	}

	/**
	 * Get principal name.
	 *
	 * @return string
	 */
	public function get_principal_name() {
		if ( isset( $this->post->principal_name ) ) {
			return $this->post->principal_name;
		}
	}

	/**
	 * Get principal post ID.
	 *
	 * @return int
	 */
	public function get_principal_post_id() {
		if ( isset( $this->post->principal_post_id ) ) {
			return $this->post->principal_post_id;
		}
	}

	/**
	 * Get available time.
	 *
	 * @return Orbis_Time
	 */
	public function get_available_time() {
		$seconds = null;

		if ( isset( $this->post->project_number_seconds ) ) {
			$seconds = $this->post->project_number_seconds;
		}

		return new Orbis_Time( $seconds );
	}

	/**
	 * Get price.
	 *
	 * @return float
	 */
	public function get_price() {
		$value = get_post_meta( $this->post->ID, '_orbis_price', true );

		if ( '' === $value ) {
			return null;
		}

		return $value;
	}

	/**
	 * Is finished.
	 *
	 * @return boolean
	 */
	public function is_finished() {
		if ( isset( $this->post->project_is_finished ) ) {
			return (boolean) $this->post->project_is_finished;
		}

		return filter_var( get_post_meta( $this->post->ID, '_orbis_project_is_finished', true ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Is invoiced.
	 *
	 * @return boolean
	 */
	public function is_invoiced() {
		if ( isset( $this->post->project_is_invoiced ) ) {
			return (boolean) $this->post->project_is_invoiced;
		}

		return filter_var( get_post_meta( $this->post->ID, '_orbis_project_is_invoiced', true ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Is invoicable.
	 *
	 * @return boolean
	 */
	public function is_invoicable() {
		return filter_var( get_post_meta( $this->post->ID, '_orbis_project_is_invoicable', true ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Register invoice.
	 *
	 * @param string $invoice_number
	 * @param float  $amount
	 * @return boolean
	 */
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
