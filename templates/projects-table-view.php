<div class="panel">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Manager', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'ID', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Client', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Project', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Comment', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Price', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Time', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Invoiceable', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Invoice Number', 'orbis-projects' ); ?></th>
				<th></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $managers as $manager ) : ?>

				<tr>
					<th rowspan="<?php echo esc_attr( count( $manager->projects ) + 1 ); ?>">
						<?php echo esc_html( $manager->name ); ?>
					</th>
				</tr>

				<?php foreach ( $manager->projects as $project ) : ?>

					<tr>
						<td>
							<a href="<?php echo esc_attr( get_permalink( $project->project_post_id ) ); ?>" style="color: #000;">
								<?php echo esc_html( $project->id ); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_attr( get_permalink( $project->principal_post_id ) ); ?>" style="color: #000;">
								<?php echo esc_html( $project->principal_name ); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_attr( get_permalink( $project->project_post_id ) ); ?>" style="color: #000;">
								<?php echo esc_html( $project->name ); ?>
							</a>
						</td>
						<td style="white-space: nowrap;">
							<?php echo esc_html( get_the_time( 'j M Y', $project->project_post_id ) ); ?>
						</td>
						<td style="white-space: nowrap;">
							<?php

							$comments_query = new WP_Comment_Query();

							$comments = $comments_query->query( array(
								'number'  => 1,
								'post_id' => $project->project_post_id,
							) );

							foreach ( $comments as $comment ) {
								$title = sprintf(
									__( '%1$s says on %2$s:', 'orbis-projects' ),
									'<strong>' . $comment->comment_author . '</strong>',
									'<strong>' . date_i18n( 'j M Y', strtotime( $comment->comment_date ) ) . '</strong>'
								);

								printf(
									'<a href="%s" data-container="body" data-trigger="hover" data-toggle="popover" data-placement="right" data-title="%s" data-content="%s" data-html="true" role="button">%s</a>',
									esc_attr( get_comment_link( $comment ) ),
									esc_attr( $title ),
									esc_attr( $comment->comment_content ),
									esc_attr( date_i18n( 'j M Y', strtotime( $comment->comment_date ) ) ) . ' <span class="glyphicon glyphicon-comment" aria-hidden="true"></span>'
								);
							}

							?>
						</td>
						<td>
							<?php echo esc_html( orbis_price( get_post_meta( $project->project_post_id, '_orbis_price', true ) ) ); ?>
						</td>
						<td style="white-space: nowrap;">
							<span style="color: <?php echo esc_attr( $project->failed ? 'Red' : 'Green' ); ?>;"><?php echo esc_html( orbis_time( $project->registered_seconds ) ); ?></span>
							/
							<?php echo esc_html( orbis_time( $project->available_seconds ) ); ?>
						</td>
						<td>
							<?php echo esc_html( $project->invoicable ? 'Ja' : 'Nee' ); ?>
						</td>
						<td><?php echo esc_html( $project->invoice_number ); ?></td>
						<td>
							<?php

							$text = '';

							$text .= '<i class="fa fa-pencil" aria-hidden="true"></i>';
							$text .= sprintf(
								'<span class="sr-only sr-only-focusable">%s</span>',
								__( 'Edit', 'orbis-projects' )
							);

							edit_post_link( $text, '', '', $project->project_post_id );

							?>
						</td>
					</tr>

				<?php endforeach; ?>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>

<script>
	jQuery( document ).ready( function( $ ) {
		$( '[data-toggle="popover"]' ).popover(); 
	} );
</script>
