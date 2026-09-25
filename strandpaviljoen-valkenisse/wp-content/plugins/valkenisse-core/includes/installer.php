<?php
/**
 * Eenmalige installatie: maakt alle pagina's aan met de juiste URL's, inhoud (uit de patronen van het thema),
 * paginasjablonen en SEO-teksten. Overschrijft nooit bestaande pagina's.
 */

defined( 'ABSPATH' ) || exit;

function vk_install_pages(): array {
	return array(
		array(
			'slug'     => 'home',
			'title'    => __( 'Home', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-home',
			'template' => '',
			'seo'      => array(
				__( 'Strandpaviljoen Valkenisse – aan zee bij Zoutelande, sinds 1956', 'valkenisse' ),
				__( 'Familiestrandpaviljoen op het strand van Valkenisse, vlak bij Zoutelande. Sinds 1956 een vertrouwde plek aan zee, met terras, strandhuisjes en strandverhuur.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'eten-drinken',
			'title'    => __( 'Eten & Drinken', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-eten-drinken',
			'template' => 'page-hero',
			'seo'      => array(
				__( 'Eten & drinken aan het strand bij Zoutelande | Strandpaviljoen Valkenisse', 'valkenisse' ),
				__( 'Een hapje en drankje met uitzicht over de Westerschelde en de Noordzee. Bekijk de kaart van Strandpaviljoen Valkenisse, op het strand bij Zoutelande.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'strandhuisjes',
			'title'    => __( 'Strandhuisjes', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-strandhuisjes',
			'template' => 'page-hero',
			'seo'      => array(
				__( 'Strandhuisje huren in Valkenisse bij Zoutelande | Strandpaviljoen Valkenisse', 'valkenisse' ),
				__( 'Huur een strandhuisje op het strand van Valkenisse, per dag, week of seizoen. Van begin april t/m september, inclusief strandstoelen, windscherm en tafeltje.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'strandverhuur',
			'title'    => __( 'Strandverhuur', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-strandverhuur',
			'template' => 'page-hero',
			'seo'      => array(
				__( 'Strandstoel, ligbed of parasol huren bij Zoutelande | Valkenisse', 'valkenisse' ),
				__( 'Strandstoelen, ligbedden, parasols en windschermen huren op het strand van Valkenisse, bij Zoutelande op Walcheren. Bekijk de actuele prijzen.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'over-ons',
			'title'    => __( 'Over ons', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-over-ons',
			'template' => 'page-hero',
			'seo'      => array(
				__( 'Over ons: familie Herwegh, sinds 1956 | Strandpaviljoen Valkenisse', 'valkenisse' ),
				__( 'In 1956 bouwde Sies Herwegh het strandpaviljoen vanaf het zand op. Sindsdien runt de familie Herwegh deze vertrouwde plek aan zee in Valkenisse.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'fotos',
			'title'    => __( "Foto's", 'valkenisse' ),
			'pattern'  => 'valkenisse/page-fotos',
			'template' => 'page-hero',
			'seo'      => array(
				__( "Foto's van het strand, terras en paviljoen | Strandpaviljoen Valkenisse", 'valkenisse' ),
				__( "Paviljoen, terras, strand, strandhuisjes en historie: bekijk de foto's van Strandpaviljoen Valkenisse op Walcheren, Zeeland.", 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'vacatures',
			'title'    => __( 'Vacatures', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-vacatures',
			'template' => 'page-hero',
			'seo'      => array(
				__( 'Vacatures: werken aan het strand | Strandpaviljoen Valkenisse', 'valkenisse' ),
				__( 'Werken aan zee in een familiebedrijf? Bekijk de actuele vacatures van Strandpaviljoen Valkenisse bij Zoutelande.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'contact',
			'title'    => __( 'Contact', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-contact',
			'template' => 'page-hero',
			'seo'      => array(
				__( 'Contact, route & openingstijden | Strandpaviljoen Valkenisse', 'valkenisse' ),
				__( 'Adres voor navigatie, postadres, telefoon, e-mail en openingstijden van Strandpaviljoen Valkenisse, bij de duinovergang Vossenhol in Groot Valkenisse.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'veelgestelde-vragen',
			'title'    => __( 'Veelgestelde vragen', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-faq',
			'template' => '',
			'seo'      => array(
				__( 'Veelgestelde vragen | Strandpaviljoen Valkenisse', 'valkenisse' ),
				__( 'Openingstijden, strandhuisjes reserveren, route en parkeren: antwoorden op de meest gestelde vragen over Strandpaviljoen Valkenisse.', 'valkenisse' ),
			),
		),
		array(
			'slug'     => 'privacybeleid',
			'title'    => __( 'Privacybeleid', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-legal',
			'template' => '',
			'seo'      => array( '', '' ),
		),
		array(
			'slug'     => 'cookiebeleid',
			'title'    => __( 'Cookiebeleid', 'valkenisse' ),
			'pattern'  => 'valkenisse/page-legal',
			'template' => '',
			'seo'      => array( '', '' ),
		),
	);
}

/**
 * Vervang <!-- wp:pattern {"slug":"…"} /--> door de inhoud van dat patroon, zodat de familie
 * de teksten en foto's direct in de pagina kan bewerken.
 */
function vk_expand_patterns( string $content, int $depth = 0 ): string {
	if ( $depth > 4 ) {
		return $content;
	}
	return (string) preg_replace_callback(
		'/<!--\s*wp:pattern\s+(\{.*?\})\s*\/-->/',
		static function ( $m ) use ( $depth ) {
			$attrs   = json_decode( $m[1], true );
			$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $attrs['slug'] ?? '' );
			return $pattern ? vk_expand_patterns( $pattern['content'], $depth + 1 ) : '';
		},
		$content
	);
}

function vk_run_installer(): array {
	$log      = array();
	$registry = WP_Block_Patterns_Registry::get_instance();

	foreach ( vk_install_pages() as $def ) {
		$existing = get_page_by_path( $def['slug'] );
		if ( $existing ) {
			$log[] = sprintf( __( 'Bestond al, niet aangepast: %s', 'valkenisse' ), $def['title'] );
			$page_id = $existing->ID;
		} else {
			$pattern = $registry->get_registered( $def['pattern'] );
			$content = $pattern ? vk_expand_patterns( $pattern['content'] ) : '';
			if ( 'valkenisse/page-legal' === $def['pattern'] ) {
				$content = str_replace( '{{title}}', esc_html( $def['title'] ), $content );
			}
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $def['title'],
					'post_name'    => $def['slug'],
					'post_content' => $content,
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				$log[] = $page_id->get_error_message();
				continue;
			}
			if ( $def['template'] ) {
				update_post_meta( $page_id, '_wp_page_template', $def['template'] );
			}
			list( $seo_title, $seo_desc ) = $def['seo'];
			if ( $seo_title ) {
				foreach ( array( 'vk_seo_title', 'rank_math_title', '_yoast_wpseo_title' ) as $key ) {
					update_post_meta( $page_id, $key, $seo_title );
				}
				foreach ( array( 'vk_seo_description', 'rank_math_description', '_yoast_wpseo_metadesc' ) as $key ) {
					update_post_meta( $page_id, $key, $seo_desc );
				}
			}
			$log[] = sprintf( __( 'Aangemaakt: %s', 'valkenisse' ), $def['title'] );
		}

		if ( 'home' === $def['slug'] ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
		}
		if ( 'privacybeleid' === $def['slug'] ) {
			update_option( 'wp_page_for_privacy_policy', $page_id );
		}
	}

	// Algemene instellingen.
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$log[] = __( 'Permalinks ingesteld op /paginanaam/.', 'valkenisse' );
	}
	if ( in_array( get_option( 'blogdescription' ), array( '', 'Just another WordPress site', 'Zomaar weer een WordPress-site' ), true ) ) {
		update_option( 'blogdescription', __( 'Sinds 1956 een vertrouwde plek aan zee.', 'valkenisse' ) );
	}
	if ( in_array( get_option( 'blogname' ), array( '', 'My WordPress Website', 'Mijn WordPress-website', 'WordPress' ), true ) ) {
		update_option( 'blogname', 'Strandpaviljoen Valkenisse' );
	}
	if ( ! get_option( 'timezone_string' ) ) {
		update_option( 'timezone_string', 'Europe/Amsterdam' );
	}
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );

	vk_seed_terms();
	flush_rewrite_rules();

	return $log;
}

add_action( 'admin_menu', static function () {
	add_submenu_page( 'valkenisse', __( 'Installatie', 'valkenisse' ), __( 'Installatie', 'valkenisse' ), 'manage_options', 'valkenisse-install', 'vk_render_installer_page', 99 );
}, 20 );

function vk_render_installer_page(): void {
	echo '<div class="wrap vk-admin"><h1>' . esc_html__( 'Installatie', 'valkenisse' ) . '</h1>';

	if ( isset( $_POST['vk_install'] ) && check_admin_referer( 'vk_install' ) && current_user_can( 'manage_options' ) ) {
		$log = vk_run_installer();
		echo '<div class="notice notice-success"><ul>';
		foreach ( $log as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></div>';
	}

	if ( 'valkenisse' !== get_template() ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Activeer eerst het thema "Valkenisse" (Weergave → Thema\'s); de pagina-inhoud komt uit dat thema.', 'valkenisse' ) . '</p></div>';
	}

	echo '<div class="vk-card"><p>' . esc_html__( 'Maakt alle pagina\'s aan (Home, Eten & Drinken, Strandhuisjes, Strandverhuur, Over ons, Foto\'s, Vacatures, Contact, Veelgestelde vragen, Privacy- en Cookiebeleid) met nette URL\'s, inhoud en SEO-teksten. Bestaande pagina\'s worden niet aangepast. Veilig om vaker te draaien.', 'valkenisse' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'vk_install' );
	submit_button( __( 'Pagina\'s aanmaken', 'valkenisse' ), 'primary', 'vk_install' );
	echo '</form></div></div>';
}
