<?php
/**
 * Title: Pagina: Restaurant
 * Slug: valkenisse/page-restaurant
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Restaurantpagina met sfeer, aanbod en reserveren.
 *
 * @package Valkenisse
 */

echo valkenisse_hero(
	'Restaurant',
	'Aan de duinen, in het bos, vlak bij het strand',
	'Lunch, diner en alles daartussen – op een van de mooiste plekken van Walcheren.',
	'terras.svg',
	72,
	valkenisse_buttons( array( array( 'Reserveer een tafel', '#reserveren', '' ), array( 'Bekijk de menukaart', '/menukaart/', 'light' ) ), 'hero__buttons' )
);
echo "\n\n";
$text = valkenisse_p( 'Ons restaurant', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Gastvrij, ontspannen en met aandacht bereid' ) . "\n\n" .
	valkenisse_p( 'Restaurant Valkenisse ligt aan de duinen, in het bos en vlak bij het strand. Hier geniet u van de beste vis- en vleesspecialiteiten, van pizza en van gerechten uit ons Noord-Afrikaanse menu.', 'is-style-lead' ) . "\n\n" .
	valkenisse_p( 'Wij serveren ook gerechten die geschikt zijn voor vegetariërs en voor mensen met een glutenvrij dieet. Voor de kinderen is er een kindermenu, en wie zin heeft in iets zoets kiest uit pannenkoeken, poffertjes en wafels.' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Bekijk de menukaart', '/menukaart/', 'outline' ) ) );
$images = valkenisse_group(
	valkenisse_image( 'gerecht-diner.svg', 'Een diner bij Restaurant Valkenisse', '4/5', 'vk-intro__main is-style-reveal' ) . "\n\n" .
	valkenisse_image( 'terras.svg', 'Het terras', '1', 'vk-intro__accent' ),
	'vk-intro__images'
);
echo valkenisse_section( 'vk-intro', valkenisse_columns( array( array( '52%', $images ), array( '', $text ) ), 'vk-split', 'center' ) );
echo "\n\n";
include __DIR__ . '/eten-drinken.php';
echo "\n\n";
include __DIR__ . '/sfeer.php';
echo "\n\n";
include __DIR__ . '/bezoek.php';
