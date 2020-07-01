<?php

global $wpdb;
global $orbis_is_projects_to_invoice;

$orbis_is_projects_to_invoice = true;

$extra = 'AND invoice_number IS NULL';

if ( filter_input( INPUT_GET, 'all', FILTER_VALIDATE_BOOLEAN ) ) {
	$extra = '';
}

$sql = "
	SELECT
		project.id ,
		project.name ,
		project.number_seconds AS available_seconds ,
		project.invoice_number AS invoice_number ,
		project.invoicable ,
		project.post_id AS project_post_id,
		manager.ID AS project_manager_id,
		manager.display_name AS project_manager_name,
		principal.id AS principal_id ,
		principal.name AS principal_name ,
		principal.post_id AS principal_post_id,
		SUM(registration.number_seconds) AS registered_seconds
	FROM
		$wpdb->orbis_projects AS project
			LEFT JOIN
		$wpdb->posts AS post
				ON project.post_id = post.ID
			LEFT JOIN
		$wpdb->users AS manager
				ON post.post_author = manager.ID
			LEFT JOIN
		$wpdb->orbis_companies AS principal
				ON project.principal_id = principal.id
			LEFT JOIN
		$wpdb->orbis_timesheets AS registration
				ON project.id = registration.project_id
	WHERE
		(
			project.finished
				OR
			project.name LIKE '%strippenkaart%'
				OR
			project.name LIKE '%adwords%'
				OR
			project.name LIKE '%marketing%'
		)
			AND
		project.invoicable
			AND
		NOT project.invoiced
			AND
		project.start_date > '2011-01-01'
		$extra
	GROUP BY
		project.id
	ORDER BY
		principal.name
	;
";

// Projects
$projects = $wpdb->get_results( $sql ); // unprepared SQL

// Managers
$managers = array();

// Projects and managers
foreach ( $projects as $project ) {
	// Find manager
	if ( ! isset( $managers[ $project->project_manager_id ] ) ) {
		$manager           = new stdClass();
		$manager->id       = $project->project_manager_id;
		$manager->name     = $project->project_manager_name;
		$manager->projects = array();

		$managers[ $manager->id ] = $manager;
	}

	$project->failed = $project->registered_seconds > $project->available_seconds;

	$manager = $managers[ $project->project_manager_id ];

	$manager->projects[] = $project;
}

ksort( $managers );

include 'projects-table-view.php';

/**
 * Update billable amount.
 *
 * @link https://github.com/wp-orbis/wp-orbis-timesheets/blob/develop/examples/update-project-billable-amount-from-post-meta.sql
 * @link https://dba.stackexchange.com/questions/217220/how-i-use-multiple-sum-with-multiple-left-joins
 */
$query = "
	SELECT
		project.id AS project_id,
		project.name AS project_name,
		project.billable_amount AS project_billable_amount,
		project.number_seconds AS project_billable_time,
		project.invoice_number AS project_invoice_number,
		project.post_id AS project_post_id,
		manager.ID AS project_manager_id,
		manager.display_name AS project_manager_name,
		principal.id AS principal_id ,
		principal.name AS principal_name ,
		principal.post_id AS principal_post_id,
		project_invoice_totals.project_billed_time,
		project_invoice_totals.project_billed_amount,
		project_invoice_totals.project_invoice_numbers,
		project_timesheet_totals.project_timesheet_time
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
				project_invoice.project_id,
				SUM( project_invoice.seconds ) AS project_billed_time,
				SUM( project_invoice.amount ) AS project_billed_amount,
				GROUP_CONCAT( DISTINCT project_invoice.invoice_number ) AS project_invoice_numbers
			FROM
				$wpdb->orbis_projects_invoices AS project_invoice
			GROUP BY
				project_invoice.project_id
		) AS project_invoice_totals ON project_invoice_totals.project_id = project.id
			LEFT JOIN
		(
			SELECT
				project_timesheet.project_id,
				SUM( project_timesheet.number_seconds ) AS project_timesheet_time
			FROM
				$wpdb->orbis_timesheets AS project_timesheet
			GROUP BY
				project_timesheet.project_id
		) AS project_timesheet_totals ON project_timesheet_totals.project_id = project.id
	WHERE
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
<div class="panel" style="font-size: 12px;">
	<table class="table table-striped table-bordered table-sm">
		<thead>
			<tr>
				<th scope="col" colspan="3"><?php \esc_html_e( 'Principal', 'orbis_projects' ); ?></th>
				<th scope="col" colspan="3"><?php \esc_html_e( 'Project', 'orbis_projects' ); ?></th>
				<th scope="col" colspan="2"><?php \esc_html_e( 'Billable', 'orbis_projects' ); ?></th>
				<th scope="col" colspan="3"><?php \esc_html_e( 'Billed', 'orbis_projects' ); ?></th>
				<th scope="col" colspan="2"><?php \esc_html_e( 'Timesheet', 'orbis_projects' ); ?></th>
				<th scope="col" colspan="3"><?php \esc_html_e( 'To Bill', 'orbis_projects' ); ?></th>
			</tr>
			<tr>
				<th scope="col"><?php \esc_html_e( 'Orbis ID', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Post ID', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Name', 'orbis_projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Orbis ID', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Post ID', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Name', 'orbis_projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Amount', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Time', 'orbis_projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Amount', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Time', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Invoices', 'orbis_projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Registered', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Available', 'orbis_projects' ); ?></th>

				<th scope="col"><?php \esc_html_e( 'Time', 'orbis_projects' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Amount', 'orbis_projects' ); ?></th>

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
						<?php echo \orbis_price( $item->project_billable_amount ); ?>
					</td>
					<td>
						<?php echo \orbis_time( $item->project_billable_time ); ?>
					</td>

					<td>
						<?php echo \orbis_price( $item->project_billed_amount ); ?>
					</td>
					<td>
						<?php echo \orbis_time( $item->project_billed_time ); ?>
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
						<?php echo \orbis_time( $item->project_timesheet_time ); ?>
					</td>
					<td>
						<?php echo \orbis_time( $item->project_billable_time ); ?>
					</td>

					<td>
						<?php 

						$to_bill_seconds = \max(
							0,
							\min(
								\intval( $item->project_timesheet_time ),
								\intval( $item->project_billable_time )
							) - \intval( $item->project_billed_time )
						);

						$to_bill_amount = ( 85 * ( $to_bill_seconds / HOUR_IN_SECONDS ) );

						if ( false !== \strpos( $item->project_name, 'Strippenkaart' ) ) {
							//$to_bill_seconds = $item->project_billable_time;
							//$to_bill_amount  = $item->project_billable_amount;
						}
						
						echo \orbis_time( $to_bill_seconds );

						?>
					</td>
					<td>
						<?php

						echo \orbis_price( $to_bill_amount );

						?>
					</td>
					<td>
						<?php

						$url = \add_query_arg(
							array(
								'company_id' => $item->principal_id,
								'project_id' => $item->project_id,
							),
							home_url( 'twinfield/invoicer' )
						);

						printf(
							'<a href="%s"><i class="fas fa-file-invoice"></i></a>',
							\esc_url( $url )
						);

						?>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>
