<?php
/**
 * Title: Pagina – Foto's
 * Slug: valkenisse/page-fotos
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1400
 * Inserter: false
 */
echo valkenisse_page_hero( 'zonsondergang', __( 'Zonsondergang boven zee bij Strandpaviljoen Valkenisse', 'valkenisse' ), __( 'Kijkje op het strand', 'valkenisse' ), __( "Foto's", 'valkenisse' ), __( 'Paviljoen, terras, strand, eten & drinken, strandhuisjes en een beetje historie.', 'valkenisse' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section"><!-- wp:valkenisse/gallery {"limit":120,"showFilters":true,"align":"wide"} /--></section>
<!-- /wp:group -->
