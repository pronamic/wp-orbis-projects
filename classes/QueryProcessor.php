<?php
/**
 * Query processor
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

class QueryProcessor {
	/**
	 * Construct.
	 */
	public function __construct() {
		\add_filter( 'query_vars', $this->query_vars( ... ) );

		\add_action( 'pre_get_posts', $this->pre_get_posts_custom_invoicable( ... ) );
		\add_action( 'pre_get_posts', $this->pre_get_posts_custom_orderby( ... ) );

		\add_filter( 'posts_clauses', $this->posts_clauses( ... ), 10, 2 );

		\add_filter( 'rest_orbis_project_query', $this->rest_query( ... ), 10, 2 );
	}

	/**
	 * Query vars.
	 *
	 * @param array $query_vars
	 * @return array
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'orbis_project_customer';
		$query_vars[] = 'orbis_project_customer_id';
		$query_vars[] = 'orbis_project_invoice_number';
		$query_vars[] = 'orbis_project_is_finished';
		$query_vars[] = 'orbis_invoicable';

		return $query_vars;
	}

	/**
	 * Pre get posts.
	 *
	 * @param WP_Query $query
	 */
	public function pre_get_posts_custom_invoicable( $query ) {
		$orderby = $query->get( 'orderby' );

		if ( 'project_invoice_number_modified' === $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', '_orbis_project_invoice_number_modified' );
		}

		if ( 'project_invoice_number' === $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', '_orbis_project_invoice_number' );
		}

		$invoicable = $query->get( 'orbis_invoicable', null );

		if ( null !== $invoicable ) {
			$invoicable = BooleanHelper::from_mixed( $invoicable );

			$meta_query = [];

			if ( $invoicable ) {
				$meta_query[] = [
					'key'     => '_orbis_project_is_invoicable',
					'value'   => '1',
					'compare' => '=',
				];
			} else {
				$meta_query['relation'] = 'OR';

				$meta_query[] = [
					'key'     => '_orbis_project_is_invoicable',
					'value'   => '1',
					'compare' => '!=',
				];

				$meta_query[] = [
					'key'     => '_orbis_project_is_invoicable',
					'compare' => 'NOT EXISTS',
				];
			}

			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Pre get posts.
	 *
	 * @param WP_Query $query
	 */
	public function pre_get_posts_custom_orderby( $query ) {
		$orderby = $query->get( 'orderby' );

		if ( 'project_finished_modified' === $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', '_orbis_project_finished_modified' );
		}
	}

	/**
	 * Posts clauses
	 *
	 * Links:
	 * http://codex.wordpress.org/WordPress_Query_Vars
	 * http://codex.wordpress.org/Custom_Queries
	 *
	 * @param array    $pieces
	 * @param WP_Query $query
	 * @return string
	 */
	public function posts_clauses( $pieces, $query ) {
		global $wpdb;

		$post_type = $query->get( 'post_type' );

		if ( 'orbis_project' !== $post_type ) {
			return $pieces;
		}

		/**
		 * Construct a subquery to join the project data.
		 *
		 * @link https://github.com/pronamic/orbis.pronamic.nl/issues/49
		 */
		$subquery_select_expr = [
			'project.post_id',
			'project.id AS project_id',
			'project.number_seconds AS project_number_seconds',
			'project.finished AS project_is_finished',
			'project.invoiced AS project_is_invoiced',
			'project.invoice_number AS project_invoice_number',
		];

		$subquery_table_references = [
			$wpdb->orbis_projects . ' AS project',
		];

		$has_customers = \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class );

		if ( $has_customers ) {
			$contacts_table = \Pronamic\Orbis\Contacts\ContactsTable::get_table_name();

			$subquery_select_expr[] = 'customer.id AS customer_id';
			$subquery_select_expr[] = 'customer.name AS customer_name';
			$subquery_select_expr[] = 'customer.post_id AS customer_post_id';

			$subquery_table_references[] = " LEFT JOIN $contacts_table AS customer ON project.customer_id = customer.id";
		}

		if ( \property_exists( $wpdb, 'orbis_timesheets' ) ) {
			$subquery_select_expr[] = 'SUM( logged_time.number_seconds ) AS project_logged_time';

			$subquery_table_references[] = " LEFT JOIN $wpdb->orbis_timesheets AS logged_time ON logged_time.project_id = project.id";
		}

		$subquery = \implode(
			' ',
			[
				'SELECT ' . \implode( ', ', $subquery_select_expr ),
				'FROM ' . \implode( ' ', $subquery_table_references ),
				'GROUP BY project.id',
			]
		);

		/**
		 * Adjust the WordPress query pieces.
		 */
		$fields = ', project_data.*';
		$join   = " LEFT JOIN ( $subquery ) AS project_data ON $wpdb->posts.id = project_data.post_id";
		$where  = '';

		$customer = $query->get( 'orbis_project_customer' );

		if ( $has_customers && ! empty( $customer ) ) {
			$where .= $wpdb->prepare( ' AND project_data.customer_name LIKE %s', '%' . $wpdb->esc_like( $customer ) . '%' );
		}

		$customer_id = $query->get( 'orbis_project_customer_id' );

		if ( $has_customers && ! empty( $customer_id ) ) {
			$where .= $wpdb->prepare( ' AND project_data.customer_id = %d', $customer_id );
		}

		$invoice_number = $query->get( 'orbis_project_invoice_number' );

		if ( ! empty( $invoice_number ) ) {
			$where .= $wpdb->prepare( ' AND project_data.project_invoice_number LIKE %s', '%' . $wpdb->esc_like( $invoice_number ) . '%' );
		}

		$is_finished = $query->get( 'orbis_project_is_finished', null );

		if ( null !== $is_finished ) {
			$is_finished = BooleanHelper::from_mixed( $is_finished );

			$where .= $wpdb->prepare( ' AND project_data.project_is_finished = %d', $is_finished );
		}

		$pieces['fields'] .= $fields;
		$pieces['join']   .= $join;
		$pieces['where']  .= $where;

		return $pieces;
	}

	/**
	 * REST query.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/rest_this-post_type_query/
	 * @param array           $args Query arguments.
	 * @param WP_REST_Request $request WordPress REST request.
	 * @return array
	 */
	public function rest_query( $args, $request ) {
		$args['orbis_project_is_finished'] = false;

		return $args;
	}
}
