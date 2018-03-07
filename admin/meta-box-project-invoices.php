<?php

global $wpdb, $post;

$orbis_project = new Orbis_Project( $post );

wp_nonce_field( 'orbis_save_project_invoices', 'orbis_project_invoices_meta_box_nonce' );

$project          = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_projects WHERE post_id = %d;", $post->ID ) );
$project_invoices = $orbis_project->get_invoices();

// check if an invoice is actually connected
if ( $project_invoices && $project_invoices[0]->id ) : ?>

	<table class="orbis-admin-table">
		<thead>
			<th scope="col"><?php esc_html_e( 'Date', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Amount', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Hours', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Invoice Number', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Final Invoice', 'orbis-projects' ); ?></th>
			<th scope="col"><?php esc_html_e( 'User', 'orbis-projects' ); ?></th>
			<th scope="col"></th>
		</thead>

		<?php $invoice_list = array(); ?>
		<!-- list of invoices -->
		<?php foreach ( $project_invoices as $invoice ) : ?>

			<tr valign="top">
				<td>
					<input id="orbis_project_invoice_date" name="_orbis_project_invoice_date_edit_<?php echo esc_html( $invoice->id ); ?>" type="date" value="<?php echo esc_html( date_format( new DateTime( $invoice->create_date ), 'Y-m-d' ) ); ?>" />
				</td>
				<td>
					<input type="text" size="10" name="_orbis_project_invoice_amount_edit_<?php echo esc_html( $invoice->id ); ?>" value="<?php echo esc_attr( empty( $invoice->amount ) ? '' : number_format_i18n( $invoice->amount, 2 ) ); ?>"/>
				</td>
				<td>
					<input type="text" size="5" name="_orbis_project_invoice_seconds_available_edit_<?php echo esc_html( $invoice->id ); ?>" value="<?php echo esc_html( orbis_time( $invoice->seconds ) ); ?>"/>
				</td>
				<td>
					<input type="text" name="_orbis_project_invoice_number_edit_<?php echo esc_html( $invoice->id ); ?>" value="<?php echo esc_html( $invoice->invoice_number ); ?>"/>
				</td>
				<td>
					<input type="radio" name="_is_final_invoice" value="<?php echo esc_html( $invoice->id ); ?>" <?php checked( $orbis_project->is_final_invoice( $invoice->invoice_number ) ); ?>>
				</td>
				<td>
					<span><?php echo esc_html( $invoice->display_name ); ?></span>
				</td>
				<td>
					<span><?php submit_button( __( 'Delete Invoice', 'orbis-projects' ), 'delete', $invoice->id, false ); ?></span>
				</td>
				<?php array_push( $invoice_list, $invoice->id ); ?>
			</tr>

		<?php endforeach; ?>

			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>
					<input type="radio" id="is_final_invoice_edit" name="_is_final_invoice" value="null" <?php checked( $orbis_project->is_final_invoice( null ) ); ?>>
					<strong><label for="is_final_invoice_edit" ><?php esc_html_e( 'No final invoice', 'orbis-projects' ); ?></label></strong>
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>
					<strong><?php esc_html_e( 'Add a new invoice:', 'orbis-projects' ); ?></strong>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>

		<input type="hidden" name="_orbis_project_invoice_list" value="
		<?php
		foreach ( $invoice_list as $invoice_id ) :
			echo esc_attr( $invoice_id ) . ',';
		endforeach;
		?>
		">

<?php else : ?>
	<table>
<?php endif; ?>

		<tfoot>
			<tr valign="top">
				<td>
					<input id="orbis_project_invoice_date" name="_orbis_project_invoice_date" type="date" value="<?php echo esc_html( date( 'Y-m-d' ) ); ?>" />
				</td>
				<td>
					<input id="orbis_project_invoice_amount" size="10" name="_orbis_project_invoice_amount" type="text" />
				</td>
				<td>
					<input id="_orbis_project_invoice_seconds_available" name="_orbis_project_invoice_seconds_available" size="5" type="text" />
				</td>
				<td>
					<input id="orbis_project_invoice_number" name="_orbis_project_invoice_number" type="text"/>
				</td>
				<td>
					<input type="radio" name="_is_final_invoice" value="new_invoice">
				</td>
				<td></td>
				<td>
					<input type="hidden" name="_project_id" value="<?php echo esc_html( $project->id ); ?>">
					<?php
					submit_button( __( 'Add Invoice', 'orbis-projects' ), 'secondary', 'orbis_projects_invoice_add', false );
					?>
				</td>
			</tr>
		</tfoot>
	</table>



<script type="text/javascript">
	( function( $ ) {
		$( document ).ready( function() {
			$( '.delete' ).on( 'click', function( e ) {
				if ( ! confirm( '<?php esc_html_e( 'Are you sure you want to delete this invoice?', 'orbis-projects' ); ?>' )) {
					if ( e.preventDefault ) {
						e.preventDefault();
					}

				return false;
				}
			} );
		} );
	} )( jQuery );
</script>
