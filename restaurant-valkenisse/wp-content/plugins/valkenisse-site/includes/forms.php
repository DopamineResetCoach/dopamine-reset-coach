<?php
/**
 * Lichte, eigen formulieren (reservering, studio, feest, contact) zonder extra plugin:
 * spambeveiliging (honeypot + tijdscontrole + limiet per bezoeker), AVG-toestemming,
 * e-mail naar het restaurant, optionele ontvangstbevestiging en een kopie onder "Aanvragen"
 * die na de ingestelde bewaartermijn automatisch wordt verwijderd.
 *
 * Werkt zonder JavaScript en blijft correct als pagina's gecachet worden.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

function valkenisse_form_types(): array {
	$contact_fields = array(
		'naam'     => array( 'label' => 'Naam', 'type' => 'text', 'required' => true, 'autocomplete' => 'name' ),
		'email'    => array( 'label' => 'E-mail', 'type' => 'email', 'required' => true, 'autocomplete' => 'email' ),
		'telefoon' => array( 'label' => 'Telefoon', 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel' ),
	);
	$remarks = array( 'opmerkingen' => array( 'label' => 'Opmerkingen', 'type' => 'textarea', 'required' => false, 'wide' => true ) );

	return array(
		'reservering' => array(
			'titel'    => 'Reserveringsaanvraag',
			'knop'     => 'Verstuur reserveringsaanvraag',
			'bedankt'  => 'Bedankt voor uw reserveringsaanvraag! Uw reservering is definitief zodra wij deze hebben bevestigd. Heeft u haast? Bel ons gerust.',
			'velden'   => $contact_fields + array(
				'datum'    => array( 'label' => 'Datum', 'type' => 'date', 'required' => true, 'future' => true ),
				'tijd'     => array( 'label' => 'Tijd', 'type' => 'time', 'required' => true ),
				'personen' => array( 'label' => 'Aantal personen', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 100 ),
			) + $remarks,
		),
		'studio'      => array(
			'titel'    => 'Aanvraag studio',
			'knop'     => 'Beschikbaarheid aanvragen',
			'bedankt'  => 'Bedankt voor uw aanvraag! Wij laten u zo snel mogelijk weten of de studio beschikbaar is.',
			'velden'   => $contact_fields + array(
				'aankomst' => array( 'label' => 'Aankomst', 'type' => 'date', 'required' => true, 'future' => true ),
				'vertrek'  => array( 'label' => 'Vertrek', 'type' => 'date', 'required' => true, 'future' => true ),
				'personen' => array( 'label' => 'Aantal personen', 'type' => 'select', 'required' => true, 'options' => array( '1', '2' ) ),
			) + $remarks,
		),
		'feest'       => array(
			'titel'    => 'Aanvraag feest of partij',
			'knop'     => 'Vraag vrijblijvend informatie aan',
			'bedankt'  => 'Bedankt voor uw aanvraag! Wij nemen zo snel mogelijk contact met u op om de mogelijkheden te bespreken.',
			'velden'   => $contact_fields + array(
				'datum'       => array( 'label' => 'Datum', 'type' => 'date', 'required' => true, 'future' => true ),
				'personen'    => array( 'label' => 'Aantal personen', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 1000 ),
				'gelegenheid' => array( 'label' => 'Soort gelegenheid', 'type' => 'select', 'required' => true, 'options' => array( 'Verjaardag', 'Bruiloft', 'Feest of jubileum', 'Buffet', 'Catering', 'Anders' ) ),
			) + $remarks,
		),
		'contact'     => array(
			'titel'    => 'Contactformulier',
			'knop'     => 'Verstuur bericht',
			'bedankt'  => 'Bedankt voor uw bericht! Wij reageren zo snel mogelijk.',
			'velden'   => array(
				'naam'     => $contact_fields['naam'],
				'email'    => $contact_fields['email'],
				'telefoon' => array_merge( $contact_fields['telefoon'], array( 'required' => false ) ),
				'bericht'  => array( 'label' => 'Bericht', 'type' => 'textarea', 'required' => true, 'wide' => true ),
			),
		),
	);
}

/* Spamtoken: tijdstempel + handtekening (werkt ook in gecachte pagina's). */
function valkenisse_form_token(): string {
	$t = (string) time();
	return $t . '.' . substr( hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) ), 0, 20 );
}

function valkenisse_form_token_ok( string $token ): bool {
	$parts = explode( '.', $token );
	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
		return false;
	}
	if ( ! hash_equals( substr( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), 0, 20 ), $parts[1] ) ) {
		return false;
	}
	$age = time() - (int) $parts[0];
	return $age >= 3 && $age <= 14 * DAY_IN_SECONDS;
}

/* -------------------------------------------------------------------------
 * Weergave
 * ---------------------------------------------------------------------- */

function valkenisse_render_form_block( array $attrs ): string {
	$types = valkenisse_form_types();
	$soort = isset( $types[ $attrs['soort'] ?? '' ] ) ? $attrs['soort'] : 'contact';
	$def   = $types[ $soort ];
	$id    = 'form-' . $soort;
	wp_enqueue_script( 'valkenisse-forms' );

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- alleen weergave van status.
	$is_this = ( $_GET['valk_form'] ?? '' ) === $soort;
	if ( $is_this && isset( $_GET['valk_ok'] ) ) {
		return valkenisse_wrapper( 'valk-form is-sent', '<div class="valk-form__success" role="status" tabindex="-1" id="' . esc_attr( $id ) . '"><h3>Verzonden</h3><p>' . esc_html( $def['bedankt'] ) . '</p></div>' );
	}
	$values = array();
	$error  = '';
	if ( $is_this && isset( $_GET['valk_fout'] ) ) {
		$error  = valkenisse_form_error_text( sanitize_key( $_GET['valk_fout'] ) );
		$stored = get_transient( 'valk_f_' . sanitize_key( $_GET['valk_k'] ?? '' ) );
		$values = is_array( $stored ) ? $stored : array();
	}
	// phpcs:enable

	$fields = '';
	foreach ( $def['velden'] as $key => $f ) {
		$fid   = $id . '-' . $key;
		$value = (string) ( $values[ $key ] ?? '' );
		$req   = ! empty( $f['required'] );
		$attr  = ( $req ? ' required aria-required="true"' : '' ) . ( isset( $f['autocomplete'] ) ? ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"' : '' );
		$label = '<label for="' . esc_attr( $fid ) . '">' . esc_html( $f['label'] ) . ( $req ? '' : ' <span class="valk-form__optional">(optioneel)</span>' ) . '</label>';
		switch ( $f['type'] ) {
			case 'textarea':
				$input = '<textarea id="' . esc_attr( $fid ) . '" name="valk[' . esc_attr( $key ) . ']" rows="5"' . $attr . '>' . esc_textarea( $value ) . '</textarea>';
				break;
			case 'select':
				$input = '<select id="' . esc_attr( $fid ) . '" name="valk[' . esc_attr( $key ) . ']"' . $attr . '><option value="">Maak een keuze</option>';
				foreach ( $f['options'] as $opt ) {
					$input .= '<option' . selected( $value, $opt, false ) . '>' . esc_html( $opt ) . '</option>';
				}
				$input .= '</select>';
				break;
			default:
				$extra = '';
				if ( 'number' === $f['type'] ) {
					$extra = ' inputmode="numeric" min="' . (int) $f['min'] . '" max="' . (int) $f['max'] . '"';
				}
				if ( ! empty( $f['future'] ) ) {
					$extra .= ' data-future="1" min="' . esc_attr( Valkenisse_Hours::now()->format( 'Y-m-d' ) ) . '"';
				}
				$input = '<input id="' . esc_attr( $fid ) . '" type="' . esc_attr( $f['type'] ) . '" name="valk[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '"' . $attr . $extra . '>';
		}
		$fields .= '<div class="valk-form__field' . ( ! empty( $f['wide'] ) ? ' is-wide' : '' ) . '">' . $label . $input . '</div>';
	}

	$privacy = get_privacy_policy_url();
	$consent = sprintf(
		'<div class="valk-form__field is-wide valk-form__consent"><label><input type="checkbox" name="valk[akkoord]" value="1" required> <span>Ik ga akkoord dat Restaurant Valkenisse mijn gegevens gebruikt om deze aanvraag te behandelen%s.</span></label></div>',
		$privacy ? ' (zie het <a href="' . esc_url( $privacy ) . '">privacybeleid</a>)' : ''
	);

	$html  = '<form class="valk-form__form" id="' . esc_attr( $id ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	$html .= $error ? '<div class="valk-form__error" role="alert" tabindex="-1">' . esc_html( $error ) . '</div>' : '';
	$html .= '<input type="hidden" name="action" value="valk_form"><input type="hidden" name="valk_soort" value="' . esc_attr( $soort ) . '">';
	$html .= '<input type="hidden" name="valk_token" value="' . esc_attr( valkenisse_form_token() ) . '">';
	$html .= '<input type="hidden" name="valk_terug" value="' . esc_attr( get_permalink() ? get_permalink() : home_url( '/' ) ) . '">';
	$html .= '<div class="valk-form__hp" aria-hidden="true"><label>Laat dit veld leeg <input type="text" name="valk_website" tabindex="-1" autocomplete="off"></label></div>';
	$html .= '<div class="valk-form__grid">' . $fields . $consent . '</div>';
	$html .= '<p class="valk-form__submit"><button type="submit" class="valk-btn valk-btn--primary">' . esc_html( $def['knop'] ) . '</button></p>';
	$html .= '</form>';
	return valkenisse_wrapper( 'valk-form valk-form--' . $soort, $html );
}

function valkenisse_form_error_text( string $code ): string {
	return array(
		'velden' => 'Niet alle verplichte velden zijn (goed) ingevuld. Controleer het formulier en probeer het opnieuw.',
		'email'  => 'Het e-mailadres lijkt niet te kloppen.',
		'datum'  => 'Kies een datum in de toekomst.',
		'spam'   => 'Het formulier kon niet worden verzonden. Wacht een paar seconden en probeer het opnieuw, of bel ons.',
		'limiet' => 'U heeft kort achter elkaar meerdere berichten verstuurd. Probeer het later opnieuw of bel ons.',
		'mail'   => 'Er ging iets mis bij het versturen. Uw aanvraag is wel bewaard, maar bel ons voor de zekerheid even.',
	)[ $code ] ?? 'Er ging iets mis. Probeer het opnieuw of neem telefonisch contact op.';
}

/* -------------------------------------------------------------------------
 * Verwerking
 * ---------------------------------------------------------------------- */

add_action( 'admin_post_nopriv_valk_form', 'valkenisse_handle_form' );
add_action( 'admin_post_valk_form', 'valkenisse_handle_form' );

function valkenisse_handle_form(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- publiek formulier; beveiligd met token, honeypot en limiet.
	$types = valkenisse_form_types();
	$soort = sanitize_key( $_POST['valk_soort'] ?? '' );
	$back  = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['valk_terug'] ?? '' ) ), home_url( '/' ) );
	if ( ! isset( $types[ $soort ] ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$def   = $types[ $soort ];
	$raw   = isset( $_POST['valk'] ) && is_array( $_POST['valk'] ) ? wp_unslash( $_POST['valk'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$token = sanitize_text_field( wp_unslash( $_POST['valk_token'] ?? '' ) );
	$hp    = sanitize_text_field( wp_unslash( $_POST['valk_website'] ?? '' ) );
	// phpcs:enable

	$values = array();
	foreach ( $def['velden'] as $key => $f ) {
		$v              = (string) ( $raw[ $key ] ?? '' );
		$values[ $key ] = 'textarea' === $f['type'] ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
	}

	$fail = static function ( string $code ) use ( $values, $back, $soort ) {
		$key = wp_generate_password( 12, false );
		set_transient( 'valk_f_' . strtolower( $key ), $values, 15 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( array( 'valk_form' => $soort, 'valk_fout' => $code, 'valk_k' => strtolower( $key ) ), $back ) . '#form-' . $soort );
		exit;
	};

	// Spam: honeypot, tijdstoken, te veel links.
	$links = preg_match_all( '#https?://#i', implode( ' ', $values ) );
	if ( '' !== $hp || ! valkenisse_form_token_ok( $token ) || $links > 2 ) {
		$fail( 'spam' );
	}
	// Limiet: max. 5 inzendingen per 10 minuten per bezoeker (IP wordt alleen gehasht).
	$ip_key = 'valk_rl_' . substr( hash_hmac( 'sha256', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ), wp_salt() ), 0, 20 ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		$fail( 'limiet' );
	}
	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	// Validatie.
	$today = Valkenisse_Hours::now()->format( 'Y-m-d' );
	foreach ( $def['velden'] as $key => $f ) {
		$v = $values[ $key ];
		if ( ! empty( $f['required'] ) && '' === $v ) {
			$fail( 'velden' );
		}
		if ( '' === $v ) {
			continue;
		}
		if ( 'email' === $f['type'] && ! is_email( $v ) ) {
			$fail( 'email' );
		}
		if ( 'date' === $f['type'] && ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) || ( ! empty( $f['future'] ) && $v < $today ) ) ) {
			$fail( 'datum' );
		}
		if ( 'number' === $f['type'] && ( ! ctype_digit( $v ) || (int) $v < $f['min'] || (int) $v > $f['max'] ) ) {
			$fail( 'velden' );
		}
		if ( 'select' === $f['type'] && ! in_array( $v, $f['options'], true ) ) {
			$fail( 'velden' );
		}
	}
	if ( 'studio' === $soort && $values['vertrek'] <= $values['aankomst'] ) {
		$fail( 'datum' );
	}
	if ( empty( $raw['akkoord'] ) ) {
		$fail( 'velden' );
	}

	// Leesbare gegevens.
	$data = array( 'Soort aanvraag' => $def['titel'] );
	foreach ( $def['velden'] as $key => $f ) {
		$v = $values[ $key ];
		if ( 'date' === $f['type'] && $v ) {
			$v = wp_date( 'l j F Y', strtotime( $v . ' 12:00' ) );
		}
		$data[ $f['label'] ] = $v;
	}
	$data['Verzonden vanaf'] = $back;

	$when  = $values['datum'] ?? ( $values['aankomst'] ?? '' );
	$title = $def['titel'] . ' – ' . $values['naam'] . ( $when ? ' – ' . wp_date( 'j-n-Y', strtotime( $when . ' 12:00' ) ) : '' );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'aanvraag',
			'post_status' => 'private',
			'post_title'  => $title,
		)
	);
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_valk_data', $data );
		update_post_meta( $post_id, '_valk_type', $def['titel'] );
	}

	// E-mail naar het restaurant.
	$to   = valkenisse_get( 'formulieren.ontvanger' );
	$to   = is_email( $to ) ? $to : get_option( 'admin_email' );
	$body = '';
	foreach ( $data as $label => $value ) {
		$body .= $label . ":\n" . ( '' === $value ? '-' : $value ) . "\n\n";
	}
	$headers = array( 'Reply-To: ' . $values['naam'] . ' <' . $values['email'] . '>' );
	$sent    = wp_mail( $to, '[Website] ' . $title, $body, $headers );

	// Ontvangstbevestiging aan de gast.
	if ( valkenisse_get( 'formulieren.bevestiging' ) ) {
		$c     = valkenisse_get( 'contact' );
		$intro = 'Beste ' . $values['naam'] . ",\n\n" . $def['bedankt'] . "\n\nUw gegevens:\n\n";
		$outro = "\nMet vriendelijke groet,\n\n" . $c['naam'] . "\n" . valkenisse_address_line() . "\n" . $c['telefoon'] . "\n" . home_url( '/' );
		wp_mail( $values['email'], 'Ontvangen: ' . strtolower( $def['titel'] ) . ' bij ' . $c['naam'], $intro . $body . $outro, array( 'Reply-To: ' . $c['naam'] . ' <' . $to . '>' ) );
	}

	if ( ! $sent ) {
		$fail( 'mail' );
	}
	wp_safe_redirect( add_query_arg( array( 'valk_form' => $soort, 'valk_ok' => 1 ), $back ) . '#form-' . $soort );
	exit;
}

/* Bewaartermijn (AVG): oude aanvragen dagelijks verwijderen. */
add_action(
	'valkenisse_daily_cleanup',
	static function () {
		$days = (int) valkenisse_get( 'formulieren.bewaartermijn', 90 );
		$old  = get_posts(
			array(
				'post_type'      => 'aanvraag',
				'post_status'    => 'any',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'date_query'     => array( array( 'before' => $days . ' days ago' ) ),
			)
		);
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}
	}
);

// Formulierpagina's met een melding niet laten cachen/indexeren.
add_action(
	'template_redirect',
	static function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['valk_form'] ) ) {
			nocache_headers();
			add_filter( 'wp_robots', 'wp_robots_no_robots' );
		}
	}
);
