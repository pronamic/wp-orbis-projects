<?php
/**
 * Content types
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

class ContentTypes {
	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize.
	 */
	public function init() {
		register_post_type(
			'orbis_project',
			[
				'label'         => __( 'Projects', 'orbis-projects' ),
				'labels'        => [
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
				],
				'public'        => true,
				'menu_position' => 30,
				'menu_icon'     => 'dashicons-portfolio',
				'supports'      => [ 'title', 'editor', 'author', 'comments', 'custom-fields', 'revisions' ],
				'has_archive'   => true,
				'show_in_rest'  => true,
				'rest_base'     => 'orbis/projects',
				'rewrite'       => [
					'slug' => _x( 'projects', 'slug', 'orbis-projects' ),
				],
			]
		);

		register_taxonomy(
			'orbis_project_category',
			[ 'orbis_project' ],
			[
				'hierarchical' => true,
				'labels'       => [
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
				],
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => [
					'slug' => _x( 'project-category', 'slug', 'orbis-projects' ),
				],
			]
		);

		register_taxonomy(
			'orbis_project_status',
			[ 'orbis_project' ],
			[
				'hierarchical' => true,
				'labels'       => [
					'name'              => _x( 'Statuses', 'taxonomy general name', 'orbis-projects' ),
					'singular_name'     => _x( 'Status', 'taxonomy singular name', 'orbis-projects' ),
					'search_items'      => __( 'Search Statuses', 'orbis-projects' ),
					'all_items'         => __( 'All Statuses', 'orbis-projects' ),
					'parent_item'       => __( 'Parent Status', 'orbis-projects' ),
					'parent_item_colon' => __( 'Parent Status:', 'orbis-projects' ),
					'edit_item'         => __( 'Edit Status', 'orbis-projects' ),
					'update_item'       => __( 'Update Status', 'orbis-projects' ),
					'add_new_item'      => __( 'Add New Status', 'orbis-projects' ),
					'new_item_name'     => __( 'New Status Name', 'orbis-projects' ),
					'menu_name'         => __( 'Statuses', 'orbis-projects' ),
				],
				'show_ui'      => true,
				'public'       => true,
				'show_in_rest' => true,
				'query_var'    => true,
				'rewrite'      => [
					'slug' => _x( 'project-status', 'slug', 'orbis-projects' ),
				],
			]
		);

		register_taxonomy_for_object_type( 'orbis_payment_method', 'orbis_project' );
	}
}
