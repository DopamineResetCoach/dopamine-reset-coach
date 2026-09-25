<?php
/**
 * Van Keulen → Site inrichten.
 *
 * Richt een schone WordPress-installatie in één klik in: pagina's met inhoud,
 * homepage, nette URL's, veelgestelde vragen en de (tijdelijke) beelden in de
 * Mediabibliotheek. Bestaande pagina's worden niet overschreven, tenzij dat
 * expliciet wordt aangevinkt. Ook beschikbaar via WP-CLI:
 *
 *     wp vankeulen inrichten [--overschrijven]
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'vk-gegevens', 'Site inrichten', 'Site inrichten', 'manage_options', 'vk-inrichten', 'vk_render_setup_page', 30 );
	},
	30
);

/**
 * Pagina-definities. 'patroon' verwijst naar patterns/<patroon>.php in het thema.
 */
function vk_setup_paginas() {
	return array(
		'home'                         => array(
			'titel'        => 'Home',
			'patroon'      => 'pagina-home',
			'volgorde'     => 0,
			'seo_titel'    => 'Caravanstalling Biggekerke, Walcheren | Van Keulen Caravanstalling',
			'omschrijving' => 'Van Keulen Caravanstalling in Biggekerke (Walcheren, Zeeland): stalling voor caravans, boten, vouwwagens, aanhangers, strandhuisjes en slaaphuisjes. Vraag direct een stallingsplaats aan.',
		),
		'caravanstalling'              => array(
			'titel'        => 'Caravanstalling in Biggekerke',
			'patroon'      => 'pagina-caravanstalling',
			'volgorde'     => 10,
			'beeld'        => 'caravan',
			'samenvatting' => 'Uw caravan binnen of buiten gestald op Walcheren, bij Van Keulen Caravanstalling in Biggekerke. Voor de winter of het hele jaar.',
			'seo_titel'    => 'Caravanstalling Zeeland – caravan stallen in Biggekerke | Van Keulen',
			'omschrijving' => 'Caravan stallen in Zeeland? Van Keulen Caravanstalling in Biggekerke (Walcheren) heeft binnenstalling in droge loodsen en een verhard buitenterrein. Vraag een stallingsplaats aan.',
		),
		'bootstalling'                 => array(
			'titel'        => 'Bootstalling op Walcheren',
			'patroon'      => 'pagina-bootstalling',
			'volgorde'     => 20,
			'beeld'        => 'boot',
			'samenvatting' => 'Na het vaarseizoen uw boot stallen in Biggekerke, midden op Walcheren. Informeer naar de mogelijkheden voor uw boot.',
			'seo_titel'    => 'Bootstalling Walcheren, Zeeland | Van Keulen Caravanstalling',
			'omschrijving' => 'Bootstalling op Walcheren: stal uw boot bij Van Keulen Caravanstalling in Biggekerke, Zeeland. Geef type en afmetingen door en vraag vrijblijvend naar de mogelijkheden.',
		),
		'vouwwagen-aanhanger-stalling' => array(
			'titel'        => 'Vouwwagen & aanhanger stalling',
			'patroon'      => 'pagina-vouwwagen-aanhanger',
			'volgorde'     => 30,
			'beeld'        => 'aanhanger',
			'samenvatting' => 'Geen ruimte thuis voor uw vouwwagen of aanhangwagen? Stal hem bij Van Keulen in Biggekerke op Walcheren.',
			'seo_titel'    => 'Vouwwagen & aanhanger stalling Zeeland | Van Keulen Biggekerke',
			'omschrijving' => 'Vouwwagen of aanhangwagen stallen in Zeeland? Van Keulen Caravanstalling in Biggekerke (Walcheren) biedt binnen- en buitenstalling. Vraag direct een stallingsplaats aan.',
		),
		'strandhuisjes-stalling'       => array(
			'titel'        => 'Strandhuisjes & slaaphuisjes stallen',
			'patroon'      => 'pagina-strandhuisjes',
			'volgorde'     => 40,
			'beeld'        => 'strandhuisjes',
			'samenvatting' => 'Stalling buiten het strandseizoen voor strandhuisjes en slaaphuisjes van de Zeeuwse kust, in Biggekerke op Walcheren.',
			'seo_titel'    => 'Strandhuisje stallen Zeeland – winterstalling | Van Keulen',
			'omschrijving' => 'Winterstalling voor strandhuisjes en slaaphuisjes van de Zeeuwse kust. Van Keulen Caravanstalling in Biggekerke, Walcheren. Informeer naar de mogelijkheden.',
		),
		'caravanstalling-walcheren'    => array(
			'titel'        => 'Stalling op Walcheren',
			'patroon'      => 'pagina-walcheren',
			'volgorde'     => 50,
			'beeld'        => 'walcheren',
			'samenvatting' => 'Caravanstalling centraal op Walcheren: Biggekerke ligt tussen Middelburg, Vlissingen en de kust bij Zoutelande en Domburg.',
			'seo_titel'    => 'Caravanstalling Walcheren – centraal in Biggekerke | Van Keulen',
			'omschrijving' => 'Caravanstalling op Walcheren nodig? Van Keulen in Biggekerke ligt centraal tussen Middelburg, Vlissingen, Zoutelande en Domburg. Stalling voor caravan, boot en strandhuisje.',
		),
		'over-ons'                     => array(
			'titel'        => 'Over Van Keulen Caravanstalling',
			'patroon'      => 'pagina-over-ons',
			'volgorde'     => 60,
			'beeld'        => 'loods',
			'samenvatting' => 'Van Keulen Caravanstalling is een stallingsbedrijf in Biggekerke op Walcheren, met binnenstalling in loodsen en een verhard buitenterrein.',
			'seo_titel'    => 'Over ons | Van Keulen Caravanstalling Biggekerke',
			'omschrijving' => 'Maak kennis met Van Keulen Caravanstalling in Biggekerke: stalling voor caravans, boten, vouwwagens, aanhangers, strandhuisjes en slaaphuisjes op Walcheren.',
		),
		'veelgestelde-vragen'          => array(
			'titel'        => 'Veelgestelde vragen',
			'patroon'      => 'pagina-veelgestelde-vragen',
			'volgorde'     => 70,
			'samenvatting' => 'Antwoorden op de meest gestelde vragen over stallen bij Van Keulen Caravanstalling in Biggekerke.',
			'seo_titel'    => 'Veelgestelde vragen over caravanstalling | Van Keulen',
			'omschrijving' => 'Wat kan ik stallen, waar zit de stalling en wat kost een stallingsplaats? Antwoorden op veelgestelde vragen over Van Keulen Caravanstalling in Biggekerke.',
		),
		'contact'                      => array(
			'titel'        => 'Contact en stallingsplaats aanvragen',
			'patroon'      => 'pagina-contact',
			'volgorde'     => 80,
			'samenvatting' => 'Vraag in vier korte stappen een stallingsplaats aan, of neem direct contact op met Van Keulen Caravanstalling in Biggekerke.',
			'seo_titel'    => 'Contact & stallingsplaats aanvragen | Van Keulen Caravanstalling',
			'omschrijving' => 'Vraag een stallingsplaats aan bij Van Keulen Caravanstalling, Dorpsstraat 57A in Biggekerke. Contactgegevens, route en aanvraagformulier.',
		),
		'privacybeleid'                => array(
			'titel'        => 'Privacybeleid',
			'patroon'      => 'pagina-privacybeleid',
			'volgorde'     => 90,
			'sjabloon'     => 'page-zonder-oproep',
			'samenvatting' => 'Hoe Van Keulen Caravanstalling omgaat met uw persoonsgegevens.',
			'noindex'      => true,
		),
		'cookiebeleid'                 => array(
			'titel'        => 'Cookiebeleid',
			'patroon'      => 'pagina-cookiebeleid',
			'volgorde'     => 91,
			'sjabloon'     => 'page-zonder-oproep',
			'samenvatting' => 'Welke cookies deze website gebruikt (vrijwel geen).',
			'noindex'      => true,
		),
	);
}

/**
 * Vragen voor de FAQ. Antwoorden zonder bevestigde informatie bevatten de
 * placeholder en worden niet aan Google doorgegeven tot ze zijn ingevuld.
 */
function vk_setup_faq() {
	$p = VK_PLACEHOLDER;
	return array(
		'Wat kan ik bij Van Keulen stallen?'                       => 'Bij Van Keulen Caravanstalling kunt u terecht voor de stalling van caravans, boten, vouwwagens, aanhangwagens, strandhuisjes en slaaphuisjes. Twijfelt u of uw object geschikt is? Vraag het vrijblijvend na via het aanvraagformulier.',
		'Waar bevindt de stalling zich?'                           => 'De stalling ligt in Biggekerke, op Walcheren in Zeeland. Het adres is [vk_adres]. Op de contactpagina vindt u een kaart en kunt u direct uw route plannen.',
		'Is er binnenstalling en buitenstalling?'                  => 'Ja. Er zijn droge, geventileerde loodsen voor binnenstalling en er is een verhard buitenterrein van ongeveer 2.000 m². Welke plek geschikt en beschikbaar is, hangt af van uw object en de periode.',
		'Kan ik ook een boot stallen?'                             => 'Ja, bij Van Keulen Caravanstalling kunt u ook een boot stallen. Geef in de aanvraag het type en de afmetingen door (bij een boot op een trailer: inclusief trailer), dan hoort u wat er mogelijk is.',
		'Kan ik een strandhuisje of slaaphuisje stallen?'          => 'Ja. Strandhuisjes en slaaphuisjes die tijdens het seizoen langs de Zeeuwse kust staan, kunnen buiten het seizoen bij Van Keulen worden gestald. Vermeld in de aanvraag de afmetingen van uw strandhuisje of slaaphuisje.',
		'Verzorgt Van Keulen ook het vervoer van mijn strandhuisje?' => $p,
		'Hoe vraag ik een stallingsplaats aan?'                    => 'Via het aanvraagformulier op deze website. In vier korte stappen geeft u door wat u wilt stallen, de afmetingen, de gewenste periode en uw contactgegevens. Van Keulen Caravanstalling neemt daarna contact met u op. Bellen of mailen kan natuurlijk ook.',
		'Wat kost een stallingsplaats?'                            => 'De prijs van een stallingsplaats kan afhankelijk zijn van het type object, het formaat en de gewenste stallingsperiode. Vraag vrijblijvend een prijs aan via het aanvraagformulier.',
		'Wanneer kan ik mijn caravan brengen en ophalen?'          => $p,
		'Kan ik tijdens de stallingsperiode bij mijn caravan of boot?' => $p,
		'Is er momenteel plaats beschikbaar?'                      => 'De beschikbaarheid verschilt per soort stalling en per periode. Vraag het na via het aanvraagformulier of neem contact op; u hoort dan of er plaats is voor uw caravan, boot of strandhuisje.',
		'Kan ik ook een camper stallen?'                           => $p,
		'Hoe zit het met de verzekering tijdens de stalling?'      => $p,
	);
}

/**
 * Meegeleverde beelden in de Mediabibliotheek zetten.
 *
 * @return array<string,int>
 */
function vk_setup_media() {
	$map = (array) get_option( 'vankeulen_media', array() );
	if ( ! function_exists( 'vankeulen_img_alts' ) ) {
		return $map;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	foreach ( vankeulen_img_alts() as $key => $alt ) {
		if ( ! empty( $map[ $key ] ) && get_post( $map[ $key ] ) ) {
			continue;
		}
		$bron = get_theme_file_path( 'assets/images/' . $key . '.webp' );
		if ( ! file_exists( $bron ) ) {
			continue;
		}
		$tmp = wp_tempnam( $key . '.webp' );
		copy( $bron, $tmp );
		$id = media_handle_sideload(
			array(
				'name'     => 'vankeulen-' . $key . '.webp',
				'tmp_name' => $tmp,
			),
			0,
			'Tijdelijk beeld – vervangen door echte foto: ' . $alt
		);
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			continue;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		$map[ $key ] = $id;
	}
	update_option( 'vankeulen_media', $map, false );
	return $map;
}

/**
 * Inhoud van een paginapatroon uit het thema.
 *
 * @param string $patroon Bestandsnaam zonder .php.
 */
function vk_setup_patroon( $patroon ) {
	$file = get_theme_file_path( 'patterns/' . $patroon . '.php' );
	if ( ! file_exists( $file ) ) {
		return '';
	}
	ob_start();
	include $file;
	return trim( ob_get_clean() );
}

/**
 * Inrichten.
 *
 * @param bool $overschrijven Bestaande pagina's overschrijven.
 * @return string[] Logregels.
 */
function vk_setup_run( $overschrijven = false ) {
	$log = array();

	if ( 'vankeulen' !== get_stylesheet() ) {
		$log[] = 'Let op: activeer eerst het thema “Van Keulen Caravanstalling” (Weergave → Thema\'s).';
		return $log;
	}
	if ( ! function_exists( 'vankeulen_img_block' ) ) {
		// Thema is in dezelfde aanroep geactiveerd (bijv. via WP-CLI).
		require_once get_theme_file_path( 'functions.php' );
	}

	// Basisinstellingen.
	update_option( 'blogname', 'Van Keulen Caravanstalling' );
	update_option( 'blogdescription', 'Stalling voor caravan, boot, vouwwagen, aanhanger en strandhuisje in Biggekerke, Walcheren' );
	update_option( 'timezone_string', 'Europe/Amsterdam' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'users_can_register', 0 );
	update_option( 'uploads_use_yearmonth_folders', 1 );
	if ( false === get_option( 'vk_settings' ) ) {
		add_option( 'vk_settings', vk_default_settings() );
	}
	$log[] = 'Basisinstellingen (naam, tijdzone, reacties uit) opgeslagen.';

	// Standaardinhoud van WordPress opruimen (alleen als die onaangeroerd is).
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'voorbeeld-pagina' => 'page' ) as $slug => $type ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		if ( $p && $p->post_modified_gmt === $p->post_date_gmt ) {
			wp_delete_post( $p->ID, true );
			$log[] = 'Voorbeeldinhoud “' . $slug . '” verwijderd.';
		}
	}
	$priv_default = get_page_by_path( 'privacy-policy' );
	if ( $priv_default && 'draft' === $priv_default->post_status ) {
		wp_delete_post( $priv_default->ID, true );
	}

	// Beelden.
	$media = vk_setup_media();
	$log[] = count( $media ) . ' beelden staan in de Mediabibliotheek.';

	// Pagina's.
	$ids = array();
	foreach ( vk_setup_paginas() as $slug => $def ) {
		$bestaand = get_page_by_path( $slug );
		if ( $bestaand && ! $overschrijven ) {
			$ids[ $slug ] = $bestaand->ID;
			$log[]        = 'Pagina “' . $def['titel'] . '” bestaat al – overgeslagen.';
			continue;
		}
		$data = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $def['titel'],
			'post_name'    => $slug,
			'post_content' => vk_setup_patroon( $def['patroon'] ),
			'post_excerpt' => $def['samenvatting'] ?? '',
			'menu_order'   => $def['volgorde'],
		);
		if ( $bestaand ) {
			$data['ID'] = $bestaand->ID;
		}
		$id = wp_insert_post( wp_slash( $data ), true );
		if ( is_wp_error( $id ) ) {
			$log[] = 'Fout bij “' . $def['titel'] . '”: ' . $id->get_error_message();
			continue;
		}
		$ids[ $slug ] = $id;
		update_post_meta( $id, '_wp_page_template', $def['sjabloon'] ?? '' );
		update_post_meta( $id, '_vk_seo_titel', $def['seo_titel'] ?? '' );
		update_post_meta( $id, '_vk_seo_omschrijving', $def['omschrijving'] ?? '' );
		update_post_meta( $id, '_vk_noindex', ! empty( $def['noindex'] ) );
		if ( ! empty( $def['beeld'] ) && ! empty( $media[ $def['beeld'] ] ) ) {
			set_post_thumbnail( $id, $media[ $def['beeld'] ] );
		}
		$log[] = 'Pagina “' . $def['titel'] . '” ' . ( $bestaand ? 'bijgewerkt' : 'aangemaakt' ) . ': /' . $slug . '/';
	}

	// Homepage en privacypagina.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
		update_option( 'page_for_posts', 0 );
	}
	if ( ! empty( $ids['privacybeleid'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['privacybeleid'] );
	}

	// Veelgestelde vragen (alleen als er nog geen zijn).
	if ( ! get_posts( array( 'post_type' => 'vk_faq', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
		$i = 0;
		foreach ( vk_setup_faq() as $vraag => $antwoord ) {
			wp_insert_post(
				wp_slash(
					array(
						'post_type'    => 'vk_faq',
						'post_status'  => 'publish',
						'post_title'   => $vraag,
						'post_content' => "<!-- wp:paragraph -->\n<p>" . esc_html( $antwoord ) . "</p>\n<!-- /wp:paragraph -->",
						'menu_order'   => ( $i += 10 ),
					)
				)
			);
		}
		$log[] = count( vk_setup_faq() ) . ' veelgestelde vragen toegevoegd.';
	}

	// Nette URL's.
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();
	$log[] = 'Nette URL\'s ingesteld (/%postname%/).';

	return $log;
}

/**
 * Beheerscherm.
 */
function vk_render_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$log = array();
	if ( isset( $_POST['vk_inrichten'] ) && check_admin_referer( 'vk_inrichten' ) ) {
		$log = vk_setup_run( ! empty( $_POST['overschrijven'] ) );
	}
	?>
	<div class="wrap">
		<h1>Site inrichten</h1>
		<p style="max-width:720px">Maakt alle pagina's aan (Home, Caravanstalling, Bootstalling, Vouwwagen &amp; aanhanger, Strandhuisjes, Stalling op Walcheren, Over ons, Veelgestelde vragen, Contact, Privacy- en Cookiebeleid), stelt de homepage en nette URL's in en vult de veelgestelde vragen. Bestaande pagina's blijven ongewijzigd, tenzij u “overschrijven” aanvinkt.</p>
		<?php if ( $log ) : ?>
			<div class="notice notice-success"><ul style="list-style:disc;padding-left:1.5em">
				<?php foreach ( $log as $regel ) : ?>
					<li><?php echo esc_html( $regel ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'vk_inrichten' ); ?>
			<p><label><input type="checkbox" name="overschrijven" value="1"> Bestaande pagina's overschrijven met de standaardinhoud <strong>(eigen wijzigingen gaan verloren)</strong></label></p>
			<?php submit_button( 'Site inrichten', 'primary', 'vk_inrichten' ); ?>
		</form>
	</div>
	<?php
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'vankeulen inrichten',
		function ( $args, $assoc ) {
			foreach ( vk_setup_run( ! empty( $assoc['overschrijven'] ) ) as $regel ) {
				WP_CLI::log( $regel );
			}
			WP_CLI::success( 'Klaar.' );
		}
	);
}
