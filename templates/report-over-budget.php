<?php
/**
 * Report over budget
 *
 * Lists the unfinished invoicable projects with more registered time than
 * available time, by percentage and by number of hours over budget.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

use Pronamic\Orbis\Projects\Duration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

\get_header();

if ( ! isset( $wpdb->orbis_timesheets ) ) :
	?>
	<div class="alert alert-warning">
		<?php \esc_html_e( 'The over budget report requires the Orbis Timesheets plugin.', 'orbis-projects' ); ?>
	</div>
	<?php

	\get_footer();

	return;
endif;

$can_read_price = \current_user_can( 'read_orbis_project_price' );

$customer_select = 'NULL AS customer_name';
$customer_join   = '';

if ( \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class ) ) {
	$contacts_table = \Pronamic\Orbis\Contacts\ContactsTable::get_table_name();

	$customer_select = 'customer.name AS customer_name';
	$customer_join   = "LEFT JOIN $contacts_table AS customer ON project.customer_id = customer.id";
}

$query = "
	SELECT
		project.name AS project_name,
		project.post_id AS project_post_id,
		project.number_seconds AS available_seconds,
		$customer_select,
		SUM( timesheet.number_seconds ) AS registered_seconds
	FROM
		$wpdb->orbis_projects AS project
			INNER JOIN
		$wpdb->orbis_timesheets AS timesheet
				ON timesheet.project_id = project.id
		$customer_join
	WHERE
		NOT project.finished
			AND
		project.invoicable
			AND
		project.number_seconds > 0
	GROUP BY
		project.id
	HAVING
		registered_seconds > available_seconds
	;
";

$projects = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

foreach ( $projects as $project ) {
	$project->over_seconds    = $project->registered_seconds - $project->available_seconds;
	$project->over_percentage = $project->registered_seconds / $project->available_seconds * 100;
	$project->over_amount     = null;

	$hourly_rate = (string) \get_post_meta( $project->project_post_id, '_orbis_hourly_rate', true );

	if ( \is_numeric( $hourly_rate ) ) {
		$project->over_amount = $hourly_rate * ( $project->over_seconds / \HOUR_IN_SECONDS );
	}
}

$projects_by_percentage = $projects;
$projects_by_time       = $projects;

\usort( $projects_by_percentage, fn( $a, $b ) => $b->over_percentage <=> $a->over_percentage );
\usort( $projects_by_time, fn( $a, $b ) => $b->over_seconds <=> $a->over_seconds );

$project_link = function ( $project ) {
	\printf(
		'<a href="%s">%s</a>',
		\esc_url( \get_permalink( $project->project_post_id ) ),
		\esc_html( \implode( ' - ', \array_filter( [ $project->customer_name, $project->project_name ] ) ) )
	);
};

?>
<div class="row">
	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-header"><?php \esc_html_e( 'Largest overrun', 'orbis-projects' ); ?></div>

			<div class="table-responsive">
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th scope="col"><?php \esc_html_e( 'Project', 'orbis-projects' ); ?></th>
							<th scope="col" class="text-end"><?php \esc_html_e( 'Over budget', 'orbis-projects' ); ?></th>
						</tr>
					</thead>

					<tbody>

						<?php foreach ( $projects_by_percentage as $project ) : ?>

							<tr>
								<td>
									<?php $project_link( $project ); ?>
								</td>
								<td class="text-end <?php echo \esc_attr( $project->over_percentage > 150 ? 'text-danger fw-bold' : '' ); ?>">
									<?php echo \esc_html( \number_format_i18n( $project->over_percentage ) . '%' ); ?>
								</td>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-header"><?php \esc_html_e( 'Largest loss', 'orbis-projects' ); ?></div>

			<div class="table-responsive">
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th scope="col"><?php \esc_html_e( 'Project', 'orbis-projects' ); ?></th>
							<th scope="col" class="text-end"><?php \esc_html_e( 'Time over budget', 'orbis-projects' ); ?></th>

							<?php if ( $can_read_price ) : ?>

								<th scope="col" class="text-end"><?php \esc_html_e( 'Costs', 'orbis-projects' ); ?></th>

							<?php endif; ?>
						</tr>
					</thead>

					<tbody>

						<?php foreach ( $projects_by_time as $project ) : ?>

							<tr>
								<td>
									<?php $project_link( $project ); ?>
								</td>
								<td class="text-end <?php echo \esc_attr( $project->over_seconds > 10 * \HOUR_IN_SECONDS ? 'text-danger fw-bold' : '' ); ?>">
									<?php echo \esc_html( Duration::try_from_seconds( $project->over_seconds )?->format() ?? '' ); ?>
								</td>

								<?php if ( $can_read_price ) : ?>

									<td class="text-end">
										<?php

										if ( null !== $project->over_amount ) {
											echo \orbis_price( $project->over_amount ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}

										?>
									</td>

								<?php endif; ?>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php

\get_footer();
