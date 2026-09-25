<?php
/**
 * Title: Pagina: Overnachten
 * Slug: valkenisse/page-overnachten
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Landingspagina voor de studio's.
 *
 * @package Valkenisse
 */

echo valkenisse_hero(
	'Overnachten in Valkenisse',
	'Slapen vlak bij duinen en zee',
	'Twee comfortabele studio’s voor twee personen, boven het restaurant en met een eigen ingang.',
	'overnachten-studio.webp',
	80,
	valkenisse_buttons( array( array( 'Beschikbaarheid aanvragen', '#aanvraag', '' ), array( 'Bel ons', '#bellen', 'light' ) ), 'hero__buttons' ),
	'is-style-hero',
	'Zonnige entree van de studio met terras, bankje en olijfboompje'
);
echo "\n\n";

// Introductie + fotogalerij.
$intro = valkenisse_group(
	valkenisse_p( 'Blijf nog wat langer', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Uw eigen plek aan de Zeeuwse kust' ) . "\n\n" .
	valkenisse_p( 'Boven Restaurant Valkenisse verhuren wij twee studio’s, elk geschikt voor twee personen. Iedere studio heeft een eigen ingang en is voorzien van alle gemakken: een kitchenette, een zithoek en een slaapkamer met boxsprings. Het strand, de duinen en het bos liggen om de hoek.', 'is-style-lead' ),
	'vk-head'
);
$gallery = '<!-- wp:gallery {"columns":3,"linkTo":"none","align":"wide","className":"vk-stay-gallery"} -->' . "\n" .
	'<figure class="wp-block-gallery alignwide has-nested-images columns-3 is-cropped vk-stay-gallery">';
foreach ( array( array( 'studio-gang.webp', 'Lichte gang met houten vloer en sfeervolle verlichting' ), array( 'studio-badkamer.webp', 'Badkamer met houten wastafelmeubel en spiegel' ), array( 'studio-douche.webp', 'Inloopdouche met regendouche en nis' ) ) as [ $valkenisse_img, $valkenisse_alt ] ) {
	$gallery .= '<!-- wp:image {"lightbox":{"enabled":true}} -->' . "\n" . '<figure class="wp-block-image"><img src="' . valkenisse_img( $valkenisse_img ) . '" alt="' . esc_attr( $valkenisse_alt ) . '"/></figure>' . "\n" . '<!-- /wp:image -->' . "\n\n";
}
$gallery = rtrim( $gallery ) . '</figure>' . "\n" . '<!-- /wp:gallery -->';
echo valkenisse_section( 'vk-stay-intro', $intro . "\n\n" . $gallery );
echo "\n\n";

// De studio's (uit het menu Studio's).
echo valkenisse_section( 'vk-studios', valkenisse_group( valkenisse_block( 'studios' ), '', 'wide' ), 'sand-light' );
echo "\n\n";

// Faciliteiten en praktische informatie.
$facilities = valkenisse_h( 'Faciliteiten', 3 ) . "\n\n" . valkenisse_list(
	array( 'Geschikt voor 2 personen', 'Eigen ingang', 'Kitchenette', 'Zithoek', 'Slaapkamer met boxsprings', 'Douche en toilet', 'Bedlinnen', 'Koffie en thee', 'Gratis wifi', 'Gratis parkeren naast het restaurant' ),
	'is-style-checklist'
);
$practical = valkenisse_h( 'Praktische informatie', 3 ) . "\n\n" . valkenisse_list(
	array(
		'Tarieven: [tarieven per nacht invullen]',
		'De prijzen zijn exclusief toeristenbelasting (€ 2,75 per persoon per nacht) en € 100 borg.',
		'Roken en huisdieren zijn niet toegestaan.',
		'Reserveren kan telefonisch of via het aanvraagformulier hieronder.',
		'Aankomst- en vertrektijden: [invullen]',
	)
);
echo valkenisse_section( 'vk-stay-info', valkenisse_columns( array( array( '', $facilities ), array( '', $practical ) ), 'vk-info-cols' ) );
echo "\n\n";

include __DIR__ . '/omgeving.php';
echo "\n\n";

// Aanvraag.
$form = valkenisse_group(
	valkenisse_p( 'Beschikbaarheid', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Vraag een studio aan' ) . "\n\n" .
	valkenisse_p( 'Laat weten wanneer u wilt komen. Wij laten u zo snel mogelijk weten of er een studio beschikbaar is. Liever direct contact? Bel ons.' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Bel ons', '#bellen', 'outline' ) ) ),
	'vk-form-intro'
);
echo valkenisse_section( 'vk-request', valkenisse_columns( array( array( '36%', $form ), array( '', valkenisse_block( 'formulier', array( 'soort' => 'studio' ) ) ) ), 'vk-form-cols' ), 'sand', 'aanvraag' );
