<?php

class Orbis_Projects_Shortcodes {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_shortcode( 'orbis_projects_active',            array( $this, 'shortcode_projects_active' ) );
		add_shortcode( 'orbis_projects_without_agreement', array( $this, 'shortcode_projects_without_agreement' ) );
		add_shortcode( 'orbis_projects_to_invoice',        array( $this, 'shortcode_projects_to_invoice' ) );
	}

	/**
	 * Projects active shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public function shortcode_projects_active() {
		$return  = '';

		ob_start();

		$this->plugin->plugin_include( 'templates/projects.php' );

		$return = ob_get_contents();

		ob_end_clean();

		return $return;
	}

	/**
	 * Projects without agreement
	*
	* @param array $atts
	* @return string
	*/
	public function shortcode_projects_without_agreement() {
		$return = '';

		ob_start();

		$this->plugin->plugin_include( 'templates/projects-without-agreement.php' );

		$return = ob_get_contents();

		ob_end_clean();

		return $return;
	}

	/**
	 * Projects to invoice
	 *
	 * @param array $atts
	 * @return string
	 */
	public function shortcode_projects_to_invoice() {
		$return = '';

		ob_start();

		$this->plugin->plugin_include( 'templates/projects-to-invoice.php' );

		$return = ob_get_contents();

		ob_end_clean();

		return $return;
	}
}
