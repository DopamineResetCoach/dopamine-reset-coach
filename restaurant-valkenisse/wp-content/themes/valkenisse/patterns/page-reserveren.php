<?php
/**
 * Title: Pagina: Reserveren
 * Slug: valkenisse/page-reserveren
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Reserveringsaanvraag met telefoonnummer.
 *
 * @package Valkenisse
 */

echo valkenisse_hero( 'Reserveren', 'Reserveer een tafel', 'Stuur een reserveringsaanvraag. Uw reservering is definitief zodra wij deze hebben bevestigd.', 'terras.svg', 52, '', 'is-style-hero hero--compact' );
echo "\n\n";
$aside = valkenisse_group(
	valkenisse_h( 'Liever direct contact?', 3, 'large' ) . "\n\n" .
	valkenisse_p( 'Voor een reservering op korte termijn of met een grote groep kunt u ons het beste bellen.' ) . "\n\n" .
	valkenisse_block( 'contact', array( 'weergave' => 'adres' ) ) . "\n\n" .
	valkenisse_h( 'Vandaag', 3, 'large' ) . "\n\n" .
	valkenisse_block( 'openingstijden' ),
	'vk-aside'
);
echo valkenisse_section( 'vk-request vk-request--page', valkenisse_columns( array( array( '', valkenisse_block( 'formulier', array( 'soort' => 'reservering' ) ) ), array( '34%', $aside ) ), 'vk-form-cols' ), '', 'form' );
