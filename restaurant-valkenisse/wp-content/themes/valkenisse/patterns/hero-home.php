<?php
/**
 * Title: Hero homepage
 * Slug: valkenisse/hero-home
 * Categories: valkenisse, featured
 * Description: Grote openingsfoto met titel, reserveerknop en openingstijden van vandaag.
 *
 * @package Valkenisse
 */

echo valkenisse_hero(
	'Genieten aan de Zeeuwse kust',
	'Restaurant Valkenisse',
	'Een sfeervolle plek om te eten, drinken en overnachten vlak bij duinen en zee.',
	'restaurant-valkenisse.webp',
	90,
	valkenisse_buttons(
		array(
			array( 'Reserveer een tafel', '#reserveren', 'light' ),
			array( 'Bekijk de menukaart', '/menukaart/', 'light' ),
			array( 'Overnachten', '/overnachten/', 'light' ),
		),
		'hero__buttons'
	) . "\n\n" . valkenisse_group( valkenisse_block( 'openingstijden' ), 'hero__meta' ),
	'is-style-hero hero--home',
	'Restaurant Valkenisse met terras tussen de bomen bij avondlicht'
);
