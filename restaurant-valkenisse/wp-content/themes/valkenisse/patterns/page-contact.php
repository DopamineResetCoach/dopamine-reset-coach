<?php
/**
 * Title: Pagina: Contact
 * Slug: valkenisse/page-contact
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Contactgegevens, openingstijden, route en contactformulier.
 *
 * @package Valkenisse
 */

echo valkenisse_hero( 'Contact', 'Kom langs of neem contact op', 'Bel ons voor een reservering, vraag of route – wij helpen u graag.', 'omgeving-bos.svg', 56, '', 'is-style-hero hero--compact' );
echo "\n\n";
echo valkenisse_section(
	'vk-contact',
	valkenisse_columns(
		array(
			array( '40%', valkenisse_h( 'Adres &amp; contact', 2, 'x-large' ) . "\n\n" . valkenisse_block( 'contact' ) ),
			array( '', valkenisse_group( valkenisse_h( 'Openingstijden', 2, 'x-large' ) . "\n\n" . valkenisse_block( 'openingstijden', array( 'weergave' => 'overzicht' ) ), 'vk-hours-anchor' ) ),
		),
		'vk-contact-cols'
	),
	'',
	'openingstijden'
);
echo "\n\n";
$route = valkenisse_p( 'Route', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Zo vindt u ons' ) . "\n\n" .
	valkenisse_p( 'Restaurant Valkenisse ligt aan de Valkenisseweg in Biggekerke, aan de duinen en vlak bij het strand. Met één klik opent u de route in Google Maps.' ) . "\n\n" .
	valkenisse_p( 'Komt u logeren in een van onze studio’s? Dan parkeert u gratis naast het restaurant.' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Route plannen', '#route', '' ), array( 'Bel direct', '#bellen', 'outline' ) ) );
echo valkenisse_section( 'vk-route', valkenisse_columns( array( array( '', $route ), array( '55%', valkenisse_image( 'omgeving-duinen.svg', 'De omgeving van Restaurant Valkenisse', '4/3', 'is-style-reveal' ) ) ), 'vk-split', 'center' ), 'sand-light' );
echo "\n\n";
$form = valkenisse_group(
	valkenisse_p( 'Schrijf ons', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Stuur een bericht' ) . "\n\n" .
	valkenisse_p( 'Voor een reservering kunt u het beste bellen of het <a href="/reserveren/">reserveringsformulier</a> gebruiken.' ),
	'vk-form-intro'
);
echo valkenisse_section( 'vk-request', valkenisse_columns( array( array( '36%', $form ), array( '', valkenisse_block( 'formulier', array( 'soort' => 'contact' ) ) ) ), 'vk-form-cols' ) );
