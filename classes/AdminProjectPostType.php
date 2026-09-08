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
	 *
	 * @param \Pronamic\Orbis\Projects\Plugin $plugin
	 */
	public function __construct(
		/**
		 * Plugin.
		 *
		 * @var \Pronamic\Orbis\Projects\Plugin
		 */
		public $plugin
	) {
		\add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', $this->edit_columns( ... ) );

		\add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', $this->custom_columns( ... ), 10, 2 );

		\add_action( 'add_meta_boxes', $this->add_meta_boxes( ... ) );

		\add_action( 'save_post_' . self::POST_TYPE, $this->save_project( ... ), 10, 2 );
		\add_action( 'save_post_' . AdminProjectTemplatePostType::POST_TYPE, $this->save_project( ... ), 10, 2 );
		\add_action( 'save_post_' . self::POST_TYPE, $this->save_project_sync( ... ), 500, 2 );
	}

	/**
	 * Edit columns.
	 */
	public function edit_columns( $columns ) {
		$columns = [
			'cb'                      => '<input type="checkbox" />',
			'title'                   => \__( 'Title', 'orbis-projects' ),
			'orbis_project_principal' => \__( 'Principal', 'orbis-projects' ),
			'orbis_project_price'     => \__( 'Price', 'orbis-projects' ),
			'orbis_project_time'      => \__( 'Time', 'orbis-projects' ),
			'author'                  => \__( 'Author', 'orbis-projects' ),
			'comments'                => \__( 'Comments', 'orbis-projects' ),
			'date'                    => \__( 'Date', 'orbis-projects' ),
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
					\printf(
						'<a href="%s">%s</a>',
						\esc_attr( \get_permalink( $orbis_project->get_principal_post_id() ) ),
						\esc_html( $orbis_project->get_principal_name() )
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

					echo \esc_html( $price->format_i18n() );
				}

				break;
			case 'orbis_project_time':
				$duration = $orbis_project->get_available_time();

				if ( null === $duration ) {
					echo '—';
				}

				if ( null !== $duration ) {
					echo \esc_html( $duration->format() );
				}

				break;
		}
	}

	/**
	 * Add meta boxes.
	 *
	 * @param string $post_type Post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( ! \post_type_supports( $post_type, 'orbis-project-details' ) ) {
			return;
		}

		\add_meta_box(
			'orbis_project_details',
			\__( 'Project Information', 'orbis-projects' ),
			$this->meta_box_details( ... ),
			$post_type,
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
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verify nonce
		$nonce = isset( $_POST['orbis_project_details_meta_box_nonce'] )
			? \sanitize_text_field( \wp_unslash( $_POST['orbis_project_details_meta_box_nonce'] ) )
			: '';

		if ( ! \wp_verify_nonce( $nonce, 'orbis_save_project_details' ) ) {
			return;
		}

		// Check permissions
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$data = [
			'_orbis_price'                     => self::parse_decimal( self::get_post_value( '_orbis_price' ) ),
			'_orbis_hourly_rate'               => self::parse_decimal( self::get_post_value( '_orbis_hourly_rate' ) ),
			'_orbis_project_principal_id'      => self::get_post_value( '_orbis_project_principal_id' ),
			'_orbis_project_agreement_id'      => self::get_post_value( '_orbis_project_agreement_id' ),
			'_orbis_project_is_finished'       => BooleanHelper::from_mixed( self::get_post_value( '_orbis_project_is_finished' ) ),
			'_orbis_project_is_invoicable'     => BooleanHelper::from_mixed( self::get_post_value( '_orbis_project_is_invoicable' ) ),
			'_orbis_project_declarability'     => self::get_post_value( '_orbis_project_declarability' ),
			'_orbis_project_invoice_number'    => self::get_post_value( '_orbis_project_invoice_number' ),
			'_orbis_invoice_reference'         => self::get_post_value( '_orbis_invoice_reference' ),
			'_orbis_invoice_line_description'  => self::get_post_value( '_orbis_invoice_line_description' ),
			'_orbis_project_start_date'        => self::get_post_value( '_orbis_project_start_date' ),
			'_orbis_project_end_date'          => self::get_post_value( '_orbis_project_end_date' ),
			'_orbis_project_billed_to'         => self::get_post_value( '_orbis_project_billed_to' ),
			'_orbis_project_seconds_available' => Duration::from_string( self::get_post_value( '_orbis_project_seconds_available' ) ?? '' )?->get_seconds(),
		];

		if ( \current_user_can( 'edit_orbis_project_administration' ) ) {
			$data['_orbis_project_is_invoiced'] = BooleanHelper::from_mixed( self::get_post_value( '_orbis_project_is_invoiced' ) );
		}

		// Finished
		$is_finished_old = BooleanHelper::from_mixed( \get_post_meta( $post_id, '_orbis_project_is_finished', true ) );
		$is_finished_new = BooleanHelper::from_mixed( $data['_orbis_project_is_finished'] ?? null );

		foreach ( $data as $key => $value ) {
			if ( null === $value || '' === $value ) {
				\delete_post_meta( $post_id, $key );
			} else {
				\update_post_meta( $post_id, $key, $value );
			}
		}

		// Action.
		if ( 'publish' === $post->post_status && $is_finished_old !== $is_finished_new ) {
			// @see https://github.com/woothemes/woocommerce/blob/v2.1.4/includes/class-wc-order.php#L1274
			\do_action( 'orbis_project_finished_update', $post_id, $is_finished_new );
		}
	}

	/**
	 * Get a scalar POST value.
	 *
	 * @param string $key Key.
	 * @return string|null
	 */
	private static function get_post_value( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce is verified before this method is called.
		if ( ! isset( $_POST[ $key ] ) || ! \is_scalar( $_POST[ $key ] ) ) {
			return null;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce is verified before this method is called.
		return \sanitize_text_field( \wp_unslash( $_POST[ $key ] ) );
	}

	/**
	 * Parse a localized decimal value.
	 *
	 * @param string|null $value Value.
	 * @return float|null
	 */
	private static function parse_decimal( $value ) {
		if ( null === $value ) {
			return null;
		}

		$value = \sanitize_text_field( $value );

		if ( '' === $value ) {
			return null;
		}

		global $wp_locale;

		$value = \str_replace( $wp_locale->number_format['thousands_sep'], '', $value );
		$value = \str_replace( $wp_locale->number_format['decimal_point'], '.', $value );

		return \is_numeric( $value ) ? (float) $value : null;
	}

	/**
	 * Sync project with Orbis tables
	 */
	public function save_project_sync( $post_id, $post ) {
		// Doing autosave.
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type.
		if ( ! ( 'orbis_project' === $post->post_type ) ) {
			return;
		}

		// Revision.
		if ( \wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Publish.
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// OK
		global $wpdb;

		// Orbis project ID
		$orbis_id = \get_post_meta( $post_id, '_orbis_project_id', true );
		$orbis_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_projects WHERE post_id = %d;", $post_id ) );

		$principal_id   = \get_post_meta( $post_id, '_orbis_project_principal_id', true );
		$is_invoicable  = \get_post_meta( $post_id, '_orbis_project_is_invoicable', true );
		$declarability  = \get_post_meta( $post_id, '_orbis_project_declarability', true );
		$is_invoiced    = \get_post_meta( $post_id, '_orbis_project_is_invoiced', true );
		$invoice_number = \get_post_meta( $post_id, '_orbis_project_invoice_number', true );
		$is_finished    = \get_post_meta( $post_id, '_orbis_project_is_finished', true );
		$seconds        = \get_post_meta( $post_id, '_orbis_project_seconds_available', true );
		$price          = \get_post_meta( $post_id, '_orbis_price', true );

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

		\update_post_meta( $post_id, '_orbis_project_id', $orbis_id );
	}
}
