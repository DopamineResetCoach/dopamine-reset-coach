<?php
/**
 * Centrale gegevens en hulpfuncties.
 *
 * Alle bedrijfsgegevens staan in één optie ('vk_settings'). Iedere plek op de
 * website (header, footer, contactpagina, knoppen, formulier, schema.org)
 * leest via deze functies, zodat een wijziging overal tegelijk doorwerkt.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Standaardwaarden. Alleen gegevens die op de bestaande website stonden zijn
 * ingevuld; al het andere blijft leeg en toont VK_PLACEHOLDER.
 */
function vk_default_settings() {
	$dagen = array();
	foreach ( array_keys( vk_dagen() ) as $dag ) {
		$dagen[ $dag ] = array(
			'status' => '',
			'van'    => '',
			'tot'    => '',
		);
	}

	return array(
		// Bedrijf.
		'bedrijfsnaam'          => 'Van Keulen Caravanstalling',
		'straat'                => 'Dorpsstraat 57A',
		'postcode'              => '4373 AD',
		'plaats'                => 'Biggekerke',
		'telefoon'              => '0118-639869',
		'whatsapp'              => '',
		'email'                 => 'info@vankeulencaravanstalling.nl',
		'kvk'                   => '',
		'google_profiel'        => '',
		'lat'                   => '',
		'lng'                   => '',

		// Openingstijden / contactmomenten.
		'openingstijden'        => $dagen,
		'openingstijden_tekst'  => '',

		// Mededeling & beschikbaarheid.
		'mededeling_aan'        => 0,
		'mededeling_tekst'      => '',
		'mededeling_link'       => '',
		'beschikbaarheid'       => 'onbekend',
		'beschikbaarheid_tekst' => '',

		// Prijzen.
		'prijzen_aan'           => 0,
		'prijzen'               => '',
		'prijzen_toelichting'   => '',

		// Aanvraagformulier.
		'objecten'              => array( 'caravan', 'boot', 'vouwwagen', 'aanhanger', 'strandhuisje', 'slaaphuisje', 'anders' ),
		'periodes'              => array( 'jaar', 'winter', 'anders' ),
		'voorkeur_tonen'        => 1,
		'aanvraag_email'        => '',
		'bevestiging_klant'     => 1,
		'bewaartermijn'         => 12,
	);
}

/**
 * Alle instellingen, aangevuld met standaardwaarden.
 */
function vk_settings() {
	// get_option() wordt door WordPress zelf al gecachet.
	return wp_parse_args( (array) get_option( 'vk_settings', array() ), vk_default_settings() );
}

/**
 * Eén instelling ophalen.
 *
 * @param string $key Sleutel.
 * @return mixed
 */
function vk_get( $key ) {
	$s = vk_settings();
	return $s[ $key ] ?? '';
}

/**
 * Is een gegeven echt ingevuld (niet leeg en geen placeholder)?
 *
 * @param string $key Sleutel.
 */
function vk_filled( $key ) {
	$v = vk_get( $key );
	if ( is_array( $v ) ) {
		return ! empty( $v );
	}
	$v = trim( (string) $v );
	return '' !== $v && false === strpos( $v, VK_PLACEHOLDER );
}

/**
 * Tekst of de placeholder.
 *
 * @param string $key Sleutel.
 */
function vk_text( $key ) {
	return vk_filled( $key ) ? (string) vk_get( $key ) : VK_PLACEHOLDER;
}

/**
 * Placeholder als HTML (herkenbaar gemarkeerd).
 */
function vk_placeholder_html() {
	return '<span class="vk-aanleveren">' . esc_html( VK_PLACEHOLDER ) . '</span>';
}

/**
 * Mag de huidige bezoeker ontbrekende gegevens zien? (redacteuren wel)
 */
function vk_toon_ontbrekend() {
	return current_user_can( 'edit_pages' );
}

/**
 * Soorten objecten voor het aanvraagformulier.
 */
function vk_objecten() {
	return array(
		'caravan'      => 'Caravan',
		'camper'       => 'Camper',
		'boot'         => 'Boot',
		'vouwwagen'    => 'Vouwwagen',
		'aanhanger'    => 'Aanhanger',
		'strandhuisje' => 'Strandhuisje',
		'slaaphuisje'  => 'Slaaphuisje',
		'anders'       => 'Anders',
	);
}

/**
 * Objecten die in het formulier getoond worden (volgens instellingen).
 */
function vk_objecten_actief() {
	$aan = (array) vk_get( 'objecten' );
	return array_intersect_key( vk_objecten(), array_flip( $aan ) );
}

/**
 * Stallingsperiodes.
 */
function vk_periodes() {
	return array(
		'jaar'   => 'Het gehele jaar',
		'winter' => 'Winterstalling',
		'anders' => 'Andere periode',
	);
}

/**
 * Periodes die in het formulier getoond worden.
 */
function vk_periodes_actief() {
	$aan = (array) vk_get( 'periodes' );
	return array_intersect_key( vk_periodes(), array_flip( $aan ) );
}

/**
 * Weekdagen (sleutels = schema.org dagnamen).
 */
function vk_dagen() {
	return array(
		'Monday'    => 'Maandag',
		'Tuesday'   => 'Dinsdag',
		'Wednesday' => 'Woensdag',
		'Thursday'  => 'Donderdag',
		'Friday'    => 'Vrijdag',
		'Saturday'  => 'Zaterdag',
		'Sunday'    => 'Zondag',
	);
}

/**
 * Zijn er openingstijden/contactmomenten ingevuld?
 */
function vk_heeft_openingstijden() {
	foreach ( (array) vk_get( 'openingstijden' ) as $d ) {
		if ( ! empty( $d['status'] ) ) {
			return true;
		}
	}
	return vk_filled( 'openingstijden_tekst' );
}

/**
 * Adres op één regel.
 */
function vk_adres_regel() {
	return trim( vk_get( 'straat' ) . ', ' . vk_get( 'postcode' ) . ' ' . vk_get( 'plaats' ), ' ,' );
}

/**
 * Telefoonnummer als tel:-link (internationaal formaat).
 *
 * @param string $nummer Nummer; standaard het ingestelde telefoonnummer.
 */
function vk_tel_e164( $nummer = null ) {
	$nummer = null === $nummer ? (string) vk_get( 'telefoon' ) : (string) $nummer;
	$d      = preg_replace( '/[^0-9+]/', '', $nummer );
	if ( '' === $d ) {
		return '';
	}
	if ( str_starts_with( $d, '00' ) ) {
		$d = '+' . substr( $d, 2 );
	} elseif ( str_starts_with( $d, '0' ) ) {
		$d = '+31' . substr( $d, 1 );
	} elseif ( ! str_starts_with( $d, '+' ) ) {
		$d = '+' . $d;
	}
	return $d;
}

/**
 * tel:-link.
 */
function vk_tel_href() {
	$e = vk_filled( 'telefoon' ) ? vk_tel_e164() : '';
	return $e ? 'tel:' . $e : '';
}

/**
 * WhatsApp-link (alleen als er een zakelijk WhatsApp-nummer is ingevuld).
 *
 * @param string $tekst Optioneel voorgevuld bericht.
 */
function vk_whatsapp_href( $tekst = '' ) {
	if ( ! vk_filled( 'whatsapp' ) ) {
		return '';
	}
	$n = ltrim( vk_tel_e164( vk_get( 'whatsapp' ) ), '+' );
	if ( '' === $n ) {
		return '';
	}
	$url = 'https://wa.me/' . $n;
	if ( $tekst ) {
		$url .= '?text=' . rawurlencode( $tekst );
	}
	return $url;
}

/**
 * Google Maps routeplanner naar het adres.
 */
function vk_route_url() {
	$dest = ( vk_filled( 'lat' ) && vk_filled( 'lng' ) )
		? vk_get( 'lat' ) . ',' . vk_get( 'lng' )
		: vk_get( 'bedrijfsnaam' ) . ', ' . vk_adres_regel();
	return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $dest );
}

/**
 * Link naar de locatie in Google Maps (Google Bedrijfsprofiel indien bekend).
 */
function vk_maps_url() {
	if ( vk_filled( 'google_profiel' ) ) {
		return (string) vk_get( 'google_profiel' );
	}
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( vk_get( 'bedrijfsnaam' ) . ', ' . vk_adres_regel() );
}

/**
 * Embed-URL voor de kaart (wordt pas na een klik geladen).
 */
function vk_maps_embed_url() {
	$q = ( vk_filled( 'lat' ) && vk_filled( 'lng' ) )
		? vk_get( 'lat' ) . ',' . vk_get( 'lng' )
		: vk_get( 'bedrijfsnaam' ) . ', ' . vk_adres_regel();
	return 'https://www.google.com/maps?q=' . rawurlencode( $q ) . '&z=14&output=embed';
}

/**
 * URL van de aanvraagpagina (contact) met optioneel voorgeselecteerd object.
 *
 * @param string $type Objectsleutel, bijv. 'boot'.
 */
function vk_aanvraag_url( $type = '' ) {
	$type = ( $type && isset( vk_objecten()[ $type ] ) ) ? $type : '';

	// Staat het formulier op de huidige pagina? Dan direct daarheen scrollen.
	if ( ! is_admin() && is_singular() && has_block( 'vankeulen/aanvraagformulier', get_queried_object_id() ) ) {
		return '#aanvraag';
	}

	$pagina = get_page_by_path( 'contact' );
	$url    = $pagina ? get_permalink( $pagina ) : home_url( '/contact/' );
	if ( $type ) {
		$url = add_query_arg( 'type', $type, $url );
	}
	return $url . '#aanvraag';
}

/**
 * Ontvanger van aanvragen.
 */
function vk_aanvraag_ontvanger() {
	foreach ( array( vk_get( 'aanvraag_email' ), vk_get( 'email' ), get_option( 'admin_email' ) ) as $mail ) {
		if ( is_email( $mail ) ) {
			return $mail;
		}
	}
	return get_option( 'admin_email' );
}

/**
 * Prijsregels uit het tekstveld: "Omschrijving | Prijs | Toelichting".
 *
 * @return array<int,array{omschrijving:string,prijs:string,toelichting:string}>
 */
function vk_prijsregels() {
	$regels = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) vk_get( 'prijzen' ) ) as $r ) {
		if ( '' === trim( $r ) ) {
			continue;
		}
		$p        = array_map( 'trim', explode( '|', $r ) );
		$regels[] = array(
			'omschrijving' => $p[0] ?? '',
			'prijs'        => $p[1] ?? '',
			'toelichting'  => $p[2] ?? '',
		);
	}
	return $regels;
}

/**
 * SVG-pictogrammen (inline, geen icon-font nodig).
 *
 * @param string $naam Naam.
 */
function vk_icon( $naam ) {
	$paths = array(
		'telefoon' => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z"/>',
		'whatsapp' => '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.7.3-.2.3-.9.9-.9 2.2 0 1.3.9 2.5 1 2.7.2.2 1.8 2.8 4.4 3.9 1.6.7 2.3.8 3.1.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3Z"/>',
		'mail'     => '<path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm9 7.2L4.3 7H4v.9l8 5.4 8-5.4V7h-.3L12 12.2Z"/>',
		'pin'      => '<path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/>',
		'route'    => '<path d="M21.7 11.3 12.7 2.3a1 1 0 0 0-1.4 0l-9 9a1 1 0 0 0 0 1.4l9 9a1 1 0 0 0 1.4 0l9-9a1 1 0 0 0 0-1.4ZM14 14.5V12h-4v3H8v-4a1 1 0 0 1 1-1h5V7.5l3.5 3.5-3.5 3.5Z"/>',
		'klok'     => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7Z"/>',
		'pijl'     => '<path d="M13.2 5.3 19.9 12l-6.7 6.7-1.4-1.4 4.3-4.3H4v-2h12.1l-4.3-4.3 1.4-1.4Z"/>',
		'aanvraag' => '<path d="M19 3h-4.2A3 3 0 0 0 9.2 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm-2 14-4-4 1.4-1.4 2.6 2.6 6.6-6.6L18 9l-8 8Z"/>',
		'vink'     => '<path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2Z"/>',
	);
	if ( ! isset( $paths[ $naam ] ) ) {
		return '';
	}
	return '<svg class="vk-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="currentColor">' . $paths[ $naam ] . '</svg>';
}

/*
 * Shortcodes voor gebruik in teksten, zodat gegevens overal gelijk blijven:
 * [vk_bedrijfsnaam] [vk_adres] [vk_telefoon] [vk_email]
 */
add_shortcode(
	'vk_bedrijfsnaam',
	function () {
		return esc_html( vk_get( 'bedrijfsnaam' ) );
	}
);
add_shortcode(
	'vk_adres',
	function () {
		return esc_html( vk_adres_regel() );
	}
);
add_shortcode(
	'vk_telefoon',
	function () {
		if ( ! vk_filled( 'telefoon' ) ) {
			return vk_placeholder_html();
		}
		return '<a href="' . esc_url( vk_tel_href(), array( 'tel' ) ) . '">' . esc_html( vk_get( 'telefoon' ) ) . '</a>';
	}
);
add_shortcode(
	'vk_email',
	function () {
		$m = antispambot( vk_get( 'email' ) );
		return '<a href="mailto:' . $m . '">' . $m . '</a>';
	}
);
