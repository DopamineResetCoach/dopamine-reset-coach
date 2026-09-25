<?php
/**
 * Centrale instellingen: één bron voor telefoon, e-mail, adressen, openingstijden en tarieven.
 * Alles wordt opgeslagen in één optie (`valkenisse_settings`) en overal op de site uitgelezen.
 */

defined( 'ABSPATH' ) || exit;

const VK_OPTION = 'valkenisse_settings';

/**
 * Weekdagen in ISO-volgorde (1 = maandag), sleutel => [label, schema.org-naam].
 */
function vk_weekdays(): array {
	return array(
		'mon' => array( __( 'Maandag', 'valkenisse' ), 'Monday' ),
		'tue' => array( __( 'Dinsdag', 'valkenisse' ), 'Tuesday' ),
		'wed' => array( __( 'Woensdag', 'valkenisse' ), 'Wednesday' ),
		'thu' => array( __( 'Donderdag', 'valkenisse' ), 'Thursday' ),
		'fri' => array( __( 'Vrijdag', 'valkenisse' ), 'Friday' ),
		'sat' => array( __( 'Zaterdag', 'valkenisse' ), 'Saturday' ),
		'sun' => array( __( 'Zondag', 'valkenisse' ), 'Sunday' ),
	);
}

/**
 * Beheerschermen en hun velden. De admin-renderer en de opslaglogica werken op basis van dit schema,
 * zodat een nieuw veld toevoegen neerkomt op één regel hier.
 *
 * Standaardwaarden zijn uitsluitend gebaseerd op strandpaviljoenherwegh.nl. Onbekende informatie = VK_TODO.
 */
function vk_settings_schema(): array {
	$hours_fields = array();
	foreach ( vk_weekdays() as $key => $day ) {
		$hours_fields[] = array(
			'key'   => "hours_{$key}",
			'label' => $day[0],
			'type'  => 'hours',
			'default' => array(
				'open'   => '11:00',
				'close'  => '',
				'closed' => 0,
			),
		);
	}

	return array(
		'vandaag'        => array(
			'title'    => __( 'Vandaag open of dicht', 'valkenisse' ),
			'intro'    => __( 'Eén keuze en de hele site (melding bovenaan, homepage, openingstijden) is bijgewerkt. Een keuze voor "vandaag" vervalt automatisch om middernacht, dan geldt weer het normale rooster.', 'valkenisse' ),
			'sections' => array(
				array(
					'title'  => __( 'Status van vandaag', 'valkenisse' ),
					'fields' => array(
						array(
							'key'     => 'today_status',
							'label'   => __( 'Vandaag', 'valkenisse' ),
							'type'    => 'radio',
							'default' => 'auto',
							'options' => array(
								'auto'           => __( 'Normaal rooster (zoals bij Openingstijden)', 'valkenisse' ),
								'open'           => __( 'Vandaag geopend vanaf …', 'valkenisse' ),
								'open_until'     => __( 'Vandaag geopend tot ongeveer …', 'valkenisse' ),
								'closed_weather' => __( '🌧 Vandaag gesloten vanwege het weer', 'valkenisse' ),
								'closed'         => __( 'Vandaag gesloten', 'valkenisse' ),
								'custom'         => __( 'Eigen tekst (hieronder)', 'valkenisse' ),
							),
						),
						array(
							'key'         => 'today_open',
							'label'       => __( 'Open vanaf (vandaag)', 'valkenisse' ),
							'type'        => 'time',
							'default'     => '11:00',
						),
						array(
							'key'         => 'today_close',
							'label'       => __( 'Open tot ongeveer (vandaag)', 'valkenisse' ),
							'type'        => 'time',
							'default'     => '',
							'description' => __( 'Wordt getoond als "tot ± 20:00".', 'valkenisse' ),
						),
						array(
							'key'         => 'today_custom',
							'label'       => __( 'Eigen tekst', 'valkenisse' ),
							'type'        => 'text',
							'default'     => '',
							'description' => __( 'Alleen bij "Eigen tekst". Bijvoorbeeld: Vandaag vanaf 13:00 geopend.', 'valkenisse' ),
						),
						array(
							'key'     => 'today_only',
							'label'   => __( 'Alleen voor vandaag', 'valkenisse' ),
							'type'    => 'checkbox',
							'default' => 1,
							'description' => __( 'Aangevinkt: morgen staat alles vanzelf weer op het normale rooster.', 'valkenisse' ),
						),
					),
				),
				array(
					'title'  => __( 'Melding bovenaan de website', 'valkenisse' ),
					'fields' => array(
						array(
							'key'     => 'notice_enabled',
							'label'   => __( 'Toon melding bovenaan', 'valkenisse' ),
							'type'    => 'checkbox',
							'default' => 1,
							'description' => __( 'Toont automatisch bv. "☀️ Vandaag geopend vanaf 11:00 – tot straks op het strand!"', 'valkenisse' ),
						),
						array(
							'key'         => 'notice_extra',
							'label'       => __( 'Extra mededeling (optioneel)', 'valkenisse' ),
							'type'        => 'text',
							'default'     => '',
							'description' => __( 'Bijvoorbeeld een tijdelijke mededeling. Laat leeg als er niets bijzonders is.', 'valkenisse' ),
						),
						array(
							'key'     => 'notice_extra_link',
							'label'   => __( 'Link bij mededeling (optioneel)', 'valkenisse' ),
							'type'    => 'url',
							'default' => '',
						),
						array(
							'key'         => 'notice_extra_until',
							'label'       => __( 'Mededeling tonen tot en met', 'valkenisse' ),
							'type'        => 'date',
							'default'     => '',
							'description' => __( 'Leeg = tot je hem zelf weghaalt.', 'valkenisse' ),
						),
					),
				),
			),
		),
		'openingstijden' => array(
			'title'    => __( 'Openingstijden', 'valkenisse' ),
			'intro'    => __( 'Het vaste rooster. Laat "tot" leeg als de sluitingstijd wisselt.', 'valkenisse' ),
			'sections' => array(
				array(
					'title'  => __( 'Rooster', 'valkenisse' ),
					'fields' => $hours_fields,
				),
				array(
					'title'  => __( 'Toelichting', 'valkenisse' ),
					'fields' => array(
						array(
							'key'     => 'hours_variable_label',
							'label'   => __( 'Tekst bij wisselende sluitingstijd', 'valkenisse' ),
							'type'    => 'text',
							'default' => __( 'sluitingstijd wisselend', 'valkenisse' ),
						),
						array(
							'key'     => 'hours_season',
							'label'   => __( 'Seizoen / periode', 'valkenisse' ),
							'type'    => 'text',
							'default' => VK_TODO,
							'description' => __( 'Bijvoorbeeld: van … tot en met …', 'valkenisse' ),
						),
						array(
							'key'     => 'hours_note',
							'label'   => __( 'Opmerking onder de openingstijden', 'valkenisse' ),
							'type'    => 'textarea',
							'default' => '',
						),
						array(
							'key'         => 'hours_schema_close',
							'label'       => __( 'Gemiddelde sluitingstijd voor Google', 'valkenisse' ),
							'type'        => 'time',
							'default'     => '',
							'description' => __( 'Google vraagt een sluitingstijd. Wordt niet op de website getoond. Leeg = geen openingstijden naar Google sturen.', 'valkenisse' ),
						),
					),
				),
			),
		),
		'contact'        => array(
			'title'    => __( 'Contactgegevens', 'valkenisse' ),
			'intro'    => __( 'Deze gegevens verschijnen automatisch in de header, footer, contactpagina, mobiele knoppenbalk en bij Google.', 'valkenisse' ),
			'sections' => array(
				array(
					'title'  => __( 'Bereikbaarheid', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'phone', 'label' => __( 'Telefoonnummer (zoals getoond)', 'valkenisse' ), 'type' => 'text', 'default' => '0118 561347' ),
						array( 'key' => 'phone_intl', 'label' => __( 'Telefoonnummer internationaal (voor de belknop)', 'valkenisse' ), 'type' => 'text', 'default' => '+31118561347', 'description' => __( 'Zonder spaties, bv. +31118561347', 'valkenisse' ) ),
						array( 'key' => 'phone_note', 'label' => __( 'Toelichting telefoon', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Telefonisch bereikbaar wanneer het paviljoen geopend is.', 'valkenisse' ) ),
						array( 'key' => 'email', 'label' => __( 'E-mailadres', 'valkenisse' ), 'type' => 'email', 'default' => 'info@strandpaviljoenherwegh.nl' ),
						array( 'key' => 'request_email', 'label' => __( 'Strandhuisje-aanvragen sturen naar', 'valkenisse' ), 'type' => 'email', 'default' => 'info@strandpaviljoenherwegh.nl' ),
					),
				),
				array(
					'title'  => __( 'Postadres', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'postal_street', 'label' => __( 'Straat + nummer', 'valkenisse' ), 'type' => 'text', 'default' => 'L. Simonsestraat 10' ),
						array( 'key' => 'postal_zip', 'label' => __( 'Postcode', 'valkenisse' ), 'type' => 'text', 'default' => '4373 AV' ),
						array( 'key' => 'postal_city', 'label' => __( 'Plaats', 'valkenisse' ), 'type' => 'text', 'default' => 'Biggekerke' ),
					),
				),
				array(
					'title'  => __( 'Locatie & navigatie', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'location_desc', 'label' => __( 'Waar ligt het paviljoen?', 'valkenisse' ), 'type' => 'textarea', 'default' => __( 'Bij de duinovergang Vossenhol in Groot Valkenisse, bij Camping Meerpaal.', 'valkenisse' ) ),
						array( 'key' => 'nav_address', 'label' => __( 'Adres voor navigatie', 'valkenisse' ), 'type' => 'text', 'default' => VK_TODO, 'description' => __( 'Het adres dat bezoekers in hun navigatie invoeren. Dit wijkt af van het postadres.', 'valkenisse' ) ),
						array( 'key' => 'parking', 'label' => __( 'Parkeren', 'valkenisse' ), 'type' => 'textarea', 'default' => __( 'Parkeergelegenheid in de nabijheid van het paviljoen.', 'valkenisse' ) ),
						array( 'key' => 'map_query', 'label' => __( 'Zoekterm voor kaart & route', 'valkenisse' ), 'type' => 'text', 'default' => 'Strandpaviljoen Valkenisse, Biggekerke', 'description' => __( 'Wordt gebruikt als er geen coördinaten zijn ingevuld.', 'valkenisse' ) ),
						array( 'key' => 'lat', 'label' => __( 'Breedtegraad (optioneel)', 'valkenisse' ), 'type' => 'text', 'default' => '', 'description' => __( 'Bijv. 51.51… — rechtsklik op de plek in Google Maps om dit te kopiëren.', 'valkenisse' ) ),
						array( 'key' => 'lng', 'label' => __( 'Lengtegraad (optioneel)', 'valkenisse' ), 'type' => 'text', 'default' => '' ),
					),
				),
				array(
					'title'  => __( 'Reserveren (eten & drinken)', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'reserve_enabled', 'label' => __( 'Knop "Reserveer nu" tonen', 'valkenisse' ), 'type' => 'checkbox', 'default' => 1, 'description' => __( 'Bezoekers kiezen daarna zelf: bellen of mailen.', 'valkenisse' ) ),
						array( 'key' => 'reserve_question', 'label' => __( 'Vraag boven de knop', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Dineren, lunchen of borrelen?', 'valkenisse' ) ),
						array( 'key' => 'reserve_mail_subject', 'label' => __( 'Onderwerp van de reserveringsmail', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Reservering', 'valkenisse' ) ),
					),
				),
				array(
					'title'  => __( 'Online & bedrijf', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'facebook', 'label' => __( 'Facebook-pagina (URL)', 'valkenisse' ), 'type' => 'url', 'default' => '' ),
						array( 'key' => 'instagram', 'label' => __( 'Instagram (URL)', 'valkenisse' ), 'type' => 'url', 'default' => '' ),
						array( 'key' => 'google_reviews', 'label' => __( 'Google-reviews (URL)', 'valkenisse' ), 'type' => 'url', 'default' => '', 'description' => __( 'Link naar de reviews op Google. Leeg = knop wordt niet getoond.', 'valkenisse' ) ),
						array( 'key' => 'menu_pdf', 'label' => __( 'Menukaart als PDF (optioneel)', 'valkenisse' ), 'type' => 'url', 'default' => '', 'description' => __( 'Extra downloadlink. De kaart zelf staat altijd als webpagina online.', 'valkenisse' ) ),
						array( 'key' => 'company', 'label' => __( 'Bedrijfsnaam (footer)', 'valkenisse' ), 'type' => 'text', 'default' => 'Familie Herwegh / VOF Herwegh' ),
						array( 'key' => 'kvk', 'label' => __( 'KvK-nummer (optioneel)', 'valkenisse' ), 'type' => 'text', 'default' => '' ),
					),
				),
			),
		),
		'strandhuisjes'  => array(
			'title'    => __( 'Strandhuisjes & tarieven', 'valkenisse' ),
			'intro'    => __( 'Nieuw seizoen? Pas het jaartal en de prijzen aan en klik op Opslaan. Lege regels worden niet getoond.', 'valkenisse' ),
			'sections' => array(
				array(
					'title'  => __( 'Algemeen', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'huts_season', 'label' => __( 'Titel boven de tarieven', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Huurprijzen 2026', 'valkenisse' ), 'description' => __( 'Nieuw seizoen? Verander hier het jaartal.', 'valkenisse' ) ),
						array( 'key' => 'huts_period', 'label' => __( 'Verhuurperiode', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Van begin april tot en met september', 'valkenisse' ) ),
						array( 'key' => 'huts_included', 'label' => __( 'Inbegrepen (één per regel)', 'valkenisse' ), 'type' => 'textarea', 'default' => __( "2 strandstoelen\n1 windscherm / luifel\nTafeltje", 'valkenisse' ) ),
						array( 'key' => 'huts_booking', 'label' => __( 'Reserveren', 'valkenisse' ), 'type' => 'textarea', 'default' => __( 'Strandhuisjes reserveren kan het gehele jaar door via e-mail, of persoonlijk/telefonisch wanneer Strandpaviljoen Valkenisse geopend is.', 'valkenisse' ) ),
						array( 'key' => 'huts_available', 'label' => __( 'Aanvraagformulier tonen', 'valkenisse' ), 'type' => 'checkbox', 'default' => 1, 'description' => __( 'Uitvinken als alles is verhuurd.', 'valkenisse' ) ),
						array( 'key' => 'huts_full_text', 'label' => __( 'Tekst als het formulier uit staat', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Alle strandhuisjes zijn op dit moment verhuurd. Neem gerust contact op voor het volgende seizoen.', 'valkenisse' ) ),
					),
				),
				array(
					'title'  => __( 'Tarieven strandhuisjes', 'valkenisse' ),
					'fields' => array(
						array(
							'key'     => 'huts_prices',
							'label'   => __( 'Tarieven', 'valkenisse' ),
							'type'    => 'rows',
							'rows'    => 10,
							'groups'  => true,
							'default' => array(
								array( 'group' => __( 'Per dag', 'valkenisse' ), 'label' => __( 'Strandhuisje', 'valkenisse' ), 'price' => '€ 20,00', 'note' => '' ),
								array( 'group' => __( 'Per week', 'valkenisse' ), 'label' => __( 'Vóór 23 mei', 'valkenisse' ), 'price' => '€ 75,00', 'note' => '' ),
								array( 'group' => __( 'Per week', 'valkenisse' ), 'label' => __( 'Van 23 mei tot 27 juni', 'valkenisse' ), 'price' => '€ 90,00', 'note' => '' ),
								array( 'group' => __( 'Per week', 'valkenisse' ), 'label' => __( 'Van 27 juni tot 29 augustus', 'valkenisse' ), 'price' => '€ 110,00', 'note' => '' ),
								array( 'group' => __( 'Per week', 'valkenisse' ), 'label' => __( 'Vanaf 29 augustus', 'valkenisse' ), 'price' => '€ 75,00', 'note' => '' ),
								array( 'group' => __( 'Per seizoen', 'valkenisse' ), 'label' => __( 'Van 25 april t/m 20 september', 'valkenisse' ), 'price' => '€ 645,00', 'note' => '' ),
							),
						),
					),
				),
			),
		),
		'strandverhuur'  => array(
			'title'    => __( 'Strandverhuur & tarieven', 'valkenisse' ),
			'intro'    => __( 'Prijzen voor strandstoelen, ligbedden, parasols en windschermen. Lege regels worden niet getoond.', 'valkenisse' ),
			'sections' => array(
				array(
					'title'  => __( 'Tarieven', 'valkenisse' ),
					'fields' => array(
						array( 'key' => 'rental_season', 'label' => __( 'Titel boven de tarieven', 'valkenisse' ), 'type' => 'text', 'default' => __( 'Huurprijzen 2026', 'valkenisse' ) ),
						array(
							'key'     => 'rental_prices',
							'label'   => __( 'Tarieven', 'valkenisse' ),
							'type'    => 'rows',
							'rows'    => 8,
							'groups'  => true,
							'default' => array(
								array( 'group' => __( 'Per dag', 'valkenisse' ), 'label' => __( 'Strandstoel', 'valkenisse' ), 'price' => '€ 5,00', 'note' => '' ),
								array( 'group' => __( 'Per dag', 'valkenisse' ), 'label' => __( 'Parasol', 'valkenisse' ), 'price' => '€ 5,00', 'note' => '' ),
								array( 'group' => __( 'Per dag', 'valkenisse' ), 'label' => __( 'Ligbed', 'valkenisse' ), 'price' => '€ 5,00', 'note' => '' ),
								array( 'group' => __( 'Per dag', 'valkenisse' ), 'label' => __( 'Windscherm', 'valkenisse' ), 'price' => '€ 5,00', 'note' => '' ),
							),
						),
						array( 'key' => 'rental_note', 'label' => __( 'Toelichting', 'valkenisse' ), 'type' => 'textarea', 'default' => '' ),
					),
				),
			),
		),
	);
}

/**
 * Alle velden plat, sleutel => velddefinitie.
 */
function vk_settings_fields(): array {
	static $fields = null;
	if ( null === $fields ) {
		$fields = array();
		foreach ( vk_settings_schema() as $page ) {
			foreach ( $page['sections'] as $section ) {
				foreach ( $section['fields'] as $field ) {
					$fields[ $field['key'] ] = $field;
				}
			}
		}
	}
	return $fields;
}

/**
 * Lees een instelling. Valt terug op de standaardwaarde uit het schema.
 */
function vk_get( string $key ) {
	$stored = get_option( VK_OPTION, array() );
	if ( is_array( $stored ) && array_key_exists( $key, $stored ) ) {
		return $stored[ $key ];
	}
	$fields = vk_settings_fields();
	return $fields[ $key ]['default'] ?? '';
}

/**
 * Sla één of meer instellingen op (samengevoegd met bestaande waarden).
 */
function vk_update( array $values ): void {
	$stored = get_option( VK_OPTION, array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	update_option( VK_OPTION, array_merge( $stored, $values ) );
	do_action( 'valkenisse_settings_updated', $values );
}

/**
 * Ontsmet een ingezonden waarde op basis van het veldtype.
 */
function vk_sanitize_field( array $field, $value ) {
	switch ( $field['type'] ) {
		case 'checkbox':
			return empty( $value ) ? 0 : 1;
		case 'email':
			return sanitize_email( (string) $value );
		case 'url':
			return esc_url_raw( trim( (string) $value ) );
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'time':
			$value = trim( (string) $value );
			return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
		case 'date':
			$value = trim( (string) $value );
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
		case 'radio':
			return array_key_exists( (string) $value, $field['options'] ) ? (string) $value : $field['default'];
		case 'hours':
			$value = is_array( $value ) ? $value : array();
			return array(
				'open'   => vk_sanitize_field( array( 'type' => 'time' ), $value['open'] ?? '' ),
				'close'  => vk_sanitize_field( array( 'type' => 'time' ), $value['close'] ?? '' ),
				'closed' => empty( $value['closed'] ) ? 0 : 1,
			);
		case 'rows':
			$rows = array();
			foreach ( (array) $value as $row ) {
				$label = sanitize_text_field( $row['label'] ?? '' );
				if ( '' === $label ) {
					continue;
				}
				$rows[] = array(
					'group' => sanitize_text_field( $row['group'] ?? '' ),
					'label' => $label,
					'price' => sanitize_text_field( $row['price'] ?? '' ),
					'note'  => sanitize_text_field( $row['note'] ?? '' ),
				);
			}
			return $rows;
		default:
			return sanitize_text_field( (string) $value );
	}
}
