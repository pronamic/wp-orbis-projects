<?php
/**
 * Page billing
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

use Pronamic\Orbis\Projects\BillingMethod;
use Pronamic\Orbis\Projects\BillingSchedule;
use Pronamic\Orbis\Projects\Duration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$required_tables = [
	'orbis_companies'  => \__( 'Orbis Companies', 'orbis-projects' ),
	'orbis_timesheets' => \__( 'Orbis Timesheets', 'orbis-projects' ),
];

$missing_tables = [];

foreach ( $required_tables as $property => $label ) {
	if ( isset( $wpdb->{$property} ) ) {
		continue;
	}

	$missing_tables[] = $label;
}

if ( [] !== $missing_tables ) :
	?>
	<div class="wrap">
		<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

		<div class="notice notice-warning">
			<p>
				<?php
				\printf(
					/* translators: %s: missing Orbis table names. */
					\esc_html__( 'The billing overview cannot be shown because the following Orbis tables are not available: %s.', 'orbis-projects' ),
					\esc_html( \implode( ', ', $missing_tables ) )
				);
				?>
			</p>
		</div>
	</div>
	<?php

	return;
endif;

$query = "
	SELECT
		project.id AS project_id,
		project.name AS project_name,
		project.billable_amount AS project_billable_amount,
		project.number_seconds AS project_billable_time,
		project.invoice_number AS project_invoice_number,
		project.finished AS project_finished,
		project.billing_method AS project_billing_method,
		project.billing_schedule AS project_billing_schedule,
		project.post_id AS project_post_id,
		manager.ID AS project_manager_id,
		manager.display_name AS project_manager_name,
		principal.id AS principal_id ,
		principal.name AS principal_name ,
		principal.post_id AS principal_post_id,
		project_invoice_totals.project_billed_time,
		project_invoice_totals.project_billed_amount,
		project_invoice_totals.project_invoice_numbers,
		project_timesheet_totals.project_timesheet_time,
		project_timesheet_totals.project_timesheet_billable_time
	FROM
		$wpdb->orbis_projects AS project
			INNER JOIN
		$wpdb->posts AS project_post
				ON project.post_id = project_post.ID
			INNER JOIN
		$wpdb->users AS manager
				ON project_post.post_author = manager.ID
			INNER JOIN
		$wpdb->orbis_companies AS principal
				ON project.principal_id = principal.id
			LEFT JOIN
		(
			SELECT
				invoice_line.project_id,
				SUM( invoice_line.seconds ) AS project_billed_time,
				SUM( invoice_line.amount ) AS project_billed_amount,
				GROUP_CONCAT( DISTINCT invoice.invoice_number ) AS project_invoice_numbers
			FROM
				$wpdb->orbis_invoices AS invoice
					INNER JOIN
				$wpdb->orbis_invoices_lines AS invoice_line
						ON invoice_line.invoice_id = invoice.id
			GROUP BY
				invoice_line.project_id
		) AS project_invoice_totals ON project_invoice_totals.project_id = project.id
			LEFT JOIN
		(
			SELECT
				project_timesheet.project_id,
				SUM( project_timesheet.number_seconds ) AS project_timesheet_time,
				SUM( IF( project_timesheet.billability = 'billable', project_timesheet.number_seconds, 0 ) ) AS project_timesheet_billable_time
			FROM
				$wpdb->orbis_timesheets AS project_timesheet
			GROUP BY
				project_timesheet.project_id
		) AS project_timesheet_totals ON project_timesheet_totals.project_id = project.id
	WHERE
		(
			project.billed_to IS NULL
				OR
			project.billed_to < DATE_SUB( NOW(), INTERVAL 1 WEEK )
		)
			AND
		project.invoicable
			AND
		NOT project.invoiced
			AND
		project.start_date > '2011-01-01'
			AND
		project.invoice_number IS NULL
	GROUP BY
		project.id
	ORDER BY
		principal.name
";

$data = $wpdb->get_results( $query );

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2><?php \esc_html_e( 'Projects', 'orbis-projects' ); ?></h2>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" colspan="3"><?php \esc_html_e( 'Principal', 'orbis-projects' ); ?></th>
				<th scope="col" colspan="5"><?php \esc_html_e( 'Project', 'orbis-projects' ); ?></th>
				<th scope="col" colspan="2"><?php \esc_html_e( 'Billable', 'orbis-projects' ); ?></th>
				<th scope="col" colspan="3"><?php \esc_html_e( 'Billed', 'orbis-projects' ); ?></th>
				<th scope="col" colspan="2"><?php \esc_html_e( 'Timesheet', 'orbis-projects' ); ?></th>
				<th scope="col" colspan="3"><?php \esc_html_e( 'To Bill', 'orbis-projects' ); ?></th>
			</tr>
			<tr>
				<th scope="col"><?php \esc_html_e( 'Orbis ID', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Post ID', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Name', 'orbis-projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Orbis ID', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Post ID', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Name', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Billing method', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Hourly rate', 'orbis-projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Amount', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Time', 'orbis-projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Amount', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Time', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Invoices', 'orbis-projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Registered', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Available', 'orbis-projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Time', 'orbis-projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Amount', 'orbis-projects' ); ?></th>

				<th scope="col"><i class="fas fa-file-invoice"></i></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $data as $item ) : ?>

				<tr>
					<td>
						<code class="text-body"><?php echo \esc_html( $item->principal_id ); ?></code>
					</td>
					<td>
						<code class="text-body"><?php echo \esc_html( $item->principal_post_id ); ?></code>
					</td>
					<td>
						<?php

						\printf(
							'<a href="%s">%s</a>',
							\esc_url( \add_query_arg( 'p', $item->principal_post_id, home_url( '/' ) ) ),
							\esc_html( $item->principal_name )
						);

						?>
					</td>

					<td>
						<code class="text-body"><?php echo \esc_html( $item->project_id ); ?></code>
					</td>
					<td>
						<code class="text-body"><?php echo \esc_html( $item->project_post_id ); ?></code>
					</td>
					<td>
						<?php

						\printf(
							'<a href="%s">%s</a>',
							\esc_url( \add_query_arg( 'p', $item->project_post_id, home_url( '/' ) ) ),
							\esc_html( $item->project_name )
						);

						?>
					</td>
					<td>
						<?php

						$billing_method   = BillingMethod::from_value( $item->project_billing_method );
						$billing_schedule = BillingSchedule::from_value( $item->project_billing_schedule );

						if ( null !== $billing_method ) {
							echo \esc_html( $billing_method->label() );
						}

						if ( BillingMethod::FixedPrice === $billing_method && null !== $billing_schedule ) {
							echo '<br />';
							echo '<small>', \esc_html( $billing_schedule->label() ), '</small>';
						}

						?>
					</td>
					<td>
						<?php

						$hourly_rate = (string) get_post_meta( $item->project_post_id, '_orbis_hourly_rate', true );

						if ( \is_numeric( $hourly_rate ) ) {
							echo \esc_html( number_format_i18n( $hourly_rate, 2 ) );
						}

						?>
					</td>

					<td>
						<?php echo \orbis_price( $item->project_billable_amount ); ?>
					</td>
					<td>
						<?php echo \esc_html( Duration::try_from_seconds( $item->project_billable_time )?->format() ?? '' ); ?>
					</td>

					<td>
						<?php echo \orbis_price( $item->project_billed_amount ); ?>
					</td>
					<td>
						<?php echo \esc_html( Duration::try_from_seconds( $item->project_billed_time )?->format() ?? '' ); ?>
					</td>
					<td>
						<?php

						$project_invoice_numbers = \wp_parse_list( $item->project_invoice_numbers );

						if ( count( $project_invoice_numbers ) > 0 ) {
							echo '<ul class="list-unstyled m-0">';

							foreach ( $project_invoice_numbers as $project_invoice_number ) {
								echo '<li>';

								$url = \home_url( '/twinfield/facturen/' . $project_invoice_number . '/' );

								\printf(
									'<a href="%s">%s</a>',
									\esc_url( $url ),
									\esc_html( $project_invoice_number )
								);

								echo '</li>';
							}

							echo '</ul>';
						}

						?>
					</td>

					<td>
						<?php echo \esc_html( Duration::try_from_seconds( $item->project_timesheet_time )?->format() ?? '' ); ?>
					</td>
					<td>
						<?php echo \esc_html( Duration::try_from_seconds( $item->project_billable_time )?->format() ?? '' ); ?>
					</td>

					<td>
						<?php

						$billable_amount = (float) $item->project_billable_amount;
						$billable_time   = (int) $item->project_billable_time;
						$billed_amount   = (float) $item->project_billed_amount;
						$billed_time     = (int) $item->project_billed_time;
						$timesheet_time  = (int) $item->project_timesheet_time;

						$to_bill_seconds = 0;
						$to_bill_amount  = null;

						switch ( $billing_method ) {
							case BillingMethod::TimeAndMaterials:
								$to_bill_seconds = \max( 0, (int) $item->project_timesheet_billable_time - $billed_time );

								if ( \is_numeric( $hourly_rate ) ) {
									$to_bill_amount = ( $hourly_rate * ( $to_bill_seconds / \HOUR_IN_SECONDS ) );
								}

								break;
							case BillingMethod::FixedPrice:
								$ratio = match ( $billing_schedule ) {
									BillingSchedule::InAdvance,
									BillingSchedule::Flexible     => 1,
									BillingSchedule::InArrears    => ( '1' === (string) $item->project_finished ) ? 1 : 0,
									BillingSchedule::ProRata      => ( $billable_time > 0 ) ? \min( 1, $timesheet_time / $billable_time ) : null,
									default                       => null,
								};

								if ( null !== $ratio ) {
									$to_bill_seconds = (int) \max( 0, \round( $billable_time * $ratio ) - $billed_time );
									$to_bill_amount  = \max( 0, \round( $billable_amount * $ratio, 2 ) - $billed_amount );
								}

								break;
							default:
								$to_bill_seconds = \max( 0, \min( $timesheet_time, $billable_time ) - $billed_time );

								if ( \is_numeric( $hourly_rate ) ) {
									$to_bill_amount = ( $hourly_rate * ( $to_bill_seconds / \HOUR_IN_SECONDS ) );
								}

								break;
						}

						echo \esc_html( Duration::from_seconds( $to_bill_seconds )->format() );

						?>
					</td>
					<td>
						<?php

						if ( null !== $to_bill_amount ) {
							echo \orbis_price( $to_bill_amount );
						}

						?>
					</td>
					<td>
						<?php

						$url = \add_query_arg(
							[
								'orbis_company_id' => $item->principal_id,
								'orbis_project_id' => $item->project_id,
							],
							home_url( 'moneybird/sales-invoices/new' )
						);

						printf(
							'<a href="%s"><span class="dashicons dashicons-media-spreadsheet"></span></a>',
							\esc_url( $url )
						);

						?>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>
