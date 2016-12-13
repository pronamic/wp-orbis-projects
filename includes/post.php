<?php

function orbis_projects_create_initial_post_types() {
	register_post_type(
		'orbis_project',
		array(
			'label'           => __( 'Projects', 'orbis-projects' ),
			'labels'          => array(
				'name'                  => __( 'Projects', 'orbis-projects' ),
				'singular_name'         => __( 'Project', 'orbis-projects' ),
				'add_new'               => _x( 'Add New', 'orbis_project', 'orbis-projects' ),
				'add_new_item'          => __( 'Add New Project', 'orbis-projects' ),
				'edit_item'             => __( 'Edit Project', 'orbis-projects' ),
				'new_item'              => __( 'New Project', 'orbis-projects' ),
				'view_item'             => __( 'View Project', 'orbis-projects' ),
				'view_items'            => __( 'View Projects', 'orbis-projects' ),
				'search_items'          => __( 'Search Projects', 'orbis-projects' ),
				'not_found'             => __( 'No projects found.', 'orbis-projects' ),
				'not_found_in_trash'    => __( 'No projects found in Trash.', 'orbis-projects' ),
				'parent_item_colon'     => __( 'Parent Project:', 'orbis-projects' ),
				'all_items'             => __( 'All Projects', 'orbis-projects' ),
				'archives'              => __( 'Project Archives', 'orbis-projects' ),
				'attributes'            => __( 'Project Attributes', 'orbis-projects' ),
				'insert_into_item'      => __( 'Insert into project', 'orbis-projects' ),
				'uploaded_to_this_item' => __( 'Uploaded to this project', 'orbis-projects' ),
				'filter_items_list'     => __( 'Filter projects list', 'orbis-projects' ),
				'items_list_navigation' => __( 'Projects list navigation', 'orbis-projects' ),
				'items_list'            => __( 'Projects list', 'orbis-projects' ),
			),
			'public'          => true,
			'menu_position'   => 30,
			'menu_icon'       => 'dashicons-portfolio',
			'capability_type' => 'orbis_project',
			'supports'        => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'revisions' ),
			'has_archive'     => true,
			'rewrite'         => array(
				'slug' => _x( 'projects', 'slug', 'orbis-projects' ),
			),
		)
	);

	register_taxonomy(
		'orbis_project_category',
		array( 'orbis_project' ),
		array(
			'hierarchical' => true,
			'labels'       => array(
				'name'              => _x( 'Categories', 'taxonomy general name', 'orbis-projects' ),
				'singular_name'     => _x( 'Category', 'taxonomy singular name', 'orbis-projects' ),
				'search_items'      => __( 'Search Categories', 'orbis-projects' ),
				'all_items'         => __( 'All Categories', 'orbis-projects' ),
				'parent_item'       => __( 'Parent Category', 'orbis-projects' ),
				'parent_item_colon' => __( 'Parent Category:', 'orbis-projects' ),
				'edit_item'         => __( 'Edit Category', 'orbis-projects' ),
				'update_item'       => __( 'Update Category', 'orbis-projects' ),
				'add_new_item'      => __( 'Add New Category', 'orbis-projects' ),
				'new_item_name'     => __( 'New Category Name', 'orbis-projects' ),
				'menu_name'         => __( 'Categories', 'orbis-projects' ),
			),
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array(
				'slug' => _x( 'project-category', 'slug', 'orbis-projects' ),
			),
		)
	);
}

add_action( 'init', 'orbis_projects_create_initial_post_types', 0 ); // highest priority
