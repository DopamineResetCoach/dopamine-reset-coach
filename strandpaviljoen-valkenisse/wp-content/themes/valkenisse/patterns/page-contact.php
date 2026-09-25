<?php
/**
 * Title: Pagina – Contact
 * Slug: valkenisse/page-contact
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1400
 * Inserter: false
 */
echo valkenisse_page_hero( 'duinovergang-vossenhol', __( 'De houten trap over de duinovergang Vossenhol, met het bord van Strandpaviljoen Valkenisse', 'valkenisse' ), __( 'Contact & route', 'valkenisse' ), __( 'Tot straks op het strand', 'valkenisse' ), __( 'Bij de duinovergang Vossenhol in Groot Valkenisse, vlak bij Zoutelande.', 'valkenisse' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section vk-contact-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section vk-contact-section"><!-- wp:valkenisse/contact-buttons {"buttons":"call,route,mail","align":"wide","className":"vk-contact-buttons"} /-->

<!-- wp:valkenisse/contact-details {"variant":"full","align":"wide"} /-->

<!-- wp:valkenisse/map {"align":"wide"} /--></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"openingstijden","align":"full","className":"vk-section","backgroundColor":"zand-licht","layout":{"type":"constrained","contentSize":"720px"}} -->
<section id="openingstijden" class="wp-block-group alignfull vk-section has-zand-licht-background-color has-background"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Openingstijden', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:valkenisse/today {"showWeek":false} /-->

<!-- wp:valkenisse/opening-hours {"variant":"table"} /-->

<!-- wp:paragraph -->
<p><a href="/veelgestelde-vragen/"><?php esc_html_e( 'Bekijk de veelgestelde vragen', 'valkenisse' ); ?></a></p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->
