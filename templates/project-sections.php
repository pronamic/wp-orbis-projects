<?php
/**
 * Project sections
 *
 * Other Orbis plugins can add tabs to the project page with the
 * `orbis_project_sections` filter. The active tab is taken from the
 * `tabs` rewrite endpoint, for example `/projects/example/tabs/invoices/`.
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

$project_sections = \apply_filters( 'orbis_project_sections', [] );

if ( empty( $project_sections ) || ! \is_array( $project_sections ) ) {
	return;
}

$project_sections = \array_values( $project_sections );

$active_tab = (string) \get_query_var( 'tabs' );

$active_section = $project_sections[0];

foreach ( $project_sections as $project_section ) {
	if ( $project_section['slug'] === $active_tab ) {
		$active_section = $project_section;

		break;
	}
}

?>
<div class="card mb-3 with-cols clearfix">
	<div class="card-header">
		<ul class="nav nav-tabs card-header-tabs" id="project-tabs">
			<?php foreach ( $project_sections as $project_section ) : ?>

				<li class="nav-item">
					<?php

					\printf(
						'<a href="%s" class="%s">%s</a>',
						\esc_url( \get_permalink() . 'tabs/' . $project_section['slug'] ),
						\esc_attr( $project_section === $active_section ? 'nav-link active' : 'nav-link' ),
						\esc_html( $project_section['name'] )
					);

					?>
				</li>

			<?php endforeach; ?>
		</ul>
	</div>

	<div class="tab-content">
		<div id="<?php echo \esc_attr( $active_section['id'] ); ?>" class="tab-pane active">
			<?php

			if ( isset( $active_section['action'] ) ) {
				\do_action( $active_section['action'] );
			}

			if ( isset( $active_section['callback'] ) ) {
				\call_user_func( $active_section['callback'] );
			}

			if ( isset( $active_section['template_part'] ) ) {
				\get_template_part( $active_section['template_part'] );
			}

			?>
		</div>
	</div>
</div>
