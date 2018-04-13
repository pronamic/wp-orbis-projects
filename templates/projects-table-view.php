<?php

wp_enqueue_script( 'wp-api' );

wp_localize_script( 'wp-api', 'wpApiSettings', array(
	'root'  => esc_url_raw( rest_url() ),
	'nonce' => wp_create_nonce( 'wp_rest' ),
) );

$statuses = get_terms( array(
	'taxonomy'   => 'orbis_project_status',
	'hide_empty' => false,
) );

?>

<div class="panel">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Client', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Project', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Comment', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Budget', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Invoicable', 'orbis-projects' ); ?></th>
				<th></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $managers as $manager ) : ?>

				<tr>
					<th colspan="8">
						<?php echo esc_html( $manager->name ); ?>
					</th>
				</tr>

				<?php foreach ( $manager->projects as $project ) : ?>

					<tr>
						<td>
							<a href="<?php echo esc_attr( get_permalink( $project->principal_post_id ) ); ?>" style="color: #000;">
								<?php echo esc_html( $project->principal_name ); ?>
							</a>
						</td>
						<td>
							<code><?php echo esc_html( $project->id ); ?></code> -  

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
									/* translators: 1st placeholder is the comment author, 2nd is the date */
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
								echo '<br />';
							}

							$project_statuses = wp_get_post_terms( $project->project_post_id, 'orbis_project_status' );

							foreach ( $project_statuses as $project_status ) {
								$status_type = get_term_meta( $project_status->term_id, 'orbis_status_type', true ) ? get_term_meta( $project_status->term_id, 'orbis_status_type', true ) : 'primary';
								printf(
									'<span class="badge badge-pill badge-%s orbis-status" data-projectid="%s" data-statusid="%s">%s</span>',
									esc_attr( $status_type ),
									esc_attr( $project->project_post_id ),
									esc_attr( $project_status->term_id ),
									esc_attr( $project_status->name )
								);
								echo '<br />';
							}

							?>

							<?php if ( $statuses ) : ?>

								<div class="dropdown show">
									<a class="badge badge-pill badge-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<?php esc_html_e( 'Add status', 'orbis-projects' ); ?>
									</a>

									<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
										<?php

										foreach ( $statuses as $status ) {
											$status_type = get_term_meta( $status->term_id, 'orbis_status_type', true ) ? get_term_meta( $status->term_id, 'orbis_status_type', true ) : 'primary';
											printf(
												'<a class="dropdown-item orbis-js-add-status" data-statusType="%s" data-projectID="%s" href="%s">%s</a>',
												esc_attr( $status_type ),
												esc_attr( $project->project_post_id ),
												esc_attr( $status->term_id ),
												esc_attr( $status->name )
											);
										}

										?>
									</div>
								</div>

							<?php endif; ?>

						</td>
						<td style="white-space: nowrap;">
							<span style="color: <?php echo esc_attr( $project->failed ? 'Red' : 'Green' ); ?>;">
								<?php
								echo $project->registered_seconds ? esc_html( orbis_time( $project->registered_seconds ) ) : '00:00';
								?>
							</span>
							/
							<?php echo esc_html( orbis_time( $project->available_seconds ) ); ?>
							<br />
							<?php echo esc_html( orbis_price( get_post_meta( $project->project_post_id, '_orbis_price', true ) ) ); ?>
						</td>
						<td>
							<?php

							$invoice_number = $project->invoice_number;
							$invoice_link   = orbis_get_invoice_link( $invoice_number );

							if ( empty( $invoice_number ) ) {
								$invoice_number = esc_html__( 'Yes', 'orbis-projects' );
							} elseif ( ! empty( $invoice_link ) ) {
								$invoice_number = sprintf(
									'<a href="%s" target="_blank">%s</a>',
									esc_attr( $invoice_link ),
									esc_html( $invoice_number )
								);
							} else {
								$invoice_number = esc_html( $invoice_number );
							}

							echo $project->invoicable ? wp_kses_post( $invoice_number ) : esc_html__( 'No', 'orbis-projects' );

							?>
						</td>
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

		function addStatusToProject( projectID, status, addStatusObject ){
			$.ajax( {
				type: 'GET',
				url: window.location.origin + '/wp-json/wp/v2/orbis-projects/' + projectID,
				dataType: 'json',
				success: function( data ) {
					if ( !isValueInObject( status.id, data.orbis_project_status ) ) {
						drawStatusHTML( projectID, status, addStatusObject );

						var statuses = data.orbis_project_status;
						statuses.push( Number( status.id ) );

						$.ajax( {
							type: 'PUT',
							cache: false,
							beforeSend: function ( xhr ) {
								xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
							},
							url: 'http://orbis.local' + '/wp-json/wp/v2/orbis-projects/' + projectID,
							dataType: 'json',
							data: {orbis_project_status: statuses},
						} );
					}
				}
			} );
		}

		function removeStatusFromProject( projectID, statusID ){
			$.ajax( {
				type: 'GET',
				url: window.location.origin + '/wp-json/wp/v2/orbis-projects/' + projectID,
				dataType: 'json',
				success: function( data ) {

					var statuses = data.orbis_project_status;
					var index = statuses.indexOf( Number( statusID ) );

					statuses.splice( index, 1 );
					if ( statuses.length == 0 ) {
						statuses = -1;
					}

					$.ajax( {
						type: 'PUT',
						cache: false,
						beforeSend: function ( xhr ) {
							xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
						},
						url: window.location.origin + '/wp-json/wp/v2/orbis-projects/' + projectID,
						dataType: 'json',
						data: {orbis_project_status: statuses},
					} );
				}
			} );
		}

		function drawStatusHTML( projectID, status, addStatusObject ){
			var classes       = 'class="badge badge-pill badge-' + status.type + ' orbis-status" ';
			var dataProjectID = 'data-projectID="' + projectID + '" ';
			var dataStatusID  = 'data-statusID="' + status.id + '" ';

			var html = '<span ' + classes + dataProjectID + dataStatusID + '>' + status.text + '</span><br />';
			$( addStatusObject ).parent().parent().prepend( html );
		}

		function isValueInObject( val, obj ){
			var inObject = false;

			Object.keys( obj ).forEach( function( key ) {
				if ( obj[key] == val ) {
					inObject = true;
				}
			});
			return inObject;
		}

		$( '.orbis-js-add-status' ).click( function( event ) {
			event.preventDefault();

			var href = this.href;
			if ( href.substr(-1) == '/' ) {
				href = href.substr( 0, href.length - 1 );
			}

			// get values from status
			var statusType = $( this ).attr( 'data-statusType' );
			var statusID = href.split( '/' ).pop();
			var statusText = $( this ).text();
			var status = { type: statusType, id: statusID, text: statusText };

			var projectID = $( this ).attr( 'data-projectID' );
			var addStatusObject = this;

			addStatusToProject( projectID, status, addStatusObject );
		} );

		$( document ).on( 'click', '.orbis-status', function() {
			var projectID = $( this ).attr( 'data-projectID' );
			var statusID = $( this ).attr( 'data-statusID' );
			$( this ).next( "br" ).remove();
			$( this ).remove();
			removeStatusFromProject( projectID, statusID );
		} );
	} );
</script>
