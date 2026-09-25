<?php
/**
 * Title: Pagina – Strandverhuur
 * Slug: valkenisse/page-strandverhuur
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1400
 * Inserter: false
 */
echo valkenisse_page_hero( 'strandstoelen', __( 'Strandstoelen en parasols op het strand van Valkenisse', 'valkenisse' ), __( 'Strandverhuur', 'valkenisse' ), __( 'Alles voor een heerlijke stranddag', 'valkenisse' ), __( 'Strandstoelen, ligbedden, parasols en windschermen, direct bij het paviljoen.', 'valkenisse' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section"><!-- wp:group {"className":"vk-reveal","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group vk-reveal"><!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Naast strandhuisjes verhuurt Strandpaviljoen Valkenisse strandstoelen, ligbedden, parasols en windschermen. Zo heb je alles voor een ontspannen dag aan zee.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"valkenisse/section-rental-grid"} /--></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"huurprijzen","align":"full","className":"vk-section vk-prices-section","backgroundColor":"zand-licht","layout":{"type":"constrained"}} -->
<section id="huurprijzen" class="wp-block-group alignfull vk-section vk-prices-section has-zand-licht-background-color has-background"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Tarieven', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Huurprijzen', 'valkenisse' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"align":"wide","className":"vk-prices-cols"} -->
<div class="wp-block-columns alignwide vk-prices-cols"><!-- wp:column {"className":"vk-reveal"} -->
<div class="wp-block-column vk-reveal"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Strandhuisje', 'valkenisse' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-prices-intro"} -->
<p class="vk-prices-intro"><?php esc_html_e( 'Inclusief 2 stoelen, 1 windscherm/luifel en een tafeltje.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:valkenisse/hut-prices {"hideBooking":true} /--></div>
<!-- /wp:column -->

<!-- wp:column {"className":"vk-reveal"} -->
<div class="wp-block-column vk-reveal"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Strandstoelen, parasols, ligbedden & windschermen', 'valkenisse' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-prices-intro"} -->
<p class="vk-prices-intro"><?php esc_html_e( 'Los te huur, per dag.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:valkenisse/rental-prices /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"className":"vk-reveal","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group vk-reveal"><!-- wp:valkenisse/hut-booking /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

