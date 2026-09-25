<?php
/**
 * Van Keulen → Livegang-check.
 *
 * Eén scherm dat vóór (en na) livegang controleert of:
 * - alle bedrijfsgegevens zijn ingevuld en er geen placeholders meer staan;
 * - er geen onbekende uitgaande links, geïnjecteerde scripts of verborgen
 *   (spam)links in pagina's, sjablonen, widgets of instellingen zitten;
 * - de technische basis (SSL, indexering, permalinks, beheerders, updates,
 *   PHP-bestanden in uploads) op orde is.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'vk-gegevens', 'Livegang-check', 'Livegang-check', 'manage_options', 'vk-livegang', 'vk_render_livegang' );
	},
	20
);

/**
 * Hosts die altijd zijn toegestaan in links.
 */
function vk_toegestane_hosts() {
	$eigen = wp_parse_url( home_url(), PHP_URL_HOST );
	return apply_filters(
		'vk_toegestane_hosts',
		array_filter(
			array(
				$eigen,
				'www.' . preg_replace( '/^www\./', '', (string) $eigen ),
				'vankeulencaravanstalling.nl',
				'www.vankeulencaravanstalling.nl',
				'www.google.com',
				'google.com',
				'maps.google.com',
				'maps.app.goo.gl',
				'goo.gl',
				'g.page',
				'wa.me',
				'api.whatsapp.com',
			)
		)
	);
}

/**
 * Alle externe links en verdachte code in een stuk HTML.
 *
 * @param string $html Inhoud.
 * @return array{links:string[],verdacht:string[]}
 */
function vk_scan_html( $html ) {
	$links    = array();
	$verdacht = array();
	if ( preg_match_all( '#(?:href|src|action|data-src)\s*=\s*["\']?\s*(https?:)?//([^/"\'\s>]+)([^"\'\s>]*)#i', (string) $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$host = strtolower( preg_replace( '/:\d+$/', '', $hit[2] ) );
			if ( ! in_array( $host, vk_toegestane_hosts(), true ) ) {
				$links[] = $host . $hit[3];
			}
		}
	}
	$patronen = array(
		'<script'                     => 'script-tag',
		'<iframe'                     => 'iframe',
		'eval('                       => 'eval()',
		'base64_decode'               => 'base64_decode',
		'document.write'              => 'document.write',
		'display:none'                => 'verborgen inhoud (display:none)',
		'display: none'               => 'verborgen inhoud (display:none)',
		'visibility:hidden'           => 'verborgen inhoud (visibility:hidden)',
		'left:-9999'                  => 'buiten beeld geplaatste inhoud',
		'font-size:0'                 => 'onzichtbare tekst (font-size:0)',
		'fromCharCode'                => 'versleutelde code (fromCharCode)',
	);
	$lower = strtolower( (string) $html );
	foreach ( $patronen as $needle => $label ) {
		if ( false !== strpos( $lower, strtolower( $needle ) ) ) {
			$verdacht[] = $label;
		}
	}
	return array(
		'links'    => array_values( array_unique( $links ) ),
		'verdacht' => array_values( array_unique( $verdacht ) ),
	);
}

/**
 * Het controlescherm.
 */
function vk_render_livegang() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;

	$rij = function ( $status, $titel, $detail = '' ) {
		$icons = array(
			'ok'   => '<span style="color:#2D6A43;font-weight:700">✓ In orde</span>',
			'let'  => '<span style="color:#9a6700;font-weight:700">! Controleren</span>',
			'fout' => '<span style="color:#b32d2e;font-weight:700">✗ Actie nodig</span>',
		);
		echo '<tr><td style="width:130px">' . $icons[ $status ] . '</td><td><strong>' . esc_html( $titel ) . '</strong>' . ( $detail ? '<br><span class="description">' . wp_kses_post( $detail ) . '</span>' : '' ) . '</td></tr>'; // phpcs:ignore
	};

	echo '<div class="wrap"><h1>Livegang-check</h1><p>Controleer dit scherm vóór livegang en daarna iedere paar maanden. Groen = in orde.</p>';

	/* 1. Bedrijfsgegevens ----------------------------------------------- */
	echo '<h2>1. Bedrijfsgegevens</h2><table class="widefat striped"><tbody>';
	$gegevens = array(
		'telefoon'       => 'Telefoonnummer (voor belknoppen en Google)',
		'email'          => 'E-mailadres',
		'google_profiel' => 'Link naar Google Bedrijfsprofiel',
		'lat'            => 'Coördinaten (exacte kaartlocatie)',
		'kvk'            => 'KvK-nummer',
	);
	foreach ( $gegevens as $k => $label ) {
		$rij( vk_filled( $k ) ? 'ok' : ( in_array( $k, array( 'telefoon', 'email' ), true ) ? 'fout' : 'let' ), $label, vk_filled( $k ) ? esc_html( (string) vk_get( $k ) ) : 'Niet ingevuld – <a href="' . esc_url( admin_url( 'admin.php?page=vk-gegevens' ) ) . '">invullen</a>' );
	}
	$rij( vk_heeft_openingstijden() ? 'ok' : 'let', 'Openingstijden / contactmomenten', vk_heeft_openingstijden() ? '' : 'Niet ingevuld. Op de website verschijnt nu de placeholder.' );
	$rij( 'onbekend' !== vk_get( 'beschikbaarheid' ) ? 'ok' : 'let', 'Beschikbaarheid', 'Status: ' . esc_html( vk_beschikbaarheid_opties()[ vk_get( 'beschikbaarheid' ) ] ?? '' ) );
	$rij( in_array( 'camper', (array) vk_get( 'objecten' ), true ) ? 'let' : 'ok', 'Camper in aanvraagformulier', in_array( 'camper', (array) vk_get( 'objecten' ), true ) ? 'Camper staat aan. Alleen laten staan als campers echt gestald kunnen worden.' : 'Uit (standaard).' );
	echo '</tbody></table>';

	/* 2. Placeholders ----------------------------------------------------- */
	echo '<h2>2. Nog aan te leveren teksten</h2>';
	$ph = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE post_status IN ('publish','draft','private','future') AND post_content LIKE %s ORDER BY post_type, menu_order",
			'%' . $wpdb->esc_like( VK_PLACEHOLDER ) . '%'
		)
	);
	echo '<table class="widefat striped"><tbody>';
	if ( ! $ph ) {
		$rij( 'ok', 'Geen placeholders meer gevonden' );
	}
	foreach ( (array) $ph as $p ) {
		$rij( 'fout', $p->post_title . ' (' . $p->post_type . ')', 'Bevat nog <code>' . esc_html( VK_PLACEHOLDER ) . '</code> – <a href="' . esc_url( get_edit_post_link( $p->ID ) ) . '">bewerken</a>' );
	}
	echo '</tbody></table>';

	/* 3. Links en scripts --------------------------------------------------- */
	echo '<h2>3. Uitgaande links, scripts en verborgen inhoud</h2><p>Alle pagina\'s, vragen, sjablonen, herbruikbare blokken, menu\'s en widgets zijn doorzocht. Toegestaan zonder melding: eigen domein, Google Maps en WhatsApp.</p>';
	$posts = $wpdb->get_results( "SELECT ID, post_title, post_type, post_content FROM {$wpdb->posts} WHERE post_status IN ('publish','draft','private','future') AND post_type NOT IN ('revision','vk_aanvraag','attachment','customize_changeset','oembed_cache')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$gevonden = 0;
	echo '<table class="widefat striped"><tbody>';
	foreach ( (array) $posts as $p ) {
		$scan = vk_scan_html( $p->post_content );
		if ( $scan['links'] || $scan['verdacht'] ) {
			++$gevonden;
			$detail = '';
			if ( $scan['links'] ) {
				$detail .= 'Externe links: <code>' . implode( '</code>, <code>', array_map( 'esc_html', array_slice( $scan['links'], 0, 15 ) ) ) . '</code><br>';
			}
			if ( $scan['verdacht'] ) {
				$detail .= 'Verdacht: ' . esc_html( implode( ', ', $scan['verdacht'] ) ) . '<br>';
			}
			$edit    = get_edit_post_link( $p->ID );
			$detail .= $edit ? '<a href="' . esc_url( $edit ) . '">bekijken</a>' : '';
			$rij( $scan['verdacht'] ? 'fout' : 'let', $p->post_title . ' (' . $p->post_type . ')', $detail );
		}
	}
	$opties = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'widget\_%' OR option_name LIKE 'theme\_mods\_%' OR option_name IN ('blogdescription','sidebars_widgets')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( (array) $opties as $o ) {
		$scan = vk_scan_html( $o->option_value );
		if ( $scan['links'] || $scan['verdacht'] ) {
			++$gevonden;
			$rij( 'fout', 'Instelling: ' . $o->option_name, 'Links: <code>' . esc_html( implode( ', ', $scan['links'] ) ) . '</code> ' . esc_html( implode( ', ', $scan['verdacht'] ) ) );
		}
	}
	if ( ! $gevonden ) {
		$rij( 'ok', 'Geen onbekende uitgaande links of verdachte code gevonden' );
	}
	echo '</tbody></table>';

	/* 4. Techniek ---------------------------------------------------------- */
	echo '<h2>4. Techniek &amp; beveiliging</h2><table class="widefat striped"><tbody>';
	$rij( is_ssl() || str_starts_with( home_url(), 'https://' ) ? 'ok' : 'fout', 'SSL (https)', 'Websiteadres: ' . esc_html( home_url() ) );
	$rij( get_option( 'blog_public' ) ? 'ok' : 'fout', 'Zichtbaar voor zoekmachines', get_option( 'blog_public' ) ? '' : 'Instellingen → Lezen → vinkje “Zoekmachines ontmoedigen” uitzetten bij livegang.' );
	$rij( '/%postname%/' === get_option( 'permalink_structure' ) ? 'ok' : 'fout', 'Nette URL\'s (/caravanstalling/)', 'Huidig: <code>' . esc_html( get_option( 'permalink_structure' ) ) . '</code>' );
	$rij( 'page' === get_option( 'show_on_front' ) ? 'ok' : 'fout', 'Homepage ingesteld als statische pagina' );

	$admins = get_users( array( 'role' => 'administrator', 'fields' => array( 'user_login' ) ) );
	$namen  = wp_list_pluck( $admins, 'user_login' );
	$rij( in_array( 'admin', $namen, true ) ? 'fout' : ( count( $namen ) > 2 ? 'let' : 'ok' ), 'Beheerdersaccounts (' . count( $namen ) . ')', esc_html( implode( ', ', $namen ) ) . ( in_array( 'admin', $namen, true ) ? ' – gebruikersnaam “admin” is onveilig.' : '' ) . ' Gebruik sterke wachtwoorden en tweestapsverificatie.' );

	$rij( ( defined( 'WP_DEBUG_DISPLAY' ) && ! WP_DEBUG_DISPLAY ) || ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'ok' : 'fout', 'Foutmeldingen verborgen voor bezoekers' );
	$rij( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ? 'ok' : 'let', 'Bestandseditor in beheer uitgeschakeld' );
	$rij( ! get_option( 'users_can_register' ) ? 'ok' : 'fout', 'Registratie van nieuwe gebruikers uit' );

	if ( ! function_exists( 'get_core_updates' ) ) {
		require_once ABSPATH . 'wp-admin/includes/update.php';
	}
	$core = get_core_updates();
	$rij( empty( $core ) || 'latest' === ( $core[0]->response ?? 'latest' ) ? 'ok' : 'fout', 'WordPress up-to-date', 'Versie ' . esc_html( get_bloginfo( 'version' ) ) );
	$plugin_updates = get_site_transient( 'update_plugins' );
	$aantal         = isset( $plugin_updates->response ) ? count( (array) $plugin_updates->response ) : 0;
	$rij( $aantal ? 'fout' : 'ok', 'Plugins up-to-date', $aantal ? $aantal . ' update(s) beschikbaar.' : '' );

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$actief = array();
	foreach ( get_plugins() as $file => $data ) {
		$actief[] = esc_html( $data['Name'] ) . ( is_plugin_active( $file ) ? '' : ' <em>(inactief – verwijderen)</em>' );
	}
	$rij( count( get_plugins() ) <= 5 ? 'ok' : 'let', 'Geïnstalleerde plugins (' . count( get_plugins() ) . ')', implode( ', ', $actief ) . '<br>Verwijder alles wat u niet herkent of niet gebruikt.' );
	$mu = get_mu_plugins();
	$rij( $mu ? 'let' : 'ok', 'Must-use plugins', $mu ? esc_html( implode( ', ', wp_list_pluck( $mu, 'Name' ) ) ) . ' – controleer of u deze kent.' : 'Geen.' );

	$uploads = wp_get_upload_dir()['basedir'];
	$php     = array();
	if ( is_dir( $uploads ) ) {
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $uploads, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $it as $f ) {
			if ( preg_match( '/\.(php\d?|phtml|phar)$/i', $f->getFilename() ) && 'index.php' !== $f->getFilename() ) {
				$php[] = str_replace( $uploads, '', $f->getPathname() );
				if ( count( $php ) > 20 ) {
					break;
				}
			}
		}
	}
	$rij( $php ? 'fout' : 'ok', 'Geen PHP-bestanden in de uploadmap', $php ? 'Gevonden: <code>' . esc_html( implode( ', ', $php ) ) . '</code> – dit wijst vaak op malware.' : '' );

	$rij( 'let', 'E-mail van het aanvraagformulier', 'Verstuur na livegang een testaanvraag via <a href="' . esc_url( vk_aanvraag_url() ) . '">het formulier</a> en controleer of deze binnenkomt op <strong>' . esc_html( vk_aanvraag_ontvanger() ) . '</strong>. Gebruik bij voorkeur SMTP van de hostingpartij (zie README).' );
	echo '</tbody></table></div>';
}
