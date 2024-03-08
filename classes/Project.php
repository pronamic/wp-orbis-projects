<?php
/**
 * Project
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use Orbis_Time;

class Project {
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
			return (bool) $this->post->project_is_finished;
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
			return (bool) $this->post->project_is_invoiced;
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
	 * Compare final invoice.
	 *
	 * @return boolean
	 */
	public function is_final_invoice( $invoice_number ) {
		return get_post_meta( $this->post->ID, '_orbis_project_invoice_number', true ) == $invoice_number; // WPCS: loose comparison ok.
	}

	/**
	 * Get invoices.
	 *
	 * @param integer $post_id
	 * @return boolean
	 */
	public function get_invoices() {
		global $wpdb;

		$project_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_projects WHERE post_id = %d;", $this->post->ID ) );

		if ( empty( $project_id ) ) {
			return [];
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT
				invoice.*,
				invoice_line.amount,
				invoice_line.seconds,
				invoice_line.start_date,
				invoice_line.end_date,
				user.display_name
			FROM
				$wpdb->orbis_invoices_lines AS invoice_line
					INNER JOIN
				$wpdb->orbis_invoices AS invoice				
						ON invoice.id = invoice_line.invoice_id
					LEFT JOIN
				$wpdb->users AS user
						ON invoice.user_id = user.ID
			WHERE
				invoice_line.project_id = %d
			GROUP BY
				invoice.id
			;
		",
				$project_id 
			) 
		);

		if ( ! is_array( $results ) ) {
			return [];
		}

		return $results;
	}

	/**
	 * Get registered time.
	 *
	 * @return int
	 */
	public function get_registered_seconds() {
		if ( isset( $this->post->project_logged_time ) ) {
			return $this->post->project_logged_time;
		}

		$value = get_post_meta( $this->post->ID, '_orbis_project_registered_time', true );

		if ( is_numeric( $value ) ) {
			return $value;
		}

		return false;
	}
}
