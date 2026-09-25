<?php
/**
 * Stallingsaanvraag-wizard.
 *
 * - Werkt zonder JavaScript (alle stappen onder elkaar); met JavaScript wordt
 *   het een stap-voor-stap wizard (assets/js/aanvraag.js, ± 3 kB).
 * - Spambeveiliging zonder externe diensten: honeypot, tijdcontrole met
 *   ondertekend tijdstempel, limiet per IP en inhoudscontrole.
 * - Iedere aanvraag gaat per e-mail naar Van Keulen én wordt als back-up in
 *   WordPress bewaard (Van Keulen → Aanvragen), met automatische verwijdering
 *   na de ingestelde bewaartermijn.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_vk_aanvraag', 'vk_verwerk_aanvraag' );
add_action( 'admin_post_vk_aanvraag', 'vk_verwerk_aanvraag' );

/**
 * Ondertekend tijdstempel voor de tijdcontrole.
 */
function vk_form_token() {
	$t = time();
	return $t . '.' . substr( hash_hmac( 'sha256', (string) $t, wp_salt( 'nonce' ) ), 0, 20 );
}

/**
 * Formuliervelden: sleutel => label.
 */
function vk_form_labels() {
	return array(
		'type'        => 'Wat wilt u stallen?',
		'type_anders' => 'Omschrijving',
		'merk'        => 'Merk / type',
		'lengte'      => 'Lengte',
		'breedte'     => 'Breedte',
		'hoogte'      => 'Hoogte',
		'periode'     => 'Gewenste periode',
		'vanaf'       => 'Vanaf wanneer?',
		'voorkeur'    => 'Voorkeur',
		'naam'        => 'Naam',
		'email'       => 'E-mailadres',
		'telefoon'    => 'Telefoonnummer',
		'woonplaats'  => 'Woonplaats',
		'opmerking'   => 'Opmerking',
		'privacy'     => 'Privacy',
	);
}

/**
 * Keuzes binnen-/buitenstalling.
 */
function vk_voorkeur_opties() {
	return array(
		'binnen' => 'Binnenstalling',
		'buiten' => 'Buitenstalling',
		'geen'   => 'Geen voorkeur',
	);
}

/**
 * Blok: het aanvraagformulier.
 *
 * @param array $a Attributen.
 */
function vk_render_aanvraagformulier( $a ) {
	$status = isset( $_GET['aanvraag'] ) ? sanitize_key( $_GET['aanvraag'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	if ( 'bedankt' === $status ) {
		return '<div class="' . esc_attr( vk_cls( $a, 'vk-aanvraag vk-aanvraag--bedankt' ) ) . '" id="aanvraag" role="status" tabindex="-1">'
			. '<div class="vk-aanvraag__bedankt">' . vk_icon( 'vink' )
			. '<h2>Bedankt voor uw aanvraag</h2>'
			. '<p>Van Keulen Caravanstalling neemt contact met u op over de mogelijkheden.</p>'
			. ( vk_get( 'bevestiging_klant' ) ? '<p class="vk-klein">U ontvangt ook een bevestiging per e-mail. Niets ontvangen? Kijk dan even in uw map met ongewenste e-mail.</p>' : '' )
			. '<p><a href="' . esc_url( remove_query_arg( 'aanvraag' ) ) . '#aanvraag">Nog een aanvraag doen</a></p>'
			. '</div></div>';
	}

	wp_enqueue_script( 'vk-aanvraag' );

	// Foutmeldingen en eerder ingevulde waarden na een mislukte verzending.
	$errors = array();
	$old    = array();
	if ( ! empty( $_GET['vk_fout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$data = get_transient( 'vk_form_' . sanitize_key( $_GET['vk_fout'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( is_array( $data ) ) {
			$errors = $data['errors'];
			$old    = $data['old'];
		}
	}
	if ( empty( $old['type'] ) && ! empty( $_GET['type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$old['type'] = sanitize_key( $_GET['type'] ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	$v   = function ( $k ) use ( $old ) {
		return esc_attr( $old[ $k ] ?? '' );
	};
	$err = function ( $k ) use ( $errors ) {
		return isset( $errors[ $k ] ) ? '<p class="vk-veld__fout" id="fout-' . esc_attr( $k ) . '">' . esc_html( $errors[ $k ] ) . '</p>' : '';
	};
	$inv = function ( $k ) use ( $errors ) {
		return isset( $errors[ $k ] ) ? ' aria-invalid="true" aria-describedby="fout-' . esc_attr( $k ) . '"' : '';
	};
	$req = '<span class="vk-verplicht" aria-hidden="true">*</span>';

	$objecten = vk_objecten_actief();
	$periodes = vk_periodes_actief();
	$privacy  = get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacybeleid/' );
	$bron     = get_permalink() ? get_permalink() : vk_aanvraag_url();

	ob_start();
	?>
	<div class="<?php echo esc_attr( vk_cls( $a, 'vk-aanvraag' ) ); ?>" id="aanvraag">
		<form class="vk-aanvraag__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate data-vk-wizard>
			<input type="hidden" name="action" value="vk_aanvraag">
			<input type="hidden" name="vk_t" value="<?php echo esc_attr( vk_form_token() ); ?>">
			<input type="hidden" name="vk_bron" value="<?php echo esc_url( $bron ); ?>">
			<div class="vk-hp" aria-hidden="true"><label>Laat dit veld leeg <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

			<div class="vk-aanvraag__voortgang" hidden>
				<p class="vk-aanvraag__stapinfo" aria-live="polite"></p>
				<ol class="vk-aanvraag__stappen" aria-hidden="true"><li>Object</li><li>Afmetingen</li><li>Periode</li><li>Gegevens</li></ol>
				<div class="vk-aanvraag__balk"><span></span></div>
			</div>

			<?php if ( $errors ) : ?>
				<div class="vk-aanvraag__fouten" role="alert" tabindex="-1">
					<p><strong>Controleer de volgende velden:</strong></p>
					<ul>
						<?php foreach ( $errors as $k => $m ) : ?>
							<li><a href="#veld-<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $m ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<p class="vk-aanvraag__uitleg">Velden met <span class="vk-verplicht">*</span> zijn verplicht.</p>

			<fieldset class="vk-stap" data-stap="1">
				<legend><span class="vk-stap__nr">Stap 1</span> Wat wilt u stallen? <?php echo $req; // phpcs:ignore ?></legend>
				<div class="vk-keuzes" id="veld-type" role="radiogroup">
					<?php foreach ( $objecten as $k => $l ) : ?>
						<label class="vk-keuze">
							<input type="radio" name="type" value="<?php echo esc_attr( $k ); ?>" required <?php checked( $old['type'] ?? '', $k ); ?><?php echo $inv( 'type' ); // phpcs:ignore ?>>
							<span><?php echo esc_html( $l ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				<?php echo $err( 'type' ); // phpcs:ignore ?>
				<div class="vk-veld" data-toon-bij="anders">
					<label for="veld-type_anders">Wat wilt u stallen? <?php echo $req; // phpcs:ignore ?></label>
					<input type="text" id="veld-type_anders" name="type_anders" value="<?php echo $v( 'type_anders' ); // phpcs:ignore ?>" maxlength="120"<?php echo $inv( 'type_anders' ); // phpcs:ignore ?>>
					<?php echo $err( 'type_anders' ); // phpcs:ignore ?>
				</div>
				<div class="vk-veld">
					<label for="veld-merk">Merk / type <span class="vk-optioneel">(optioneel)</span></label>
					<input type="text" id="veld-merk" name="merk" value="<?php echo $v( 'merk' ); // phpcs:ignore ?>" maxlength="120" placeholder="Bijv. Hobby De Luxe 540">
				</div>
			</fieldset>

			<fieldset class="vk-stap" data-stap="2">
				<legend><span class="vk-stap__nr">Stap 2</span> Afmetingen</legend>
				<p class="vk-stap__hulp">In meters, inclusief dissel. Een schatting is prima.</p>
				<div class="vk-rij vk-rij--3">
					<div class="vk-veld">
						<label for="veld-lengte">Lengte <?php echo $req; // phpcs:ignore ?></label>
						<div class="vk-eenheid"><input type="text" inputmode="decimal" id="veld-lengte" name="lengte" value="<?php echo $v( 'lengte' ); // phpcs:ignore ?>" required pattern="[0-9]+([.,][0-9]{1,2})?" placeholder="7,20"<?php echo $inv( 'lengte' ); // phpcs:ignore ?>><span>m</span></div>
						<?php echo $err( 'lengte' ); // phpcs:ignore ?>
					</div>
					<div class="vk-veld">
						<label for="veld-breedte">Breedte <?php echo $req; // phpcs:ignore ?></label>
						<div class="vk-eenheid"><input type="text" inputmode="decimal" id="veld-breedte" name="breedte" value="<?php echo $v( 'breedte' ); // phpcs:ignore ?>" required pattern="[0-9]+([.,][0-9]{1,2})?" placeholder="2,30"<?php echo $inv( 'breedte' ); // phpcs:ignore ?>><span>m</span></div>
						<?php echo $err( 'breedte' ); // phpcs:ignore ?>
					</div>
					<div class="vk-veld">
						<label for="veld-hoogte">Hoogte <span class="vk-optioneel">(indien bekend)</span></label>
						<div class="vk-eenheid"><input type="text" inputmode="decimal" id="veld-hoogte" name="hoogte" value="<?php echo $v( 'hoogte' ); // phpcs:ignore ?>" pattern="[0-9]+([.,][0-9]{1,2})?" placeholder="2,60"<?php echo $inv( 'hoogte' ); // phpcs:ignore ?>><span>m</span></div>
						<?php echo $err( 'hoogte' ); // phpcs:ignore ?>
					</div>
				</div>
			</fieldset>

			<fieldset class="vk-stap" data-stap="3">
				<legend><span class="vk-stap__nr">Stap 3</span> Gewenste periode</legend>
				<div class="vk-veld">
					<label for="veld-vanaf">Vanaf wanneer? <?php echo $req; // phpcs:ignore ?></label>
					<input type="date" id="veld-vanaf" name="vanaf" value="<?php echo $v( 'vanaf' ); // phpcs:ignore ?>" required min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"<?php echo $inv( 'vanaf' ); // phpcs:ignore ?>>
					<?php echo $err( 'vanaf' ); // phpcs:ignore ?>
				</div>
				<?php if ( $periodes ) : ?>
					<div class="vk-veld">
						<span class="vk-veld__label" id="label-periode">Periode <?php echo $req; // phpcs:ignore ?></span>
						<div class="vk-keuzes vk-keuzes--rij" id="veld-periode" role="radiogroup" aria-labelledby="label-periode">
							<?php foreach ( $periodes as $k => $l ) : ?>
								<label class="vk-keuze">
									<input type="radio" name="periode" value="<?php echo esc_attr( $k ); ?>" required <?php checked( $old['periode'] ?? '', $k ); ?>>
									<span><?php echo esc_html( $l ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php echo $err( 'periode' ); // phpcs:ignore ?>
					</div>
				<?php endif; ?>
				<?php if ( vk_get( 'voorkeur_tonen' ) ) : ?>
					<div class="vk-veld">
						<span class="vk-veld__label" id="label-voorkeur">Voorkeur <span class="vk-optioneel">(optioneel)</span></span>
						<div class="vk-keuzes vk-keuzes--rij" role="radiogroup" aria-labelledby="label-voorkeur">
							<?php foreach ( vk_voorkeur_opties() as $k => $l ) : ?>
								<label class="vk-keuze">
									<input type="radio" name="voorkeur" value="<?php echo esc_attr( $k ); ?>" <?php checked( $old['voorkeur'] ?? '', $k ); ?>>
									<span><?php echo esc_html( $l ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</fieldset>

			<fieldset class="vk-stap" data-stap="4">
				<legend><span class="vk-stap__nr">Stap 4</span> Uw gegevens</legend>
				<div class="vk-rij vk-rij--2">
					<div class="vk-veld">
						<label for="veld-naam">Naam <?php echo $req; // phpcs:ignore ?></label>
						<input type="text" id="veld-naam" name="naam" value="<?php echo $v( 'naam' ); // phpcs:ignore ?>" required autocomplete="name" maxlength="100"<?php echo $inv( 'naam' ); // phpcs:ignore ?>>
						<?php echo $err( 'naam' ); // phpcs:ignore ?>
					</div>
					<div class="vk-veld">
						<label for="veld-woonplaats">Woonplaats <?php echo $req; // phpcs:ignore ?></label>
						<input type="text" id="veld-woonplaats" name="woonplaats" value="<?php echo $v( 'woonplaats' ); // phpcs:ignore ?>" required autocomplete="address-level2" maxlength="100"<?php echo $inv( 'woonplaats' ); // phpcs:ignore ?>>
						<?php echo $err( 'woonplaats' ); // phpcs:ignore ?>
					</div>
					<div class="vk-veld">
						<label for="veld-email">E-mailadres <?php echo $req; // phpcs:ignore ?></label>
						<input type="email" id="veld-email" name="email" value="<?php echo $v( 'email' ); // phpcs:ignore ?>" required autocomplete="email" maxlength="150"<?php echo $inv( 'email' ); // phpcs:ignore ?>>
						<?php echo $err( 'email' ); // phpcs:ignore ?>
					</div>
					<div class="vk-veld">
						<label for="veld-telefoon">Telefoonnummer <?php echo $req; // phpcs:ignore ?></label>
						<input type="tel" id="veld-telefoon" name="telefoon" value="<?php echo $v( 'telefoon' ); // phpcs:ignore ?>" required autocomplete="tel" maxlength="30" pattern="[0-9+\(\)\s\-]{8,}"<?php echo $inv( 'telefoon' ); // phpcs:ignore ?>>
						<?php echo $err( 'telefoon' ); // phpcs:ignore ?>
					</div>
				</div>
				<div class="vk-veld">
					<label for="veld-opmerking">Opmerking <span class="vk-optioneel">(optioneel)</span></label>
					<textarea id="veld-opmerking" name="opmerking" rows="4" maxlength="2000"><?php echo esc_textarea( $old['opmerking'] ?? '' ); ?></textarea>
				</div>
				<div class="vk-veld vk-veld--check">
					<label for="veld-privacy">
						<input type="checkbox" id="veld-privacy" name="privacy" value="1" required<?php echo $inv( 'privacy' ); // phpcs:ignore ?>>
						<span>Ik ga akkoord dat Van Keulen Caravanstalling mijn gegevens gebruikt om contact met mij op te nemen over deze aanvraag. Lees het <a href="<?php echo esc_url( $privacy ); ?>" target="_blank">privacybeleid</a>. <?php echo $req; // phpcs:ignore ?></span>
					</label>
					<?php echo $err( 'privacy' ); // phpcs:ignore ?>
				</div>
				<button type="submit" class="vk-knop vk-knop--primair wp-element-button vk-aanvraag__verstuur">Verstuur mijn aanvraag</button>
				<p class="vk-klein">Vrijblijvend. U ontvangt een reactie van Van Keulen Caravanstalling.</p>
			</fieldset>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Decimaal getal (komma of punt) naar float of null.
 *
 * @param string $s Invoer.
 */
function vk_meters( $s ) {
	$s = str_replace( ',', '.', trim( (string) $s ) );
	return is_numeric( $s ) ? round( (float) $s, 2 ) : null;
}

/**
 * Verwerken van een verzonden aanvraag.
 */
function vk_verwerk_aanvraag() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- publiek formulier op gecachete pagina's; beveiligd via honeypot, ondertekend tijdstempel en limiet.
	$post = wp_unslash( $_POST );

	$fallback = strtok( vk_aanvraag_url(), '#' );
	$bron     = wp_validate_redirect( esc_url_raw( (string) ( $post['vk_bron'] ?? '' ) ), $fallback );
	$bron     = $bron ? strtok( $bron, '#' ) : $fallback;
	$bron     = remove_query_arg( array( 'aanvraag', 'vk_fout', 'type' ), $bron );

	$bedankt = add_query_arg( 'aanvraag', 'bedankt', $bron ) . '#aanvraag';

	// 1. Spamcontroles. Bij spam doen we alsof het gelukt is (geen hint voor bots).
	$is_spam = ! empty( $post['website'] );
	$token   = explode( '.', (string) ( $post['vk_t'] ?? '' ) );
	if ( 2 !== count( $token ) || ! hash_equals( substr( hash_hmac( 'sha256', $token[0], wp_salt( 'nonce' ) ), 0, 20 ), $token[1] ) ) {
		$is_spam = true;
	} elseif ( time() - (int) $token[0] < 4 ) {
		$is_spam = true; // Sneller dan een mens kan invullen.
	}
	if ( $is_spam ) {
		wp_safe_redirect( $bedankt, 303 );
		exit;
	}

	$ip_key = 'vk_rl_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) . wp_salt() );
	$pogingen = (int) get_transient( $ip_key );
	if ( $pogingen >= 5 ) {
		wp_die( 'U heeft in korte tijd meerdere aanvragen verstuurd. Probeer het later opnieuw of neem telefonisch of per e-mail contact op.', 'Even geduld', array( 'response' => 429, 'back_link' => true ) );
	}

	// 2. Opschonen.
	$d = array(
		'type'        => sanitize_key( $post['type'] ?? '' ),
		'type_anders' => sanitize_text_field( $post['type_anders'] ?? '' ),
		'merk'        => sanitize_text_field( $post['merk'] ?? '' ),
		'lengte'      => sanitize_text_field( $post['lengte'] ?? '' ),
		'breedte'     => sanitize_text_field( $post['breedte'] ?? '' ),
		'hoogte'      => sanitize_text_field( $post['hoogte'] ?? '' ),
		'periode'     => sanitize_key( $post['periode'] ?? '' ),
		'vanaf'       => sanitize_text_field( $post['vanaf'] ?? '' ),
		'voorkeur'    => sanitize_key( $post['voorkeur'] ?? '' ),
		'naam'        => sanitize_text_field( $post['naam'] ?? '' ),
		'email'       => sanitize_email( $post['email'] ?? '' ),
		'telefoon'    => sanitize_text_field( $post['telefoon'] ?? '' ),
		'woonplaats'  => sanitize_text_field( $post['woonplaats'] ?? '' ),
		'opmerking'   => sanitize_textarea_field( $post['opmerking'] ?? '' ),
		'privacy'     => empty( $post['privacy'] ) ? '' : '1',
	);
	// phpcs:enable

	// 3. Valideren.
	$e = array();
	if ( ! isset( vk_objecten_actief()[ $d['type'] ] ) ) {
		$e['type'] = 'Kies wat u wilt stallen.';
	}
	if ( 'anders' === $d['type'] && '' === $d['type_anders'] ) {
		$e['type_anders'] = 'Omschrijf wat u wilt stallen.';
	}
	foreach ( array( 'lengte' => true, 'breedte' => true, 'hoogte' => false ) as $k => $verplicht ) {
		$m = vk_meters( $d[ $k ] );
		if ( '' === $d[ $k ] ) {
			if ( $verplicht ) {
				$e[ $k ] = 'Vul de ' . $k . ' in (in meters).';
			}
		} elseif ( null === $m || $m <= 0 || $m > 40 ) {
			$e[ $k ] = 'Vul bij ' . $k . ' een getal in meters in, bijvoorbeeld 2,30.';
		}
	}
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d['vanaf'] ) ) {
		$e['vanaf'] = 'Kies vanaf wanneer u wilt stallen.';
	}
	if ( vk_periodes_actief() && ! isset( vk_periodes_actief()[ $d['periode'] ] ) ) {
		$e['periode'] = 'Kies de gewenste periode.';
	}
	if ( '' !== $d['voorkeur'] && ! isset( vk_voorkeur_opties()[ $d['voorkeur'] ] ) ) {
		$d['voorkeur'] = '';
	}
	if ( mb_strlen( $d['naam'] ) < 2 ) {
		$e['naam'] = 'Vul uw naam in.';
	}
	if ( ! is_email( $d['email'] ) ) {
		$e['email'] = 'Vul een geldig e-mailadres in.';
	}
	if ( strlen( preg_replace( '/\D/', '', $d['telefoon'] ) ) < 8 ) {
		$e['telefoon'] = 'Vul een geldig telefoonnummer in.';
	}
	if ( '' === $d['woonplaats'] ) {
		$e['woonplaats'] = 'Vul uw woonplaats in.';
	}
	if ( '1' !== $d['privacy'] ) {
		$e['privacy'] = 'Geef toestemming om contact met u op te nemen.';
	}

	// Inhoudscontrole: links in naam/woonplaats of veel links in de opmerking = spam.
	if ( preg_match( '#https?://|www\.#i', $d['naam'] . $d['woonplaats'] . $d['merk'] ) || preg_match_all( '#https?://#i', $d['opmerking'] ) > 1 ) {
		wp_safe_redirect( $bedankt, 303 );
		exit;
	}

	if ( $e ) {
		$key = wp_generate_password( 12, false );
		unset( $d['privacy'] );
		set_transient( 'vk_form_' . strtolower( $key ), array( 'errors' => $e, 'old' => $d ), 15 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'vk_fout', strtolower( $key ), $bron ) . '#aanvraag', 303 );
		exit;
	}

	set_transient( $ip_key, $pogingen + 1, HOUR_IN_SECONDS );

	// 4. Opslaan als back-up.
	$object = 'anders' === $d['type'] ? 'Anders: ' . $d['type_anders'] : vk_objecten()[ $d['type'] ];
	$id     = wp_insert_post(
		array(
			'post_type'   => 'vk_aanvraag',
			'post_status' => 'private',
			'post_title'  => $object . ' – ' . $d['naam'],
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		foreach ( $d as $k => $val ) {
			update_post_meta( $id, '_vk_' . $k, $val );
		}
		update_post_meta( $id, '_vk_bron', $bron );
	}

	// 5. E-mail naar Van Keulen.
	$regels = vk_aanvraag_regels( $d );
	$body   = "Nieuwe stallingsaanvraag via de website\n\n";
	foreach ( $regels as $label => $waarde ) {
		$body .= str_pad( $label . ':', 22 ) . $waarde . "\n";
	}
	$body .= "\nBeantwoorden kan direct via ‘Antwoorden’ in uw e-mailprogramma.\n";
	if ( $id && ! is_wp_error( $id ) ) {
		$body .= 'Bekijken in WordPress: ' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . "\n";
	}

	$verzonden = wp_mail(
		vk_aanvraag_ontvanger(),
		'Stallingsaanvraag: ' . $object . ' – ' . $d['naam'],
		$body,
		array( 'Reply-To: ' . str_replace( array( "\r", "\n", ',' ), '', $d['naam'] ) . ' <' . $d['email'] . '>' )
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_vk_mail_verzonden', $verzonden ? 'ja' : 'nee' );
	}

	// 6. Ontvangstbevestiging voor de klant.
	if ( vk_get( 'bevestiging_klant' ) ) {
		$klant  = 'Beste ' . $d['naam'] . ",\n\n";
		$klant .= 'Bedankt voor uw aanvraag voor een stallingsplaats bij ' . vk_get( 'bedrijfsnaam' ) . ". Wij nemen contact met u op over de mogelijkheden.\n\n";
		$klant .= "Uw aanvraag:\n";
		foreach ( $regels as $label => $waarde ) {
			$klant .= '- ' . $label . ': ' . $waarde . "\n";
		}
		$klant .= "\nMet vriendelijke groet,\n" . vk_get( 'bedrijfsnaam' ) . "\n" . vk_adres_regel() . "\n";
		if ( vk_filled( 'telefoon' ) ) {
			$klant .= 'Tel. ' . vk_get( 'telefoon' ) . "\n";
		}
		$klant .= home_url( '/' ) . "\n";
		wp_mail( $d['email'], 'Uw stallingsaanvraag bij ' . vk_get( 'bedrijfsnaam' ), $klant, array( 'Reply-To: ' . vk_aanvraag_ontvanger() ) );
	}

	wp_safe_redirect( $bedankt, 303 );
	exit;
}

/**
 * Leesbare regels van een aanvraag.
 *
 * @param array $d Gegevens.
 * @return array<string,string>
 */
function vk_aanvraag_regels( $d ) {
	$m = function ( $k ) use ( $d ) {
		$x = vk_meters( $d[ $k ] ?? '' );
		return null === $x ? '–' : number_format_i18n( $x, 2 ) . ' m';
	};
	return array(
		'Object'      => 'anders' === $d['type'] ? 'Anders: ' . $d['type_anders'] : ( vk_objecten()[ $d['type'] ] ?? $d['type'] ),
		'Merk / type' => $d['merk'] ? $d['merk'] : '–',
		'Lengte'      => $m( 'lengte' ),
		'Breedte'     => $m( 'breedte' ),
		'Hoogte'      => $m( 'hoogte' ),
		'Vanaf'       => $d['vanaf'] ? wp_date( 'j F Y', strtotime( $d['vanaf'] . ' 12:00' ) ) : '–',
		'Periode'     => vk_periodes()[ $d['periode'] ] ?? '–',
		'Voorkeur'    => vk_voorkeur_opties()[ $d['voorkeur'] ] ?? '–',
		'Naam'        => $d['naam'],
		'E-mail'      => $d['email'],
		'Telefoon'    => $d['telefoon'],
		'Woonplaats'  => $d['woonplaats'],
		'Opmerking'   => $d['opmerking'] ? $d['opmerking'] : '–',
	);
}

/* ---------------------------------------------------------------------------
 * Beheer: overzicht en detailweergave van aanvragen.
 * ------------------------------------------------------------------------ */

add_filter(
	'manage_vk_aanvraag_posts_columns',
	function () {
		return array(
			'cb'        => '<input type="checkbox">',
			'title'     => 'Aanvraag',
			'vk_tel'    => 'Telefoon',
			'vk_plaats' => 'Woonplaats',
			'vk_mail'   => 'E-mail verzonden',
			'date'      => 'Ontvangen',
		);
	}
);

add_action(
	'manage_vk_aanvraag_posts_custom_column',
	function ( $col, $id ) {
		$map = array(
			'vk_tel'    => '_vk_telefoon',
			'vk_plaats' => '_vk_woonplaats',
			'vk_mail'   => '_vk_mail_verzonden',
		);
		if ( isset( $map[ $col ] ) ) {
			echo esc_html( get_post_meta( $id, $map[ $col ], true ) );
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes_vk_aanvraag',
	function () {
		remove_meta_box( 'submitdiv', 'vk_aanvraag', 'side' );
		add_meta_box(
			'vk_aanvraag_details',
			'Gegevens van de aanvraag',
			function ( $post ) {
				$d = array();
				foreach ( array_keys( vk_form_labels() ) as $k ) {
					$d[ $k ] = (string) get_post_meta( $post->ID, '_vk_' . $k, true );
				}
				echo '<table class="widefat striped"><tbody>';
				foreach ( vk_aanvraag_regels( $d ) as $label => $waarde ) {
					echo '<tr><th style="width:180px">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $waarde ) ) . '</td></tr>';
				}
				echo '<tr><th>Ontvangen</th><td>' . esc_html( get_the_date( 'j F Y H:i', $post ) ) . '</td></tr>';
				echo '<tr><th>E-mail verzonden</th><td>' . esc_html( get_post_meta( $post->ID, '_vk_mail_verzonden', true ) ) . '</td></tr>';
				echo '</tbody></table>';
				if ( is_email( $d['email'] ) ) {
					echo '<p><a class="button button-primary" href="mailto:' . esc_attr( $d['email'] ) . '?subject=' . rawurlencode( 'Uw stallingsaanvraag bij ' . vk_get( 'bedrijfsnaam' ) ) . '">Beantwoorden per e-mail</a> ';
					echo '<a class="button" href="tel:' . esc_attr( vk_tel_e164( $d['telefoon'] ) ) . '">Bellen</a> ';
					echo '<a class="button button-link-delete" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Naar prullenbak</a></p>';
				}
			},
			'vk_aanvraag',
			'normal',
			'high'
		);
	}
);

/**
 * AVG: aanvragen ouder dan de bewaartermijn automatisch verwijderen.
 */
add_action(
	'vk_opschonen_aanvragen',
	function () {
		$maanden = max( 1, (int) vk_get( 'bewaartermijn' ) );
		$oud     = get_posts(
			array(
				'post_type'      => 'vk_aanvraag',
				'post_status'    => 'any',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'date_query'     => array( array( 'before' => $maanden . ' months ago' ) ),
			)
		);
		foreach ( $oud as $id ) {
			wp_delete_post( $id, true );
		}
	}
);
