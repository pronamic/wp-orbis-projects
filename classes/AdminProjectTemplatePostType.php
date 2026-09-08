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
	 * Construct.
	 *
	 * @param ProjectScheduler $project_scheduler Project scheduler.
	 */
	public function __construct(
		/**
		 * Project scheduler.
		 *
		 * @var ProjectScheduler
		 */
		private $project_scheduler
	) {
		\add_filter( 'manage_edit-orbis_project_tmpl_columns', $this->edit_columns( ... ) );

		\add_action( 'manage_orbis_project_tmpl_posts_custom_column', $this->custom_columns( ... ), 10, 2 );

		\add_action( 'add_meta_boxes', $this->add_meta_boxes( ... ) );

		\add_action( 'save_post_orbis_project_tmpl', $this->save_project_template( ... ), 10, 2 );

		\add_filter( 'post_row_actions', $this->add_row_actions( ... ), 10, 2 );

		\add_action( 'admin_post_orbis_create_project_from_template', $this->handle_create_project_now( ... ) );

		\add_action( 'admin_notices', $this->render_admin_notices( ... ) );

		\add_filter( 'parent_file', $this->parent_file( ... ) );
	}

	/**
	 * Highlight the "Projects" menu when editing this post type, since it is registered with `show_in_menu` disabled.
	 *
	 * @param string $parent_file Parent file.
	 * @return string
	 */
	public function parent_file( $parent_file ) {
		$screen = \get_current_screen();

		if ( null === $screen || 'orbis_project_tmpl' !== $screen->post_type ) {
			return $parent_file;
		}

		return 'edit.php?post_type=orbis_project';
	}

	/**
	 * Edit columns.
	 *
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function edit_columns( $columns ) {
		return [
			'cb'                                        => '<input type="checkbox" />',
			'title'                                     => \__( 'Title', 'orbis-projects' ),
			'orbis_project_template_next_project_title' => \__( 'Next Project', 'orbis-projects' ),
			'orbis_project_template_creation_date'      => \__( 'Next Creation Date', 'orbis-projects' ),
			'orbis_project_template_creation_modifier'  => \__( 'Recurrence', 'orbis-projects' ),
			'author'                                    => \__( 'Author', 'orbis-projects' ),
			'date'                                      => \__( 'Date', 'orbis-projects' ),
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
			case 'orbis_project_template_next_project_title':
				$next_project = $this->project_scheduler->get_next_project_preview( $post_id );

				echo \esc_html( null === $next_project ? \__( 'N/A', 'orbis-projects' ) : $next_project['title'] );

				break;
			case 'orbis_project_template_creation_date':
				echo \esc_html( \get_post_meta( $post_id, '_orbis_project_template_creation_date', true ) );

				break;
			case 'orbis_project_template_creation_modifier':
				echo \esc_html( \get_post_meta( $post_id, '_orbis_project_template_creation_date_modifier', true ) );

				break;
		}
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		\add_meta_box(
			'orbis_project_template_schedule',
			\__( 'Project Template Schedule', 'orbis-projects' ),
			$this->meta_box_schedule( ... ),
			'orbis_project_tmpl',
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

		if ( 'orbis_project_tmpl' !== $post->post_type ) {
			return;
		}

		$nonce = isset( $_POST['orbis_project_template_schedule_meta_box_nonce'] ) && \is_scalar( $_POST['orbis_project_template_schedule_meta_box_nonce'] )
			? \sanitize_text_field( \wp_unslash( $_POST['orbis_project_template_schedule_meta_box_nonce'] ) )
			: '';

		if ( ! \wp_verify_nonce( $nonce, 'orbis_save_project_template_schedule' ) ) {
			return;
		}

		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$keys = [
			'_orbis_project_template_creation_date',
			'_orbis_project_template_creation_date_modifier',
			'_orbis_project_template_start_date_modifier',
			'_orbis_project_template_end_date_modifier',
		];

		foreach ( $keys as $key ) {
			$value = isset( $_POST[ $key ] ) && \is_scalar( $_POST[ $key ] )
				? \sanitize_text_field( \wp_unslash( $_POST[ $key ] ) )
				: '';

			if ( '' === $value ) {
				\delete_post_meta( $post_id, $key );

				continue;
			}

			\update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Add row actions.
	 *
	 * @param array<string, string> $actions Row actions.
	 * @param \WP_Post              $post    Post.
	 * @return array<string, string>
	 */
	public function add_row_actions( $actions, $post ) {
		if ( 'orbis_project_tmpl' !== $post->post_type ) {
			return $actions;
		}

		if ( ! \current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		if ( 'publish' !== $post->post_status ) {
			return $actions;
		}

		if ( '' === \get_post_meta( $post->ID, '_orbis_project_template_creation_date', true ) ) {
			return $actions;
		}

		$url = \wp_nonce_url(
			\admin_url( 'admin-post.php?action=orbis_create_project_from_template&post=' . $post->ID ),
			'orbis_create_project_from_template_' . $post->ID
		);

		$actions['orbis_create_project_from_template'] = \sprintf(
			'<a href="%s">%s</a>',
			\esc_url( $url ),
			\esc_html__( 'Create project', 'orbis-projects' )
		);

		return $actions;
	}

	/**
	 * Handle manual "create project now" request.
	 */
	public function handle_create_project_now() {
		$post_id = isset( $_GET['post'] ) && \is_scalar( $_GET['post'] ) ? \absint( $_GET['post'] ) : 0;

		\check_admin_referer( 'orbis_create_project_from_template_' . $post_id );

		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			\wp_die( \esc_html__( 'You are not allowed to do this.', 'orbis-projects' ), 403 );
		}

		$post = \get_post( $post_id );

		if ( ! $post instanceof \WP_Post || 'orbis_project_tmpl' !== $post->post_type || 'publish' !== $post->post_status ) {
			\wp_die( \esc_html__( 'Invalid project template.', 'orbis-projects' ), 400 );
		}

		$creation_date = \get_post_meta( $post_id, '_orbis_project_template_creation_date', true );

		if ( '' === $creation_date ) {
			\wp_die( \esc_html__( 'This project template has no creation date set.', 'orbis-projects' ), 400 );
		}

		// Bypass the daily due-date check on purpose, this is a manual, immediate trigger.
		$project_id = $this->project_scheduler->create_project_from_template( $post_id, $creation_date );

		if ( null === $project_id ) {
			\wp_die( \esc_html__( 'No project was created from this template.', 'orbis-projects' ), 400 );
		}

		$redirect_to = \wp_get_referer();

		if ( false === $redirect_to ) {
			$redirect_to = \admin_url( 'edit.php?post_type=orbis_project_tmpl' );
		}

		\wp_safe_redirect( \add_query_arg( 'orbis_project_created', $project_id, $redirect_to ) );

		exit;
	}

	/**
	 * Render admin notices.
	 */
	public function render_admin_notices() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used to conditionally show a notice, not to process data.
		$project_id = isset( $_GET['orbis_project_created'] ) && \is_scalar( $_GET['orbis_project_created'] ) ? \absint( $_GET['orbis_project_created'] ) : 0;

		if ( 0 === $project_id ) {
			return;
		}

		$screen = \get_current_screen();

		if ( ! $screen instanceof \WP_Screen || 'orbis_project_tmpl' !== $screen->post_type ) {
			return;
		}

		$edit_link = \get_edit_post_link( $project_id );

		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php if ( null !== $edit_link ) : ?>
					<?php
					printf(
						/* translators: %s: link to the created project */
						\esc_html__( 'Project created from template: %s.', 'orbis-projects' ),
						'<a href="' . \esc_url( $edit_link ) . '">' . \esc_html( \get_the_title( $project_id ) ) . '</a>'
					);
					?>
				<?php else : ?>
					<?php \esc_html_e( 'Project created from template.', 'orbis-projects' ); ?>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}
}
