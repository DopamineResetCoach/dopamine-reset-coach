<?php
/**
 * Hulpfuncties die door blokken, SEO en thema gedeeld worden.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Escape tekst voor HTML en markeer nog aan te leveren informatie zodat dit vóór livegang opvalt.
 */
function vk_text( $text ): string {
	$html = esc_html( (string) $text );
	return str_replace( esc_html( VK_TODO ), '<mark class="vk-todo">' . esc_html( VK_TODO ) . '</mark>', $html );
}

/**
 * Is deze waarde (nog) een placeholder of leeg?
 */
function vk_is_missing( $value ): bool {
	$value = trim( (string) $value );
	return '' === $value || str_contains( $value, VK_TODO );
}

/**
 * Huidige datum/tijd in de tijdzone van de site (Instellingen → Algemeen).
 */
function vk_now(): DateTimeImmutable {
	return new DateTimeImmutable( 'now', wp_timezone() );
}

/**
 * Sleutel van de huidige weekdag, bv. "mon".
 */
function vk_today_key(): string {
	return strtolower( vk_now()->format( 'D' ) );
}

/**
 * Het rooster voor één dag.
 */
function vk_hours_for( string $day ): array {
	$hours = vk_get( "hours_{$day}" );
	return wp_parse_args( is_array( $hours ) ? $hours : array(), array( 'open' => '', 'close' => '', 'closed' => 0 ) );
}

/**
 * Tekst voor één dag in het rooster, bv. "11:00 – sluitingstijd wisselend".
 */
function vk_hours_label( array $hours ): string {
	if ( ! empty( $hours['closed'] ) ) {
		return __( 'Gesloten', 'valkenisse' );
	}
	if ( '' === $hours['open'] ) {
		return VK_TODO;
	}
	if ( '' === $hours['close'] ) {
		/* translators: 1: openingstijd, 2: tekst voor wisselende sluitingstijd */
		return sprintf( __( 'vanaf %1$s (%2$s)', 'valkenisse' ), $hours['open'], vk_tr( vk_get( 'hours_variable_label' ), 'hours_variable_label' ) );
	}
	return sprintf( '%s – ± %s', $hours['open'], $hours['close'] );
}

/**
 * De actuele status van vandaag, gecombineerd uit de "Vandaag"-instelling en het vaste rooster.
 *
 * @return array{state:string,open:bool,headline:string,notice:string,icon:string}
 */
function vk_today(): array {
	$status = (string) vk_get( 'today_status' );
	$date   = (string) vk_get( 'today_status_date' );

	// Een keuze "alleen voor vandaag" vervalt automatisch de volgende dag.
	if ( 'auto' !== $status && vk_get( 'today_only' ) && $date !== vk_now()->format( 'Y-m-d' ) ) {
		$status = 'auto';
	}

	$hours = vk_hours_for( vk_today_key() );
	$open  = (string) ( vk_get( 'today_open' ) ?: $hours['open'] );
	$close = (string) vk_get( 'today_close' );

	switch ( $status ) {
		case 'open':
			$state    = 'open';
			$headline = sprintf( __( 'We zijn vandaag geopend vanaf %s', 'valkenisse' ), $open );
			if ( $close ) {
				$headline = sprintf( __( 'We zijn vandaag geopend van %1$s tot ± %2$s', 'valkenisse' ), $open, $close );
			}
			break;
		case 'open_until':
			$state    = 'open';
			$headline = $close
				? sprintf( __( 'Vandaag geopend tot ± %s', 'valkenisse' ), $close )
				: sprintf( __( 'We zijn vandaag geopend vanaf %s', 'valkenisse' ), $open );
			break;
		case 'closed_weather':
			$state    = 'closed';
			$headline = __( 'Vanwege het weer zijn we vandaag gesloten', 'valkenisse' );
			break;
		case 'closed':
			$state    = 'closed';
			$headline = __( 'Vandaag zijn we gesloten', 'valkenisse' );
			break;
		case 'custom':
			$state    = 'custom';
			$headline = vk_tr( (string) vk_get( 'today_custom' ), 'today_custom' );
			break;
		default:
			if ( ! empty( $hours['closed'] ) ) {
				$state    = 'closed';
				$headline = __( 'Vandaag zijn we gesloten', 'valkenisse' );
			} elseif ( '' === $hours['open'] ) {
				$state    = 'unknown';
				$headline = '';
			} else {
				$state    = 'open';
				$headline = $hours['close']
					? sprintf( __( 'We zijn vandaag geopend van %1$s tot ± %2$s', 'valkenisse' ), $hours['open'], $hours['close'] )
					: sprintf( __( 'We zijn vandaag geopend vanaf %s', 'valkenisse' ), $hours['open'] );
			}
	}

	$icon = 'open' === $state ? '☀️' : '🌊';
	if ( 'closed_weather' === $status ) {
		$icon = '🌧';
	}

	$notice = $headline;
	if ( 'open' === $state ) {
		$notice = $headline . ' – ' . __( 'tot straks op het strand!', 'valkenisse' );
	}

	return array(
		'state'    => $state,
		'status'   => $status,
		'open'     => 'open' === $state,
		'headline' => $headline,
		'notice'   => $notice,
		'icon'     => $icon,
	);
}

/**
 * Telefoon-, mail- en route-links.
 */
function vk_tel_url(): string {
	$number = preg_replace( '/[^0-9+]/', '', (string) vk_get( 'phone_intl' ) );
	return $number ? 'tel:' . $number : '';
}

function vk_mail_url(): string {
	$email = (string) vk_get( 'email' );
	return is_email( $email ) ? 'mailto:' . antispambot( $email ) : '';
}

function vk_has_coords(): bool {
	return is_numeric( vk_get( 'lat' ) ) && is_numeric( vk_get( 'lng' ) );
}

function vk_route_url(): string {
	if ( vk_has_coords() ) {
		$destination = vk_get( 'lat' ) . ',' . vk_get( 'lng' );
	} elseif ( ! vk_is_missing( vk_get( 'nav_address' ) ) ) {
		$destination = (string) vk_get( 'nav_address' );
	} else {
		$destination = (string) vk_get( 'map_query' );
	}
	return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $destination );
}

/**
 * Postadres als regels.
 */
function vk_postal_lines(): array {
	return array_filter(
		array(
			(string) vk_get( 'postal_street' ),
			trim( vk_get( 'postal_zip' ) . ' ' . vk_get( 'postal_city' ) ),
		)
	);
}

/**
 * URL van een pagina op basis van slug, zodat links blijven werken als een pagina wordt verplaatst of vertaald.
 */
function vk_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		$id = function_exists( 'pll_get_post' ) ? ( pll_get_post( $page->ID ) ?: $page->ID ) : $page->ID;
		return (string) get_permalink( $id );
	}
	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Eenvoudig SVG-icoon (inline, geen icon font = sneller).
 */
function vk_icon( string $name ): string {
	$paths = array(
		'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'route' => '<path d="M12 21s-6-5.3-6-11a6 6 0 1 1 12 0c0 5.7-6 11-6 11z"/><circle cx="12" cy="10" r="2.2"/>',
		'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'menu'  => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'hut'   => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/>',
		'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'sun'   => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'check' => '<path d="m5 12 5 5 9-10"/>',
		'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'down'  => '<path d="M12 5v14M6 13l6 6 6-6"/>',
		'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
		'facebook'  => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8"/>',
		'star'  => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="vk-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}
