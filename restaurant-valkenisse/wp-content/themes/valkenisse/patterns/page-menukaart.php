<?php
/**
 * Title: Pagina: Menukaart
 * Slug: valkenisse/page-menukaart
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Menukaart uit het menu Menukaart (dynamisch).
 *
 * @package Valkenisse
 */

echo valkenisse_hero(
	'Eten &amp; drinken',
	'Menukaart',
	'Lunch, diner, pizza, Noord-Afrikaanse gerechten en meer. Vegetarisch of glutenvrij? Kijk naar de labels of vraag het ons.',
	'gerecht-diner.svg',
	56,
	'',
	'is-style-hero hero--compact'
);
echo "\n\n";
echo valkenisse_section(
	'vk-menu-page',
	valkenisse_group( valkenisse_block( 'menukaart' ), 'vk-menu-wrap', 'wide' ) . "\n\n" .
	valkenisse_group(
		valkenisse_p( 'Allergieën of dieetwensen? Meld het ons bij het bestellen, dan denken we graag met u mee.', 'vk-note' ) . "\n\n" .
		valkenisse_buttons( array( array( 'Reserveer een tafel', '#reserveren', '' ), array( 'Bel ons', '#bellen', 'outline' ) ) ),
		'vk-menu-foot',
		'wide'
	)
);
