<?php
/**
 * Commenter
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

class Commenter {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Hooks
		add_action( 'orbis_project_finished_update', [ $this, 'project_finished_update' ], 10, 2 );
		add_action( 'orbis_project_invoice_number_update', [ $this, 'project_invoice_number_update' ], 10, 3 );
	}

	/**
	 * Project finished update
	 *
	 * @param int $post_id
	 */
	public function project_finished_update( $post_id, $is_finished ) {
		// Date
		update_post_meta( $post_id, '_orbis_project_finished_modified', time() );

		// Comment
		$user = wp_get_current_user();

		$comment_content = sprintf(
			/* translators: first and second placeholder is the state of the project, opened or completed, and the third is the user name. */
			__( 'This "%1$s" project is just "%2$s" by %3$s.', 'orbis-projects' ),
			$is_finished ? __( 'opened', 'orbis-projects' ) : __( 'completed', 'orbis-projects' ),
			$is_finished ? __( 'completed', 'orbis-projects' ) : __( 'opened', 'orbis-projects' ),
			$user->display_name
		);

		$data = [
			'comment_post_ID' => $post_id,
			'comment_content' => $comment_content,
			'comment_author'  => 'Orbis',
			'comment_type'    => 'orbis_comment',
		];

		wp_insert_comment( $data );
	}
}
