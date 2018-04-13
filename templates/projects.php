<?php

global $wpdb;

// Managers
$sql = "
	SELECT
		project.id,
		project.name,
		project.number_seconds AS available_seconds,
		project.invoice_number AS invoice_number,
		project.invoicable,
		project.post_id AS project_post_id,
		manager.ID AS project_manager_id,
		manager.display_name AS project_manager_name,
		principal.id AS principal_id,
		principal.name AS principal_name,
		principal.post_id AS principal_post_id,
		SUM(registration.number_seconds) AS registered_seconds
	FROM
		$wpdb->orbis_projects AS project
			LEFT JOIN
		$wpdb->posts AS post
				ON project.post_id = post.ID
			LEFT JOIN
		$wpdb->users AS manager
				ON	post.post_author = manager.ID
			LEFT JOIN
		$wpdb->orbis_companies AS principal
				ON project.principal_id = principal.id
			LEFT JOIN
		$wpdb->orbis_timesheets AS registration
				ON project.id = registration.project_id
	WHERE
		NOT project.finished
	GROUP BY
		project.id
	ORDER BY
		%s ;
";

// Order by
$order_by = 'principal.name , project.name';
if ( isset( $_GET['order'] ) ) { // WPCS: CSRF ok.
	switch ( $_GET['order'] ) { // WPCS: CSRF ok.
		case 'id':
			$order_by = 'project.id DESC';
			break;
	}
}

// Build query
$sql = sprintf( $sql, $order_by );

// Projects
$projects = $wpdb->get_results( $sql ); // WPCS: unprepared SQL ok.

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

	$orbis_project = new Orbis_Project( get_post( $project->project_post_id ) );

	$project->registered_seconds = $orbis_project->get_registered_seconds();

	$project->failed = $project->registered_seconds > $project->available_seconds;

	$manager = $managers[ $project->project_manager_id ];

	$manager->projects[] = $project;
}

ksort( $managers );

$parameters = $_GET; // WPCS: CSRF ok.

?>
<p>
	<?php esc_html_e( 'Order By:', 'orbis-projects' ); ?> <a href="?order=name"><?php esc_html_e( 'Name', 'orbis-projects' ); ?></a> | <a href="?order=id"><?php esc_html_e( 'Number', 'orbis-projects' ); ?></a><br />
</p>

<?php

require 'projects-table-view.php';
