<?php
/**
 * Aanvraagformulier strandhuisje.
 *
 * Dit is een AANVRAAG, geen definitieve reservering: de aanvraag wordt per e-mail naar het paviljoen gestuurd
 * en bewaard onder Strandpaviljoen → Aanvragen. Werkt zonder JavaScript en zonder extra formulierplugin.
 */

defined( 'ABSPATH' ) || exit;

function vk_hut_types(): array {
	return array(
		'dag'     => __( 'Dag', 'valkenisse' ),
		'week'    => __( 'Week', 'valkenisse' ),
		'seizoen' => __( 'Seizoen', 'valkenisse' ),
	);
}

function vk_render_hut_request_form( array $attrs = array() ): string {
	if ( ! vk_get( 'huts_available' ) ) {
		return '<div class="vk-form vk-form--closed"><p>' . vk_text( vk_tr( (string) vk_get( 'huts_full_text' ), 'huts_full_text' ) ) . '</p>' . vk_render_contact_buttons( array( 'buttons' => 'mail,call' ) ) . '</div>';
	}

	$status = sanitize_key( $_GET['aanvraag'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'verzonden' === $status ) {
		return '<div class="vk-form vk-form--done" role="status" tabindex="-1" id="aanvraag-bedankt"><p class="vk-form__done-icon" aria-hidden="true">☀️</p><p class="vk-form__done-title">' . esc_html__( 'Bedankt!', 'valkenisse' ) . '</p><p>' . esc_html__( 'We nemen contact met je op over de beschikbaarheid.', 'valkenisse' ) . '</p></div>';
	}

	$error = '';
	if ( 'fout' === $status ) {
		$error = '<div class="vk-form__error" role="alert">' . esc_html__( 'Controleer je naam, e-mailadres en de gewenste periode, en probeer het opnieuw.', 'valkenisse' ) . '</div>';
	} elseif ( 'verlopen' === $status ) {
		$error = '<div class="vk-form__error" role="alert">' . esc_html__( 'Het formulier was verlopen. Vul het hieronder nog een keer in, dan komt het goed.', 'valkenisse' ) . '</div>';
	} elseif ( 'mislukt' === $status ) {
		$error = '<div class="vk-form__error" role="alert">' . esc_html__( 'Het versturen is niet gelukt. Mail of bel ons gerust even.', 'valkenisse' ) . '</div>';
	}

	ob_start();
	?>
	<form class="vk-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php echo $error; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hierboven ge-escaped. ?>
		<input type="hidden" name="action" value="vk_hut_request" />
		<input type="hidden" name="vk_ts" value="<?php echo esc_attr( (string) time() ); ?>" />
		<input type="hidden" name="vk_return" value="<?php echo esc_url( get_permalink() ?: home_url( '/' ) ); ?>" />
		<?php wp_nonce_field( 'vk_hut_request', 'vk_nonce' ); ?>
		<p class="vk-form__hp" aria-hidden="true"><label>Website <input type="text" name="vk_website" tabindex="-1" autocomplete="off" /></label></p>

		<div class="vk-form__grid">
			<p class="vk-field">
				<label for="vk-name"><?php esc_html_e( 'Naam', 'valkenisse' ); ?> <span aria-hidden="true">*</span></label>
				<input id="vk-name" name="vk_name" type="text" required autocomplete="name" />
			</p>
			<p class="vk-field">
				<label for="vk-email"><?php esc_html_e( 'E-mailadres', 'valkenisse' ); ?> <span aria-hidden="true">*</span></label>
				<input id="vk-email" name="vk_email" type="email" required autocomplete="email" inputmode="email" />
			</p>
			<p class="vk-field">
				<label for="vk-phone"><?php esc_html_e( 'Telefoonnummer', 'valkenisse' ); ?></label>
				<input id="vk-phone" name="vk_phone" type="tel" autocomplete="tel" inputmode="tel" />
			</p>
			<p class="vk-field">
				<label for="vk-count"><?php esc_html_e( 'Aantal strandhuisjes', 'valkenisse' ); ?></label>
				<input id="vk-count" name="vk_count" type="number" min="1" max="10" value="1" inputmode="numeric" />
			</p>
		</div>

		<fieldset class="vk-field vk-choice">
			<legend><?php esc_html_e( 'Gewenste huur', 'valkenisse' ); ?> <span aria-hidden="true">*</span></legend>
			<?php foreach ( vk_hut_types() as $value => $label ) : ?>
				<label class="vk-choice__option">
					<input type="radio" name="vk_type" value="<?php echo esc_attr( $value ); ?>" <?php checked( 'dag', $value ); ?> required />
					<span><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>

		<p class="vk-field">
			<label for="vk-period"><?php esc_html_e( 'Gewenste datum / periode', 'valkenisse' ); ?> <span aria-hidden="true">*</span></label>
			<input id="vk-period" name="vk_period" type="text" required placeholder="<?php esc_attr_e( 'bv. 14 juli, of 3 t/m 10 augustus', 'valkenisse' ); ?>" />
		</p>

		<p class="vk-field">
			<label for="vk-message"><?php esc_html_e( 'Opmerking', 'valkenisse' ); ?></label>
			<textarea id="vk-message" name="vk_message" rows="4"></textarea>
		</p>

		<p class="vk-form__privacy">
			<?php
			echo esc_html__( 'Je gegevens gebruiken we alleen om je aanvraag te beantwoorden.', 'valkenisse' );
			if ( get_page_by_path( 'privacybeleid' ) ) {
				echo ' <a href="' . esc_url( vk_page_url( 'privacybeleid' ) ) . '">' . esc_html__( 'Privacybeleid', 'valkenisse' ) . '</a>';
			}
			?>
		</p>

		<p class="vk-form__submit">
			<button type="submit" class="vk-btn vk-btn--primary vk-btn--large"><?php echo vk_icon( 'hut' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Vraag een strandhuisje aan', 'valkenisse' ); ?></button>
		</p>
		<p class="vk-form__disclaimer"><?php esc_html_e( 'Dit is een aanvraag, geen definitieve reservering. We laten je weten of er een strandhuisje beschikbaar is.', 'valkenisse' ); ?></p>
	</form>
	<?php
	return (string) ob_get_clean();
}

add_action( 'admin_post_nopriv_vk_hut_request', 'vk_handle_hut_request' );
add_action( 'admin_post_vk_hut_request', 'vk_handle_hut_request' );

function vk_handle_hut_request(): void {
	$return = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['vk_return'] ?? '' ) ), home_url( '/' ) );
	$back   = static function ( string $status ) use ( $return ) {
		wp_safe_redirect( add_query_arg( 'aanvraag', $status, $return ) . ( 'verzonden' === $status ? '#aanvraag-bedankt' : '#aanvragen' ) );
		exit;
	};

	// Let op bij paginacaching: houd de cache van de strandhuisjespagina korter dan 12 uur (zie README).
	if ( ! isset( $_POST['vk_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['vk_nonce'] ), 'vk_hut_request' ) ) {
		$back( 'verlopen' );
	}

	// Spamfilters: honeypot, minimaal 3 seconden invultijd, max. 5 aanvragen per uur per IP.
	$ip_key = 'vk_req_' . md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
	$count  = (int) get_transient( $ip_key );
	$ts     = (int) ( $_POST['vk_ts'] ?? 0 );
	if ( ! empty( $_POST['vk_website'] ) || time() - $ts < 3 || $count >= 5 ) {
		$back( 'verzonden' ); // Stil negeren.
	}

	$data = array(
		'name'    => sanitize_text_field( wp_unslash( $_POST['vk_name'] ?? '' ) ),
		'email'   => sanitize_email( wp_unslash( $_POST['vk_email'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['vk_phone'] ?? '' ) ),
		'type'    => sanitize_key( $_POST['vk_type'] ?? '' ),
		'period'  => sanitize_text_field( wp_unslash( $_POST['vk_period'] ?? '' ) ),
		'count'   => max( 1, min( 10, (int) ( $_POST['vk_count'] ?? 1 ) ) ),
		'message' => sanitize_textarea_field( wp_unslash( $_POST['vk_message'] ?? '' ) ),
	);
	$types = vk_hut_types();

	if ( '' === $data['name'] || ! is_email( $data['email'] ) || '' === $data['period'] || ! isset( $types[ $data['type'] ] ) ) {
		$back( 'fout' );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	// Bewaren in het beheer, zodat er nooit een aanvraag kwijtraakt.
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'vk_aanvraag',
			'post_status' => 'private',
			'post_title'  => $data['name'],
		)
	);
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, 'vk_email', $data['email'] );
		update_post_meta( $post_id, 'vk_phone', $data['phone'] );
		update_post_meta( $post_id, 'vk_type', $types[ $data['type'] ] );
		update_post_meta( $post_id, 'vk_period', $data['period'] );
		update_post_meta( $post_id, 'vk_count', $data['count'] );
		update_post_meta( $post_id, 'vk_message', $data['message'] );
	}

	// E-mail naar het paviljoen.
	$to      = (string) vk_get( 'request_email' ) ?: (string) vk_get( 'email' );
	$subject = sprintf( __( 'Aanvraag strandhuisje: %1$s (%2$s, %3$s)', 'valkenisse' ), $data['name'], $types[ $data['type'] ], $data['period'] );
	$body    = implode(
		"\n",
		array(
			__( 'Nieuwe aanvraag voor een strandhuisje via de website.', 'valkenisse' ),
			'',
			__( 'Naam', 'valkenisse' ) . ': ' . $data['name'],
			__( 'E-mail', 'valkenisse' ) . ': ' . $data['email'],
			__( 'Telefoon', 'valkenisse' ) . ': ' . $data['phone'],
			__( 'Gewenste huur', 'valkenisse' ) . ': ' . $types[ $data['type'] ],
			__( 'Datum / periode', 'valkenisse' ) . ': ' . $data['period'],
			__( 'Aantal strandhuisjes', 'valkenisse' ) . ': ' . $data['count'],
			__( 'Opmerking', 'valkenisse' ) . ': ' . $data['message'],
			'',
			__( 'Beantwoord deze e-mail om direct te reageren.', 'valkenisse' ),
		)
	);
	$sent = wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>' ) );

	// Ontvangstbevestiging naar de aanvrager.
	wp_mail(
		$data['email'],
		__( 'We hebben je aanvraag voor een strandhuisje ontvangen', 'valkenisse' ),
		sprintf(
			/* translators: 1: naam, 2: type, 3: periode */
			__( "Hoi %1\$s,\n\nBedankt voor je aanvraag voor een strandhuisje (%2\$s, %3\$s). Dit is nog geen definitieve reservering: we nemen contact met je op over de beschikbaarheid.\n\nTot op het strand!\nFamilie Herwegh – Strandpaviljoen Valkenisse", 'valkenisse' ),
			$data['name'],
			$types[ $data['type'] ],
			$data['period']
		),
		array( 'Reply-To: ' . $to )
	);

	$back( $sent || $post_id ? 'verzonden' : 'mislukt' );
}
