<?php
/**
 * Admin project post type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use Pronamic\WordPress\Money\Money;

class AdminProjectPostType {
	/**
	 * Post type.
	 */
	const POST_TYPE = 'orbis_project';

	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', [ $this, 'edit_columns' ] );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'custom_columns' ], 10, 2 );

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_project' ], 10, 2 );
		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_project_sync' ], 500, 2 );
	}

	/**
	 * Edit columns.
	 */
	public function edit_columns( $columns ) {
		$columns = [
			'cb'                      => '<input type="checkbox" />',
			'title'                   => __( 'Title', 'orbis-projects' ),
			'orbis_project_principal' => __( 'Principal', 'orbis-projects' ),
			'orbis_project_price'     => __( 'Price', 'orbis-projects' ),
			'orbis_project_time'      => __( 'Time', 'orbis-projects' ),
			'author'                  => __( 'Author', 'orbis-projects' ),
			'comments'                => __( 'Comments', 'orbis-projects' ),
			'date'                    => __( 'Date', 'orbis-projects' ),
		];

		return $columns;
	}

	/**
	 * Custom columns.
	 *
	 * @param string $column
	 */
	public function custom_columns( $column, $post_id ) {
		$orbis_project = new Project( $post_id );

		switch ( $column ) {
			case 'orbis_project_principal':
				if ( $orbis_project->has_principal() ) {
					printf(
						'<a href="%s">%s</a>',
						esc_attr( get_permalink( $orbis_project->get_principal_post_id() ) ),
						esc_html( $orbis_project->get_principal_name() )
					);
				}

				break;
			case 'orbis_project_price':
				$value = $orbis_project->get_price();

				if ( null === $value ) {
					echo '—';
				}

				if ( null !== $value ) {
					$price = new Money( $value, 'EUR' );

					echo esc_html( $price->format_i18n() );
				}

				break;
			case 'orbis_project_time':
				echo esc_html( $orbis_project->get_available_time()->format() );

				break;
		}
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'orbis_project_details',
			__( 'Project Information', 'orbis-projects' ),
			[ $this, 'meta_box_details' ],
			'orbis_project',
			'normal',
			'high'
		);
	}

	/**
	 * Meta box.
	 *
	 * @param mixed $post
	 */
	public function meta_box_details( $post ) {
		include __DIR__ . '/../admin/meta-box-project-details.php';
	}

	/**
	 * Save project.
	 *
	 * @param int   $post_id
	 * @param mixed $post
	 */
	public function save_project( $post_id, $post ) {
		// Doing autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verify nonce
		$nonce = filter_input( INPUT_POST, 'orbis_project_details_meta_box_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'orbis_save_project_details' ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// OK
		global $wp_locale;

		$definition = [
			'_orbis_price'                    => [
				'filter'  => FILTER_VALIDATE_FLOAT,
				'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
				'options' => [ 'decimal' => $wp_locale->number_format['decimal_point'] ],
			],
			'_orbis_hourly_rate'              => [
				'filter'  => FILTER_VALIDATE_FLOAT,
				'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
				'options' => [ 'decimal' => $wp_locale->number_format['decimal_point'] ],
			],
			'_orbis_project_principal_id'     => FILTER_VALIDATE_INT,
			'_orbis_project_agreement_id'     => FILTER_VALIDATE_INT,
			'_orbis_project_is_finished'      => FILTER_VALIDATE_BOOLEAN,
			'_orbis_project_is_invoicable'    => FILTER_VALIDATE_BOOLEAN,
			'_orbis_project_declarability'    => FILTER_SANITIZE_STRING,
			'_orbis_project_invoice_number'   => FILTER_SANITIZE_STRING,
			'_orbis_invoice_reference'        => FILTER_SANITIZE_STRING,
			'_orbis_invoice_line_description' => FILTER_SANITIZE_STRING,
			'_orbis_project_start_date'       => FILTER_SANITIZE_STRING,
			'_orbis_project_end_date'         => FILTER_SANITIZE_STRING,
			'_orbis_project_billed_to'        => FILTER_SANITIZE_STRING,
		];

		if ( current_user_can( 'edit_orbis_project_administration' ) ) {
			$definition['_orbis_project_is_invoiced'] = FILTER_VALIDATE_BOOLEAN;
		}

		$data = filter_input_array( INPUT_POST, $definition );

		$data['_orbis_project_seconds_available'] = orbis_filter_time_input( INPUT_POST, '_orbis_project_seconds_available' );

		// Finished
		$is_finished_old = filter_var( get_post_meta( $post_id, '_orbis_project_is_finished', true ), FILTER_VALIDATE_BOOLEAN );
		$is_finished_new = filter_var( $data['_orbis_project_is_finished'], FILTER_VALIDATE_BOOLEAN );

		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Action
		if ( 'publish' === $post->post_status && $is_finished_old !== $is_finished_new ) {
			// @see https://github.com/woothemes/woocommerce/blob/v2.1.4/includes/class-wc-order.php#L1274
			do_action( 'orbis_project_finished_update', $post_id, $is_finished_new );
		}
	}

	/**
	 * Sync project with Orbis tables
	 */
	public function save_project_sync( $post_id, $post ) {
		// Doing autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type
		if ( ! ( 'orbis_project' === $post->post_type ) ) {
			return;
		}

		// Revision
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Publish
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// OK
		global $wpdb;

		// Orbis project ID
		$orbis_id = get_post_meta( $post_id, '_orbis_project_id', true );
		$orbis_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_projects WHERE post_id = %d;", $post_id ) );

		$principal_id   = get_post_meta( $post_id, '_orbis_project_principal_id', true );
		$is_invoicable  = get_post_meta( $post_id, '_orbis_project_is_invoicable', true );
		$declarability  = get_post_meta( $post_id, '_orbis_project_declarability', true );
		$is_invoiced    = get_post_meta( $post_id, '_orbis_project_is_invoiced', true );
		$invoice_number = get_post_meta( $post_id, '_orbis_project_invoice_number', true );
		$is_finished    = get_post_meta( $post_id, '_orbis_project_is_finished', true );
		$seconds        = get_post_meta( $post_id, '_orbis_project_seconds_available', true );
		$price          = get_post_meta( $post_id, '_orbis_price', true );

		$data = [];
		$form = [];

		$data['name'] = $post->post_title;
		$form['name'] = '%s';

		if ( ! empty( $principal_id ) ) {
			$data['principal_id'] = $principal_id;
			$form['principal_id'] = '%d';
		}

		$data['start_date'] = get_the_time( 'Y-m-d', $post );
		$form['start_date'] = '%s';

		$data['number_seconds'] = $seconds;
		$form['number_seconds'] = '%d';

		$data['invoicable'] = $is_invoicable;
		$form['invoicable'] = '%d';

		$data['declarability'] = $declarability;
		$form['declarability'] = '%s';

		$data['invoiced'] = $is_invoiced;
		$form['invoiced'] = '%d';

		$data['invoice_number'] = empty( $invoice_number ) ? null : $invoice_number;
		$form['invoice_number'] = '%s';

		$data['finished'] = $is_finished;
		$form['finished'] = '%d';

		$data['billable_amount'] = $price;
		$form['billable_amount'] = '%f';

		if ( empty( $orbis_id ) ) {
			$data['post_id'] = $post_id;
			$form['post_id'] = '%d';

			$result = $wpdb->insert( $wpdb->orbis_projects, $data, $form );

			if ( false !== $result ) {
				$orbis_id = $wpdb->insert_id;
			}
		} else {
			$result = $wpdb->update(
				$wpdb->orbis_projects,
				$data,
				[ 'id' => $orbis_id ],
				$form,
				[ '%d' ]
			);
		}

		update_post_meta( $post_id, '_orbis_project_id', $orbis_id );
	}
}
