<?php
/**
 * Title: Pagina – Eten & Drinken
 * Slug: valkenisse/page-eten-drinken
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1400
 * Inserter: false
 */
echo valkenisse_page_hero( 'eten-terras', __( 'Gerechten en drankjes op het terras van Strandpaviljoen Valkenisse met uitzicht op zee', 'valkenisse' ), __( 'Aan tafel', 'valkenisse' ), __( 'Eten & drinken aan zee', 'valkenisse' ), __( 'Een hapje en een drankje met uitzicht over de Westerschelde en de Noordzee.', 'valkenisse' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","layout":{"type":"constrained","contentSize":"760px"}} -->
<section class="wp-block-group alignfull vk-section"><!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Bij Strandpaviljoen Valkenisse schuif je aan in de gemoedelijke sfeer van een familiepaviljoen, met het strand direct voor de deur. Hieronder vind je onze actuele kaart.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"vk-h2-small"} -->
<h2 class="wp-block-heading vk-h2-small"><?php esc_html_e( 'Onze kaart', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:valkenisse/menu {"showNav":true} /--></section>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"valkenisse/section-view"} /-->

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","backgroundColor":"zand-licht","layout":{"type":"constrained","contentSize":"760px"}} -->
<section class="wp-block-group alignfull vk-section has-zand-licht-background-color has-background"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Vandaag op het strand', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:valkenisse/today {"showWeek":true} /-->

<!-- wp:valkenisse/contact-buttons {"buttons":"route,call"} /--></section>
<!-- /wp:group -->
