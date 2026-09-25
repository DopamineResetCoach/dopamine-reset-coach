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

<!-- wp:pattern {"slug":"valkenisse/section-rental-grid"} /-->

<!-- wp:group {"className":"vk-reveal","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group vk-reveal"><!-- wp:heading {"className":"vk-h2-small"} -->
<h2 class="wp-block-heading vk-h2-small"><?php esc_html_e( 'Prijzen', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:valkenisse/rental-prices /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"valkenisse/section-cta"} /-->
