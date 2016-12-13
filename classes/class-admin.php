<?php

class Orbis_Projects_Admin {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Project post type
		$this->project_post_type = new Orbis_Projects_AdminProjectPostType( $plugin );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'orbis-autocomplete' );
		wp_enqueue_style( 'select2' );
	}
}
