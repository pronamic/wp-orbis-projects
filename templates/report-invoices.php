<?php
/**
 * Report invoices
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

if ( ! \current_user_can( 'read_orbis_project_price' ) ) {
	\wp_die( \esc_html__( 'You are not allowed to view project invoices.', 'orbis-projects' ), 403 );
}

\get_header();

require __DIR__ . '/report-period-filter.php';

$condition = '1 = 1';

if ( null !== $period_start ) {
	$condition = $wpdb->prepare(
		'invoice.invoice_date >= %s',
		\gmdate( 'Y-m-d', \strtotime( $period_start ) )
	);
}

$customer_select = 'NULL AS customer_name, NULL AS customer_post_id';
$customer_join   = '';

if ( \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class ) ) {
	$contacts_table = \Pronamic\Orbis\Contacts\ContactsTable::get_table_name();

	$customer_select = 'customer.name AS customer_name, customer.post_id AS customer_post_id';
	$customer_join   = "LEFT JOIN $contacts_table AS customer ON project.customer_id = customer.id";
}

$query = "
	SELECT
		invoice.invoice_number,
		invoice.invoice_date,
		invoice.invoice_data,
		project.name AS project_name,
		project.post_id AS project_post_id,
		$customer_select,
		SUM( invoice_line.amount ) AS amount
	FROM
		$wpdb->orbis_invoices_lines AS invoice_line
			INNER JOIN
		$wpdb->orbis_invoices AS invoice
				ON invoice.id = invoice_line.invoice_id
			INNER JOIN
		$wpdb->orbis_projects AS project
				ON project.id = invoice_line.project_id
		$customer_join
	WHERE
		$condition
	GROUP BY
		invoice.id, project.id
	ORDER BY
		invoice.invoice_date DESC, invoice.invoice_number DESC
	;
";

$invoices = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

?>
<div class="card">
	<div class="table-responsive">
		<table class="table table-striped table-hover mb-0">
			<thead>
				<tr>
					<th scope="col"><?php \esc_html_e( 'Customer', 'orbis-projects' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Project', 'orbis-projects' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Date', 'orbis-projects' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Invoice', 'orbis-projects' ); ?></th>
					<th scope="col" class="text-end"><?php \esc_html_e( 'Amount', 'orbis-projects' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="row" colspan="4"><?php \esc_html_e( 'Total', 'orbis-projects' ); ?></th>
					<td class="text-end">
						<?php echo \orbis_price( \array_sum( \wp_list_pluck( $invoices, 'amount' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
			</tfoot>

			<tbody>

				<?php foreach ( $invoices as $invoice ) : ?>

					<tr>
						<td>
							<?php if ( null !== $invoice->customer_post_id ) : ?>

								<a href="<?php echo \esc_url( \get_permalink( $invoice->customer_post_id ) ); ?>"><?php echo \esc_html( $invoice->customer_name ); ?></a>

							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo \esc_url( \get_permalink( $invoice->project_post_id ) ); ?>"><?php echo \esc_html( $invoice->project_name ); ?></a>
						</td>
						<td>
							<?php echo \esc_html( \wp_date( \get_option( 'date_format' ), \strtotime( $invoice->invoice_date ) ) ); ?>
						</td>
						<td>
							<?php

							$invoice_url  = \apply_filters( 'orbis_invoice_url', '', $invoice->invoice_data );
							$invoice_text = \apply_filters( 'orbis_invoice_text', $invoice->invoice_number, $invoice->invoice_data );

							if ( '' !== $invoice_url ) {
								\printf(
									'<a href="%s" target="_blank">%s</a>',
									\esc_url( $invoice_url ),
									\esc_html( $invoice_text )
								);
							} else {
								echo \esc_html( $invoice_text );
							}

							?>
						</td>
						<td class="text-end">
							<?php echo \orbis_price( $invoice->amount ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>
</div>
<?php

\get_footer();
