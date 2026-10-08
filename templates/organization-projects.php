<?php
/**
 * Organization projects
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use Pronamic\WordPress\Money\Money;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $orbis_project;

$can_read_price = \current_user_can( 'read_orbis_project_price' );

$query = new WP_Query(
	[
		'post_type'               => 'orbis_project',
		'posts_per_page'          => 25,
		'orbis_project_client_id' => \get_the_ID(),
	]
);

if ( $query->have_posts() ) : ?>

	<div class="table-responsive">
		<table class="table table-striped mb-0">
			<thead>
				<tr>
					<th class="border-top-0"><?php \esc_html_e( 'Project', 'orbis-projects' ); ?></th>

					<?php if ( $can_read_price ) : ?>

						<th class="border-top-0"><?php \esc_html_e( 'Price', 'orbis-projects' ); ?></th>

					<?php endif; ?>

					<th class="border-top-0"><?php \esc_html_e( 'Time', 'orbis-projects' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php

				while ( $query->have_posts() ) :
					$query->the_post();

					?>

					<tr id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
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

							<?php endif; ?>
						</td>
					</tr>

				<?php endwhile; ?>
			</tbody>
		</table>
	</div>

	<?php \wp_reset_postdata(); ?>

<?php else : ?>

	<div class="card-body">
		<p class="text-muted m-0">
			<?php \esc_html_e( 'No projects found.', 'orbis-projects' ); ?>
		</p>
	</div>

<?php endif; ?>
