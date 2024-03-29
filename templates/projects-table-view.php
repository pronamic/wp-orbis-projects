<?php

use Pronamic\WordPress\Money\Money;

wp_enqueue_script( 'wp-api' );

wp_localize_script(
	'wp-api',
	'wpApiSettings',
	[
		'root'  => esc_url_raw( rest_url() ),
		'nonce' => wp_create_nonce( 'wp_rest' ),
	] 
);

$statuses = get_terms(
	[
		'taxonomy'   => 'orbis_project_status',
		'hide_empty' => false,
	] 
);

if ( ! isset( $groups ) ) {
	$groups = $managers;
}

?>

<div class="card">
	<table class="table table-striped mb-0">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Client', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Project', 'orbis-projects' ); ?></th>
				
				<?php if ( isset( $orbis_is_projects_to_invoice ) ) : ?>

					<th scope="col"><?php esc_html_e( 'Invoice', 'orbis-projects' ); ?></th>

				<?php endif; ?>

				<th scope="col"><?php esc_html_e( 'Date', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Comment', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Period', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Budget', 'orbis-projects' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Invoicable', 'orbis-projects' ); ?></th>
				<th></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $groups as $group ) : ?>

				<?php if ( ! empty( $group->projects ) ) : ?>

					<tr>
						<th colspan="8">
							<?php echo esc_html( $group->name ); ?>
						</th>
					</tr>

				<?php endif; ?>

				<?php foreach ( $group->projects as $project ) : ?>

					<tr>
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

						<?php if ( isset( $orbis_is_projects_to_invoice ) ) : ?>

							<td>
								<?php

								$invoice_references = [
									get_post_meta( $project->principal_post_id, '_orbis_invoice_reference', true ),
									get_post_meta( $project->project_post_id, '_orbis_invoice_reference', true ),
								];

								$invoice_references = array_filter( $invoice_references );
								$invoice_references = array_unique( $invoice_references );

								$invoice_line_description = get_post_meta( $project->project_post_id, '_orbis_invoice_line_description', true );

								if ( empty( $invoice_line_description ) ) {
									$invoice_line_description = $project->name;
								}

								$invoice_line_description = sprintf(
									'%1$s - %2$s',
									$project->id,
									$invoice_line_description
								);

								?>
								<dl>
									<dt><?php esc_html_e( 'Invoice reference', 'orbis-projects' ); ?></dt>
									<dd>
										<?php echo nl2br( esc_html( implode( "\r\n", $invoice_references ) ) ); ?>
									</dd>

									<dt><?php esc_html_e( 'Line Description', 'orbis-projects' ); ?></dt>
									<dd>
										<?php echo nl2br( esc_html( $invoice_line_description ) ); ?>
									</dd>
								</dl>
							</td>

						<?php endif; ?>

						<td style="white-space: nowrap;">
							<?php echo esc_html( get_the_time( 'D j M Y', $project->project_post_id ) ); ?>
						</td>
						<td style="white-space: nowrap;">
							<?php

							$comments_query = new WP_Comment_Query();

							$comments = $comments_query->query(
								[
									'number'  => 1,
									'post_id' => $project->project_post_id,
								] 
							);

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
									esc_attr( date_i18n( 'j M Y', strtotime( $comment->comment_date ) ) ) . ' <span class="fas fa-comment" aria-hidden="true"></span>'
								);
								echo '<br />';
							}

							$project_statuses = wp_get_post_terms( $project->project_post_id, 'orbis_project_status' );

							foreach ( $project_statuses as $project_status ) {
								$status_type = get_term_meta( $project_status->term_id, 'orbis_status_type', true ) ? get_term_meta( $project_status->term_id, 'orbis_status_type', true ) : 'primary';
								printf(
									'<span class="badge rounded-pill text-bg-%s orbis-status" data-projectid="%s" data-statusid="%s">%s</span>',
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
									<a class="badge rounded-pill text-bg-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
									<i class="fas fa-spinner fa-spin fa-fw d-none orbis-saving-<?php echo esc_attr( $project->project_post_id ); ?>"></i>
								</div>

							<?php endif; ?>

						</td>
						<td style="white-space: nowrap;">
							<?php

							$start_date = null;

							$start_date_string = \get_post_meta( $project->project_post_id, '_orbis_project_start_date', true );

							if ( '' !== $start_date_string ) {
								$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $start_date_string );

								$start_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
							}

							$end_date = null;

							$end_date_string = \get_post_meta( $project->project_post_id, '_orbis_project_end_date', true );

							if ( '' !== $end_date_string ) {
								$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $end_date_string );

								$end_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
							}

							if ( null !== $start_date && null !== $end_date ) {
								printf(
									/* translators: 1: Period start date, 2: Period end date. */
									\__( '%1$s - %2$s', 'orbis-projects' ),
									( null === $start_date ) ? '?' : \esc_html( \date_i18n( 'D j M Y', $start_date->getTimestamp() ) ),
									( null === $end_date ) ? '?' : \esc_html( \date_i18n( 'D j M Y', $end_date->getTimestamp() ) )
								);
							}

							?>
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
							<?php
							if ( get_post_meta( $project->project_post_id, '_orbis_price', true ) ) {
								$price = new Money( get_post_meta( $project->project_post_id, '_orbis_price', true ), 'EUR' );
								$price = $price->format_i18n();
								echo esc_html( $price );
							}
							?>
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

							$text .= '<i class="fas fa-edit" aria-hidden="true"></i>';
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
			var icon = $( ".orbis-saving-" + projectID );
			$.ajax( {
				type: 'GET',
				url: window.location.origin + '/wp-json/wp/v2/orbis-projects/' + projectID,
				dataType: 'json',
				success: function( data ) {
					if ( !isValueInObject( status.id, data.orbis_project_status ) ) {
						icon.removeClass( 'd-none' );
						drawStatusHTML( projectID, status, addStatusObject );

						var statuses = data.orbis_project_status;
						statuses.push( Number( status.id ) );

						$.ajax( {
							type: 'PUT',
							cache: false,
							beforeSend: function ( xhr ) {
								xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
							},
							url: window.location.origin + '/wp-json/wp/v2/orbis-projects/' + projectID,
							dataType: 'json',
							data: {orbis_project_status: statuses},
							success: function() {
								icon.addClass( 'd-none' );
							},
							error: function( errorThrown ) {
								icon.removeClass( 'fa-spin' );
								icon.removeClass( 'fa-spinner' );
								icon.addClass( 'fa-exclamation-triangle' );
								icon.addClass( 'text-danger' );
								icon.data( "toggle", 'popover' );
								icon.data( "content", errorThrown.responseJSON.message );
								icon.data( "trigger", 'hover' );
								icon.popover();
							}
						} );
					}
				}
			} );
		}

		function removeStatusFromProject( projectID, statusID ){
			var icon = $( ".orbis-saving-" + projectID );
			icon.removeClass( 'd-none' );
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
						success: function() {
							icon.addClass( 'd-none' );
						},
						error: function( errorThrown ) {
							icon.removeClass( 'fa-spin' );
							icon.removeClass( 'fa-spinner' );
							icon.addClass( 'fa-exclamation-triangle' );
							icon.addClass( 'text-danger' );
							icon.data( "toggle", 'popover' );
							icon.data( "content", errorThrown.responseJSON.message );
							icon.data( "trigger", 'hover' );
							icon.popover();
						}
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

		// add status when clicking option
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

		// remove status when clicking on status
		$( document ).on( 'click', '.orbis-status', function() {
			var projectID = $( this ).attr( 'data-projectID' );
			var statusID = $( this ).attr( 'data-statusID' );
			$( this ).next( "br" ).remove();
			$( this ).remove();
			removeStatusFromProject( projectID, statusID );
		} );
	} );
</script>
