<?php

global $wpdb, $post;

$orbis_project = new Pronamic\Orbis\Projects\Project( $post );

wp_nonce_field( 'orbis_save_project_details', 'orbis_project_details_meta_box_nonce' );

$orbis_id     = get_post_meta( $post->ID, '_orbis_project_id', true );
$principal_id = get_post_meta( $post->ID, '_orbis_project_principal_id', true );
$seconds      = get_post_meta( $post->ID, '_orbis_project_seconds_available', true );
$agreement_id = get_post_meta( $post->ID, '_orbis_project_agreement_id', true );

$invoice_reference        = get_post_meta( $post->ID, '_orbis_invoice_reference', true );
$invoice_line_description = get_post_meta( $post->ID, '_orbis_invoice_line_description', true );

$price = $orbis_project->get_price();

$project = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_projects WHERE post_id = %d;", $post->ID ) );

if ( $project ) {
	$orbis_id       = $project->id;
	$principal_id   = $project->principal_id;
	$invoice_number = $project->invoice_number;
	$seconds        = $project->number_seconds;
}

$principal = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->orbis_companies WHERE id= %d;", $principal_id ) );

$hourly_rate = get_post_meta( $post->ID, '_orbis_hourly_rate', true );

$start_date = null;

$start_date_string = \get_post_meta( $post->ID, '_orbis_project_start_date', true );

if ( '' !== $start_date_string ) {
	$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $start_date_string );

	$start_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
}

$end_date = null;

$end_date_string = \get_post_meta( $post->ID, '_orbis_project_end_date', true );

if ( '' !== $end_date_string ) {
	$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $end_date_string );

	$end_date = ( false === $value ) ? null : $value->setTime( 0, 0 );
}

$billed_to = null;

$billed_to_string = \get_post_meta( $post->ID, '_orbis_project_billed_to', true );

if ( '' !== $billed_to_string ) {
	$value = DateTimeImmutable::createFromFormat( 'Y-m-d', $billed_to_string );

	$billed_to = ( false === $value ) ? null : $value->setTime( 0, 0 );
}

$final_invoice_number = \get_post_meta( $post->ID, '_orbis_project_invoice_number', true );

?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_project_id"><?php esc_html_e( 'Orbis ID', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<input id="orbis_project_id" name="_orbis_project_id" value="<?php echo esc_attr( $orbis_id ); ?>" type="text" class="regular-text" readonly="readonly" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="_orbis_project_principal_id"><?php esc_html_e( 'Client', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<select id="_orbis_project_principal_id" name="_orbis_project_principal_id" class="orbis-id-control orbis_company_id_field regular-text" placeholder="<?php esc_html_e( 'Select Client', 'orbis-projects' ); ?>">
					<option id="orbis_select2_default" selected="selected" value="<?php echo esc_attr( $principal_id ); ?>">
						<?php echo esc_attr( $principal ); ?>
					</option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_project_price"><?php esc_html_e( 'Price', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_project_price" name="_orbis_price" value="<?php echo esc_attr( empty( $price ) ? '' : number_format_i18n( $price, 2 ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_project_hourly_rate"><?php esc_html_e( 'Hourly Rate', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_project_hourly_rate" name="_orbis_hourly_rate" value="<?php echo esc_attr( empty( $hourly_rate ) ? '' : number_format_i18n( $hourly_rate, 2 ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="_orbis_project_seconds_available"><?php esc_html_e( 'Time', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<input size="5" id="_orbis_project_seconds_available" name="_orbis_project_seconds_available" value="<?php echo esc_attr( orbis_time( $seconds ) ); ?>" type="text" />

				<p class="description">
					<?php esc_html_e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis-projects' ); ?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="_orbis_project_agreement_id">
					<?php esc_html_e( 'Agreement ID', 'orbis-projects' ); ?>
				</label>
			</th>
			<td>
				<input size="5" type="text" id="_orbis_project_agreement_id" name="_orbis_project_agreement_id" value="<?php echo esc_attr( $agreement_id ); ?>" />

				<a id="choose-from-library-link" class="button"
					data-choose="<?php esc_attr_e( 'Choose an Agreement', 'orbis-projects' ); ?>"
					data-type="<?php echo esc_attr( 'application/pdf, plain/text' ); ?>"
					data-element="<?php echo esc_attr( '_orbis_project_agreement_id' ); ?>"
					data-update="<?php esc_attr_e( 'Set as Agreement', 'orbis-projects' ); ?>"><?php esc_html_e( 'Choose a Agreement', 'orbis-projects' ); ?></a>

				<p class="description">
					<?php esc_html_e( 'You can select an .PDF or .TXT file from the WordPress media library.', 'orbis-projects' ); ?><br />
					<?php esc_html_e( 'If you received the agreement by mail print the complete mail conversation with an PDF printer.', 'orbis-projects' ); ?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="_orbis_project_is_finished">
					<?php esc_html_e( 'Finished', 'orbis-projects' ); ?>
				</label>
			</th>
			<td>
				<label for="_orbis_project_is_finished">
					<input type="checkbox" value="yes" id="_orbis_project_is_finished" name="_orbis_project_is_finished" <?php checked( $orbis_project->is_finished() ); ?> />
					<?php esc_html_e( 'Project is finished', 'orbis-projects' ); ?>
				</label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="_orbis_project_is_invoicable">
					<?php esc_html_e( 'Invoicable', 'orbis-projects' ); ?>
				</label>
			</th>
			<td>
				<label for="_orbis_project_is_invoicable">
					<input type="checkbox" value="yes" id="_orbis_project_is_invoicable" name="_orbis_project_is_invoicable" <?php checked( $orbis_project->is_invoicable() ); ?> />
					<?php esc_html_e( 'Project is invoicable', 'orbis-projects' ); ?>
				</label>
			</td>
		</tr>

		<?php if ( current_user_can( 'edit_orbis_project_administration' ) ) : ?>

			<tr valign="top">
				<th scope="row">
					<label for="_orbis_project_is_invoiced">
						<?php esc_html_e( 'Invoiced', 'orbis-projects' ); ?>
					</label>
				</th>
				<td>
					<label for="_orbis_project_is_invoiced">
						<input type="checkbox" value="yes" id="_orbis_project_is_invoiced" name="_orbis_project_is_invoiced" <?php checked( $orbis_project->is_invoiced() ); ?> />
						<?php esc_html_e( 'Project is invoiced', 'orbis-projects' ); ?>
					</label>
				</td>
			</tr>

		<?php endif; ?>

		<tr>
			<th scope="row">
				<label for="_orbis_invoice_reference"><?php esc_html_e( 'Invoice reference', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<textarea id="_orbis_invoice_reference" name="_orbis_invoice_reference" rows="2" cols="60"><?php echo esc_textarea( $invoice_reference ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="_orbis_invoice_line_description"><?php esc_html_e( 'Invoice Line Description', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<input type="text" id="_orbis_invoice_line_description" name="_orbis_invoice_line_description" value="<?php echo esc_attr( $invoice_line_description ); ?>" class="regular-text" />
			</td>
		</tr>

		<?php
		$terms = get_the_terms( $post->ID, 'orbis_payment_method' );

		if ( ! is_wp_error( $terms ) ) :
			$term = ( false !== $terms ) ? array_shift( $terms ) : $terms;
			?>
			<tr valign="top">
				<th scope="row">
					<label for="orbis_project_payment_method"><?php esc_html_e( 'Payment Method', 'orbis-projects' ); ?></label>
				</th>
			<td>
				<?php
					wp_dropdown_categories(
						[
							'name'             => 'tax_input[orbis_payment_method]',
							'show_option_none' => __( '— Select Payment Method —', 'orbis-projects' ),
							'hide_empty'       => false,
							'selected'         => is_object( $term ) ? $term->term_id : false,
							'taxonomy'         => 'orbis_payment_method',
						] 
					);
				?>
			</td>
		</tr>
		<?php endif; ?>

		<tr valign="top">
			<th scope="row">
				<label for="orbis_project_start_date"><?php esc_html_e( 'Period', 'orbis-tasks' ); ?></label>
			</th>
			<td>
				<?php

				echo \wp_kses(
					\sprintf(
						/**
						 * The 'to' includes the end date, in Dutch it is translated as 'tot en met'.
						 *
						 * @link https://taaladvies.net/tot-of-tot-en-met/
						 */
						/* translators: 1: input for start date, 2: input for end date */
						_x( '%1$s to %2$s', 'including', 'orbis-projects' ),
						\sprintf( 
							'<input id="orbis_project_start_date" name="_orbis_project_start_date" value="%s" type="date" />',
							\esc_attr( null === $start_date ? '' : $start_date->format( 'Y-m-d' ) )
						),
						\sprintf(
							'<input id="orbis_project_end_date" name="_orbis_project_end_date" value="%s" type="date" />',
							esc_attr( null === $end_date ? '' : $end_date->format( 'Y-m-d' ) )
						)
					),
					[
						'input' => [
							'id'    => true,
							'name'  => true,
							'value' => true,
							'type'  => true,
						],
					]
				);

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="orbis_project_billed_to"><?php echo esc_html( _x( 'Billed to', 'including', 'orbis-projects' ) ); ?></label>
			</th>
			<td>
				<input id="orbis_project_billed_to" name="_orbis_project_billed_to" value="<?php echo esc_attr( null === $billed_to ? '' : $billed_to->format( 'Y-m-d' ) ); ?>" type="date" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="orbis_project_invoice_number"><?php esc_html_e( 'Final invoice number', 'orbis-projects' ); ?></label>
			</th>
			<td>
				<input id="orbis_project_invoice_number" name="_orbis_project_invoice_number" value="<?php echo esc_attr( $final_invoice_number ); ?>" type="text" />
			</td>
		</tr>
	</tbody>
</table>

<?php

// @see https://github.com/WordPress/WordPress/blob/master/wp-admin/js/custom-background.js#L23

wp_enqueue_media();

?>

<script type="text/javascript">
	( function( $ ) {
		$( document ).ready( function() {
			var frame;

			$('#choose-from-library-link').click( function( event ) {
				var $el = $( this );

				event.preventDefault();

				// If the media frame already exists, reopen it.
				if ( frame ) {
					frame.open();
					return;
				}

				// Create the media frame.
				frame = wp.media.frames.projectAgreement = wp.media( {
					// Set the title of the modal.
					title: $el.data( 'choose' ),

					// Tell the modal to show only images.
					library: {
						type: $el.data( 'type' ),
					},

					// Customize the submit button.
					button: {
						// Set the text of the button.
						text: $el.data( 'update' ),
						// Tell the button not to close the modal, since we're
						// going to refresh the page when the image is selected.
						close: false
					}
				} );

				// When an image is selected, run a callback.
				frame.on( 'select', function() {
					// Grab the selected attachment.
					var attachment = frame.state().get( 'selection' ).first();

					var element_id = $el.data( 'element' );

					$( "#" + element_id ).val( attachment.id );

					frame.close();
				} );

				// Finally, open the modal.
				frame.open();
			} );
		} );
	} )( jQuery );
</script>
