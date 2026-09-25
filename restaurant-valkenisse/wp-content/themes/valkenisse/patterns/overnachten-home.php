<?php
/**
 * Title: Overnachten (homepage)
 * Slug: valkenisse/overnachten-home
 * Categories: valkenisse, featured
 * Description: Uitgelichte sectie over de studio's boven het restaurant.
 *
 * @package Valkenisse
 */

$text = valkenisse_p( 'Overnachten', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Blijf nog wat langer' ) . "\n\n" .
	valkenisse_p( 'Boven het restaurant verhuren wij twee sfeervolle studio’s voor twee personen, elk met een eigen ingang. Wakker worden vlak bij de duinen, overdag naar het strand en ’s avonds gewoon beneden aan tafel.', 'is-style-lead' ) . "\n\n" .
	valkenisse_list( array( '2 personen', 'Eigen ingang', 'Gratis wifi', 'Gratis parkeren' ), 'is-style-checklist' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Bekijk onze studio’s', '/overnachten/', '' ) ) );
$images = valkenisse_group(
	valkenisse_image( 'studio-interieur.svg', 'Interieur van een studio boven Restaurant Valkenisse', '4/3', 'vk-stay__main is-style-reveal' ) . "\n\n" .
	valkenisse_image( 'studio-detail.svg', 'Detail van een studio', '4/5', 'vk-stay__accent' ),
	'vk-stay__images'
);
echo valkenisse_section( 'vk-stay', valkenisse_columns( array( array( '40%', $text ), array( '', $images ) ), 'vk-split vk-split--reverse', 'center' ) );
