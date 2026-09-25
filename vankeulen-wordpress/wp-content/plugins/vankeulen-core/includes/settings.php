<?php
/**
 * Beheerscherm "Van Keulen → Bedrijfsgegevens".
 *
 * Eén overzichtelijk scherm voor de eigenaar: contactgegevens,
 * openingstijden, mededeling, beschikbaarheid, prijzen en formulier.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

const VK_CAP = 'edit_pages';

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			'Van Keulen',
			'Van Keulen',
			VK_CAP,
			'vk-gegevens',
			'vk_render_settings_page',
			'dashicons-store',
			3
		);
		add_submenu_page( 'vk-gegevens', 'Bedrijfsgegevens', 'Bedrijfsgegevens', VK_CAP, 'vk-gegevens', 'vk_render_settings_page' );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'vk_settings_group',
			'vk_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'vk_sanitize_settings',
				'default'           => vk_default_settings(),
			)
		);
	}
);

// Redacteuren (de eigenaar) mogen de bedrijfsgegevens opslaan.
add_filter(
	'option_page_capability_vk_settings_group',
	function () {
		return VK_CAP;
	}
);

/**
 * Opschonen en valideren.
 *
 * @param array $in Invoer.
 */
function vk_sanitize_settings( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$def = vk_default_settings();
	$out = array();

	foreach ( array( 'bedrijfsnaam', 'straat', 'postcode', 'plaats', 'telefoon', 'whatsapp', 'kvk', 'lat', 'lng', 'mededeling_tekst', 'beschikbaarheid_tekst' ) as $k ) {
		$out[ $k ] = sanitize_text_field( $in[ $k ] ?? '' );
	}
	foreach ( array( 'email', 'aanvraag_email' ) as $k ) {
		$out[ $k ] = sanitize_email( $in[ $k ] ?? '' );
	}
	foreach ( array( 'google_profiel', 'mededeling_link' ) as $k ) {
		$out[ $k ] = esc_url_raw( trim( $in[ $k ] ?? '' ), array( 'https', 'http' ) );
	}
	foreach ( array( 'openingstijden_tekst', 'prijzen', 'prijzen_toelichting' ) as $k ) {
		$out[ $k ] = sanitize_textarea_field( $in[ $k ] ?? '' );
	}
	foreach ( array( 'mededeling_aan', 'prijzen_aan', 'voorkeur_tonen', 'bevestiging_klant' ) as $k ) {
		$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
	}

	$out['beschikbaarheid'] = array_key_exists( $in['beschikbaarheid'] ?? '', vk_beschikbaarheid_opties() ) ? $in['beschikbaarheid'] : 'onbekend';
	$out['bewaartermijn']   = max( 1, min( 60, absint( $in['bewaartermijn'] ?? 12 ) ) );
	$out['objecten']        = array_values( array_intersect( (array) ( $in['objecten'] ?? array() ), array_keys( vk_objecten() ) ) );
	$out['periodes']        = array_values( array_intersect( (array) ( $in['periodes'] ?? array() ), array_keys( vk_periodes() ) ) );

	if ( empty( $out['objecten'] ) ) {
		$out['objecten'] = $def['objecten'];
		add_settings_error( 'vk_settings', 'objecten', 'Kies minimaal één soort object voor het aanvraagformulier. De standaardkeuze is hersteld.' );
	}

	foreach ( array( 'lat', 'lng' ) as $k ) {
		if ( '' !== $out[ $k ] ) {
			$out[ $k ] = is_numeric( str_replace( ',', '.', $out[ $k ] ) ) ? (string) (float) str_replace( ',', '.', $out[ $k ] ) : '';
		}
	}

	$out['openingstijden'] = array();
	foreach ( array_keys( vk_dagen() ) as $dag ) {
		$r      = $in['openingstijden'][ $dag ] ?? array();
		$status = in_array( $r['status'] ?? '', array( '', 'open', 'afspraak', 'gesloten' ), true ) ? ( $r['status'] ?? '' ) : '';
		$van    = preg_match( '/^\d{2}:\d{2}$/', $r['van'] ?? '' ) ? $r['van'] : '';
		$tot    = preg_match( '/^\d{2}:\d{2}$/', $r['tot'] ?? '' ) ? $r['tot'] : '';
		$out['openingstijden'][ $dag ] = compact( 'status', 'van', 'tot' );
	}

	return $out;
}

/**
 * Beschikbaarheidsopties.
 */
function vk_beschikbaarheid_opties() {
	return array(
		'onbekend'    => 'Niet tonen op de website',
		'beschikbaar' => 'Er is plaats beschikbaar',
		'beperkt'     => 'Beperkt plaats beschikbaar',
		'wachtlijst'  => 'Vol – aanmelden voor de wachtlijst',
		'informeer'   => 'Informeer naar de beschikbaarheid',
	);
}

/**
 * Het beheerscherm.
 */
function vk_render_settings_page() {
	if ( ! current_user_can( VK_CAP ) ) {
		return;
	}
	$s    = vk_settings();
	$name = 'vk_settings';

	$text = function ( $key, $label, $help = '', $type = 'text', $attrs = '' ) use ( $s, $name ) {
		printf(
			'<tr><th scope="row"><label for="vk-%1$s">%2$s</label></th><td><input type="%5$s" class="regular-text" id="vk-%1$s" name="%3$s[%1$s]" value="%4$s" %6$s>%7$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_attr( is_scalar( $s[ $key ] ) ? $s[ $key ] : '' ),
			esc_attr( $type ),
			$attrs, // phpcs:ignore WordPress.Security.EscapeOutput -- vaste attributen.
			$help ? '<p class="description">' . wp_kses_post( $help ) . '</p>' : ''
		);
	};
	$area = function ( $key, $label, $help = '', $rows = 4 ) use ( $s, $name ) {
		printf(
			'<tr><th scope="row"><label for="vk-%1$s">%2$s</label></th><td><textarea class="large-text" rows="%5$d" id="vk-%1$s" name="%3$s[%1$s]">%4$s</textarea>%6$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $name ),
			esc_textarea( $s[ $key ] ),
			(int) $rows,
			$help ? '<p class="description">' . wp_kses_post( $help ) . '</p>' : ''
		);
	};
	$check = function ( $key, $label, $help = '' ) use ( $s, $name ) {
		printf(
			'<tr><th scope="row">%2$s</th><td><label><input type="checkbox" name="%3$s[%1$s]" value="1" %4$s> %2$s</label>%5$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $name ),
			checked( ! empty( $s[ $key ] ), true, false ),
			$help ? '<p class="description">' . wp_kses_post( $help ) . '</p>' : ''
		);
	};
	?>
	<div class="wrap vk-admin">
		<h1>Bedrijfsgegevens Van Keulen Caravanstalling</h1>
		<p class="vk-admin__intro">Alles wat u hier invult, verschijnt automatisch op de juiste plekken: header, footer, contactpagina, belknoppen, WhatsApp-knop, het aanvraagformulier en de gegevens voor Google (schema.org). Lege velden tonen op de website <code><?php echo esc_html( VK_PLACEHOLDER ); ?></code> of worden verborgen.</p>
		<nav class="vk-admin__nav">
			<a href="#vk-contact">Contactgegevens</a>
			<a href="#vk-tijden">Openingstijden</a>
			<a href="#vk-mededeling">Mededeling &amp; beschikbaarheid</a>
			<a href="#vk-prijzen">Prijzen</a>
			<a href="#vk-formulier">Aanvraagformulier</a>
		</nav>
		<?php settings_errors( 'vk_settings' ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'vk_settings_group' ); ?>

			<h2 id="vk-contact">Contactgegevens</h2>
			<table class="form-table" role="presentation">
				<?php
				$text( 'bedrijfsnaam', 'Bedrijfsnaam' );
				$text( 'straat', 'Straat en huisnummer' );
				$text( 'postcode', 'Postcode' );
				$text( 'plaats', 'Plaats' );
				$text( 'telefoon', 'Telefoonnummer', 'Bijvoorbeeld <code>0118 123 456</code> of <code>06 12 34 56 78</code>. Wordt overal een “bel direct”-knop.', 'tel' );
				$text( 'whatsapp', 'Zakelijk WhatsApp-nummer', 'Alleen invullen als dit nummer WhatsApp-berichten ontvangt. Leeg = geen WhatsApp-knop op de website.', 'tel' );
				$text( 'email', 'E-mailadres (openbaar)', '', 'email' );
				$text( 'kvk', 'KvK-nummer', 'Optioneel. Wordt getoond in de footer en aan Google doorgegeven.' );
				$text( 'google_profiel', 'Link naar Google Bedrijfsprofiel', 'Open uw bedrijf in Google Maps → Delen → Link kopiëren. Wordt gebruikt voor “Bekijk locatie” en voor Google (sameAs).', 'url' );
				$text( 'lat', 'Breedtegraad (optioneel)', 'Voor een exacte kaart- en routelocatie, bijv. <code>51.4850</code>. Rechtsklik in Google Maps op de ingang van de stalling om de coördinaten te kopiëren.' );
				$text( 'lng', 'Lengtegraad (optioneel)', 'Bijv. <code>3.5260</code>.' );
				?>
			</table>

			<h2 id="vk-tijden">Openingstijden / contactmomenten</h2>
			<p>Vul per dag in wanneer klanten terecht kunnen (brengen/halen of bellen). Laat alles op “niet tonen” staan als u geen vaste tijden hanteert.</p>
			<table class="widefat striped vk-admin__tijden">
				<thead><tr><th>Dag</th><th>Status</th><th>Van</th><th>Tot</th></tr></thead>
				<tbody>
				<?php foreach ( vk_dagen() as $dag => $label ) : $r = $s['openingstijden'][ $dag ] ?? array(); ?>
					<tr>
						<td><?php echo esc_html( $label ); ?></td>
						<td>
							<select name="vk_settings[openingstijden][<?php echo esc_attr( $dag ); ?>][status]">
								<option value="" <?php selected( $r['status'] ?? '', '' ); ?>>Niet tonen</option>
								<option value="open" <?php selected( $r['status'] ?? '', 'open' ); ?>>Geopend</option>
								<option value="afspraak" <?php selected( $r['status'] ?? '', 'afspraak' ); ?>>Op afspraak</option>
								<option value="gesloten" <?php selected( $r['status'] ?? '', 'gesloten' ); ?>>Gesloten</option>
							</select>
						</td>
						<td><input type="time" name="vk_settings[openingstijden][<?php echo esc_attr( $dag ); ?>][van]" value="<?php echo esc_attr( $r['van'] ?? '' ); ?>"></td>
						<td><input type="time" name="vk_settings[openingstijden][<?php echo esc_attr( $dag ); ?>][tot]" value="<?php echo esc_attr( $r['tot'] ?? '' ); ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<table class="form-table" role="presentation">
				<?php $area( 'openingstijden_tekst', 'Toelichting', 'Bijvoorbeeld: “Brengen en ophalen van uw caravan gaat op afspraak.”', 3 ); ?>
			</table>

			<h2 id="vk-mededeling">Mededeling &amp; beschikbaarheid</h2>
			<table class="form-table" role="presentation">
				<?php
				$check( 'mededeling_aan', 'Mededeling tonen', 'Een smalle balk boven aan iedere pagina, bijvoorbeeld “Winterstalling 2026–2027: aanmelden kan tot 1 oktober”.' );
				$text( 'mededeling_tekst', 'Tekst mededeling' );
				$text( 'mededeling_link', 'Link bij mededeling (optioneel)', '', 'url' );
				?>
				<tr>
					<th scope="row"><label for="vk-beschikbaarheid">Beschikbaarheid</label></th>
					<td>
						<select id="vk-beschikbaarheid" name="vk_settings[beschikbaarheid]">
							<?php foreach ( vk_beschikbaarheid_opties() as $k => $l ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $s['beschikbaarheid'], $k ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">Verschijnt als label in de hero, de aanvraag-oproep en de footer. Houd dit actueel – zet het op “Niet tonen” als u het niet zeker weet.</p>
					</td>
				</tr>
				<?php $text( 'beschikbaarheid_tekst', 'Toelichting beschikbaarheid (optioneel)', 'Bijv. “Binnenstalling: wachtlijst. Buitenstalling: plaats beschikbaar.”' ); ?>
			</table>

			<h2 id="vk-prijzen">Prijzen</h2>
			<table class="form-table" role="presentation">
				<?php
				$check( 'prijzen_aan', 'Vaste tarieven publiceren', 'Uit = de website toont “Benieuwd naar de prijs? Vraag vrijblijvend naar de mogelijkheden.”' );
				$area( 'prijzen', 'Tarieven', 'Eén tarief per regel, in de vorm <code>Omschrijving | Prijs | Toelichting</code>.<br>Voorbeeld: <code>Caravan binnenstalling tot 6 meter | € … per seizoen | incl. btw</code>', 6 );
				$area( 'prijzen_toelichting', 'Toelichting onder de tarieven', '', 3 );
				?>
			</table>

			<h2 id="vk-formulier">Aanvraagformulier</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Wat kan gestald worden?</th>
					<td>
						<?php foreach ( vk_objecten() as $k => $l ) : ?>
							<label style="display:inline-block;min-width:11em;margin:0 1em .4em 0"><input type="checkbox" name="vk_settings[objecten][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, (array) $s['objecten'], true ) ); ?>> <?php echo esc_html( $l ); ?></label>
						<?php endforeach; ?>
						<p class="description">Alleen aangevinkte objecten zijn in stap 1 te kiezen. Vink “Camper” alleen aan als campers daadwerkelijk gestald kunnen worden.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Stallingsperiodes</th>
					<td>
						<?php foreach ( vk_periodes() as $k => $l ) : ?>
							<label style="display:inline-block;min-width:11em;margin:0 1em .4em 0"><input type="checkbox" name="vk_settings[periodes][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, (array) $s['periodes'], true ) ); ?>> <?php echo esc_html( $l ); ?></label>
						<?php endforeach; ?>
						<p class="description">Toon alleen periodes die u daadwerkelijk aanbiedt. Niets aangevinkt = alleen de vraag “Vanaf wanneer?”.</p>
					</td>
				</tr>
				<?php
				$check( 'voorkeur_tonen', 'Vraag naar voorkeur binnen- of buitenstalling' );
				$text( 'aanvraag_email', 'Aanvragen ontvangen op', 'Leeg = het openbare e-mailadres hierboven.', 'email' );
				$check( 'bevestiging_klant', 'Klant ontvangt een ontvangstbevestiging per e-mail' );
				$text( 'bewaartermijn', 'Aanvragen bewaren (maanden)', 'Aanvragen worden ook in WordPress bewaard (menu Van Keulen → Aanvragen) en na deze termijn automatisch verwijderd (AVG).', 'number', 'min="1" max="60" style="width:6em"' );
				?>
			</table>

			<?php submit_button( 'Gegevens opslaan' ); ?>
		</form>
	</div>
	<style>
		.vk-admin__intro{max-width:760px;font-size:14px}
		.vk-admin__nav{display:flex;flex-wrap:wrap;gap:.5rem 1.25rem;margin:1rem 0;padding:.75rem 1rem;background:#fff;border:1px solid #dcdcde;position:sticky;top:32px;z-index:5}
		.vk-admin h2{margin-top:2.5rem;padding-top:1rem;border-top:1px solid #dcdcde;scroll-margin-top:90px}
		.vk-admin__tijden{max-width:640px}
	</style>
	<?php
}
