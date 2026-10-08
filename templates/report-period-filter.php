<?php
/**
 * Report period filter
 *
 * Renders the period filter form of the project reports and sets the
 * selected period in `$period_start`, a `strtotime()` compatible string
 * or `null` for all time.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$period_options = [
	'-1 week'   => \__( 'Last week', 'orbis-projects' ),
	'-1 month'  => \__( 'Last month', 'orbis-projects' ),
	'-3 months' => \__( 'Last 3 months', 'orbis-projects' ),
	'-6 months' => \__( 'Last 6 months', 'orbis-projects' ),
	'-1 year'   => \__( 'Last year', 'orbis-projects' ),
	'all'       => \__( 'All time', 'orbis-projects' ),
];

$period = \array_key_exists( 'start', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['start'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( ! \array_key_exists( $period, $period_options ) ) {
	$period = '-1 week';
}

$period_start = ( 'all' === $period ) ? null : $period;

?>
<form class="d-flex justify-content-end gap-2 mb-3" method="get" action="">
	<label class="visually-hidden" for="orbis-projects-report-period"><?php \esc_html_e( 'Period', 'orbis-projects' ); ?></label>

	<select name="start" id="orbis-projects-report-period" class="form-select w-auto">
		<?php

		foreach ( $period_options as $value => $label ) {
			\printf(
				'<option value="%s" %s>%s</option>',
				\esc_attr( $value ),
				\selected( $period, $value, false ),
				\esc_html( $label )
			);
		}

		?>
	</select>

	<button type="submit" class="btn btn-primary"><?php \esc_html_e( 'Filter', 'orbis-projects' ); ?></button>
</form>
