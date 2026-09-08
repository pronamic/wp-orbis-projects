<?php
/**
 * Project template schedule meta box.
 *
 * @package Pronamic\Orbis\Projects
 */

\wp_nonce_field( 'orbis_save_project_template_schedule', 'orbis_project_template_schedule_meta_box_nonce' );

$creation_date          = \get_post_meta( $post->ID, '_orbis_project_template_creation_date', true );
$creation_date_modifier = \get_post_meta( $post->ID, '_orbis_project_template_creation_date_modifier', true );
$start_date_modifier    = \get_post_meta( $post->ID, '_orbis_project_template_start_date_modifier', true );
$end_date_modifier      = \get_post_meta( $post->ID, '_orbis_project_template_end_date_modifier', true );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="orbis_project_template_creation_date"><?php \esc_html_e( 'Next Creation Date', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_creation_date" name="_orbis_project_template_creation_date" value="<?php echo \esc_attr( $creation_date ); ?>" type="date" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="orbis_project_template_creation_date_modifier"><?php \esc_html_e( 'Recurrence', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_creation_date_modifier" name="_orbis_project_template_creation_date_modifier" value="<?php echo \esc_attr( $creation_date_modifier ); ?>" class="regular-text" type="text" placeholder="+1 month" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="orbis_project_template_start_date_modifier"><?php \esc_html_e( 'Start Date Offset', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_start_date_modifier" name="_orbis_project_template_start_date_modifier" value="<?php echo \esc_attr( $start_date_modifier ); ?>" class="regular-text" type="text" placeholder="+0 days" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="orbis_project_template_end_date_modifier"><?php \esc_html_e( 'End Date Offset', 'orbis-projects' ); ?></label></th>
			<td><input id="orbis_project_template_end_date_modifier" name="_orbis_project_template_end_date_modifier" value="<?php echo \esc_attr( $end_date_modifier ); ?>" class="regular-text" type="text" placeholder="+1 month -1 day" /></td>
		</tr>
	</tbody>
</table>

<?php

$next_project = $this->project_scheduler->get_next_project_preview( $post->ID );
$date_format  = \get_option( 'date_format' );
$format_date  = static fn( $date ) => null === $date ? \__( 'N/A', 'orbis-projects' ) : \wp_date( $date_format, $date->getTimestamp() );

?>
<h3><?php \esc_html_e( 'Next Project', 'orbis-projects' ); ?></h3>

<table class="widefat striped" style="max-width: 480px;">
	<tbody>
		<tr>
			<th scope="row"><?php \esc_html_e( 'Creation Date', 'orbis-projects' ); ?></th>
			<td><?php echo \esc_html( null === $next_project ? \__( 'N/A', 'orbis-projects' ) : $format_date( $next_project['creation_date'] ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php \esc_html_e( 'Title', 'orbis-projects' ); ?></th>
			<td><?php echo \esc_html( null === $next_project ? \__( 'N/A', 'orbis-projects' ) : $next_project['title'] ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php \esc_html_e( 'Start Date', 'orbis-projects' ); ?></th>
			<td><?php echo \esc_html( null === $next_project ? \__( 'N/A', 'orbis-projects' ) : $format_date( $next_project['start_date'] ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php \esc_html_e( 'End Date', 'orbis-projects' ); ?></th>
			<td><?php echo \esc_html( null === $next_project ? \__( 'N/A', 'orbis-projects' ) : $format_date( $next_project['end_date'] ) ); ?></td>
		</tr>
	</tbody>
</table>

<?php

$merge_tags       = [
	'start_date_month',
	'start_date_year',
	'start_date_quarter',
	'start_date_week',
	'end_date_month',
	'end_date_year',
	'end_date_quarter',
	'end_date_week',
];
$merge_tag_values = $this->project_scheduler->get_merge_tag_examples( $post->ID );

?>
<h3><?php \esc_html_e( 'Merge Tags', 'orbis-projects' ); ?></h3>

<p>
	<?php \esc_html_e( 'The following merge tags can be used in the title and content of this project template. They will be replaced with the values shown below when a project is created from this template.', 'orbis-projects' ); ?>
</p>

<table class="widefat striped" style="max-width: 480px;">
	<thead>
		<tr>
			<th><?php \esc_html_e( 'Tag', 'orbis-projects' ); ?></th>
			<th><?php \esc_html_e( 'Example', 'orbis-projects' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $merge_tags as $merge_tag ) : ?>
			<tr>
				<td><code>{<?php echo \esc_html( $merge_tag ); ?>}</code></td>
				<td><?php echo \esc_html( $merge_tag_values[ '{' . $merge_tag . '}' ] ?? \__( 'N/A', 'orbis-projects' ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
