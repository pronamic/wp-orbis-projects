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
		add_filter( 'query_vars', [ $this, 'query_vars' ] );

		add_action( 'pre_get_posts', [ $this, 'pre_get_posts_custom_invoicable' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts_custom_orderby' ] );

		add_filter( 'posts_clauses', [ $this, 'posts_clauses' ], 10, 2 );

		add_filter( 'rest_orbis_project_query', [ $this, 'rest_query' ], 10, 2 );
	}

	/**
	 * Query vars.
	 *
	 * @param array $query_vars
	 * @return array
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'orbis_project_principal';
		$query_vars[] = 'orbis_project_client_id';
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
			$invoicable = filter_var( $invoicable, FILTER_VALIDATE_BOOLEAN );

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

		if ( 'orbis_project' === $post_type ) {
			// Fields
			$fields = ',
				project.id AS project_id,
				project.number_seconds AS project_number_seconds,
				project.finished AS project_is_finished,
				project.invoiced AS project_is_invoiced
			';

			// Join
			$join = "
				LEFT JOIN
					$wpdb->orbis_projects AS project
						ON $wpdb->posts.ID = project.post_id
			";

			if ( property_exists( $wpdb, 'orbis_companies' ) ) {
				$fields .= ',
				principal.id AS principal_id,
				principal.name AS principal_name,
				principal.post_id AS principal_post_id
			';

				$join .= "
					LEFT JOIN
						$wpdb->orbis_companies AS principal
							ON project.principal_id = principal.id
				";
			}

			// Where
			$where = '';

			$principal = $query->get( 'orbis_project_principal' );

			if ( ! empty( $principal ) ) {
				$where .= $wpdb->prepare( ' AND principal.name LIKE %s', '%' . $wpdb->esc_like( $principal ) . '%' );
			}

			$client_id = $query->get( 'orbis_project_client_id' );

			if ( ! empty( $client_id ) ) {
				$where .= $wpdb->prepare( ' AND principal.post_id LIKE %d ', $client_id );
			}

			$invoice_number = $query->get( 'orbis_project_invoice_number' );

			if ( ! empty( $invoice_number ) ) {
				$where .= $wpdb->prepare( ' AND project.invoice_number LIKE %s', '%' . $wpdb->esc_like( $invoice_number ) . '%' );
			}

			$is_finished = $query->get( 'orbis_project_is_finished', null );

			if ( null !== $is_finished ) {
				$is_finished = filter_var( $is_finished, FILTER_VALIDATE_BOOLEAN );

				$where .= $wpdb->prepare( ' AND project.finished = %d', $is_finished );
			}

			// Pieces
			$pieces['join']   .= $join;
			$pieces['fields'] .= $fields;
			$pieces['where']  .= $where;
		}

		return $pieces;
	}

	/**
	 * REST query.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/rest_this-post_type_query/
	 * @param array $args Query arguments.
	 * @param WP_REST_Request $request WordPress REST request.
	 * @return array
	 */
	public function rest_query( $args, $request ) {
		$args['orbis_project_is_finished'] = false;

		return $args;
	}
}
