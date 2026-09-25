<?php
/**
 * Centrale restaurantgegevens: contact, reserveren, openingstijden, mededeling en formulieren.
 *
 * Alles staat in één optie (valkenisse_settings) zodat een wijziging direct overal
 * doorwerkt: header, footer, homepage, contactpagina, mobiele actiebalk en structured data.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

const VALKENISSE_OPTION       = 'valkenisse_settings';
const VALKENISSE_MAX_SEASONS  = 6;
const VALKENISSE_MAX_EXCEPT   = 12;
const VALKENISSE_DAY_NAMES    = array( 1 => 'Maandag', 2 => 'Dinsdag', 3 => 'Woensdag', 4 => 'Donderdag', 5 => 'Vrijdag', 6 => 'Zaterdag', 7 => 'Zondag' );

/**
 * Standaardwaarden. Alleen gegevens die op restaurantvalkenisse.nl zijn aangetroffen
 * (zie BRONNEN-EN-CONTROLE.md). Ontbrekende gegevens blijven leeg.
 */
function valkenisse_default_settings(): array {
	$day = static fn( string $open, string $close = '', bool $closed = false ) => array(
		'open'   => $open,
		'sluit'  => $close,
		'dicht'  => $closed,
	);
	$week = static function ( array $overrides, array $default ) {
		$days = array();
		for ( $i = 1; $i <= 7; $i++ ) {
			$days[ $i ] = $overrides[ $i ] ?? $default;
		}
		return $days;
	};
	$closed = $day( '', '', true );

	return array(
		'contact'     => array(
			'naam'           => 'Restaurant Valkenisse',
			'straat'         => 'Valkenisseweg 76',
			'postcode'       => '4373 RP',
			'plaats'         => 'Biggekerke',
			'telefoon'       => '0118-566255',
			'email'          => 'restvalk@zeelandnet.nl',
			'reserveer_modus' => 'formulier',
			'reserveer_url'  => '',
			'booking_url'    => '',
			'facebook'       => '',
			'instagram'      => '',
		),
		'seizoenen'   => array(
			array(
				'label'      => 'April t/m juni (vanaf Pasen)',
				'van'        => '04-01',
				'tot'        => '06-30',
				'pasen'      => true,
				'dagen'      => $week( array(), $day( '10:30' ) ),
				'keuken_van' => '12:00',
				'keuken_tot' => '22:00',
				'opmerking'  => '7 dagen per week geopend',
			),
			array(
				'label'      => 'Juli en augustus',
				'van'        => '07-01',
				'tot'        => '08-31',
				'pasen'      => false,
				'dagen'      => $week( array(), $day( '10:00' ) ),
				'keuken_van' => '12:00',
				'keuken_tot' => '22:00',
				'opmerking'  => '',
			),
			array(
				'label'      => 'September',
				'van'        => '09-01',
				'tot'        => '09-30',
				'pasen'      => false,
				'dagen'      => $week( array( 1 => $closed ), $day( '11:00', '21:00' ) ),
				'keuken_van' => '12:00',
				'keuken_tot' => '21:00',
				'opmerking'  => '',
			),
			array(
				'label'      => 'Oktober',
				'van'        => '10-01',
				'tot'        => '10-31',
				'pasen'      => false,
				'dagen'      => $week( array( 1 => $closed, 2 => $closed, 3 => $closed ), $day( '11:00', '21:00' ) ),
				'keuken_van' => '12:00',
				'keuken_tot' => '21:00',
				'opmerking'  => '',
			),
		),
		'buiten_seizoen' => 'Voor de openingstijden in deze periode kunt u ons het beste even bellen.',
		'uitzonderingen' => array(),
		'mededeling'  => array(
			'actief'    => false,
			'tekst'     => '',
			'link'      => '',
			'link_tekst' => '',
			'tot'       => '',
		),
		'formulieren' => array(
			'ontvanger'     => 'restvalk@zeelandnet.nl',
			'bevestiging'   => true,
			'bewaartermijn' => 90,
		),
		'seo'         => array(
			'schema' => true,
		),
	);
}

/**
 * Volledige instellingen, aangevuld met standaardwaarden.
 */
function valkenisse_settings(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$saved = get_option( VALKENISSE_OPTION, null );
	$cache = is_array( $saved ) ? array_replace_recursive( valkenisse_default_settings(), $saved ) : valkenisse_default_settings();
	// Lijsten volledig overnemen (niet samenvoegen met standaardwaarden).
	if ( is_array( $saved ) ) {
		foreach ( array( 'seizoenen', 'uitzonderingen' ) as $list ) {
			if ( isset( $saved[ $list ] ) ) {
				$cache[ $list ] = $saved[ $list ];
			}
		}
	}
	return $cache;
}

add_action(
	'update_option_' . VALKENISSE_OPTION,
	static function () {
		// Pagina-caches legen zodat nieuwe tijden direct zichtbaar zijn.
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		do_action( 'valkenisse_settings_updated' );
	}
);

/**
 * Eén waarde ophalen met puntnotatie, bijvoorbeeld valkenisse_get( 'contact.telefoon' ).
 */
function valkenisse_get( string $path, $fallback = '' ) {
	$value = valkenisse_settings();
	foreach ( explode( '.', $path ) as $key ) {
		if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
			return $fallback;
		}
		$value = $value[ $key ];
	}
	return $value;
}

/** Telefoonnummer als tel:-link, bv. +31118566255. */
function valkenisse_phone_href(): string {
	$digits = preg_replace( '/\D+/', '', (string) valkenisse_get( 'contact.telefoon' ) );
	if ( '' === $digits ) {
		return '';
	}
	if ( str_starts_with( $digits, '0031' ) ) {
		$digits = substr( $digits, 4 );
	} elseif ( str_starts_with( $digits, '31' ) && strlen( $digits ) === 11 ) {
		$digits = substr( $digits, 2 );
	} elseif ( str_starts_with( $digits, '0' ) ) {
		$digits = substr( $digits, 1 );
	}
	return 'tel:+31' . $digits;
}

/** Volledig adres op één regel. */
function valkenisse_address_line(): string {
	$c = valkenisse_get( 'contact' );
	return trim( $c['straat'] . ', ' . trim( $c['postcode'] . ' ' . $c['plaats'] ), ', ' );
}

/** Link naar routeplanner (opent Google Maps pas na klik: geen cookies vooraf). */
function valkenisse_route_url(): string {
	$c = valkenisse_get( 'contact' );
	return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $c['naam'] . ', ' . valkenisse_address_line() );
}

/** URL achter elke "Reserveer een tafel"-knop. */
function valkenisse_reserve_url(): string {
	$mode = valkenisse_get( 'contact.reserveer_modus' );
	if ( 'extern' === $mode && valkenisse_get( 'contact.reserveer_url' ) ) {
		return esc_url_raw( valkenisse_get( 'contact.reserveer_url' ) );
	}
	if ( 'telefoon' === $mode ) {
		return valkenisse_phone_href();
	}
	$page = get_page_by_path( 'reserveren' );
	return $page ? get_permalink( $page ) : home_url( '/reserveren/' );
}

/* -------------------------------------------------------------------------
 * Opslaan + opschonen
 * ---------------------------------------------------------------------- */

add_action(
	'admin_init',
	static function () {
		register_setting(
			'valkenisse',
			VALKENISSE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'valkenisse_sanitize_settings',
				'default'           => valkenisse_default_settings(),
			)
		);
	}
);

function valkenisse_sanitize_time( $value ): string {
	$value = trim( str_replace( array( '.', 'u' ), ':', (string) $value ) );
	if ( preg_match( '/^(\d{1,2})(?::(\d{2}))?$/', $value, $m ) ) {
		$h = (int) $m[1];
		$i = isset( $m[2] ) ? (int) $m[2] : 0;
		if ( $h <= 24 && $i < 60 ) {
			return sprintf( '%02d:%02d', $h, $i );
		}
	}
	return '';
}

function valkenisse_sanitize_monthday( $value ): string {
	$value = trim( (string) $value );
	// Accepteer 1-4, 01-04, 1/4 en 2026-04-01 (jaar wordt genegeerd). Formaat intern: MM-DD.
	if ( preg_match( '/^\d{4}-(\d{2})-(\d{2})$/', $value, $m ) ) {
		return $m[1] . '-' . $m[2];
	}
	if ( preg_match( '/^(\d{1,2})[-\/.](\d{1,2})$/', $value, $m ) ) {
		$d = (int) $m[1];
		$mo = (int) $m[2];
		if ( checkdate( $mo, $d, 2024 ) ) {
			return sprintf( '%02d-%02d', $mo, $d );
		}
	}
	return '';
}

function valkenisse_sanitize_settings( $input ): array {
	$input    = is_array( $input ) ? wp_unslash( $input ) : array();
	$current  = valkenisse_settings();
	$defaults = valkenisse_default_settings();
	$out      = $current;
	$section  = sanitize_key( $input['_section'] ?? '' );

	if ( 'contact' === $section ) {
		$c = $input['contact'] ?? array();
		foreach ( array( 'naam', 'straat', 'postcode', 'plaats', 'telefoon' ) as $key ) {
			$out['contact'][ $key ] = sanitize_text_field( $c[ $key ] ?? '' );
		}
		$out['contact']['email']           = sanitize_email( $c['email'] ?? '' );
		$out['contact']['reserveer_modus'] = in_array( $c['reserveer_modus'] ?? '', array( 'formulier', 'telefoon', 'extern' ), true ) ? $c['reserveer_modus'] : 'formulier';
		foreach ( array( 'reserveer_url', 'booking_url', 'facebook', 'instagram' ) as $key ) {
			$out['contact'][ $key ] = esc_url_raw( trim( $c[ $key ] ?? '' ) );
		}
	}

	if ( 'openingstijden' === $section ) {
		$seasons = array();
		foreach ( (array) ( $input['seizoenen'] ?? array() ) as $s ) {
			$label = sanitize_text_field( $s['label'] ?? '' );
			$van   = valkenisse_sanitize_monthday( $s['van'] ?? '' );
			$tot   = valkenisse_sanitize_monthday( $s['tot'] ?? '' );
			if ( '' === $label && '' === $van && '' === $tot ) {
				continue; // Lege rij.
			}
			$days = array();
			for ( $i = 1; $i <= 7; $i++ ) {
				$d          = $s['dagen'][ $i ] ?? array();
				$days[ $i ] = array(
					'open'  => valkenisse_sanitize_time( $d['open'] ?? '' ),
					'sluit' => valkenisse_sanitize_time( $d['sluit'] ?? '' ),
					'dicht' => ! empty( $d['dicht'] ),
				);
				if ( '' === $days[ $i ]['open'] && '' === $days[ $i ]['sluit'] ) {
					$days[ $i ]['dicht'] = true;
				}
			}
			$seasons[] = array(
				'label'      => $label,
				'van'        => $van ? $van : '01-01',
				'tot'        => $tot ? $tot : '12-31',
				'pasen'      => ! empty( $s['pasen'] ),
				'dagen'      => $days,
				'keuken_van' => valkenisse_sanitize_time( $s['keuken_van'] ?? '' ),
				'keuken_tot' => valkenisse_sanitize_time( $s['keuken_tot'] ?? '' ),
				'opmerking'  => sanitize_text_field( $s['opmerking'] ?? '' ),
			);
		}
		$out['seizoenen']      = array_slice( $seasons, 0, VALKENISSE_MAX_SEASONS );
		$out['buiten_seizoen'] = sanitize_text_field( $input['buiten_seizoen'] ?? '' );

		$exceptions = array();
		foreach ( (array) ( $input['uitzonderingen'] ?? array() ) as $e ) {
			$date = sanitize_text_field( $e['datum'] ?? '' );
			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
				continue;
			}
			$exceptions[] = array(
				'datum'     => $date,
				'dicht'     => ! empty( $e['dicht'] ),
				'open'      => valkenisse_sanitize_time( $e['open'] ?? '' ),
				'sluit'     => valkenisse_sanitize_time( $e['sluit'] ?? '' ),
				'opmerking' => sanitize_text_field( $e['opmerking'] ?? '' ),
			);
		}
		usort( $exceptions, static fn( $a, $b ) => strcmp( $a['datum'], $b['datum'] ) );
		$out['uitzonderingen'] = array_slice( $exceptions, 0, VALKENISSE_MAX_EXCEPT );
	}

	if ( 'mededeling' === $section ) {
		$m                 = $input['mededeling'] ?? array();
		$out['mededeling'] = array(
			'actief'     => ! empty( $m['actief'] ),
			'tekst'      => sanitize_text_field( $m['tekst'] ?? '' ),
			'link'       => esc_url_raw( $m['link'] ?? '' ),
			'link_tekst' => sanitize_text_field( $m['link_tekst'] ?? '' ),
			'tot'        => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $m['tot'] ?? '' ) ? $m['tot'] : '',
		);
	}

	if ( 'formulieren' === $section ) {
		$f                  = $input['formulieren'] ?? array();
		$out['formulieren'] = array(
			'ontvanger'     => sanitize_email( $f['ontvanger'] ?? '' ),
			'bevestiging'   => ! empty( $f['bevestiging'] ),
			'bewaartermijn' => max( 7, min( 730, absint( $f['bewaartermijn'] ?? $defaults['formulieren']['bewaartermijn'] ) ) ),
		);
		$out['seo']['schema'] = ! empty( $input['seo']['schema'] );
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * Beheerpagina
 * ---------------------------------------------------------------------- */

add_action(
	'admin_menu',
	static function () {
		add_menu_page(
			'Openingstijden & contact',
			'Openingstijden',
			'edit_pages',
			'valkenisse',
			'valkenisse_render_settings_page',
			'dashicons-clock',
			3
		);
	}
);

// Redacteuren (bv. medewerkers) mogen de openingstijden ook aanpassen.
add_filter( 'option_page_capability_valkenisse', static fn() => 'edit_pages' );

function valkenisse_settings_tabs(): array {
	return array(
		'openingstijden' => 'Openingstijden',
		'contact'        => 'Contact & reserveren',
		'mededeling'     => 'Mededeling',
		'formulieren'    => 'Formulieren & SEO',
	);
}

function valkenisse_render_settings_page(): void {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$tabs = valkenisse_settings_tabs();
	$tab  = sanitize_key( $_GET['tab'] ?? 'openingstijden' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tab  = isset( $tabs[ $tab ] ) ? $tab : 'openingstijden';
	$s    = valkenisse_settings();
	$name = VALKENISSE_OPTION;
	?>
	<div class="wrap valk-admin">
		<h1>Openingstijden &amp; contact</h1>
		<p class="valk-lead">Wat u hier invult, verschijnt automatisch op de hele website: homepage, contactpagina, footer, mobiele knoppen en in Google (structured data).</p>
		<?php settings_errors(); ?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a class="nav-tab <?php echo $slug === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=valkenisse&tab=' . $slug ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'valkenisse' ); ?>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[_section]" value="<?php echo esc_attr( $tab ); ?>">
			<?php
			switch ( $tab ) {
				case 'contact':
					valkenisse_render_contact_tab( $s, $name );
					break;
				case 'mededeling':
					valkenisse_render_notice_tab( $s, $name );
					break;
				case 'formulieren':
					valkenisse_render_forms_tab( $s, $name );
					break;
				default:
					valkenisse_render_hours_tab( $s, $name );
			}
			submit_button( 'Wijzigingen opslaan' );
			?>
		</form>
	</div>
	<?php
}

function valkenisse_field( string $label, string $html, string $help = '' ): void {
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $html is opgebouwd met esc_* functies.
	if ( $help ) {
		echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
	}
	echo '</td></tr>';
}

function valkenisse_input( string $name, $value, string $type = 'text', string $extra = '' ): string {
	return sprintf( '<input type="%s" name="%s" value="%s" class="regular-text" %s>', esc_attr( $type ), esc_attr( $name ), esc_attr( (string) $value ), $extra );
}

function valkenisse_render_contact_tab( array $s, string $n ): void {
	$c = $s['contact'];
	echo '<h2>Contactgegevens</h2><table class="form-table" role="presentation">';
	valkenisse_field( 'Naam', valkenisse_input( "{$n}[contact][naam]", $c['naam'] ) );
	valkenisse_field( 'Straat en huisnummer', valkenisse_input( "{$n}[contact][straat]", $c['straat'] ) );
	valkenisse_field( 'Postcode', valkenisse_input( "{$n}[contact][postcode]", $c['postcode'] ) );
	valkenisse_field( 'Plaats', valkenisse_input( "{$n}[contact][plaats]", $c['plaats'] ) );
	valkenisse_field( 'Telefoon', valkenisse_input( "{$n}[contact][telefoon]", $c['telefoon'], 'tel' ), 'Zoals het op de website moet staan, bv. 0118-566255. De bel-knoppen worden automatisch aangemaakt.' );
	valkenisse_field( 'E-mail', valkenisse_input( "{$n}[contact][email]", $c['email'], 'email' ) );
	echo '</table><h2>Reserveren</h2><table class="form-table" role="presentation">';
	$modes = array(
		'formulier' => 'Reserveringsformulier op de website (pagina "Reserveren")',
		'telefoon'  => 'Alleen telefonisch (knop belt direct)',
		'extern'    => 'Extern reserveringssysteem (link hieronder)',
	);
	$radios = '';
	foreach ( $modes as $key => $label ) {
		$radios .= sprintf( '<label style="display:block;margin:.2em 0"><input type="radio" name="%s" value="%s" %s> %s</label>', esc_attr( "{$n}[contact][reserveer_modus]" ), esc_attr( $key ), checked( $c['reserveer_modus'], $key, false ), esc_html( $label ) );
	}
	valkenisse_field( 'Knop "Reserveer een tafel"', $radios, 'Bepaalt waar álle reserveerknoppen op de website naartoe gaan.' );
	valkenisse_field( 'Link reserveringssysteem', valkenisse_input( "{$n}[contact][reserveer_url]", $c['reserveer_url'], 'url' ), 'Alleen invullen als u een eigen online reserveringssysteem gebruikt.' );
	valkenisse_field( "Boekingslink studio's", valkenisse_input( "{$n}[contact][booking_url]", $c['booking_url'], 'url' ), "Optioneel: de link naar uw accommodatiepagina op een boekingsplatform. Leeg laten = alleen aanvraagformulier en telefoon." );
	echo '</table><h2>Social media (optioneel)</h2><table class="form-table" role="presentation">';
	valkenisse_field( 'Facebook', valkenisse_input( "{$n}[contact][facebook]", $c['facebook'], 'url' ) );
	valkenisse_field( 'Instagram', valkenisse_input( "{$n}[contact][instagram]", $c['instagram'], 'url' ) );
	echo '</table>';
}

function valkenisse_render_hours_tab( array $s, string $n ): void {
	$today = Valkenisse_Hours::day( Valkenisse_Hours::now() );
	echo '<div class="valk-today"><strong>Zo ziet de website het vandaag:</strong> ' . esc_html( Valkenisse_Hours::today_sentence( $today ) ) . '</div>';
	echo '<p>Vul per periode de openingstijden in. De website kiest automatisch de periode die vandaag geldt. Laat "sluit" leeg als u geen vaste sluitingstijd wilt tonen (dan staat er "vanaf 10:30"). Een lege dag of "gesloten" = gesloten.</p>';

	$seasons = $s['seizoenen'];
	for ( $i = count( $seasons ); $i < VALKENISSE_MAX_SEASONS; $i++ ) {
		$seasons[] = null;
	}
	foreach ( $seasons as $idx => $season ) {
		$p      = "{$n}[seizoenen][{$idx}]";
		$empty  = null === $season;
		$season = $season ?? array(
			'label'      => '',
			'van'        => '',
			'tot'        => '',
			'pasen'      => false,
			'dagen'      => array(),
			'keuken_van' => '',
			'keuken_tot' => '',
			'opmerking'  => '',
		);
		$van = $season['van'] ? implode( '-', array_reverse( explode( '-', $season['van'] ) ) ) : '';
		$tot = $season['tot'] ? implode( '-', array_reverse( explode( '-', $season['tot'] ) ) ) : '';
		printf( '<details class="valk-season" %s><summary>%s</summary>', $empty ? '' : 'open', $empty ? '+ Nieuwe periode toevoegen' : esc_html( $season['label'] ? $season['label'] : 'Periode ' . ( $idx + 1 ) ) );
		echo '<div class="valk-season-grid">';
		printf( '<label>Naam periode<br><input type="text" name="%s[label]" value="%s" placeholder="bv. Zomer"></label>', esc_attr( $p ), esc_attr( $season['label'] ) );
		printf( '<label>Van (dag-maand)<br><input type="text" name="%s[van]" value="%s" placeholder="01-04" size="6"></label>', esc_attr( $p ), esc_attr( $van ) );
		printf( '<label>Tot en met (dag-maand)<br><input type="text" name="%s[tot]" value="%s" placeholder="30-06" size="6"></label>', esc_attr( $p ), esc_attr( $tot ) );
		printf( '<label class="valk-check"><input type="checkbox" name="%s[pasen]" value="1" %s> Periode begint op Paaszondag (de datum bij "van" wordt dan genegeerd)</label>', esc_attr( $p ), checked( ! empty( $season['pasen'] ), true, false ) );
		echo '</div><table class="widefat valk-days"><thead><tr><th>Dag</th><th>Open</th><th>Sluit</th><th>Gesloten</th></tr></thead><tbody>';
		foreach ( VALKENISSE_DAY_NAMES as $num => $day_name ) {
			$d = $season['dagen'][ $num ] ?? array( 'open' => '', 'sluit' => '', 'dicht' => false );
			printf(
				'<tr><th scope="row">%1$s</th><td><input type="time" name="%2$s[dagen][%3$d][open]" value="%4$s"></td><td><input type="time" name="%2$s[dagen][%3$d][sluit]" value="%5$s"></td><td><label><input type="checkbox" name="%2$s[dagen][%3$d][dicht]" value="1" %6$s> gesloten</label></td></tr>',
				esc_html( $day_name ),
				esc_attr( $p ),
				(int) $num,
				esc_attr( $d['open'] ),
				esc_attr( $d['sluit'] ),
				checked( ! $empty && ! empty( $d['dicht'] ), true, false )
			);
		}
		echo '</tbody></table><div class="valk-season-grid">';
		printf( '<label>Keuken open vanaf<br><input type="time" name="%s[keuken_van]" value="%s"></label>', esc_attr( $p ), esc_attr( $season['keuken_van'] ) );
		printf( '<label>Keuken open tot<br><input type="time" name="%s[keuken_tot]" value="%s"></label>', esc_attr( $p ), esc_attr( $season['keuken_tot'] ) );
		printf( '<label class="valk-wide">Opmerking (optioneel)<br><input type="text" name="%s[opmerking]" value="%s" class="regular-text"></label>', esc_attr( $p ), esc_attr( $season['opmerking'] ) );
		echo '</div>';
		if ( ! $empty ) {
			echo '<p class="description">Periode verwijderen? Maak naam, van en tot leeg en sla op.</p>';
		}
		echo '</details>';
	}

	echo '<h2>Buiten de periodes</h2><table class="form-table" role="presentation">';
	valkenisse_field( 'Tekst', sprintf( '<input type="text" name="%s[buiten_seizoen]" value="%s" class="large-text">', esc_attr( $n ), esc_attr( $s['buiten_seizoen'] ) ), 'Wordt getoond op dagen die in geen enkele periode vallen.' );
	echo '</table>';

	echo '<h2>Afwijkende dagen</h2><p>Bijvoorbeeld feestdagen, een besloten feest of een extra sluitingsdag. Deze gaan vóór de gewone periode. Verlopen data kunt u leegmaken.</p>';
	echo '<table class="widefat valk-days"><thead><tr><th>Datum</th><th>Gesloten</th><th>Open</th><th>Sluit</th><th>Opmerking (bv. "Besloten gezelschap")</th></tr></thead><tbody>';
	$exceptions = $s['uitzonderingen'];
	for ( $i = 0; $i < VALKENISSE_MAX_EXCEPT; $i++ ) {
		$e = $exceptions[ $i ] ?? array( 'datum' => '', 'dicht' => false, 'open' => '', 'sluit' => '', 'opmerking' => '' );
		$p = "{$n}[uitzonderingen][{$i}]";
		printf(
			'<tr><td><input type="date" name="%1$s[datum]" value="%2$s"></td><td><input type="checkbox" name="%1$s[dicht]" value="1" %3$s></td><td><input type="time" name="%1$s[open]" value="%4$s"></td><td><input type="time" name="%1$s[sluit]" value="%5$s"></td><td><input type="text" name="%1$s[opmerking]" value="%6$s" class="regular-text"></td></tr>',
			esc_attr( $p ),
			esc_attr( $e['datum'] ),
			checked( $e['dicht'], true, false ),
			esc_attr( $e['open'] ),
			esc_attr( $e['sluit'] ),
			esc_attr( $e['opmerking'] )
		);
	}
	echo '</tbody></table>';
}

function valkenisse_render_notice_tab( array $s, string $n ): void {
	$m = $s['mededeling'];
	echo '<p>Een korte mededeling bovenaan elke pagina, bijvoorbeeld "Vandaag vanaf 17:00 gesloten wegens besloten feest".</p><table class="form-table" role="presentation">';
	valkenisse_field( 'Tonen', sprintf( '<label><input type="checkbox" name="%s[mededeling][actief]" value="1" %s> Mededeling tonen op de website</label>', esc_attr( $n ), checked( $m['actief'], true, false ) ) );
	valkenisse_field( 'Tekst', sprintf( '<input type="text" name="%s[mededeling][tekst]" value="%s" class="large-text" maxlength="160">', esc_attr( $n ), esc_attr( $m['tekst'] ) ), 'Houd het kort (max. 160 tekens).' );
	valkenisse_field( 'Link (optioneel)', valkenisse_input( "{$n}[mededeling][link]", $m['link'], 'url' ) );
	valkenisse_field( 'Linktekst', valkenisse_input( "{$n}[mededeling][link_tekst]", $m['link_tekst'] ), 'Bijvoorbeeld "Lees meer".' );
	valkenisse_field( 'Automatisch verbergen na', valkenisse_input( "{$n}[mededeling][tot]", $m['tot'], 'date' ), 'Optioneel. Na deze datum verdwijnt de mededeling vanzelf.' );
	echo '</table>';
}

function valkenisse_render_forms_tab( array $s, string $n ): void {
	$f = $s['formulieren'];
	echo '<h2>Formulieren</h2><table class="form-table" role="presentation">';
	valkenisse_field( 'Aanvragen sturen naar', valkenisse_input( "{$n}[formulieren][ontvanger]", $f['ontvanger'], 'email' ), 'Reserveringen, studio- en feestaanvragen en contactberichten komen op dit adres binnen. Alle aanvragen zijn daarnaast terug te vinden onder "Aanvragen".' );
	valkenisse_field( 'Ontvangstbevestiging', sprintf( '<label><input type="checkbox" name="%s[formulieren][bevestiging]" value="1" %s> Stuur de gast automatisch een ontvangstbevestiging</label>', esc_attr( $n ), checked( $f['bevestiging'], true, false ) ) );
	valkenisse_field( 'Bewaartermijn (dagen)', valkenisse_input( "{$n}[formulieren][bewaartermijn]", $f['bewaartermijn'], 'number', 'min="7" max="730" style="width:7em"' ), 'Aanvragen worden na deze termijn automatisch verwijderd (AVG). Vermeld dezelfde termijn in het privacybeleid.' );
	echo '</table><h2>Zoekmachines</h2><table class="form-table" role="presentation">';
	valkenisse_field( 'Structured data', sprintf( '<label><input type="checkbox" name="%s[seo][schema]" value="1" %s> Restaurantgegevens en openingstijden als Schema.org-data aan Google doorgeven</label>', esc_attr( $n ), checked( $s['seo']['schema'], true, false ) ), 'Zet uit als uw SEO-plugin al een LocalBusiness/Restaurant-schema voor dit bedrijf uitvoert.' );
	echo '</table>';
}
