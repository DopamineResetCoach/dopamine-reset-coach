<?php
/**
 * Title: Openingstijden & contact
 * Slug: valkenisse/bezoek
 * Categories: valkenisse, footer
 * Description: Openingstijden van vandaag en dit seizoen, adres en knoppen.
 *
 * @package Valkenisse
 */

$cols = array(
	array( '36%', valkenisse_p( 'Kom langs', 'is-style-eyebrow' ) . "\n\n" . valkenisse_h( 'Tot snel aan de kust' ) . "\n\n" . valkenisse_p( 'Wij vragen u vooraf te reserveren. Bel ons gerust of stuur een reserveringsaanvraag.' ) . "\n\n" . valkenisse_buttons( array( array( 'Reserveer een tafel', '#reserveren', '' ), array( 'Bel ons', '#bellen', 'outline' ) ) ) ),
	array( '', valkenisse_h( 'Openingstijden', 3, 'large', 'vk-visit__label' ) . "\n\n" . valkenisse_block( 'openingstijden', array( 'weergave' => 'seizoen' ) ) ),
	array( '', valkenisse_h( 'Adres &amp; contact', 3, 'large', 'vk-visit__label' ) . "\n\n" . valkenisse_block( 'contact', array( 'weergave' => 'adres' ) ) ),
);
echo valkenisse_section( 'vk-visit', valkenisse_columns( $cols, 'vk-visit__cols' ), 'sand' );
