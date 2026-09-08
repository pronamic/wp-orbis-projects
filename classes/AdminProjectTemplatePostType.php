<?php
/**
 * Admin project template post type.
 *
 * @package Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

/**
 * Admin project template post type.
 */
class AdminProjectTemplatePostType {
	/**
	 * Post type.
	 */
	const POST_TYPE = 'orbis_project_tmpl';

	/**
	 * Construct.
	 */
	public function __construct() {
		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', $this->edit_columns( ... ) );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', $this->custom_columns( ... ), 10, 2 );

		add_action( 'add_meta_boxes', $this->add_meta_boxes( ... ) );

		add_action( 'save_post_' . self::POST_TYPE, $this->save_project_template( ... ), 10, 2 );
	}

	/**
	 * Edit columns.
	 *
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function edit_columns( $columns ) {
		return [
			'cb'                                       => '<input type="checkbox" />',
			'title'                                    => __( 'Title', 'orbis-projects' ),
			'orbis_project_template_creation_date'     => __( 'Next Creation Date', 'orbis-projects' ),
			'orbis_project_template_creation_modifier' => __( 'Recurrence', 'orbis-projects' ),
			'author'                                   => __( 'Author', 'orbis-projects' ),
			'date'                                     => __( 'Date', 'orbis-projects' ),
		];
	}

	/**
	 * Custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'orbis_project_template_creation_date':
				echo esc_html( get_post_meta( $post_id, '_orbis_project_template_creation_date', true ) );

				break;
			case 'orbis_project_template_creation_modifier':
				echo esc_html( get_post_meta( $post_id, '_orbis_project_template_creation_date_modifier', true ) );

				break;
		}
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'orbis_project_template_schedule',
			__( 'Project Template Schedule', 'orbis-projects' ),
			$this->meta_box_schedule( ... ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Schedule meta box.
	 *
	 * @param \WP_Post $post Post.
	 */
	public function meta_box_schedule( $post ) {
		include __DIR__ . '/../admin/meta-box-project-template-schedule.php';
	}

	/**
	 * Save project template.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post.
	 */
	public function save_project_template( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( self::POST_TYPE !== $post->post_type ) {
			return;
		}

		$nonce = isset( $_POST['orbis_project_template_schedule_meta_box_nonce'] ) && is_scalar( $_POST['orbis_project_template_schedule_meta_box_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['orbis_project_template_schedule_meta_box_nonce'] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'orbis_save_project_template_schedule' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$keys = [
			'_orbis_project_template_creation_date',
			'_orbis_project_template_creation_date_modifier',
			'_orbis_project_template_start_date_modifier',
			'_orbis_project_template_end_date_modifier',
		];

		foreach ( $keys as $key ) {
			$value = isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] )
				? sanitize_text_field( wp_unslash( $_POST[ $key ] ) )
				: '';

			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );

				continue;
			}

			update_post_meta( $post_id, $key, $value );
		}
	}
}
