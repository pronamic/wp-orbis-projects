<?php

global $post;

use Pronamic\WordPress\Money\Money;

$orbis_project = new Pronamic\Orbis\Projects\Project( $post );
$invoices      = $orbis_project->get_invoices();

if ( $invoices && $invoices[0]->id ) : ?>

	<div class="table-responsive">
		<table class="table table-striped mb-0">
			<thead>
				<tr>
					<th class="border-top-0"><?php esc_html_e( 'Date', 'orbis-projects' ); ?></th>
					<th class="border-top-0"><?php esc_html_e( 'Amount', 'orbis-projects' ); ?></th>
					<th class="border-top-0"><?php esc_html_e( 'Hours', 'orbis-projects' ); ?></th>
					<th class="border-top-0"><?php esc_html_e( 'Invoice', 'orbis-projects' ); ?></th>
					<th class="border-top-0"><?php esc_html_e( 'Start date', 'orbis-projects' ); ?></th>
					<th class="border-top-0"><?php esc_html_e( 'End date', 'orbis-projects' ); ?></th>
					<th class="border-top-0"><?php esc_html_e( 'User', 'orbis-projects' ); ?></th>
				</tr>
			</thead>
				<?php
					$amount_total = 0;
					$hours_total  = 0;
				?>
			<tbody>
				<?php foreach ( $invoices as $invoice ) : ?>
					<?php
						$amount_total += $invoice->amount;
						$hours_total  += $invoice->seconds;
					?>
					<tr id="post-<?php the_ID(); ?>">
						<td>
							<?php echo esc_html( date_format( new DateTime( $invoice->created_at ), 'd-m-Y' ) ); ?>
						</td>
						<td>
							<?php
							$amount = new Money( $invoice->amount, 'EUR' );
							echo esc_html( $amount->format_i18n() );
							?>
						</td>
						<td>
							<?php
							if ( $invoice->seconds ) {
								echo esc_html( orbis_time( $invoice->seconds ) );
							}
							?>
						</td>
						<td>
							<?php

							$invoice_url  = \apply_filters( 'orbis_invoice_url', '', $invoice->invoice_data );
							$invoice_text = \apply_filters( 'orbis_invoice_text', $invoice->invoice_number, $invoice->invoice_data );

							if ( '' !== $invoice_url ) {
								printf(
									'<a href="%s" target="_blank">%s</a>',
									esc_url( $invoice_url ),
									esc_html( $invoice_text )
								);
							} else {
								echo esc_html( $invoice_text );
							}

							if ( get_post_meta( $post->ID, '_orbis_project_invoice_number', true ) === $invoice->invoice_number ) {
								printf(
									' <span title="%s">✓</span>',
									esc_html__( 'This is the final invoice.', 'orbis-projects' )
								);
							}
							?>
						</td>
						<td>
							<?php 

							if ( null !== $invoice->start_date ) {
								$start_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $invoice->start_date, new \DateTimeZone( 'UTC' ) );

								if ( false !== $start_date ) {
									$start_date = $start_date->setTime( 0, 0 );

									echo \esc_html( \date_i18n( 'D j M Y', $start_date->getTimestamp() ) );
								}
							}

							?>
						</td>
						<td>
							<?php 

							if ( null !== $invoice->end_date ) {
								$end_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $invoice->end_date, new \DateTimeZone( 'UTC' ) );

								if ( false !== $end_date ) {
									$end_date = $end_date->setTime( 0, 0 );

									echo \esc_html( \date_i18n( 'D j M Y', $end_date->getTimestamp() ) );
								}
							}

							?>
						</td>
						<td>
							<?php echo esc_html( $invoice->display_name ); ?>
						</td>
					</tr>

				<?php endforeach; ?>
					<tr>
						<td>
							<strong><?php esc_html_e( 'Total:', 'orbis-projects' ); ?></strong>
						</td>
						<td>
							<strong>
								<?php
								$amount_total = new Money( $amount_total, 'EUR' );
								echo esc_html( $amount_total->format_i18n() );
								?>
							</strong>
						</td>
						<td>
							<strong><?php echo esc_html( orbis_time( $hours_total ) ); ?></strong>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
			</tbody>
		</table>
	</div>

	<?php wp_reset_postdata(); ?>

<?php else : ?>

	<div class="card-body">
		<p class="text-muted m-0">
			<?php esc_html_e( 'No invoices found.', 'orbis-projects' ); ?>
		</p>
	</div>

<?php endif; ?>
