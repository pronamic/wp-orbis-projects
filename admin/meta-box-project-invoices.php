<?php

global $wpdb, $post;

$orbis_project = new Orbis_Project( $post );

wp_nonce_field( 'orbis_save_project_invoices', 'orbis_project_invoices_meta_box_nonce' );

$project = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_projects WHERE post_id = %d;", $post->ID ) );
$project_invoices = $wpdb->get_results( $wpdb->prepare( "
	SELECT
		*
	FROM
		$wpdb->orbis_projects_invoices AS invoices
			LEFT JOIN
		$wpdb->users AS users
			ON invoices.user_id = users.ID
	WHERE
		project_id = %d;
", $project->id ) );

if ( $project_invoices ) : ?>

	<table class="widefat table">
		<thead>
			<th scope="col"><?php esc_html_e( 'Date', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Amount', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Hours', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Invoice Number', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'User', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Is Final Invoice', 'orbis-projects' ); ?></th>
			<th scope="col"></th>
			<th scope="col"></th>
		</thead>

		<?php foreach ( $project_invoices as $invoice ) : ?>

			<tr valign="top">
				<td>
					<span><?php echo esc_html( date_format( new DateTime( $invoice->create_date ), 'd-m-Y' ) ) ?></span>
				</td>
				<td>
					<input type="text" size="5" name="_orbis_project_invoice_amount_edit_<?php echo esc_html( $invoice->id ) ?>" value="<?php echo esc_html( $invoice->amount ) ?>"/>
				</td>
				<td>
					<input type="text" size="5" name="_orbis_project_invoice_seconds_available_edit_<?php echo esc_html( $invoice->id ) ?>" value="<?php echo esc_html( orbis_time( $invoice->hours ) ) ?>"/>
				</td>
				<td>
					<input type="text" name="_orbis_project_invoice_number_edit_<?php echo esc_html( $invoice->id ) ?>" value="<?php echo esc_html( $invoice->invoice_number ) ?>"/>
				</td>
				<td>
					<span><?php echo esc_html( $invoice->display_name ) ?></span>
				</td>
				<td>
					<?php if ( 1 <= $invoice->is_final_invoice ) : ?>
						<input type="radio" name="_is_final_invoice_edit" value="<?php echo esc_html( $invoice->id ) ?>" checked>
					<?php else : ?>
						<input type="radio" name="_is_final_invoice_edit" value="<?php echo esc_html( $invoice->id ) ?>">
					<?php endif; ?>
				</td>
				<td>
					<span style="float: right;">
						<?php submit_button( __( 'Edit Invoice', 'orbis-projects' ), 'secondary', $invoice->id, false );?>
					</span>
				</td>
				<td>
					<span style="float: right;">
						<?php submit_button( __( 'Delete Invoice', 'orbis-projects' ), 'delete', $invoice->id, false );?>
					</span>
				</td>
			</tr>

		<?php endforeach; ?>
	</table>

<?php endif; ?>

<p>
	<strong><?php esc_html_e( 'Add New Invoice:', 'orbis-projects' ); ?></strong>
</p>
<table class="widefat table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_project_invoice_number"><?php esc_html_e( 'Invoice Number', 'orbis-projects' ); ?></label>
		</th>
		<td>
			<input id="orbis_project_invoice_number" name="_orbis_project_invoice_number" type="text" class="regular-text" />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="orbis_project_invoice_amount"><?php esc_html_e( 'Amount', 'orbis-projects' ); ?></label>
		</th>
		<td>
			<input id="orbis_project_invoice_amount" name="_orbis_project_invoice_amount" type="text" class="regular-text" />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="_orbis_project_seconds_available"><?php esc_html_e( 'Invoice Time', 'orbis-projects' ); ?></label>
		</th>
		<td>
			<input size="5" id="_orbis_project_invoice_seconds_available" name="_orbis_project_invoice_seconds_available" value="<?php echo esc_attr( orbis_time( $seconds ) ); ?>" type="text" />

			<p class="description">
				<?php esc_html_e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis-projects' ); ?>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="_orbis_project_is_final_invoice"><?php esc_html_e( 'Is Final Invoice', 'orbis-projects' ); ?></label>
		</th>
		<td>
			<input type="checkbox" name="_orbis_project_is_final_invoice" value="1">
		</td>
	</tr>

	<tr>
		<td>
			<input type="hidden" name="_project_id" value="<?php echo esc_html( $project->id ) ?>">
			<?php
			submit_button( __( 'Add Invoice', 'orbis-projects' ), 'secondary', 'orbis_projects_invoice_add', false );
			?>
		</td>
	</tr>
</table>

<script type="text/javascript">
	( function( $ ) {
		$( document ).ready( function() {
			$( '.delete' ).on( 'click', function( e ) {
				if ( ! confirm( '<?php _e( 'Are you sure you want to delete this invoice?', 'orbis-projects' ) ?>' )) {
					if ( e.preventDefault ) {
						e.preventDefault();
					}

				return false;
				}
			} );
		} );
	} )( jQuery );
</script>