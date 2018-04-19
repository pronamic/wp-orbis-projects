<?php

global $wpdb;

$extra_select  = '';
$extra_join    = '';

$orderby = '';

if ( orbis_plugin_activated( 'companies' ) ) {
	$extra_select .= "
	,
	principal.id AS principal_id,
	principal.name AS principal_name,
	principal.post_id AS principal_post_id
	";
	$extra_join .= "
	LEFT JOIN
		$wpdb->orbis_companies AS principal
			ON project.principal_id = principal.id
	";
	$orderby .= "
	ORDER BY
		principal.name
	";
}

if ( orbis_plugin_activated( 'timesheets' ) ) {
	$extra_select .= "
	, SUM( registration.number_seconds ) AS registered_seconds
	";

	$extra_join .= "
	LEFT JOIN
		$wpdb->orbis_timesheets AS registration
			ON project.id = registration.project_id
	";
}

$sql = "
	SELECT
		project.id ,
		project.name ,
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
				ON post.post_author = manager.ID
			$extra_join
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
	GROUP BY
		project.id
	$orderby
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

	$project->registered_seconds = isset( $project->registered_seconds ) ? $project->registered_second : 0;

	$project->failed = $project->registered_seconds > $project->available_seconds;

	$manager = $managers[ $project->project_manager_id ];

	$manager->projects[] = $project;
}

ksort( $managers );

require 'projects-table-view.php';
