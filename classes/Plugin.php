<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use stdClass;
use WP_Post;

class Plugin {
	/**
	 * Content types.
	 *
	 * @var ContentTypes
	 */
	public $content_types;

	/**
	 * Query processor.
	 *
	 * @var QueryProcessor
	 */
	public $query_processor;

	/**
	 * Shortcodes.
	 *
	 * @var Shortcodes
	 */
	public $shortcodes;

	/**
	 * Commenter.
	 *
	 * @var Commenter
	 */
	public $commenter;

	/**
	 * Project post type.
	 *
	 * @var AdminProjectPostType
	 */
	public $project_post_type;

	/**
	 * Project scheduler.
	 *
	 * @var ProjectScheduler
	 */
	public $project_scheduler;

	/**
	 * Admin.
	 *
	 * @var Admin|null
	 */
	public $admin;

	/**
	 * Theme.
	 *
	 * @var Theme|null
	 */
	public $theme;

	public function __construct( $file ) {
		add_action( 'init', $this->init( ... ) );

		add_action( 'the_post', $this->the_post( ... ) );

		add_action( 'p2p_init', $this->p2p_init( ... ) );

		$this->content_types     = new ContentTypes();
		$this->query_processor   = new QueryProcessor();
		$this->shortcodes        = new Shortcodes( $this );
		$this->commenter         = new Commenter( $this );
		$this->project_post_type = new AdminProjectPostType( $this );
		$this->project_scheduler = new ProjectScheduler( $this );

		if ( is_admin() ) {
			$this->admin = new Admin( $this );
		} else {
			$this->theme = new Theme( $this );
		}

		\add_action(
			'rest_api_init',
			function (): void {
				\register_rest_field(
					'orbis_project',
					'orbis_project_id',
					[
						'get_callback' => function () {
							$project_post = \get_post();

							if ( ! $project_post instanceof WP_Post ) {
								return null;
							}

							return $project_post->project_id;
						},
					]
				);

				\register_rest_field(
					'orbis_project',
					'select2_text',
					[
						'get_callback' => function () {
							$project_post = \get_post();

							if ( ! $project_post instanceof WP_Post ) {
								return null;
							}

							return \sprintf(
								'%s. %s - %s ( %s )',
								$project_post->project_id,
								$project_post->principal_name,
								$project_post->post_title,
								isset( $project_post->project_logged_time ) ? ( Duration::try_from_seconds( $project_post->project_logged_time )?->format() ?? '' ) . ' / ' . ( Duration::try_from_seconds( $project_post->project_number_seconds )?->format() ?? '' ) : ( Duration::try_from_seconds( $project_post->project_number_seconds )?->format() ?? '' )
							);
						},
					]
				);
			}
		);
	}

	public function init() {
		global $wpdb;

		$wpdb->orbis_projects       = $wpdb->prefix . 'orbis_projects';
		$wpdb->orbis_invoices       = $wpdb->prefix . 'orbis_invoices';
		$wpdb->orbis_invoices_lines = $wpdb->prefix . 'orbis_invoices_lines';

		$version = '1.1.3';

		if ( \get_option( 'orbis_projects_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_projects_db_version', $version );
		}
	}

	/**
	 * Install
	 *
	 * @mysql UPDATE wp_options SET option_value = 0 WHERE option_name = 'orbis_db_version';
	 *
	 * @see Orbis_Plugin::install()
	 */
	public function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE $wpdb->orbis_projects (
				id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED DEFAULT NULL,
				name VARCHAR(128) NOT NULL,
				principal_id BIGINT(16) UNSIGNED DEFAULT NULL,
				start_date DATE NOT NULL DEFAULT '0000-00-00',
				number_seconds INT(16) NOT NULL DEFAULT 0,
				invoicable BOOLEAN NOT NULL DEFAULT TRUE,
				invoiced BOOLEAN NOT NULL DEFAULT FALSE,
				invoice_number VARCHAR(128) DEFAULT NULL,
				finished BOOLEAN NOT NULL DEFAULT FALSE,
				billable_amount DECIMAL(15,2) DEFAULT NULL,
				declarability VARCHAR(16) DEFAULT '',
				PRIMARY KEY  (id),
				KEY post_id (post_id),
				KEY principal_id (principal_id)
			) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		\dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_projects );
	}

	/**
	 * The post.
	 *
	 * @param mixed $post
	 */
	public function the_post( $post ) {
		unset( $GLOBALS['orbis_project'] );

		if ( 'orbis_project' !== get_post_type( $post ) ) {
			return;
		}

		$GLOBALS['orbis_project'] = new Project( $post );
	}

	/**
	 * Posts to posts initialize
	 */
	public function p2p_init() {
		// The `orbis_person` post type is registered by the Orbis People plugin, which may not be active.
		if ( ! post_type_exists( 'orbis_person' ) ) {
			return;
		}

		p2p_register_connection_type(
			[
				'name'        => 'orbis_projects_to_persons',
				'from'        => 'orbis_project',
				'to'          => 'orbis_person',
				'title'       => [
					'from' => __( 'Involved Persons', 'orbis-projects' ),
					'to'   => __( 'Projects', 'orbis-projects' ),
				],
				'from_labels' => [
					'singular_name' => __( 'Project', 'orbis-projects' ),
					'search_items'  => __( 'Search project', 'orbis-projects' ),
					'not_found'     => __( 'No projects found.', 'orbis-projects' ),
					'create'        => __( 'Add Project', 'orbis-projects' ),
					'new_item'      => __( 'New Project', 'orbis-projects' ),
					'add_new_item'  => __( 'Add New Project', 'orbis-projects' ),
				],
				'to_labels'   => [
					'singular_name' => __( 'Person', 'orbis-projects' ),
					'search_items'  => __( 'Search person', 'orbis-projects' ),
					'not_found'     => __( 'No persons found.', 'orbis-projects' ),
					'create'        => __( 'Add Person', 'orbis-projects' ),
					'new_item'      => __( 'New Person', 'orbis-projects' ),
					'add_new_item'  => __( 'Add New Person', 'orbis-projects' ),
				],
			]
		);
	}
}
