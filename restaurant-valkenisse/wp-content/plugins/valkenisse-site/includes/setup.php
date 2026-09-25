<?php
/**
 * Eenmalige inrichting: pagina's, menu, categorieën en startinhoud.
 * Veilig om opnieuw te draaien: bestaande pagina's en inhoud worden niet overschreven.
 *
 * Starten via de melding in het dashboard of met WP-CLI: wp valkenisse setup
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

function valkenisse_setup_pages(): array {
	// slug => [titel, patroon, SEO-titel, SEO-omschrijving, bovenliggende slug].
	return array(
		'home'             => array( 'Home', 'valkenisse/page-home', 'Restaurant Valkenisse | Eten & overnachten aan de Zeeuwse kust', 'Restaurant Valkenisse ligt aan de duinen, in het bos en vlak bij het strand van Walcheren. Lunch, diner, pizza en Noord-Afrikaanse gerechten. Reserveer een tafel.' ),
		'restaurant'       => array( 'Restaurant', 'valkenisse/page-restaurant', 'Restaurant bij de duinen in Biggekerke | Restaurant Valkenisse', 'Lunchen of dineren aan de duinen van Walcheren, vlak bij het strand. Vis- en vleesspecialiteiten, pizza en Noord-Afrikaanse gerechten.' ),
		'menukaart'        => array( 'Menukaart', 'valkenisse/page-menukaart', 'Menukaart | Restaurant Valkenisse', 'Bekijk de menukaart van Restaurant Valkenisse: lunch, diner, pizza, Noord-Afrikaans menu, pannenkoeken en kindermenu.' ),
		'overnachten'      => array( 'Overnachten', 'valkenisse/page-overnachten', "Overnachten in Valkenisse | Studio's boven het restaurant", "Twee studio's voor 2 personen boven Restaurant Valkenisse, met eigen ingang, kitchenette en gratis wifi. Vlak bij duinen en strand." ),
		'feesten-partijen' => array( 'Feesten & Partijen', 'valkenisse/page-feesten', 'Feestlocatie op Walcheren | Feesten & partijen bij Restaurant Valkenisse', 'Iets te vieren? Verjaardagen, bruiloften en andere feesten, buffetten en catering bij Restaurant Valkenisse aan de Zeeuwse kust.' ),
		'galerij'          => array( 'Galerij', 'valkenisse/page-galerij', "Foto's | Restaurant Valkenisse", "Foto's van Restaurant Valkenisse: het restaurant, het terras, gerechten, de studio's en de omgeving aan de Zeeuwse kust." ),
		'contact'          => array( 'Contact', 'valkenisse/page-contact', 'Contact & route | Restaurant Valkenisse, Biggekerke', 'Adres, telefoon, openingstijden en route naar Restaurant Valkenisse, Valkenisseweg 76 in Biggekerke.' ),
		'reserveren'       => array( 'Reserveren', 'valkenisse/page-reserveren', 'Reserveer een tafel | Restaurant Valkenisse', 'Reserveer een tafel bij Restaurant Valkenisse. Stuur een reserveringsaanvraag of bel ons direct.' ),
		'nieuws'           => array( 'Nieuws', '', 'Nieuws | Restaurant Valkenisse', 'Actueel nieuws van Restaurant Valkenisse.' ),
		'privacybeleid'    => array( 'Privacybeleid', 'valkenisse/page-privacy', 'Privacybeleid | Restaurant Valkenisse', '' ),
		'cookiebeleid'     => array( 'Cookiebeleid', 'valkenisse/page-cookies', 'Cookiebeleid | Restaurant Valkenisse', '' ),
	);
}

function valkenisse_pattern_content( string $slug ): string {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	return $pattern ? (string) $pattern['content'] : '';
}

function valkenisse_run_setup(): array {
	$log = array();
	valkenisse_register_post_types();
	valkenisse_register_gallery_taxonomy();

	// Algemene instellingen.
	update_option( 'blogname', 'Restaurant Valkenisse' );
	update_option( 'blogdescription', 'Eten, drinken en overnachten aan de Zeeuwse kust' );
	update_option( 'timezone_string', 'Europe/Amsterdam' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	if ( false === get_option( VALKENISSE_OPTION ) ) {
		add_option( VALKENISSE_OPTION, valkenisse_default_settings() );
	}

	// Pagina's.
	$ids = array();
	foreach ( valkenisse_setup_pages() as $slug => [ $title, $pattern, $seo_title, $seo_desc ] ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			$log[]        = "Pagina bestaat al: {$title}";
			continue;
		}
		$template = in_array( $slug, array( 'privacybeleid', 'cookiebeleid' ), true ) ? 'page-tekst' : '';
		$id       = wp_insert_post(
			array(
				'post_type'     => 'page',
				'post_status'   => 'publish',
				'post_title'    => $title,
				'post_name'     => $slug,
				'post_content'  => $pattern ? valkenisse_pattern_content( $pattern ) : '',
				'page_template' => $template,
				'menu_order'    => count( $ids ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$ids[ $slug ] = $id;
			update_post_meta( $id, '_valk_seo_titel', $seo_title );
			update_post_meta( $id, '_valk_seo_omschrijving', $seo_desc );
			$log[] = "Pagina aangemaakt: {$title}";
		}
	}
	// Echte foto's uit het thema in de mediabibliotheek zetten (responsieve formaten, vervangbaar).
	if ( isset( $ids['home'] ) ) {
		$photo = valkenisse_import_theme_photo( 'restaurant-valkenisse.webp', 'Restaurant Valkenisse met terras tussen de bomen bij avondlicht', array( 'restaurant', 'terras' ) );
		if ( $photo ) {
			valkenisse_attach_photo_to_page( (int) $ids['home'], 'restaurant-valkenisse.webp', $photo );
			if ( ! has_post_thumbnail( (int) $ids['home'] ) ) {
				set_post_thumbnail( (int) $ids['home'], $photo );
			}
			$log[] = 'Foto van het restaurant in de mediabibliotheek gezet en op de homepage geplaatst';
		}
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] ?? 0 );
	update_option( 'page_for_posts', $ids['nieuws'] ?? 0 );
	update_option( 'wp_page_for_privacy_policy', $ids['privacybeleid'] ?? 0 );

	// Hoofdmenu (wordt door header, mobiel menu en footer gebruikt).
	$nav_id = (int) get_option( 'valkenisse_nav_id' );
	if ( ! $nav_id || 'wp_navigation' !== get_post_type( $nav_id ) ) {
		$links = '';
		foreach ( array( 'restaurant', 'menukaart', 'overnachten', 'feesten-partijen', 'galerij', 'contact' ) as $slug ) {
			if ( isset( $ids[ $slug ] ) ) {
				$links .= sprintf(
					'<!-- wp:navigation-link {"label":"%s","type":"page","id":%d,"url":"%s","kind":"post-type"} /-->',
					esc_attr( get_the_title( $ids[ $slug ] ) ),
					(int) $ids[ $slug ],
					esc_url( get_permalink( $ids[ $slug ] ) )
				);
			}
		}
		$nav_id = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => 'Hoofdmenu',
				'post_content' => $links,
			)
		);
		update_option( 'valkenisse_nav_id', (int) $nav_id );
		$log[] = 'Hoofdmenu aangemaakt';
	}

	// Menukaart-categorieën (structuur van de huidige website + gevraagde indeling).
	$categories = array(
		array( 'Lunch', 'lunch', 10, '', '' ),
		array( "Tosti's en broodjes", 'tostis-en-broodjes', 11, '', 'lunch' ),
		array( '12-uurtje', '12-uurtje', 12, '', 'lunch' ),
		array( 'Pannenkoeken, poffertjes & wafels', 'pannenkoeken-poffertjes-wafels', 15, '', '' ),
		array( 'Diner', 'diner', 20, 'Eén rekening per tafel.', '' ),
		array( 'Voorgerechten', 'voorgerechten', 21, '', 'diner' ),
		array( 'Vleesgerechten', 'vleesgerechten', 22, 'Alle vleesgerechten zijn inclusief friet.', 'diner' ),
		array( 'Visgerechten', 'visgerechten', 23, 'Alle visgerechten zijn inclusief friet.', 'diner' ),
		array( 'Vegetarisch', 'vegetarisch', 24, '', 'diner' ),
		array( 'Maaltijdsalades', 'maaltijdsalades', 25, '', 'diner' ),
		array( 'Bijgerechten', 'bijgerechten', 26, '', 'diner' ),
		array( 'Desserts', 'desserts', 27, '', 'diner' ),
		array( 'Noord-Afrikaans menu', 'noord-afrikaans', 30, 'Driegangenmenu: vooraf een gebakken sardientje op een bedje van frisse sla, daarna een tajine naar keuze en tot slot een kannetje verse muntthee.', '' ),
		array( 'Pizza', 'pizza', 40, '', '' ),
		array( 'Kindermenu', 'kindermenu', 50, 'Alle kindergerechten zijn inclusief kinderijsje en voor kinderen tot 12 jaar.', '' ),
		array( 'Dranken', 'dranken', 70, '', '' ),
	);
	foreach ( $categories as [ $name, $slug, $order, $desc, $parent ] ) {
		if ( term_exists( $slug, 'menu_categorie' ) ) {
			continue;
		}
		$parent_term = $parent ? get_term_by( 'slug', $parent, 'menu_categorie' ) : null;
		$term        = wp_insert_term( $name, 'menu_categorie', array( 'slug' => $slug, 'description' => $desc, 'parent' => $parent_term ? $parent_term->term_id : 0 ) );
		if ( ! is_wp_error( $term ) ) {
			update_term_meta( $term['term_id'], 'volgorde', $order );
		}
	}
	foreach ( array( 'Vegetarisch' => 'vegetarisch', 'Glutenvrij' => 'glutenvrij' ) as $name => $slug ) {
		if ( ! term_exists( $slug, 'dieet' ) ) {
			wp_insert_term( $name, 'dieet', array( 'slug' => $slug ) );
		}
	}
	foreach ( array( 'Restaurant', 'Terras', 'Gerechten', "Studio's", 'Feesten', 'Omgeving' ) as $name ) {
		if ( ! term_exists( sanitize_title( $name ), 'fotocategorie' ) ) {
			wp_insert_term( $name, 'fotocategorie' );
		}
	}

	// Foto bovenaan de pagina Overnachten.
	if ( isset( $ids['overnachten'] ) ) {
		$stay = valkenisse_import_theme_photo( 'overnachten-studio.webp', 'Zonnige entree van de studio met terras, bankje en olijfboompje', array( 'studios' ) );
		if ( $stay ) {
			valkenisse_attach_photo_to_page( (int) $ids['overnachten'], 'overnachten-studio.webp', $stay );
			if ( ! has_post_thumbnail( (int) $ids['overnachten'] ) ) {
				set_post_thumbnail( (int) $ids['overnachten'], $stay );
			}
		}
		// Fotogalerij van de studio's.
		foreach ( array(
			'studio-gang.webp'     => 'Lichte gang met houten vloer en sfeervolle verlichting',
			'studio-badkamer.webp' => 'Badkamer met houten wastafelmeubel en spiegel',
			'studio-douche.webp'   => 'Inloopdouche met regendouche en nis',
		) as $file => $alt ) {
			$photo = valkenisse_import_theme_photo( $file, $alt, array( 'studios' ) );
			if ( $photo ) {
				valkenisse_attach_gallery_photo( (int) $ids['overnachten'], $file, $photo );
			}
		}
	}

	// Menukaart-PDF in de mediabibliotheek en op de pagina Menukaart.
	if ( isset( $ids['menukaart'] ) ) {
		$pdf = valkenisse_import_theme_photo( VALKENISSE_DIR . 'data/Menukaart-Restaurant-Valkenisse.pdf', 'Menukaart Restaurant Valkenisse' );
		$content = (string) get_post_field( 'post_content', (int) $ids['menukaart'] );
		if ( $pdf && str_contains( $content, '<!-- wp:valkenisse/menukaart-pdf /-->' ) ) {
			$content = str_replace( '<!-- wp:valkenisse/menukaart-pdf /-->', '<!-- wp:valkenisse/menukaart-pdf {"pdfId":' . $pdf . '} /-->', $content );
			wp_update_post( array( 'ID' => (int) $ids['menukaart'], 'post_content' => wp_slash( $content ) ) );
			$log[] = 'Menukaart-PDF op de pagina Menukaart geplaatst';
		}
	}

	// Sfeerfoto's bij de dinerkaart (aangeleverd door het restaurant).
	$diner = get_term_by( 'slug', 'diner', 'menu_categorie' );
	if ( $diner && ! get_term_meta( $diner->term_id, 'fotos', true ) ) {
		$diner_photos = array_filter(
			array(
				valkenisse_import_theme_photo( 'diner-vleesgerecht.webp', 'Vleesgerecht met jus, gepofte trostomaatjes en krokante uitjes', array( 'gerechten' ) ),
				valkenisse_import_theme_photo( 'diner-gamba-pasta.webp', "Gamba's met spaghetti, knoflookolie en geroosterde trostomaatjes", array( 'gerechten' ) ),
			)
		);
		if ( $diner_photos ) {
			update_term_meta( $diner->term_id, 'fotos', implode( ',', $diner_photos ) );
			$log[] = "Twee foto's bij de dinerkaart geplaatst";
		}
	}

	// Startinhoud – uitsluitend wat op de huidige website staat (zie BRONNEN-EN-CONTROLE.md).
	if ( ! get_posts( array( 'post_type' => 'gerecht', 'posts_per_page' => 1, 'post_status' => 'any' ) ) ) {
		$result = valkenisse_import_csv( VALKENISSE_DIR . 'data/menukaart.csv', true );
		$log[]  = sprintf( 'Menukaart geïmporteerd: %d gerechten', $result['added'] );
	}

	if ( ! get_posts( array( 'post_type' => 'studio', 'posts_per_page' => 1, 'post_status' => 'any' ) ) ) {
		$facilities = "Geschikt voor 2 personen\nEigen ingang\nKitchenette\nZithoek\nSlaapkamer met boxsprings\nDouche en toilet\nBed- en badlinnen\nKoffie en thee\nGratis wifi\nGratis parkeren naast het restaurant";
		foreach ( array( 'Studio 1', 'Studio 2' ) as $i => $name ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'studio',
					'post_status'  => 'publish',
					'post_title'   => $name,
					'menu_order'   => $i,
					'post_excerpt' => 'Studio boven het restaurant voor 2 personen, met eigen ingang.',
					'post_content' => '<!-- wp:paragraph --><p>Comfortabele studio boven het restaurant met eigen ingang, een kitchenette, zithoek en een slaapkamer met boxsprings. Douche en toilet, bedlinnen, koffie en thee en gratis wifi zijn inbegrepen.</p><!-- /wp:paragraph -->',
				)
			);
			update_post_meta( $id, '_valk_personen', '2' );
			update_post_meta( $id, '_valk_faciliteiten', $facilities );
		}
		$log[] = "Twee studio's aangemaakt (naam en foto's nog aanvullen)";
	}

	if ( ! get_posts( array( 'post_type' => 'arrangement', 'posts_per_page' => 1, 'post_status' => 'any' ) ) ) {
		foreach ( array( 'Barbecuebuffet', 'Steengrill', 'Marokkaans buffet', 'Warm en koud buffet' ) as $i => $name ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'arrangement',
					'post_status' => 'draft',
					'post_title'  => $name,
					'menu_order'  => $i,
				)
			);
			update_post_meta( $id, '_valk_omschrijving', '[Omschrijving overnemen van de huidige website en controleren of dit buffet nog actueel is.]' );
			update_post_meta( $id, '_valk_prijs', '[prijs p.p.]' );
		}
		$log[] = 'Vier buffetten aangemaakt als CONCEPT – controleren en publiceren';
	}

	flush_rewrite_rules();
	update_option( 'valkenisse_setup_done', VALKENISSE_VERSION );
	return $log;
}

/**
 * Zet een foto uit de themamap in de mediabibliotheek (eenmalig) en geeft het ID terug.
 */
function valkenisse_import_theme_photo( string $file, string $alt, array $gallery_terms = array() ): int {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_valk_theme_source', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => basename( $file ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	$source = str_contains( $file, '/' ) ? $file : get_theme_file_path( 'assets/img/' . $file );
	if ( ! is_readable( $source ) ) {
		return 0;
	}
	$file = basename( $file );
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $file );
	copy( $source, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	if ( wp_attachment_is_image( $id ) ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	update_post_meta( $id, '_valk_theme_source', $file );
	if ( $gallery_terms ) {
		wp_set_object_terms( $id, $gallery_terms, 'fotocategorie' );
	}
	return (int) $id;
}

/**
 * Vervangt in een pagina de themaverwijzing naar een foto door de foto uit de mediabibliotheek
 * (met ID, zodat WordPress srcset/sizes toevoegt en de eigenaar hem kan vervangen).
 */
function valkenisse_attach_photo_to_page( int $page_id, string $file, int $attachment_id ): void {
	$content   = (string) get_post_field( 'post_content', $page_id );
	$theme_url = get_theme_file_uri( 'assets/img/' . $file );
	$new_url   = (string) wp_get_attachment_url( $attachment_id );
	if ( ! $new_url || ! str_contains( $content, $theme_url ) ) {
		return;
	}
	$content = str_replace( '"url":"' . $theme_url . '"', '"url":"' . $new_url . '","id":' . $attachment_id, $content );
	// Alleen het fotoblok met deze foto krijgt de wp-image-klasse.
	$content = preg_replace( '#<img class="wp-block-cover__image-background"( alt="[^"]*" src="' . preg_quote( $theme_url, '#' ) . '")#', '<img class="wp-block-cover__image-background wp-image-' . $attachment_id . '"$1', $content, 1 );
	$content = str_replace( $theme_url, $new_url, $content );
	wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( $content ) ) );
}

/**
 * Vervangt een galerijfoto uit het thema door de foto uit de mediabibliotheek (met ID en formaat "large").
 */
function valkenisse_attach_gallery_photo( int $page_id, string $file, int $attachment_id ): void {
	$content   = (string) get_post_field( 'post_content', $page_id );
	$theme_url = get_theme_file_uri( 'assets/img/' . $file );
	$large     = wp_get_attachment_image_src( $attachment_id, 'large' );
	if ( ! $large || ! str_contains( $content, $theme_url ) ) {
		return;
	}
	$pattern = '#<!-- wp:image \\{"lightbox":\\{"enabled":true\\}\\} -->\\n<figure class="wp-block-image"><img src="' . preg_quote( $theme_url, '#' ) . '" alt="([^"]*)"/></figure>#';
	$replace = '<!-- wp:image {"lightbox":{"enabled":true},"id":' . $attachment_id . ',"sizeSlug":"large","linkDestination":"none"} -->' . "\n" . '<figure class="wp-block-image size-large"><img src="' . esc_url( $large[0] ) . '" alt="$1" class="wp-image-' . $attachment_id . '"/></figure>';
	$new     = preg_replace( $pattern, $replace, $content, 1 );
	if ( $new && $new !== $content ) {
		wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( $new ) ) );
	}
}

/* Dashboardmelding met knop -------------------------------------------- */

add_action(
	'admin_notices',
	static function () {
		if ( get_option( 'valkenisse_setup_done' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( 'valkenisse' !== get_stylesheet() ) {
			echo '<div class="notice notice-warning"><p><strong>Restaurant Valkenisse:</strong> activeer eerst het thema "Valkenisse" (Weergave → Thema\'s) en richt daarna de website in.</p></div>';
			return;
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=valk_setup' ), 'valk_setup' );
		echo '<div class="notice notice-info"><p><strong>Restaurant Valkenisse:</strong> de website is nog niet ingericht. Hiermee worden de pagina\'s, het menu en de menukaart-categorieën aangemaakt. Bestaande pagina\'s blijven ongewijzigd.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Website inrichten</a></p></div>';
	}
);

add_action(
	'admin_post_valk_setup',
	static function () {
		check_admin_referer( 'valk_setup' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Geen toegang.' );
		}
		$log = valkenisse_run_setup();
		set_transient( 'valk_setup_log', $log, 300 );
		wp_safe_redirect( admin_url( 'index.php?valk_setup=1' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	static function () {
		$log = get_transient( 'valk_setup_log' );
		if ( ! $log ) {
			return;
		}
		delete_transient( 'valk_setup_log' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Website ingericht.</strong></p><ul style="list-style:disc;margin-left:2em">';
		foreach ( $log as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></div>';
	}
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'valkenisse setup',
		static function () {
			foreach ( valkenisse_run_setup() as $line ) {
				WP_CLI::log( $line );
			}
			WP_CLI::success( 'Restaurant Valkenisse is ingericht.' );
		}
	);
}
