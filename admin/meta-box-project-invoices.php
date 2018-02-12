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
		</thead>

		<?php foreach ( $project_invoices as $invoice ) : ?>

			<tr valign="top">
				<td>
					<span><?php echo esc_html( date_format( new DateTime( $invoice->create_date ), 'd-m-Y' ) ) ?></span>
				</td>
				<td>
					<span><?php echo esc_html( orbis_price( $invoice->amount ) ) ?></span>
				</td>
				<td>
					<span><?php echo esc_html( orbis_time( $invoice->hours ) ) ?></span>
				</td>
				<td>
					<span><?php echo esc_html( $invoice->invoice_number ) ?></span>
				</td>
				<td>
					<span><?php echo esc_html( $invoice->display_name ) ?></span>
				</td>
			</tr>

		<?php endforeach; ?>
	</table>

<?php endif; ?>

<p>
	<strong>Add new invoice:</strong>
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
			<label for="_orbis_project_seconds_available"><?php esc_html_e( 'Invoice Time', 'orbis-projects' ); ?></label>
		</th>
		<td>
			<input size="5" id="_orbis_project_invoice_seconds_available" name="_orbis_project_invoice_seconds_available" value="<?php echo esc_attr( orbis_time( $seconds ) ); ?>" type="text" />

			<p class="description">
				<?php esc_html_e( 'You can enter time as 1.5 or 1:30 (they both mean 1 hour and 30 minutes).', 'orbis-projects' ); ?>
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

	<tr>
		<td>
			<input type="hidden" name="_project_id" value="<?php echo esc_html( $project->id ) ?>">
			<?php
			submit_button( __( 'Add Invoice', 'orbis-projects' ), 'secondary', 'orbis_projects_invoice_add', false );
			?>
		</td>
	</tr>
</table>
