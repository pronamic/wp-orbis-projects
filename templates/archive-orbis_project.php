<?php
/**
 * Archive projects
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use Pronamic\WordPress\Money\Money;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $orbis_project;

$can_read_price = \current_user_can( 'read_orbis_project_price' );

\get_header();

?>
<div class="card">
	<?php \get_template_part( 'templates/search_form' ); ?>

	<?php if ( \have_posts() ) : ?>

		<div class="table-responsive">
			<table class="table table-striped table-condense table-hover">
				<thead>
					<tr>
						<th><?php \esc_html_e( 'Client', 'orbis-projects' ); ?></th>
						<th><?php \esc_html_e( 'Project', 'orbis-projects' ); ?></th>

						<?php if ( $can_read_price ) : ?>

							<th><?php \esc_html_e( 'Price', 'orbis-projects' ); ?></th>

						<?php endif; ?>

						<th><?php \esc_html_e( 'Time', 'orbis-projects' ); ?></th>
						<th><span class="visually-hidden"><?php \esc_html_e( 'Actions', 'orbis-projects' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php

					while ( \have_posts() ) :
						\the_post();

						?>

						<tr id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
							<td>
								<?php

								if ( $orbis_project->has_principal() ) {
									\printf(
										'<a href="%s">%s</a>',
										\esc_url( (string) \get_permalink( $orbis_project->get_principal_post_id() ) ),
										\esc_html( $orbis_project->get_principal_name() )
									);
								}

								?>
							</td>
							<td>
								<a href="<?php \the_permalink(); ?>"><?php \the_title(); ?></a>

								<?php \get_template_part( 'templates/table-cell-comments' ); ?>
							</td>

							<?php if ( $can_read_price ) : ?>

								<td>
									<?php

									$price = $orbis_project->get_price();

									if ( ! empty( $price ) ) {
										$money = new Money( $price, 'EUR' );

										echo \esc_html( $money->format_i18n() );
									}

									?>
								</td>

							<?php endif; ?>

							<td class="project-time">
								<?php

								echo \esc_html( $orbis_project->get_available_time()?->format() ?? '' );

								if ( \function_exists( 'orbis_project_the_logged_time' ) ) :
									$classes   = [];
									$classes[] = \orbis_project_in_time() ? 'text-success' : 'text-error';

									?>

									<span class="<?php echo \esc_attr( \implode( ' ', $classes ) ); ?>"><?php \orbis_project_the_logged_time(); ?></span>

									<?php
								endif;

								?>
							</td>
							<td>
								<?php \get_template_part( 'templates/table-cell-actions' ); ?>
							</td>
						</tr>

					<?php endwhile; ?>
				</tbody>
			</table>
		</div>

	<?php else : ?>

		<div class="card-body">
			<?php \get_template_part( 'templates/content-none' ); ?>
		</div>

	<?php endif; ?>
</div>

<?php

if ( \function_exists( 'orbis_content_nav' ) ) {
	\orbis_content_nav();
} else {
	\the_posts_pagination();
}

\get_footer();
