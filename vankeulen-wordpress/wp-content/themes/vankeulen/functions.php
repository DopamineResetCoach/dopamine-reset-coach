<?php
/**
 * Van Keulen Caravanstalling – blokthema.
 *
 * Het thema regelt uitsluitend de vormgeving (kleuren, typografie, sjablonen,
 * patronen). Bedrijfsgegevens, formulieren, FAQ en SEO zitten in de plugin
 * "Van Keulen Core", zodat een thema-wissel nooit gegevens kost.
 *
 * @package VanKeulen
 */

defined( 'ABSPATH' ) || exit;

define( 'VANKEULEN_THEME_VERSION', '1.0.0' );

/**
 * Thema-ondersteuning.
 */
add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'custom-logo', array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_editor_style( 'assets/css/theme.css' );

		// Pagina's krijgen een samenvatting: die verschijnt in de paginakop én
		// dient als standaard meta-omschrijving voor Google.
		add_post_type_support( 'page', 'excerpt' );

		// Grote hero-beelden: één extra formaat voor brede schermen.
		add_image_size( 'vk-breed', 1920, 0, false );
	}
);

/**
 * Stylesheet. WordPress plaatst dit bestand inline in de <head> (via 'path'),
 * waardoor er geen render-blokkerend CSS-verzoek nodig is.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		// theme.min.css wordt gemaakt met tools/minify-css.py; valt terug op theme.css.
		$rel  = file_exists( get_theme_file_path( 'assets/css/theme.min.css' ) ) && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'assets/css/theme.min.css' : 'assets/css/theme.css';
		$path = get_theme_file_path( $rel );
		wp_enqueue_style( 'vankeulen', get_theme_file_uri( $rel ), array(), (string) filemtime( $path ) );
		wp_style_add_data( 'vankeulen', 'path', $path );
	}
);

// Ruimere grens voor inline CSS (standaard 20 kB): alle blok- en themastijlen
// staan dan in de HTML, zonder render-blokkerende CSS-verzoeken. Gecomprimeerd
// (gzip/brotli) is dat slechts enkele kB extra.
add_filter(
	'styles_inline_size_limit',
	function () {
		return 110000;
	}
);

/**
 * Het lettertype vooraf laden, zodat de hero-kop direct in Archivo verschijnt.
 */
add_action(
	'wp_head',
	function () {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/archivo-latin-wght-normal.woff2' ) )
		);
	},
	1
);

/**
 * Patrooncategorie voor de eigen secties.
 */
add_action(
	'init',
	function () {
		register_block_pattern_category( 'vankeulen', array( 'label' => 'Van Keulen – secties' ) );
		register_block_pattern_category( 'vankeulen-paginas', array( 'label' => 'Van Keulen – complete pagina\'s' ) );

		// Blokstijlen die de eigenaar in de editor kan kiezen.
		register_block_style( 'core/group', array( 'name' => 'vk-kaart', 'label' => 'Kaart (wit vlak)' ) );
		register_block_style( 'core/paragraph', array( 'name' => 'vk-bovenkop', 'label' => 'Bovenkop (klein, hoofdletters)' ) );
		register_block_style( 'core/list', array( 'name' => 'vk-vinkjes', 'label' => 'Vinkjeslijst' ) );
		register_block_style( 'core/image', array( 'name' => 'vk-foto', 'label' => 'Foto met afgeronde hoeken' ) );
	}
);

/**
 * Afbeelding voor een patroon.
 *
 * Bij het inrichten van de site (plugin Van Keulen Core → Site inrichten)
 * worden de meegeleverde beelden in de Mediabibliotheek geplaatst. Patronen
 * gebruiken dan de bijlage (met srcset, lazy loading en bewerkbare alt-tekst).
 * Zonder import valt het patroon terug op het bestand in het thema.
 *
 * @param string $key Bestandsnaam zonder extensie, bijv. 'hero-terrein'.
 * @return array{id:int,url:string,alt:string}
 */
function vankeulen_img( $key ) {
	$map = get_option( 'vankeulen_media', array() );
	$alt = vankeulen_img_alts()[ $key ] ?? '';

	if ( ! empty( $map[ $key ] ) && wp_attachment_is_image( (int) $map[ $key ] ) ) {
		$id  = (int) $map[ $key ];
		$src = wp_get_attachment_image_src( $id, 'full' );
		$att = get_post_meta( $id, '_wp_attachment_image_alt', true );
		return array(
			'id'  => $id,
			'url' => $src ? $src[0] : '',
			'alt' => $att ? $att : $alt,
		);
	}

	return array(
		'id'  => 0,
		'url' => get_theme_file_uri( 'assets/images/' . $key . '.webp' ),
		'alt' => $alt,
	);
}

/**
 * Standaard alt-teksten voor de meegeleverde (tijdelijke) beelden.
 * Vervang de beelden door echte foto's van Van Keulen en pas de alt-tekst
 * in de Mediabibliotheek aan.
 */
function vankeulen_img_alts() {
	return array(
		'hero-terrein'    => 'Caravans binnen gestald in de loods van Van Keulen Caravanstalling in Biggekerke',
		'caravan'         => 'Gestalde caravans in de droge loods bij Van Keulen',
		'boot'            => 'Boot op trailer, klaar voor de bootstalling',
		'aanhanger'       => 'Vouwwagen en aanhangwagen in de stalling',
		'strandhuisjes'   => 'Strandhuisjes aan de Zeeuwse kust',
		'walcheren'       => 'Polderlandschap bij Biggekerke op Walcheren',
		'loods'           => 'Binnenstalling: caravans in de loods van Van Keulen in Biggekerke',
	);
}

/**
 * Hulpfunctie voor patronen: blokattributen en klassen voor een afbeelding.
 *
 * @param string $key Beeldsleutel.
 * @return array{json:string,class:string,url:string,alt:string,id:int}
 */
function vankeulen_img_block( $key ) {
	$img  = vankeulen_img( $key );
	$json = $img['id'] ? '"id":' . $img['id'] . ',' : '';
	return array(
		'json'  => $json,
		'class' => $img['id'] ? ' wp-image-' . $img['id'] : '',
		'url'   => esc_url( $img['url'] ),
		'alt'   => esc_attr( $img['alt'] ),
		'id'    => $img['id'],
	);
}

/**
 * Melding in het beheer wanneer de plugin ontbreekt.
 */
add_action(
	'admin_notices',
	function () {
		if ( defined( 'VK_CORE_VERSION' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>Van Keulen thema:</strong> activeer de plugin <em>Van Keulen Core</em> voor contactgegevens, het aanvraagformulier, FAQ en SEO.</p></div>';
	}
);

/**
 * Betere 'sizes' voor beelden die nooit schermbreed zijn (kaarten, foto's in
 * kolommen). De browser laadt zo op mobiel en desktop een kleiner bestand.
 */
add_filter(
	'render_block',
	function ( $html, $block ) {
		$naam = $block['blockName'] ?? '';
		if ( 'core/image' !== $naam && 'core/media-text' !== $naam ) {
			return $html;
		}
		$cls   = $block['attrs']['className'] ?? '';
		$sizes = '';
		if ( 'core/media-text' === $naam ) {
			$sizes = '(max-width: 600px) 100vw, 52vw';
		} elseif ( str_contains( $cls, 'vk-dienst__beeld' ) || str_contains( $cls, 'is-style-vk-foto' ) ) {
			$sizes = '(max-width: 781px) 100vw, 620px';
		}
		if ( ! $sizes ) {
			return $html;
		}
		// Markeren; WordPress voegt srcset/sizes pas later toe (zie hieronder).
		$p = new WP_HTML_Tag_Processor( $html );
		while ( $p->next_tag( 'img' ) ) {
			$p->set_attribute( 'data-vk-sizes', $sizes );
		}
		return $p->get_updated_html();
	},
	20,
	2
);

add_filter(
	'wp_content_img_tag',
	function ( $img ) {
		if ( ! str_contains( $img, 'data-vk-sizes' ) ) {
			return $img;
		}
		$p = new WP_HTML_Tag_Processor( $img );
		if ( $p->next_tag( 'img' ) ) {
			$sizes = $p->get_attribute( 'data-vk-sizes' );
			$p->remove_attribute( 'data-vk-sizes' );
			$huidig = (string) $p->get_attribute( 'sizes' );
			if ( $huidig ) {
				// 'auto, …' (lazy beelden, WP 6.7+) behouden en aanvullen.
				$p->set_attribute( 'sizes', str_starts_with( $huidig, 'auto' ) ? 'auto, ' . $sizes : $sizes );
			}
		}
		return $p->get_updated_html();
	}
);

/**
 * Favicon (het logomerk) zolang er geen eigen site-icoon is ingesteld
 * (Weergave → Editor → Site-icoon).
 */
add_action(
	'wp_head',
	function () {
		if ( has_site_icon() ) {
			return;
		}
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_theme_file_uri( 'assets/images/favicon.svg' ) ) );
	},
	2
);
