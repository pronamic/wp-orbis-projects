<?php

global $wpdb;

$extra_select  = '';
$extra_join    = '';
$extra_orderby = '';

if ( isset( $wpdb->orbis_companies ) ) {
	$extra_select .= '
	,
	principal.id AS principal_id,
	principal.name AS principal_name,
	principal.post_id AS principal_post_id
	';

	$extra_join .= "
	LEFT JOIN
		$wpdb->orbis_companies AS principal
			ON project.principal_id = principal.id
	";

	$extra_orderby .= '
	, principal.name
	';
}

if ( isset( $wpdb->orbis_timesheets ) ) {
	$extra_select .= '
	, SUM( registration.number_seconds ) AS registered_seconds
	';

	$extra_join .= "
	LEFT JOIN
		$wpdb->orbis_timesheets AS registration
			ON project.id = registration.project_id
	";
}

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
		manager.display_name AS project_manager_name
		$extra_select
	FROM
		$wpdb->orbis_projects AS project
			LEFT JOIN
		$wpdb->posts AS post
				ON project.post_id = post.ID
			LEFT JOIN
		$wpdb->users AS manager
				ON	post.post_author = manager.ID
			$extra_join
	WHERE
		NOT project.finished
	GROUP BY
		project.id
	ORDER BY
		%s ;
";

// Order by
$order_by = 'project.name' . $extra_orderby;

if ( isset( $_GET['order'] ) ) { // WPCS: CSRF ok.
	switch ( $_GET['order'] ) { // WPCS: CSRF ok.
		case 'id':
			$order_by = 'project.id DESC';
			break;
	}
}

// Build query
$sql = sprintf( $sql, $order_by );

// Projects.
$projects = $wpdb->get_results( $sql ); // WPCS: unprepared SQL ok.

// Groups.
$groups = [];

// Managers.
foreach ( $projects as $project ) {
	// Find manager
	if ( ! isset( $groups[ $project->project_manager_id ] ) ) {
		$group           = new stdClass();
		$group->id       = $project->project_manager_id;
		$group->name     = $project->project_manager_name;
		$group->projects = [];

		$groups[ $group->id ] = $group;
	}
}

$groups = wp_list_sort( $groups, 'name', 'ASC', true );

$groups['marketing'] = (object) [
	'id'       => 'marketing',
	'name'     => 'Marketing',
	'projects' => [],
];

$groups['strippenkaart'] = (object) [
	'id'       => 'strippenkaart',
	'name'     => 'Strippenkaarten',
	'projects' => [],
];

$groups['pronamic'] = (object) [
	'id'       => 'pronamic',
	'name'     => 'Pronamic',
	'projects' => [],
];

// Projects and managers
foreach ( $projects as $project ) {
	$post = get_post( $project->project_post_id );

	$group_id = $project->project_manager_id;

	if ( has_term( 'pronamic', 'orbis_project_category', $post ) ) {
		$group_id = 'pronamic';
	}

	if ( has_term( 'strippenkaart', 'orbis_project_category', $post ) ) {
		$group_id = 'strippenkaart';
	}

	if ( has_term( 'online-marketing', 'orbis_project_category', $post ) ) {
		$group_id = 'marketing';
	}

	$orbis_project = new Pronamic\Orbis\Projects\Project( $post );

	$project->registered_seconds = $orbis_project->get_registered_seconds();

	$project->failed = $project->registered_seconds > $project->available_seconds;

	$group = $groups[ $group_id ];

	$group->projects[] = $project;
}

$parameters = $_GET; // WPCS: CSRF ok.

?>
<p>
	<?php esc_html_e( 'Order By:', 'orbis-projects' ); ?> <a href="?order=name"><?php esc_html_e( 'Name', 'orbis-projects' ); ?></a> | <a href="?order=id"><?php esc_html_e( 'Number', 'orbis-projects' ); ?></a><br />
</p>

<?php

require 'projects-table-view.php';
