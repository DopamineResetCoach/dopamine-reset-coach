<?php
/**
 * Title: Pagina: Cookiebeleid
 * Slug: valkenisse/page-cookies
 * Categories: valkenisse-paginas
 * Post Types: page
 * Description: Cookiebeleid afgestemd op deze website.
 *
 * @package Valkenisse
 */

$valkenisse_sections = array(
	array( null, 'Cookies zijn kleine bestanden die een website op uw apparaat opslaat. Op deze pagina leest u welke cookies deze website gebruikt.' ),
	array( 'Welke cookies gebruiken wij?', 'Deze website gebruikt geen cookies voor statistieken, advertenties of het volgen van bezoekers. Daarom vragen wij ook geen toestemming via een cookiemelding.' ),
	array( 'Functionele cookies', 'Alleen als u inlogt op het beheergedeelte van de website (medewerkers van het restaurant) plaatst WordPress functionele cookies die nodig zijn om ingelogd te blijven.' ),
	array( 'Externe diensten', 'Google Maps wordt niet op onze pagina’s ingeladen. Klikt u op “Route plannen”, dan opent u de website van Google; daar geldt het cookie- en privacybeleid van Google.' ),
	array( 'Wijzigingen', 'Voegen wij later bijvoorbeeld statistieken of een ingesloten kaart toe, dan passen wij dit cookiebeleid aan en vragen wij waar nodig eerst uw toestemming. Laatst bijgewerkt: [datum].' ),
);
foreach ( $valkenisse_sections as [ $valkenisse_title, $valkenisse_text ] ) {
	if ( $valkenisse_title ) {
		echo valkenisse_h( $valkenisse_title, 2, 'x-large' ) . "\n\n";
	}
	echo valkenisse_p( $valkenisse_text ) . "\n\n";
}
