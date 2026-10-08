<?php
/**
 * Single project
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use DateTimeImmutable;
use Pronamic\WordPress\Money\Money;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $orbis_project;

\get_header();

while ( \have_posts() ) :
	\the_post();

	$project_post_id = (int) \get_the_ID();

	?>
	<div id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
		<div class="row">
			<div class="col-md-8">
				<?php \do_action( 'orbis_before_main_content' ); ?>

				<?php if ( '' !== \trim( \get_the_content() ) ) : ?>

					<div class="card mb-3">
						<div class="card-header"><?php \esc_html_e( 'Description', 'orbis-projects' ); ?></div>

						<div class="card-body">
							<?php \the_content(); ?>
						</div>

						<?php \get_template_part( 'templates/post-card-footer' ); ?>
					</div>

				<?php endif; ?>

				<?php include __DIR__ . '/project-sections.php'; ?>

				<?php \do_action( 'orbis_after_main_content' ); ?>

				<?php \comments_template( '', true ); ?>
			</div>

			<div class="col-md-4">
				<?php \do_action( 'orbis_before_side_content' ); ?>

				<div class="card mb-3">
					<div class="card-header"><?php \esc_html_e( 'Project Status', 'orbis-projects' ); ?></div>

					<div class="card-body">
						<h5 class="entry-meta"><?php \esc_html_e( 'Project budget', 'orbis-projects' ); ?></h5>

						<?php

						$price = $orbis_project->get_price();

						if ( ! empty( $price ) && \current_user_can( 'read_orbis_project_price', $project_post_id ) ) :
							$money = new Money( $price, 'EUR' );

							?>

							<p class="project-time">
								<?php echo \esc_html( $money->format_i18n() ); ?>
							</p>

						<?php endif; ?>

						<p class="project-time">
							<?php echo \esc_html( $orbis_project->get_available_time()?->format() ?? '' ); ?>

							<?php

							if ( \function_exists( 'orbis_project_the_logged_time' ) ) :
								$classes   = [];
								$classes[] = \orbis_project_in_time() ? 'text-success' : 'text-error';

								?>

								<span class="<?php echo \esc_attr( \implode( ' ', $classes ) ); ?>"><?php \orbis_project_the_logged_time(); ?></span>

							<?php endif; ?>
						</p>
					</div>
				</div>

				<div class="card mb-3">
					<div class="card-header"><?php \esc_html_e( 'Project Details', 'orbis-projects' ); ?></div>

					<div class="card-body">
						<dl>
							<?php if ( $orbis_project->has_customer() ) : ?>

								<dt><?php \esc_html_e( 'Customer', 'orbis-projects' ); ?></dt>
								<dd>
									<?php

									\printf(
										'<a href="%s">%s</a>',
										\esc_url( (string) \get_permalink( $orbis_project->get_customer_post_id() ) ),
										\esc_html( $orbis_project->get_customer_name() )
									);

									?>
								</dd>

							<?php endif; ?>

							<dt><?php \esc_html_e( 'Posted on', 'orbis-projects' ); ?></dt>
							<dd><?php echo \esc_html( (string) \get_the_date() ); ?></dd>

							<dt><?php \esc_html_e( 'Posted by', 'orbis-projects' ); ?></dt>
							<dd><?php echo \esc_html( \get_the_author() ); ?></dd>

							<?php

							$agreement_id = (int) \get_post_meta( $project_post_id, '_orbis_project_agreement_id', true );

							if ( $agreement_id > 0 && \current_user_can( 'read_orbis_project_agreement', $project_post_id ) ) :
								?>

								<dt><?php \esc_html_e( 'Agreement', 'orbis-projects' ); ?></dt>
								<dd>
									<a href="<?php echo \esc_url( (string) \get_permalink( $agreement_id ) ); ?>"><?php echo \esc_html( \get_the_title( $agreement_id ) ); ?></a>
								</dd>

							<?php endif; ?>

							<?php

							$start_date = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) \get_post_meta( $project_post_id, '_orbis_project_start_date', true ) );
							$end_date   = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) \get_post_meta( $project_post_id, '_orbis_project_end_date', true ) );

							if ( false !== $start_date && false !== $end_date ) :
								?>

								<dt><?php \esc_html_e( 'Period', 'orbis-projects' ); ?></dt>
								<dd>
									<?php

									\printf(
										/* translators: 1: Period start date, 2: Period end date. */
										\esc_html__( '%1$s - %2$s', 'orbis-projects' ),
										\esc_html( \date_i18n( 'D j M Y', $start_date->getTimestamp() ) ),
										\esc_html( \date_i18n( 'D j M Y', $end_date->getTimestamp() ) )
									);

									?>
								</dd>

							<?php endif; ?>

							<dt><?php \esc_html_e( 'Status', 'orbis-projects' ); ?></dt>
							<dd>
								<?php if ( $orbis_project->is_finished() ) : ?>

									<span class="badge text-bg-success"><?php \esc_html_e( 'Finished', 'orbis-projects' ); ?></span>

								<?php else : ?>

									<span class="badge text-bg-secondary"><?php \esc_html_e( 'Not finished', 'orbis-projects' ); ?></span>

								<?php endif; ?>

								<?php if ( $orbis_project->is_invoiced() ) : ?>

									<span class="badge text-bg-success"><?php \esc_html_e( 'Invoiced', 'orbis-projects' ); ?></span>

								<?php else : ?>

									<span class="badge text-bg-secondary"><?php \esc_html_e( 'Not invoiced', 'orbis-projects' ); ?></span>

								<?php endif; ?>

								<?php if ( $orbis_project->is_invoicable() ) : ?>

									<span class="badge text-bg-success"><?php \esc_html_e( 'Invoicable', 'orbis-projects' ); ?></span>

								<?php else : ?>

									<span class="badge text-bg-secondary"><?php \esc_html_e( 'Not invoicable', 'orbis-projects' ); ?></span>

								<?php endif; ?>

								<?php

								$project_statuses = \wp_get_post_terms( $project_post_id, 'orbis_project_status' );

								if ( \is_array( $project_statuses ) ) {
									foreach ( $project_statuses as $project_status ) {
										$status_type = (string) \get_term_meta( $project_status->term_id, 'orbis_status_type', true );

										\printf(
											'<span class="badge text-bg-%s orbis-status">%s</span>',
											\esc_attr( '' === $status_type ? 'primary' : $status_type ),
											\esc_html( $project_status->name )
										);
									}
								}

								?>
							</dd>

							<?php if ( \has_term( '', 'orbis_payment_method' ) ) : ?>

								<dt><?php \esc_html_e( 'Payment Method', 'orbis-projects' ); ?></dt>
								<dd><?php \the_terms( $project_post_id, 'orbis_payment_method' ); ?></dd>

							<?php endif; ?>

							<?php

							$invoice_texts = [
								'_orbis_invoice_header_text'      => \__( 'Invoice Header Text', 'orbis-projects' ),
								'_orbis_invoice_footer_text'      => \__( 'Invoice Footer Text', 'orbis-projects' ),
								'_orbis_invoice_line_description' => \__( 'Invoice Line Description', 'orbis-projects' ),
							];

							foreach ( $invoice_texts as $meta_key => $label ) :
								$invoice_text = (string) \get_post_meta( $project_post_id, $meta_key, true );

								if ( '' === $invoice_text ) {
									continue;
								}

								?>

								<dt><?php echo \esc_html( $label ); ?></dt>
								<dd><?php echo \nl2br( \esc_html( $invoice_text ) ); ?></dd>

							<?php endforeach; ?>

							<?php

							$invoice_number = (string) \get_post_meta( $project_post_id, '_orbis_project_invoice_number', true );

							if ( '' !== $invoice_number ) :
								$invoice_link = \function_exists( 'orbis_get_invoice_link' ) ? \orbis_get_invoice_link( $invoice_number ) : '';

								?>

								<dt><?php \esc_html_e( 'Final Invoice', 'orbis-projects' ); ?></dt>
								<dd>
									<?php

									if ( ! empty( $invoice_link ) ) {
										\printf(
											'<a href="%s" target="_blank">%s</a>',
											\esc_url( $invoice_link ),
											\esc_html( $invoice_number )
										);
									} else {
										echo \esc_html( $invoice_number );
									}

									?>
								</dd>

							<?php endif; ?>

							<?php if ( null !== \get_edit_post_link() ) : ?>

								<dt><?php \esc_html_e( 'Actions', 'orbis-projects' ); ?></dt>
								<dd><?php \edit_post_link( \__( 'Edit', 'orbis-projects' ) ); ?></dd>

							<?php endif; ?>
						</dl>
					</div>
				</div>

				<?php include __DIR__ . '/project-persons.php'; ?>

				<?php \do_action( 'orbis_after_side_content' ); ?>
			</div>
		</div>
	</div>
	<?php

endwhile;

\get_footer();
