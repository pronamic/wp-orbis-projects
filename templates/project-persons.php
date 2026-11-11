<?php
/**
 * Project persons
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! \function_exists( 'p2p_connection_exists' ) || ! \p2p_connection_exists( 'orbis_projects_to_persons' ) ) {
	return;
}

$query = new WP_Query(
	[
		'connected_type'  => 'orbis_projects_to_persons',
		'connected_items' => \get_queried_object(),
		'nopaging'        => true, // phpcs:ignore WordPressVIPMinimum.Performance.NoPaging.nopaging_nopaging
	]
);

?>
<div class="card mb-3">
	<div class="card-header"><?php \esc_html_e( 'Involved Persons', 'orbis-projects' ); ?></div>

	<?php if ( $query->have_posts() ) : ?>

		<ul class="list-group list-group-flush">
			<?php while ( $query->have_posts() ) : ?>

				<?php $query->the_post(); ?>

				<li class="list-group-item">
					<div class="d-flex">
						<?php if ( \has_post_thumbnail() ) : ?>

							<div class="flex-shrink-0 me-3">
								<a href="<?php \the_permalink(); ?>">
									<?php \the_post_thumbnail( 'avatar' ); ?>
								</a>
							</div>

						<?php endif; ?>

						<div class="flex-grow-1">
							<a href="<?php \the_permalink(); ?>"><?php \the_title(); ?></a><br />

							<?php

							// The Orbis Contacts blocks take the contact from the current post in the loop.
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Block output is escaped by the block render callback.
							echo \render_block(
								[
									'blockName' => 'orbis-contacts/email-addresses',
									'attrs'     => [
										'className' => 'small',
									],
								]
							);

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Block output is escaped by the block render callback.
							echo \render_block(
								[
									'blockName' => 'orbis-contacts/phone-numbers',
									'attrs'     => [
										'className' => 'small',
									],
								]
							);

							?>
						</div>
					</div>
				</li>

			<?php endwhile; ?>
		</ul>

		<?php \wp_reset_postdata(); ?>

	<?php else : ?>

		<div class="card-body">
			<p class="text-muted m-0">
				<?php \esc_html_e( 'No persons involved.', 'orbis-projects' ); ?>
			</p>
		</div>

	<?php endif; ?>
</div>
