<?php
/**
 * Search form advanced
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$customer       = (string) \get_query_var( 'orbis_project_customer' );
$invoice_number = (string) \get_query_var( 'orbis_project_invoice_number' );

?>
<div id="advanced-search" class="<?php echo \esc_attr( '' !== $customer || '' !== $invoice_number ? 'show' : 'collapse' ); ?>">
	<fieldset>
		<legend><?php \esc_html_e( 'Advanced Search', 'orbis-projects' ); ?></legend>

		<div class="form-group">
			<label for="orbis_project_customer"><?php \esc_html_e( 'Customer', 'orbis-projects' ); ?></label>
			<input id="orbis_project_customer" class="form-control" name="orbis_project_customer" value="<?php echo \esc_attr( $customer ); ?>" type="text" placeholder="<?php \esc_attr_e( 'Search on Customer', 'orbis-projects' ); ?>">
		</div>

		<div class="form-group">
			<label for="orbis_project_invoice_number"><?php \esc_html_e( 'Invoice Number', 'orbis-projects' ); ?></label>
			<input id="orbis_project_invoice_number" class="form-control" name="orbis_project_invoice_number" value="<?php echo \esc_attr( $invoice_number ); ?>" type="text" placeholder="<?php \esc_attr_e( 'Search on Invoice Number', 'orbis-projects' ); ?>">
		</div>

		<div class="form-footer">
			<button type="submit" class="btn btn-primary"><?php \esc_html_e( 'Search', 'orbis-projects' ); ?></button>
			<button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#advanced-search"><?php \esc_html_e( 'Cancel', 'orbis-projects' ); ?></button>
		</div>
	</fieldset>
</div>
