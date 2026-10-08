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
						<div class="flex-shrink-0">
							<a href="<?php \the_permalink(); ?>">
								<?php

								if ( \has_post_thumbnail() ) {
									\the_post_thumbnail( 'avatar' );
								} else {
									echo \get_avatar( (string) \get_post_meta( (int) \get_the_ID(), '_orbis_email', true ), 60 );
								}

								?>
							</a>
						</div>

						<div class="flex-grow-1 ms-3">
							<a href="<?php \the_permalink(); ?>"><?php \the_title(); ?></a><br />

							<?php

							$email = (string) \get_post_meta( (int) \get_the_ID(), '_orbis_email', true );

							if ( '' !== $email ) {
								\printf(
									'<a class="text-secondary" style="font-size: .8em" href="%s">%s</a><br />',
									\esc_url( 'mailto:' . $email ),
									\esc_html( $email )
								);
							}

							$phone_numbers = \array_filter(
								[
									(string) \get_post_meta( (int) \get_the_ID(), '_orbis_phone_number', true ),
									(string) \get_post_meta( (int) \get_the_ID(), '_orbis_mobile_number', true ),
								]
							);

							$phone_number = \reset( $phone_numbers );

							if ( false !== $phone_number ) {
								\printf(
									'<a class="text-secondary" style="font-size: .8em" href="%s">%s</a><br />',
									\esc_url( 'tel:' . $phone_number ),
									\esc_html( $phone_number )
								);
							}

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
