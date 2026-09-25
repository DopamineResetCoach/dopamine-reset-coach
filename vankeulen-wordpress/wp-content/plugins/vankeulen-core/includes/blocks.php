<?php
/**
 * Dynamische blokken. Ze lezen hun inhoud uit de centrale bedrijfsgegevens,
 * dus de eigenaar hoeft een telefoonnummer of adres maar op één plek te
 * wijzigen. In de editor zijn ze te vinden onder de categorie "Van Keulen".
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'block_categories_all',
	function ( $cats ) {
		array_unshift( $cats, array( 'slug' => 'vankeulen', 'title' => 'Van Keulen' ) );
		return $cats;
	}
);

add_action( 'init', 'vk_register_blocks' );

/**
 * Registreert scripts en blokken.
 */
function vk_register_blocks() {
	wp_register_script(
		'vk-editor',
		VK_CORE_URL . 'assets/js/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-plugins', 'wp-editor', 'wp-data', 'wp-core-data' ),
		VK_CORE_VERSION,
		true
	);
	wp_register_script( 'vk-aanvraag', VK_CORE_URL . 'assets/js/aanvraag.js', array(), VK_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'vk-kaart', VK_CORE_URL . 'assets/js/kaart.js', array(), VK_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	$blocks = array(
		'logo'              => array(
			'title'      => 'Logo Van Keulen',
			'attributes' => array( 'variant' => array( 'type' => 'string', 'default' => 'donker' ) ),
		),
		'knop'              => array(
			'title'      => 'Knop (aanvragen / bellen / route)',
			'attributes' => array(
				'soort' => array( 'type' => 'string', 'default' => 'aanvragen' ),
				'stijl' => array( 'type' => 'string', 'default' => 'primair' ),
				'label' => array( 'type' => 'string', 'default' => '' ),
				'type'  => array( 'type' => 'string', 'default' => '' ),
				'url'   => array( 'type' => 'string', 'default' => '' ),
			),
		),
		'contactgegevens'   => array(
			'title'      => 'Contactgegevens',
			'attributes' => array( 'metNaam' => array( 'type' => 'boolean', 'default' => true ) ),
		),
		'openingstijden'    => array( 'title' => 'Openingstijden' ),
		'beschikbaarheid'   => array(
			'title'      => 'Beschikbaarheid',
			'attributes' => array( 'stijl' => array( 'type' => 'string', 'default' => 'badge' ) ),
		),
		'mededeling'        => array( 'title' => 'Mededelingenbalk' ),
		'prijzen'           => array( 'title' => 'Prijzen / prijs aanvragen' ),
		'faq'               => array(
			'title'      => 'Veelgestelde vragen',
			'attributes' => array( 'aantal' => array( 'type' => 'number', 'default' => 0 ) ),
		),
		'aanvraagformulier' => array( 'title' => 'Stallingsaanvraag (wizard)' ),
		'kaart'             => array(
			'title'      => 'Kaart & route',
			'attributes' => array( 'knoppen' => array( 'type' => 'boolean', 'default' => true ) ),
		),
		'kruimelpad'        => array( 'title' => 'Kruimelpad' ),
		'mobiele-balk'      => array( 'title' => 'Mobiele actiebalk' ),
		'copyright'         => array( 'title' => 'Copyrightregel' ),
	);

	foreach ( $blocks as $slug => $cfg ) {
		$attributes              = $cfg['attributes'] ?? array();
		$attributes['className'] = array( 'type' => 'string', 'default' => '' );
		register_block_type(
			'vankeulen/' . $slug,
			array(
				'api_version'     => 3,
				'title'           => $cfg['title'],
				'category'        => 'vankeulen',
				'attributes'      => $attributes,
				'supports'        => array( 'html' => false, 'customClassName' => true ),
				'editor_script'   => 'vk-editor',
				'render_callback' => 'vk_render_' . str_replace( '-', '_', $slug ),
			)
		);
	}
}

/**
 * Extra klassen van de gebruiker.
 *
 * @param array  $a    Attributen.
 * @param string $base Basisklasse.
 */
function vk_cls( $a, $base ) {
	return trim( $base . ' ' . ( $a['className'] ?? '' ) );
}

/* ------------------------------------------------------------------------ */

/**
 * Logo: eigen logo (Weergave → Editor → Site-logo) of het woordmerk.
 *
 * @param array $a Attributen.
 */
function vk_render_logo( $a ) {
	$licht = 'licht' === ( $a['variant'] ?? '' );
	if ( ! $licht && has_custom_logo() ) {
		return '<div class="' . esc_attr( vk_cls( $a, 'vk-logo vk-logo--eigen' ) ) . '">' . get_custom_logo() . '</div>';
	}
	$merk = '<svg class="vk-logo__merk" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false">'
		. '<rect width="48" height="48" rx="9" fill="' . ( $licht ? '#F1EBDF' : '#14284B' ) . '"/>'
		. '<path d="M6 35h36" stroke="' . ( $licht ? '#2D6A43' : '#B89D6E' ) . '" stroke-width="2.5" stroke-linecap="round"/>'
		. '<path d="M11 31V20.5c0-2.5 2-4.5 4.5-4.5H31c3.9 0 7 3.1 7 7v8H11Z" fill="' . ( $licht ? '#14284B' : '#F7F5F0' ) . '"/>'
		. '<rect x="15" y="20" width="7" height="5" rx="1" fill="' . ( $licht ? '#F1EBDF' : '#14284B' ) . '"/>'
		. '<rect x="25" y="20" width="7" height="5" rx="1" fill="' . ( $licht ? '#F1EBDF' : '#14284B' ) . '"/>'
		. '<circle cx="21" cy="32" r="3" fill="' . ( $licht ? '#2D6A43' : '#B89D6E' ) . '"/>'
		. '<path d="M38 29h4" stroke="' . ( $licht ? '#14284B' : '#F7F5F0' ) . '" stroke-width="2" stroke-linecap="round"/>'
		. '</svg>';
	return sprintf(
		'<a class="%1$s" href="%2$s" rel="home" aria-label="%3$s – naar de homepage">%4$s<span class="vk-logo__tekst"><span class="vk-logo__naam">Van Keulen</span><span class="vk-logo__sub">Caravanstalling</span></span></a>',
		esc_attr( vk_cls( $a, 'vk-logo' . ( $licht ? ' vk-logo--licht' : '' ) ) ),
		esc_url( home_url( '/' ) ),
		esc_attr( vk_get( 'bedrijfsnaam' ) ),
		$merk
	);
}

/**
 * Knop.
 *
 * @param array $a Attributen.
 */
function vk_render_knop( $a ) {
	$soort  = $a['soort'] ?? 'aanvragen';
	$stijl  = in_array( $a['stijl'] ?? '', array( 'primair', 'secundair', 'licht', 'tekstlink' ), true ) ? $a['stijl'] : 'primair';
	$label  = trim( (string) ( $a['label'] ?? '' ) );
	$extern = false;
	$icon   = '';
	$href   = '';

	switch ( $soort ) {
		case 'bellen':
			$href  = vk_tel_href();
			$label = $label ? $label : 'Bel ' . ( vk_filled( 'telefoon' ) ? vk_get( 'telefoon' ) : 'Van Keulen' );
			$icon  = vk_icon( 'telefoon' );
			break;
		case 'whatsapp':
			$href   = vk_whatsapp_href( 'Hallo Van Keulen, ik heb een vraag over een stallingsplaats.' );
			$label  = $label ? $label : 'WhatsApp';
			$icon   = vk_icon( 'whatsapp' );
			$extern = true;
			break;
		case 'route':
			$href   = vk_route_url();
			$label  = $label ? $label : 'Plan uw route';
			$icon   = vk_icon( 'route' );
			$extern = true;
			break;
		case 'locatie':
			$href   = vk_maps_url();
			$label  = $label ? $label : 'Bekijk locatie';
			$icon   = vk_icon( 'pin' );
			$extern = true;
			break;
		case 'email':
			$href  = vk_filled( 'email' ) ? 'mailto:' . antispambot( vk_get( 'email' ) ) : '';
			$label = $label ? $label : 'Mail Van Keulen';
			$icon  = vk_icon( 'mail' );
			break;
		case 'prijs':
			$href  = vk_aanvraag_url( $a['type'] ?? '' );
			$label = $label ? $label : 'Prijs aanvragen';
			break;
		case 'link':
			$href  = $a['url'] ?? '';
			$label = $label ? $label : 'Meer informatie';
			break;
		case 'aanvragen':
		default:
			$href  = vk_aanvraag_url( $a['type'] ?? '' );
			$label = $label ? $label : 'Vraag een stallingsplaats aan';
			break;
	}

	if ( 'tekstlink' === $stijl ) {
		$icon = '';
	}

	if ( ! $href ) {
		// Gegeven ontbreekt: bezoekers zien niets, redacteuren een herinnering.
		if ( ! vk_toon_ontbrekend() ) {
			return '';
		}
		return '<span class="' . esc_attr( vk_cls( $a, 'vk-knop vk-knop--ontbreekt' ) ) . '">' . esc_html( $label ) . ' – ' . esc_html( VK_PLACEHOLDER ) . '</span>';
	}

	$pijl = 'tekstlink' === $stijl ? ' <span aria-hidden="true">→</span>' : '';

	$data = ( in_array( $soort, array( 'aanvragen', 'prijs' ), true ) && ! empty( $a['type'] ) ) ? ' data-vk-type="' . esc_attr( $a['type'] ) . '"' : '';

	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s%7$s>%4$s<span>%5$s</span>%6$s</a>',
		esc_attr( vk_cls( $a, 'vk-knop vk-knop--' . $stijl . ' vk-knop--' . $soort . ( 'tekstlink' === $stijl ? '' : ' wp-element-button' ) ) ),
		esc_url( $href, array( 'http', 'https', 'tel', 'mailto' ) ),
		$extern ? ' target="_blank" rel="noopener"' : '',
		$icon,
		esc_html( $label ),
		$pijl,
		$data
	);
}

/**
 * Contactgegevens.
 *
 * @param array $a Attributen.
 */
function vk_render_contactgegevens( $a ) {
	$out = '<address class="' . esc_attr( vk_cls( $a, 'vk-contact' ) ) . '">';
	if ( ! empty( $a['metNaam'] ) ) {
		$out .= '<strong class="vk-contact__naam">' . esc_html( vk_get( 'bedrijfsnaam' ) ) . '</strong>';
	}
	$out .= '<a class="vk-contact__regel" href="' . esc_url( vk_maps_url() ) . '" target="_blank" rel="noopener">' . vk_icon( 'pin' ) . '<span>' . esc_html( vk_get( 'straat' ) ) . '<br>' . esc_html( trim( vk_get( 'postcode' ) . ' ' . vk_get( 'plaats' ) ) ) . '</span></a>';

	if ( vk_filled( 'telefoon' ) ) {
		$out .= '<a class="vk-contact__regel" href="' . esc_url( vk_tel_href(), array( 'tel' ) ) . '">' . vk_icon( 'telefoon' ) . '<span>' . esc_html( vk_get( 'telefoon' ) ) . '</span></a>';
	} else {
		$out .= '<span class="vk-contact__regel">' . vk_icon( 'telefoon' ) . '<span>Telefoon: ' . vk_placeholder_html() . '</span></span>';
	}

	if ( vk_filled( 'email' ) ) {
		$mail = antispambot( vk_get( 'email' ) );
		$out .= '<a class="vk-contact__regel" href="mailto:' . $mail . '">' . vk_icon( 'mail' ) . '<span>' . $mail . '</span></a>';
	}

	if ( vk_whatsapp_href() ) {
		$out .= '<a class="vk-contact__regel" href="' . esc_url( vk_whatsapp_href() ) . '" target="_blank" rel="noopener">' . vk_icon( 'whatsapp' ) . '<span>WhatsApp: ' . esc_html( vk_get( 'whatsapp' ) ) . '</span></a>';
	}

	if ( vk_filled( 'kvk' ) ) {
		$out .= '<span class="vk-contact__klein">KvK ' . esc_html( vk_get( 'kvk' ) ) . '</span>';
	}

	return $out . '</address>';
}

/**
 * Openingstijden / contactmomenten.
 *
 * @param array $a Attributen.
 */
function vk_render_openingstijden( $a ) {
	$rows = '';
	foreach ( vk_dagen() as $dag => $label ) {
		$r = vk_get( 'openingstijden' )[ $dag ] ?? array();
		if ( empty( $r['status'] ) ) {
			continue;
		}
		switch ( $r['status'] ) {
			case 'open':
				$tijd = ( $r['van'] && $r['tot'] ) ? $r['van'] . ' – ' . $r['tot'] : 'Geopend';
				break;
			case 'afspraak':
				$tijd = 'Op afspraak';
				break;
			default:
				$tijd = 'Gesloten';
		}
		$rows .= '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . esc_html( $tijd ) . '</td></tr>';
	}

	$out = '<div class="' . esc_attr( vk_cls( $a, 'vk-tijden' ) ) . '">';
	if ( $rows ) {
		$out .= '<table class="vk-tijden__tabel"><caption class="screen-reader-text">Openingstijden</caption><tbody>' . $rows . '</tbody></table>';
	}
	if ( vk_filled( 'openingstijden_tekst' ) ) {
		$out .= wpautop( esc_html( vk_get( 'openingstijden_tekst' ) ) );
	}
	if ( ! $rows && ! vk_filled( 'openingstijden_tekst' ) ) {
		$out .= '<p>Openingstijden / contactmomenten: ' . vk_placeholder_html() . '</p>';
	}
	return $out . '</div>';
}

/**
 * Beschikbaarheid.
 *
 * @param array $a Attributen.
 */
function vk_render_beschikbaarheid( $a ) {
	$status = vk_get( 'beschikbaarheid' );
	if ( 'onbekend' === $status || ! isset( vk_beschikbaarheid_opties()[ $status ] ) ) {
		return '';
	}
	$stijl = 'blok' === ( $a['stijl'] ?? '' ) ? 'blok' : 'badge';
	$out   = '<p class="' . esc_attr( vk_cls( $a, 'vk-beschikbaar vk-beschikbaar--' . $stijl . ' vk-beschikbaar--' . $status ) ) . '" role="status">';
	$out  .= '<span class="vk-beschikbaar__stip" aria-hidden="true"></span><span><strong>' . esc_html( vk_beschikbaarheid_opties()[ $status ] ) . '</strong>';
	if ( vk_filled( 'beschikbaarheid_tekst' ) ) {
		$out .= '<span class="vk-beschikbaar__tekst"> ' . esc_html( vk_get( 'beschikbaarheid_tekst' ) ) . '</span>';
	}
	return $out . '</span></p>';
}

/**
 * Mededelingenbalk.
 *
 * @param array $a Attributen.
 */
function vk_render_mededeling( $a ) {
	if ( ! vk_get( 'mededeling_aan' ) || ! vk_filled( 'mededeling_tekst' ) ) {
		return '';
	}
	$tekst = esc_html( vk_get( 'mededeling_tekst' ) );
	if ( vk_filled( 'mededeling_link' ) ) {
		$tekst = '<a href="' . esc_url( vk_get( 'mededeling_link' ) ) . '">' . $tekst . ' <span aria-hidden="true">→</span></a>';
	}
	return '<div class="' . esc_attr( vk_cls( $a, 'vk-mededeling' ) ) . '" role="note"><p>' . $tekst . '</p></div>';
}

/**
 * Prijzen of "Benieuwd naar de prijs?".
 *
 * @param array $a Attributen.
 */
function vk_render_prijzen( $a ) {
	$regels = vk_prijsregels();
	$out    = '<div class="' . esc_attr( vk_cls( $a, 'vk-prijzen' ) ) . '">';

	if ( vk_get( 'prijzen_aan' ) && $regels ) {
		$out .= '<table class="vk-prijzen__tabel"><thead><tr><th scope="col">Stalling</th><th scope="col">Tarief</th></tr></thead><tbody>';
		foreach ( $regels as $r ) {
			$out .= '<tr><th scope="row">' . esc_html( $r['omschrijving'] );
			if ( $r['toelichting'] ) {
				$out .= '<span class="vk-prijzen__toelichting">' . esc_html( $r['toelichting'] ) . '</span>';
			}
			$out .= '</th><td>' . esc_html( $r['prijs'] ) . '</td></tr>';
		}
		$out .= '</tbody></table>';
		if ( vk_filled( 'prijzen_toelichting' ) ) {
			$out .= wpautop( esc_html( vk_get( 'prijzen_toelichting' ) ) );
		}
		$out .= vk_render_knop( array( 'soort' => 'prijs', 'label' => 'Stallingsplaats aanvragen' ) );
	} else {
		$out .= '<h3 class="vk-prijzen__kop">Benieuwd naar de prijs?</h3>';
		$out .= '<p>De prijs van uw stallingsplaats kan afhankelijk zijn van het type object, het formaat en de gewenste stallingsperiode. Vraag vrijblijvend naar de mogelijkheden.</p>';
		$out .= vk_render_knop( array( 'soort' => 'prijs' ) );
	}
	return $out . '</div>';
}

/**
 * Kaart: privacyvriendelijk. Google Maps wordt pas geladen na een klik, zodat
 * de pagina snel blijft en er zonder toestemming geen gegevens naar Google gaan.
 *
 * @param array $a Attributen.
 */
function vk_render_kaart( $a ) {
	wp_enqueue_script( 'vk-kaart' );
	$bg = function_exists( 'vankeulen_img' ) ? vankeulen_img( 'walcheren' )['url'] : '';
	return sprintf(
		'<div class="%1$s" id="locatie">
			<div class="vk-kaart__vlak" data-vk-kaart="%2$s" style="%3$s">
				<div class="vk-kaart__laag">
					<p class="vk-kaart__adres">%4$s<span><strong>%5$s</strong><br>%6$s</span></p>
					<button type="button" class="vk-knop vk-knop--licht wp-element-button vk-kaart__laad">Kaart laden</button>
					<p class="vk-kaart__privacy">De kaart wordt geladen via Google Maps. Daarbij worden gegevens met Google uitgewisseld.</p>
				</div>
			</div>
			%7$s%8$s
		</div>',
		esc_attr( vk_cls( $a, 'vk-kaart' ) ),
		esc_url( vk_maps_embed_url() ),
		$bg ? 'background-image:url(' . esc_url( $bg ) . ')' : '',
		vk_icon( 'pin' ),
		esc_html( vk_get( 'bedrijfsnaam' ) ),
		esc_html( vk_adres_regel() ),
		isset( $a['knoppen'] ) && ! $a['knoppen'] ? '' : '<div class="vk-knoppen vk-kaart__knoppen">' . vk_render_knop( array( 'soort' => 'route' ) ),
		isset( $a['knoppen'] ) && ! $a['knoppen'] ? '' : vk_render_knop( array( 'soort' => 'locatie', 'stijl' => 'secundair' ) ) . '</div>'
	);
}

/**
 * Kruimelpad (met BreadcrumbList in schema.php).
 *
 * @param array $a Attributen.
 */
function vk_render_kruimelpad( $a ) {
	$items = vk_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return '';
	}
	$out  = '<nav class="' . esc_attr( vk_cls( $a, 'vk-kruimelpad' ) ) . '" aria-label="Kruimelpad"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		$out .= $i === $last
			? '<li aria-current="page">' . esc_html( $item['naam'] ) . '</li>'
			: '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['naam'] ) . '</a></li>';
	}
	return $out . '</ol></nav>';
}

/**
 * Kruimelpad-items voor de huidige pagina.
 *
 * @return array<int,array{naam:string,url:string}>
 */
function vk_breadcrumb_items() {
	$items = array(
		array(
			'naam' => 'Home',
			'url'  => home_url( '/' ),
		),
	);
	if ( is_front_page() ) {
		return $items;
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return $items;
		}
		foreach ( array_reverse( get_post_ancestors( $post ) ) as $anc ) {
			$items[] = array(
				'naam' => get_the_title( $anc ),
				'url'  => get_permalink( $anc ),
			);
		}
		$items[] = array(
			'naam' => get_the_title( $post ),
			'url'  => get_permalink( $post ),
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'naam' => 'Pagina niet gevonden',
			'url'  => '',
		);
	}
	return $items;
}

/**
 * Mobiele actiebalk (alleen zichtbaar op kleine schermen).
 *
 * @param array $a Attributen.
 */
function vk_render_mobiele_balk( $a ) {
	$links = '';
	if ( vk_tel_href() ) {
		$links .= '<a href="' . esc_url( vk_tel_href(), array( 'tel' ) ) . '">' . vk_icon( 'telefoon' ) . '<span>Bellen</span></a>';
	}
	if ( vk_whatsapp_href() ) {
		$links .= '<a href="' . esc_url( vk_whatsapp_href( 'Hallo Van Keulen, ik heb een vraag over een stallingsplaats.' ) ) . '" target="_blank" rel="noopener">' . vk_icon( 'whatsapp' ) . '<span>WhatsApp</span></a>';
	}
	$links .= '<a class="vk-mobiel__cta" href="' . esc_url( vk_aanvraag_url() ) . '">' . vk_icon( 'aanvraag' ) . '<span>Stalling aanvragen</span></a>';

	return '<nav class="' . esc_attr( vk_cls( $a, 'vk-mobiel' ) ) . '" aria-label="Snel contact">' . $links . '</nav>';
}

/**
 * Copyrightregel.
 *
 * @param array $a Attributen.
 */
function vk_render_copyright( $a ) {
	$tekst = '© ' . wp_date( 'Y' ) . ' ' . vk_get( 'bedrijfsnaam' );
	if ( vk_filled( 'kvk' ) ) {
		$tekst .= ' · KvK ' . vk_get( 'kvk' );
	}
	return '<p class="' . esc_attr( vk_cls( $a, 'vk-copyright has-small-font-size' ) ) . '">' . esc_html( $tekst ) . '</p>';
}
