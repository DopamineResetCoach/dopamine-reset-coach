<?php
/**
 * Title: Introductie (welkom)
 * Slug: valkenisse/intro
 * Categories: valkenisse, text
 * Description: Rustige introductie met asymmetrische foto's.
 *
 * @package Valkenisse
 */

$images = valkenisse_group(
	valkenisse_image( 'terras.svg', 'Het terras van Restaurant Valkenisse', '4/5', 'vk-intro__main is-style-reveal' ) . "\n\n" .
	valkenisse_image( 'gerecht-lunch.svg', 'Een gerecht van Restaurant Valkenisse', '1', 'vk-intro__accent' ),
	'vk-intro__images'
);
$text = valkenisse_p( 'Welkom bij Valkenisse', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Even weg. Even genieten.' ) . "\n\n" .
	valkenisse_p( 'Aan de duinen, in het bos en vlak bij het strand ligt Restaurant Valkenisse. Een plek om even niets te hoeven en alles te proeven: van een ontspannen lunch tot een uitgebreid diner met vis- en vleesspecialiteiten.', 'is-style-lead' ) . "\n\n" .
	valkenisse_p( 'Ook voor vegetarische en glutenvrije gerechten bent u bij ons aan het juiste adres.' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Ontdek ons restaurant', '/restaurant/', 'outline' ) ) );
echo valkenisse_section( 'vk-intro', valkenisse_columns( array( array( '52%', $images ), array( '', $text ) ), 'vk-split', 'center' ) );
