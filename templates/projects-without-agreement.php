<?php

// Create a new filtering function that will add our where clause to the query
function filter_where( $where = '' ) {
	// posts for March 1 to March 15, 2010
	$where .= " AND post_date >= '2013-01-01'";
	return $where;
}

add_filter( 'posts_where', 'filter_where' );

// @codingStandardsIgnoreStart
$query = new WP_Query( array(
	'post_type'      => 'orbis_project',
	'posts_per_page' => 50,
	'meta_query'     => array(
		array(
			'key'     => '_orbis_project_agreement_id',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_orbis_project_is_invoicable',
			'compare' => 'EXISTS',
		),
	),
) );
// @codingStandardsIgnoreEnd

remove_filter( 'posts_where', 'filter_where' );

if ( $query->have_posts() ) : ?>

	<div class="panel">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Orbis ID', 'orbis-projects' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Project Manager', 'orbis-projects' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Principal', 'orbis-projects' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Title', 'orbis-projects' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Actions', 'orbis-projects' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php
					// @codingStandardsIgnoreStart
					while ( $query->have_posts() ) : $query->the_post();
					// @codingStandardsIgnoreEnd
				?>

					<tr>
						<td>
							<?php echo esc_html( get_post_meta( get_the_ID(), '_orbis_project_id', true ) ); ?>
						</td>
						<td>
							<?php the_author(); ?>
						</td>
						<td>
							<?php

							global $orbis_project;

						if ( $orbis_project->has_principal() ) {
							printf(
								'<a href="%s">%s</a>',
								esc_attr( get_permalink( $orbis_project->get_principal_post_id() ) ),
								esc_html( $orbis_project->get_principal_name() )
							);
						}

							?>
						</td>
						<td>
							<a href="<?php the_permalink(); ?>">
								<?php the_title(); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_attr( get_edit_post_link( get_the_ID() ) ); ?>">
								<?php esc_html_e( 'Edit', 'orbis-projects' ); ?>
							</a>
						</td>
					</tr>

				<?php endwhile; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>
