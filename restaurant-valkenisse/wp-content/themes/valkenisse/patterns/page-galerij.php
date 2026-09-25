<?php
/**
 * Title: Pagina: Galerij
 * Slug: valkenisse/page-galerij
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Fotogalerij met filters en lightbox.
 *
 * @package Valkenisse
 */

echo valkenisse_hero( 'Galerij', 'Een kijkje bij Valkenisse', 'Het restaurant, het terras, onze gerechten, de studio’s en de omgeving.', 'omgeving-duinen.svg', 56, '', 'is-style-hero hero--compact' );
echo "\n\n";
echo valkenisse_section( 'vk-gallery-page', valkenisse_group( valkenisse_block( 'galerij' ), '', 'wide' ) );
