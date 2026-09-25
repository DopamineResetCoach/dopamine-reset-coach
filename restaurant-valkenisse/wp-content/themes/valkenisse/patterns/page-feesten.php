<?php
/**
 * Title: Pagina: Feesten & Partijen
 * Slug: valkenisse/page-feesten
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Feesten, buffetten, catering en aanvraagformulier.
 *
 * @package Valkenisse
 */

echo valkenisse_hero(
	'Feesten &amp; partijen',
	'Iets te vieren? Samen maken we er iets bijzonders van',
	'Verjaardagen, bruiloften en andere feesten, buffetten en catering aan de Zeeuwse kust.',
	'feesten-buffet.webp',
	72,
	valkenisse_buttons( array( array( 'Vraag vrijblijvend informatie aan', '#aanvraag', '' ), array( 'Bel ons', '#bellen', 'light' ) ), 'hero__buttons' ),
	'is-style-hero',
	'Buffet met tajine en paella op het terras van Restaurant Valkenisse'
);
echo "\n\n";

$occasions = valkenisse_group(
	valkenisse_p( 'De mogelijkheden', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Uw feest aan de duinen' ) . "\n\n" .
	valkenisse_p( 'Of het nu gaat om een verjaardag, een bruiloft of een ander feest: wij denken graag met u mee. [Controleren en aanvullen: ruimte voor circa 100 personen – overnemen zoals vermeld op de huidige website.]', 'is-style-lead' ),
	'vk-head'
);
$list = valkenisse_columns(
	array(
		array( '', valkenisse_h( 'Verjaardagen', 3, 'large' ) . "\n\n" . valkenisse_p( '[Korte omschrijving van de mogelijkheden.]' ) ),
		array( '', valkenisse_h( 'Bruiloften', 3, 'large' ) . "\n\n" . valkenisse_p( '[Korte omschrijving van de mogelijkheden.]' ) ),
		array( '', valkenisse_h( 'Feesten', 3, 'large' ) . "\n\n" . valkenisse_p( '[Korte omschrijving van de mogelijkheden.]' ) ),
		array( '', valkenisse_h( 'Catering', 3, 'large' ) . "\n\n" . valkenisse_p( '[Korte omschrijving van de cateringmogelijkheden.]' ) ),
	),
	'vk-occasions'
);
echo valkenisse_section( 'vk-party-intro', $occasions . "\n\n" . $list );
echo "\n\n";

echo valkenisse_section(
	'vk-buffets-section',
	valkenisse_group( valkenisse_block( 'buffetten', array( 'titel' => 'Buffetten', 'intro' => 'Kies het buffet dat bij uw gezelschap past. Prijzen zijn per persoon.' ) ), '', 'wide' ),
	'sand-light'
);
echo "\n\n";

echo valkenisse_group( valkenisse_image( 'feest.svg', 'Een feest bij Restaurant Valkenisse', '21/9', 'is-style-reveal' ), 'vk-wide-photo', 'full' );
echo "\n\n";

$form = valkenisse_group(
	valkenisse_p( 'Vrijblijvend', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Vertel ons over uw feest' ) . "\n\n" .
	valkenisse_p( 'Vul het formulier in en wij nemen zo snel mogelijk contact met u op om de mogelijkheden te bespreken.' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Bel ons', '#bellen', 'outline' ) ) ),
	'vk-form-intro'
);
echo valkenisse_section( 'vk-request', valkenisse_columns( array( array( '36%', $form ), array( '', valkenisse_block( 'formulier', array( 'soort' => 'feest' ) ) ) ), 'vk-form-cols' ), 'sand', 'aanvraag' );
