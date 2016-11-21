<?php

function orbis_projects_create_initial_post_types() {
	register_post_type(
		'orbis_project',
		array(
			'label'           => __( 'Projects', 'orbis_projects' ),
			'labels'          => array(
				'name'               => __( 'Projects', 'orbis_projects' ),
				'singular_name'      => __( 'Project', 'orbis_projects' ),
				'add_new'            => _x( 'Add New', 'orbis_project', 'orbis_projects' ),
				'add_new_item'       => __( 'Add New Project', 'orbis_projects' ),
				'edit_item'          => __( 'Edit Project', 'orbis_projects' ),
				'new_item'           => __( 'New Project', 'orbis_projects' ),
				'all_items'          => __( 'All Projects', 'orbis_projects' ),
				'view_item'          => __( 'View Project', 'orbis_projects' ),
				'search_items'       => __( 'Search Projects', 'orbis_projects' ),
				'not_found'          => __( 'No projects found.', 'orbis_projects' ),
				'not_found_in_trash' => __( 'No projects found in Trash.', 'orbis_projects' ),
				'parent_item_colon'  => __( 'Parent Project:', 'orbis_projects' ),
				'menu_name'          => __( 'Projects', 'orbis_projects' ),
			),
			'public'          => true,
			'menu_position'   => 30,
			'menu_icon'       => 'dashicons-portfolio',
			'capability_type' => 'orbis_project',
			'supports'        => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'revisions' ),
			'has_archive'     => true,
			'rewrite'         => array(
				'slug' => _x( 'projects', 'slug', 'orbis_projects' ),
			),
		)
	);

	register_taxonomy(
		'orbis_project_category',
		array( 'orbis_project' ),
		array(
			'hierarchical' => true,
			'labels'       => array(
				'name'              => _x( 'Categories', 'taxonomy general name', 'orbis_projects' ),
				'singular_name'     => _x( 'Category', 'taxonomy singular name', 'orbis_projects' ),
				'search_items'      => __( 'Search Categories', 'orbis_projects' ),
				'all_items'         => __( 'All Categories', 'orbis_projects' ),
				'parent_item'       => __( 'Parent Category', 'orbis_projects' ),
				'parent_item_colon' => __( 'Parent Category:', 'orbis_projects' ),
				'edit_item'         => __( 'Edit Category', 'orbis_projects' ),
				'update_item'       => __( 'Update Category', 'orbis_projects' ),
				'add_new_item'      => __( 'Add New Category', 'orbis_projects' ),
				'new_item_name'     => __( 'New Category Name', 'orbis_projects' ),
				'menu_name'         => __( 'Categories', 'orbis_projects' ),
			),
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array(
				'slug' => _x( 'project-category', 'slug', 'orbis_projects' ),
			),
		)
	);
}

add_action( 'init', 'orbis_projects_create_initial_post_types', 0 ); // highest priority
