<?php

class Orbis_Projects_Commenter {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Hooks
		add_action( 'orbis_project_finished_update', array( $this, 'project_finished_update' ), 10, 2 );
		add_action( 'orbis_project_invoice_number_update', array( $this, 'project_invoice_number_update' ), 10, 3 );
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
			__( 'This "%1$s" project is just "%2$s" by %3$s.', 'orbis-projects' ),
			$is_finished ? __( 'opened', 'orbis-projects' ) : __( 'completed', 'orbis-projects' ),
			$is_finished ? __( 'completed', 'orbis-projects' ) : __( 'opened', 'orbis-projects' ),
			$user->display_name
		);

		$data = array(
			'comment_post_ID' => $post_id,
			'comment_content' => $comment_content,
			'comment_author'  => 'Orbis',
			'comment_type'    => 'orbis_comment',
		);

		wp_insert_comment( $data );
	}

	/**
	 * Project finished update
	 *
	 * @param int $post_id
	 */
	public function project_invoice_number_update( $post_id, $invoice_number_old, $invoice_number_new ) {
		// Date
		update_post_meta( $post_id, '_orbis_project_invoice_number_modified', time() );

		// Comment
		$user = wp_get_current_user();

		$text = $invoice_number_new;

		$invoice_link = orbis_get_invoice_link( $invoice_number_new );
		if ( ! empty( $invoice_link ) ) {
			$text = sprintf(
				'<a href="%s">%s</a>',
				esc_attr( $invoice_link ),
				$invoice_number_new
			);
		}

		$comment_content = sprintf(
			__( "Invoice Number '%s' was registered as final invoice on this project by %s.", 'orbis-projects' ),
			$text,
			$user->display_name
		);

		$data = array(
			'comment_post_ID' => $post_id,
			'comment_content' => $comment_content,
			'comment_author'  => 'Orbis',
			'comment_type'    => 'orbis_comment',
		);

		$comment_id = wp_insert_comment( $data );
	}
}
