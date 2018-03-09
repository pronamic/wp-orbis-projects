<?php

$orbis_project = new Orbis_Project( get_post() );

wp_nonce_field( 'orbis_save_project_invoices', 'orbis_project_invoices_meta_box_nonce' );

// @see https://github.com/woocommerce/woocommerce/blob/3.3.3/assets/js/admin/settings-views-html-settings-tax.js#L207-L257

?>

<input type="hidden" name="_orbis_project_invoice_list" value="<?php echo esc_attr( implode( ',', wp_list_pluck( $project_invoices, 'id' ) ) ); ?>" />

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

	<tbody>

		<?php foreach ( $orbis_project->get_invoices() as $invoice ) : ?>

			<tr valign="top">
				<td>
					<input name="_orbis_project_invoice_date_edit_<?php echo esc_html( $invoice->id ); ?>" type="date" value="<?php echo esc_html( empty( $invoice->create_date ) ? '' : date_format( new DateTime( $invoice->create_date ), 'Y-m-d' ) ); ?>" />
				</td>
				<td>
					<input type="text" size="10" name="_orbis_project_invoice_amount_edit_<?php echo esc_html( $invoice->id ); ?>" value="<?php echo esc_attr( empty( $invoice->amount ) ? '' : number_format_i18n( $invoice->amount, 2 ) ); ?>" placeholder="0" />
				</td>
				<td>
					<input type="text" size="5" name="_orbis_project_invoice_seconds_available_edit_<?php echo esc_html( $invoice->id ); ?>" value="<?php echo esc_html( orbis_time( $invoice->seconds ) ); ?>" placeholder="00:00" />
				</td>
				<td>
					<input type="text" name="_orbis_project_invoice_number_edit_<?php echo esc_html( $invoice->id ); ?>" value="<?php echo esc_html( $invoice->invoice_number ); ?>" />
				</td>
				<td>
					<label>
						<input id="" type="radio" name="_is_final_invoice" value="<?php echo esc_html( $invoice->id ); ?>" <?php checked( $orbis_project->is_final_invoice( $invoice->invoice_number ) ); ?> />

						<?php esc_html_e( 'Final Invoice', 'orbis-projects' ); ?>
					</label>
				</td>
				<td>
					<span><?php echo esc_html( $invoice->display_name ); ?></span>
				</td>
				<td>
					<span><?php submit_button( __( 'Delete Invoice', 'orbis-projects' ), 'delete', $invoice->id, false ); ?></span>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>

	<tfoot>
		<tr>
			<td>
				<strong><?php esc_html_e( 'Register New Ivoice', 'orbis-projects' ); ?></strong>
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td>
				<label>
					<input type="radio" name="_is_final_invoice" value="null" <?php checked( $orbis_project->is_final_invoice( null ) ); ?> />

					<?php esc_html_e( 'No final invoice', 'orbis-projects' ); ?>
				</label>
			</td>
			<td></td>
			<td></td>
		</tr>

		<tr valign="top">
			<td>
				<input name="_orbis_project_invoice_date" type="date" value="<?php echo esc_html( date( 'Y-m-d' ) ); ?>" />
			</td>
			<td>
				<input id="orbis_project_invoice_amount" size="10" name="_orbis_project_invoice_amount" type="text" placeholder="0" />
			</td>
			<td>
				<input id="_orbis_project_invoice_seconds_available" name="_orbis_project_invoice_seconds_available" size="5" type="text" placeholder="00:00" />
			</td>
			<td>
				<input id="orbis_project_invoice_number" name="_orbis_project_invoice_number" type="text" />
			</td>
			<td>
				<label>
					<input type="radio" name="_is_final_invoice" value="new_invoice" />

					<?php esc_html_e( 'Final Invoice', 'orbis-projects' ); ?>
				</label>
			</td>
			<td>
				<?php echo esc_html( wp_get_current_user()->display_name ); ?>
			</td>
			<td>
				<input type="hidden" name="_project_id" value="<?php echo esc_html( $project->id ); ?>" />

				<?php submit_button( __( 'Add Invoice', 'orbis-projects' ), 'secondary', 'orbis_projects_invoice_add', false ); ?>
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
