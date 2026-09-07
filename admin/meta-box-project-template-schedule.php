<?php
/**
 * Project template schedule meta box.
 *
 * @package Pronamic\Orbis\Projects
 */

wp_nonce_field( 'orbis_save_project_template_schedule', 'orbis_project_template_schedule_meta_box_nonce' );

$creation_date          = get_post_meta( $post->ID, '_orbis_project_template_creation_date', true );
$creation_date_modifier = get_post_meta( $post->ID, '_orbis_project_template_creation_date_modifier', true );
$start_date_modifier    = get_post_meta( $post->ID, '_orbis_project_template_start_date_modifier', true );
$end_date_modifier      = get_post_meta( $post->ID, '_orbis_project_template_end_date_modifier', true );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="orbis_project_template_creation_date"><?php esc_html_e( 'Next Creation Date', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_creation_date" name="_orbis_project_template_creation_date" value="<?php echo esc_attr( $creation_date ); ?>" type="date" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="orbis_project_template_creation_date_modifier"><?php esc_html_e( 'Recurrence', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_creation_date_modifier" name="_orbis_project_template_creation_date_modifier" value="<?php echo esc_attr( $creation_date_modifier ); ?>" class="regular-text" type="text" placeholder="+1 month" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="orbis_project_template_start_date_modifier"><?php esc_html_e( 'Start Date Offset', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_start_date_modifier" name="_orbis_project_template_start_date_modifier" value="<?php echo esc_attr( $start_date_modifier ); ?>" class="regular-text" type="text" placeholder="+0 days" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="orbis_project_template_end_date_modifier"><?php esc_html_e( 'End Date Offset', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_end_date_modifier" name="_orbis_project_template_end_date_modifier" value="<?php echo esc_attr( $end_date_modifier ); ?>" class="regular-text" type="text" placeholder="+1 month -1 day" /></td>
		</tr>
	</tbody>
</table>
