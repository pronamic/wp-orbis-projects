<?php
/**
 * Project scheduler.
 *
 * @package Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use WP_Error;
use WP_Post;
use WP_Query;

/**
 * Schedule recurring project creation from project templates.
 */
class ProjectScheduler {
	/**
	 * Action Scheduler group.
	 */
	const GROUP = 'orbis-projects';

	/**
	 * Project template post type.
	 */
	const POST_TYPE = 'orbis_project_tmpl';

	/**
	 * Construct.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct(
		/**
		 * Plugin.
		 *
		 * @var \Pronamic\Orbis\Projects\Plugin
		 */
		private $plugin
	) {
		add_action( 'init', $this->init( ... ) );
		add_action( 'orbis_projects_schedule_create_projects', $this->schedule_all( ... ) );
		add_action( 'orbis_projects_schedule_paged_create_projects', $this->schedule_paged( ... ), 10, 1 );
		add_action( 'orbis_projects_create_project_from_template', $this->create_project_from_template( ... ), 10, 2 );
	}

	/**
	 * Initialize recurring project creation.
	 */
	public function init() {
		if ( false !== as_has_scheduled_action( 'orbis_projects_schedule_create_projects', [], self::GROUP ) ) {
			return;
		}

		as_schedule_recurring_action(
			strtotime( 'tomorrow' ),
			DAY_IN_SECONDS,
			'orbis_projects_schedule_create_projects',
			[],
			self::GROUP,
			true
		);
	}

	/**
	 * Schedule all due project templates.
	 */
	public function schedule_all() {
		$query = new WP_Query( $this->get_query_args() );

		for ( $page = 1; $page <= $query->max_num_pages; $page++ ) {
			as_enqueue_async_action(
				'orbis_projects_schedule_paged_create_projects',
				[ 'page' => $page ],
				self::GROUP
			);
		}
	}

	/**
	 * Schedule due project templates from one page.
	 *
	 * @param int $page Page number.
	 */
	public function schedule_paged( $page ) {
		$args                  = $this->get_query_args();
		$args['paged']         = $page;
		$args['no_found_rows'] = true;

		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {
			$creation_date = get_post_meta( $post->ID, '_orbis_project_template_creation_date', true );

			as_enqueue_async_action(
				'orbis_projects_create_project_from_template',
				[
					'post_id'       => $post->ID,
					'creation_date' => $creation_date,
				],
				self::GROUP
			);
		}
	}

	/**
	 * Create a project from a template.
	 *
	 * @param int    $post_id       Template post ID.
	 * @param string $creation_date Scheduled creation date.
	 * @return int|null Created project post ID, or null if no project was created.
	 */
	public function create_project_from_template( $post_id, $creation_date ) {
		$template = get_post( $post_id );

		if ( ! $template instanceof WP_Post || self::POST_TYPE !== $template->post_type || 'publish' !== $template->post_status ) {
			return null;
		}

		if ( get_post_meta( $template->ID, '_orbis_project_template_creation_date', true ) !== $creation_date ) {
			return null;
		}

		$base_date  = $this->get_date( $creation_date );
		$recurrence = get_post_meta( $template->ID, '_orbis_project_template_creation_date_modifier', true );

		if ( null === $base_date || '' === $recurrence ) {
			return null;
		}

		$next_creation_date = $this->modify_date( $base_date, $recurrence );

		if ( null === $next_creation_date ) {
			return null;
		}

		$start_date = $this->get_modified_template_date( $template->ID, '_orbis_project_template_start_date_modifier', $base_date );
		$end_date   = $this->get_modified_template_date( $template->ID, '_orbis_project_template_end_date_modifier', $base_date );
		$project_id = wp_insert_post(
			[
				'post_type'    => 'orbis_project',
				'post_status'  => 'publish',
				'post_author'  => $template->post_author,
				'post_title'   => $this->replace_merge_tags( $template->post_title, $base_date, $start_date, $end_date ),
				'post_content' => $this->replace_merge_tags( $template->post_content, $base_date, $start_date, $end_date ),
			],
			true
		);

		if ( $project_id instanceof WP_Error ) {
			return null;
		}

		$this->copy_project_meta( $template->ID, $project_id );
		$this->update_project_period( $project_id, $start_date, $end_date );
		$this->copy_project_terms( $template->ID, $project_id );

		$project = get_post( $project_id );

		if ( $project instanceof WP_Post ) {
			$this->plugin->project_post_type->save_project_sync( $project_id, $project );
		}

		update_post_meta( $template->ID, '_orbis_project_template_creation_date', $next_creation_date->format( 'Y-m-d' ) );

		return $project_id;
	}

	/**
	 * Get project template query arguments.
	 *
	 * @return array<string, mixed>
	 */
	private function get_query_args() {
		return [
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Scheduler must select templates due today.
			'meta_query'     => [
				[
					'key'     => '_orbis_project_template_creation_date',
					'value'   => wp_date( 'Y-m-d' ),
					'compare' => '<=',
					'type'    => 'DATE',
				],
			],
		];
	}

	/**
	 * Get a template date after applying its modifier.
	 *
	 * @param int               $post_id   Template post ID.
	 * @param string            $meta_key  Modifier meta key.
	 * @param DateTimeImmutable $base_date Base date.
	 * @return DateTimeImmutable|null
	 */
	private function get_modified_template_date( $post_id, $meta_key, $base_date ) {
		$modifier = get_post_meta( $post_id, $meta_key, true );

		if ( '' === $modifier ) {
			return null;
		}

		return $this->modify_date( $base_date, $modifier );
	}

	/**
	 * Parse a local date.
	 *
	 * @param string $value Date string.
	 * @return DateTimeImmutable|null
	 */
	private function get_date( $value ) {
		$date   = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
		$errors = DateTimeImmutable::getLastErrors();

		if ( false === $date || ( is_array( $errors ) && ( 0 !== $errors['warning_count'] || 0 !== $errors['error_count'] ) ) ) {
			return null;
		}

		return $date;
	}

	/**
	 * Modify a date safely.
	 *
	 * @param DateTimeImmutable $date     Date.
	 * @param string            $modifier Date modifier.
	 * @return DateTimeImmutable|null
	 */
	private function modify_date( $date, $modifier ) {
		try {
			$modified_date = $date->modify( $modifier );
		} catch ( Exception ) {
			return null;
		}

		return ( false === $modified_date ) ? null : $modified_date;
	}

	/**
	 * Copy project metadata.
	 *
	 * @param int $template_id Template post ID.
	 * @param int $project_id  Project post ID.
	 */
	private function copy_project_meta( $template_id, $project_id ) {
		$meta = get_post_meta( $template_id );

		foreach ( array_keys( $meta ) as $key ) {
			if ( '_orbis_project_id' === $key || str_starts_with( $key, '_orbis_project_template_' ) ) {
				continue;
			}

			foreach ( get_post_meta( $template_id, $key, false ) as $value ) {
				add_post_meta( $project_id, $key, $value );
			}
		}
	}

	/**
	 * Update generated project period.
	 *
	 * @param int                    $project_id Project post ID.
	 * @param DateTimeImmutable|null $start_date Start date.
	 * @param DateTimeImmutable|null $end_date   End date.
	 */
	private function update_project_period( $project_id, $start_date, $end_date ) {
		if ( null !== $start_date ) {
			update_post_meta( $project_id, '_orbis_project_start_date', $start_date->format( 'Y-m-d' ) );
		}

		if ( null !== $end_date ) {
			update_post_meta( $project_id, '_orbis_project_end_date', $end_date->format( 'Y-m-d' ) );
		}
	}

	/**
	 * Copy project taxonomy terms.
	 *
	 * @param int $template_id Template post ID.
	 * @param int $project_id  Project post ID.
	 */
	private function copy_project_terms( $template_id, $project_id ) {
		$taxonomies = [
			'orbis_project_category',
			'orbis_project_status',
			'orbis_payment_method',
		];

		foreach ( $taxonomies as $taxonomy ) {
			$term_ids = wp_get_object_terms(
				$template_id,
				$taxonomy,
				[
					'fields' => 'ids',
				]
			);

			if ( is_wp_error( $term_ids ) ) {
				continue;
			}

			wp_set_object_terms( $project_id, $term_ids, $taxonomy );
		}
	}

	/**
	 * Replace project period merge tags.
	 *
	 * @param string                 $text          Text.
	 * @param DateTimeImmutable      $creation_date Creation date.
	 * @param DateTimeImmutable|null $start_date    Start date.
	 * @param DateTimeImmutable|null $end_date      End date.
	 * @return string
	 */
	private function replace_merge_tags( $text, $creation_date, $start_date, $end_date ) {
		$replace_pairs = [];

		$dates = [
			'start_date' => $start_date,
			'end_date'   => $end_date,
		];

		foreach ( $dates as $prefix => $date ) {
			$date ??= $creation_date;

			$replace_pairs[ '{' . $prefix . '_month}' ]   = wp_date( 'F', $date->getTimestamp() );
			$replace_pairs[ '{' . $prefix . '_year}' ]    = wp_date( 'Y', $date->getTimestamp() );
			$replace_pairs[ '{' . $prefix . '_quarter}' ] = (string) ceil( (int) $date->format( 'n' ) / 3 );
			$replace_pairs[ '{' . $prefix . '_week}' ]    = ltrim( $date->format( 'W' ), '0' );
		}

		return strtr( $text, $replace_pairs );
	}
}
