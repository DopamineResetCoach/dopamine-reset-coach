<?php
/**
 * Structured data (schema.org, JSON-LD) voor Google.
 *
 * - LocalBusiness (type SelfStorage) met adres, contact, openingstijden en
 *   een koppeling naar het Google Bedrijfsprofiel (sameAs).
 * - WebSite en WebPage.
 * - BreadcrumbList op alle pagina's behalve de homepage.
 * - FAQPage op pagina's met het blok "Veelgestelde vragen" (alleen vragen met
 *   een definitief antwoord – nooit placeholders).
 *
 * Alleen daadwerkelijk ingevulde gegevens worden doorgegeven.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'vk_output_schema', 20 );

/**
 * JSON-LD uitvoeren.
 */
function vk_output_schema() {
	if ( is_admin() || is_404() ) {
		return;
	}
	$home  = home_url( '/' );
	$graph = array();

	// LocalBusiness.
	$biz = array(
		'@type'       => array( 'SelfStorage', 'LocalBusiness' ),
		'@id'         => $home . '#bedrijf',
		'name'        => vk_get( 'bedrijfsnaam' ),
		'url'         => $home,
		'description' => 'Stalling voor caravans, boten, vouwwagens, aanhangwagens, strandhuisjes en slaaphuisjes in Biggekerke op Walcheren (Zeeland).',
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => vk_get( 'straat' ),
			'postalCode'      => vk_get( 'postcode' ),
			'addressLocality' => vk_get( 'plaats' ),
			'addressRegion'   => 'Zeeland',
			'addressCountry'  => 'NL',
		),
		'areaServed'  => array(
			array( '@type' => 'Place', 'name' => 'Walcheren' ),
			array( '@type' => 'AdministrativeArea', 'name' => 'Zeeland' ),
		),
		'knowsAbout'  => array( 'Caravanstalling', 'Bootstalling', 'Vouwwagenstalling', 'Aanhangerstalling', 'Stalling van strandhuisjes', 'Stalling van slaaphuisjes', 'Winterstalling' ),
	);
	if ( vk_filled( 'telefoon' ) ) {
		$biz['telephone'] = vk_tel_e164();
	}
	if ( vk_filled( 'email' ) ) {
		$biz['email'] = vk_get( 'email' );
	}
	if ( vk_filled( 'lat' ) && vk_filled( 'lng' ) ) {
		$biz['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) vk_get( 'lat' ),
			'longitude' => (float) vk_get( 'lng' ),
		);
	}
	$biz['hasMap'] = vk_maps_url();
	if ( vk_filled( 'google_profiel' ) ) {
		$biz['sameAs'] = array( vk_get( 'google_profiel' ) );
	}
	if ( function_exists( 'vankeulen_img' ) ) {
		$img = vankeulen_img( 'hero-terrein' );
		if ( $img['id'] ) {
			$biz['image'] = $img['url'];
		}
	}
	if ( has_custom_logo() ) {
		$logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $logo ) {
			$biz['logo'] = $logo[0];
		}
	}
	$uren = array();
	foreach ( (array) vk_get( 'openingstijden' ) as $dag => $r ) {
		if ( 'open' === ( $r['status'] ?? '' ) && ! empty( $r['van'] ) && ! empty( $r['tot'] ) ) {
			$uren[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => 'https://schema.org/' . $dag,
				'opens'     => $r['van'],
				'closes'    => $r['tot'],
			);
		}
	}
	if ( $uren ) {
		$biz['openingHoursSpecification'] = $uren;
	}
	$graph[] = $biz;

	$graph[] = array(
		'@type'     => 'WebSite',
		'@id'       => $home . '#website',
		'url'       => $home,
		'name'      => vk_get( 'bedrijfsnaam' ),
		'inLanguage' => 'nl-NL',
		'publisher' => array( '@id' => $home . '#bedrijf' ),
	);

	if ( is_singular() ) {
		$url  = is_front_page() ? $home : get_permalink();
		$page = array(
			'@type'      => 'WebPage',
			'@id'        => $url . '#webpagina',
			'url'        => $url,
			'name'       => wp_get_document_title(),
			'description' => vk_seo_omschrijving(),
			'isPartOf'   => array( '@id' => $home . '#website' ),
			'about'      => array( '@id' => $home . '#bedrijf' ),
			'inLanguage' => 'nl-NL',
		);

		$items = vk_breadcrumb_items();
		if ( ! is_front_page() && count( $items ) > 1 ) {
			$list = array();
			foreach ( $items as $i => $item ) {
				$list[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $item['naam'],
					'item'     => $item['url'],
				);
			}
			$graph[]            = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => $url . '#kruimelpad',
				'itemListElement' => $list,
			);
			$page['breadcrumb'] = array( '@id' => $url . '#kruimelpad' );
		}

		$post = get_post();
		if ( $post && has_block( 'vankeulen/faq', $post ) ) {
			$vragen = array();
			foreach ( vk_faq_items() as $f ) {
				if ( ! vk_faq_is_definitief( $f ) ) {
					continue;
				}
				$vragen[] = array(
					'@type'          => 'Question',
					'name'           => get_the_title( $f ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => trim( wp_strip_all_tags( do_shortcode( do_blocks( $f->post_content ) ) ) ),
					),
				);
			}
			if ( $vragen ) {
				$page['@type']      = array( 'WebPage', 'FAQPage' );
				$page['mainEntity'] = $vragen;
			}
		}
		$graph[] = $page;
	}

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
	) . '</script>' . "\n";
}
