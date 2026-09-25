<?php
/**
 * Title: Eten & drinken (tegels)
 * Slug: valkenisse/eten-drinken
 * Categories: valkenisse, gallery
 * Description: Vier grote fototegels: lunch, diner, pizza en Noord-Afrikaans menu.
 *
 * @package Valkenisse
 */

$tiles = array(
	array( 'Lunch', 'lunch', 'gerecht-lunch.svg', 'Lunchen aan de rand van de duinen, binnen of op het terras.' ),
	array( 'Diner', 'diner', 'gerecht-diner.svg', 'Vis- en vleesspecialiteiten voor een ontspannen avond.' ),
	array( 'Pizza', 'pizza', 'gerecht-pizza.svg', 'Voor wie zin heeft in iets eenvoudigs en lekkers.' ),
	array( 'Noord-Afrikaans', 'noord-afrikaans', 'gerecht-noord-afrikaans.svg', 'Tajines met kip, vis of garnalen en diverse groenten.' ),
);
$cols = array();
foreach ( $tiles as [ $title, $anchor, $img, $text ] ) {
	$url    = '/menukaart/#' . $anchor;
	$cols[] = array(
		'',
		valkenisse_group(
			valkenisse_image( $img, $title . ' bij Restaurant Valkenisse', '3/4', '', $url ) . "\n\n" .
			valkenisse_h( '<a href="' . $url . '">' . $title . '</a>', 3, '', 'vk-tile__title' ) . "\n\n" .
			valkenisse_p( $text, 'vk-tile__text' ),
			'is-style-tile-link vk-tile'
		),
	);
}
$head = valkenisse_group(
	valkenisse_group( valkenisse_p( 'Eten &amp; drinken', 'is-style-eyebrow' ) . "\n\n" . valkenisse_h( 'Van zonnige lunch tot<br>lange avond aan tafel' ), 'vk-head__text' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Bekijk de volledige menukaart', '/menukaart/', 'outline' ) ), 'vk-head__cta' ),
	'vk-head',
	'wide',
	array( 'type' => 'flex', 'flexWrap' => 'wrap', 'justifyContent' => 'space-between', 'verticalAlignment' => 'bottom' )
);
echo valkenisse_section( 'vk-food', $head . "\n\n" . valkenisse_columns( $cols, 'vk-tiles' ), 'sand-light' );
