<?php

class Orbis_Projects_Theme {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Hooks
		add_filter( 'post_class', array( $this, 'post_class' ) );
	}

	/**
	 * Post class
	 *
	 * @param array $classes
	 */
	public function post_class( $classes ) {
		global $post;

		if ( isset( $post->project_is_finished ) ) {
			$classes[] = $post->project_is_finished ? 'orbis-status-finished' : 'orbis-status-open';
		}

		return $classes;
	}
}
