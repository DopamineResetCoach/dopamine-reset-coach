<?php
/**
 * Voorbereiding op een Duitse (of Engelse) versie.
 *
 * - Alle vaste teksten in plugin en thema zijn vertaalbaar (text domain "valkenisse").
 * - Vrije teksten uit het beheer (meldingen, toelichtingen) worden bij Polylang aangemeld als "Strings",
 *   zodat ze per taal vertaald kunnen worden onder Talen → Vertalingen.
 * - Pagina's koppel je per taal in Polylang; links in blokken zoeken automatisch de vertaalde pagina op (vk_page_url()).
 * - Feitelijke gegevens (telefoon, adres, prijzen) blijven één keer ingevoerd en gelden voor alle talen.
 *
 * Er wordt niets automatisch vertaald.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Welke instellingen zijn vrije tekst die per taal kan verschillen?
 */
function vk_translatable_settings(): array {
	return array(
		'today_custom',
		'notice_extra',
		'hours_variable_label',
		'hours_season',
		'hours_note',
		'phone_note',
		'location_desc',
		'parking',
		'huts_period',
		'huts_booking',
		'huts_full_text',
		'rental_note',
	);
}

/**
 * Geef de vertaalde versie van een vrije tekst terug (als Polylang actief is).
 */
function vk_tr( string $text, string $key = '' ): string {
	if ( '' === $text || ! function_exists( 'pll__' ) ) {
		return $text;
	}
	return (string) pll__( $text );
}

add_action( 'init', static function () {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$fields = vk_settings_fields();
	foreach ( vk_translatable_settings() as $key ) {
		$value = (string) vk_get( $key );
		if ( '' !== $value ) {
			pll_register_string( $key, $value, 'Strandpaviljoen Valkenisse', 'textarea' === ( $fields[ $key ]['type'] ?? '' ) );
		}
	}
} );
