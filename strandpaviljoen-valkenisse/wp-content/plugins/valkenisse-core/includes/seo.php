<?php
/**
 * SEO.
 *
 * - Structured data (Restaurant/LocalBusiness met actuele openingstijden, JobPosting, BreadcrumbList): altijd,
 *   want die komt rechtstreeks uit de centrale gegevens.
 * - Meta title/description, Open Graph en breadcrumbs: alleen als er GEEN Rank Math of Yoast actief is.
 *   Advies: installeer Rank Math (niet ook Yoast) en zet daar de eigen "Local SEO"-schema uit.
 */

defined( 'ABSPATH' ) || exit;

function vk_has_seo_plugin(): bool {
	return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' );
}

/* -------------------------------------------------------------------------
 * Eenvoudige SEO-velden per pagina (alleen zonder SEO-plugin)
 * ---------------------------------------------------------------------- */

add_action( 'init', static function () {
	foreach ( array( 'vk_seo_title', 'vk_seo_description' ) as $key ) {
		register_post_meta(
			'',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
			)
		);
	}
} );

add_action( 'add_meta_boxes', static function () {
	if ( vk_has_seo_plugin() ) {
		return;
	}
	foreach ( array( 'page', 'post', 'vk_vacature' ) as $type ) {
		add_meta_box( 'vk_seo', __( 'Zoekmachines (Google)', 'valkenisse' ), 'vk_render_seo_box', $type, 'normal', 'low' );
	}
} );

function vk_render_seo_box( WP_Post $post ): void {
	wp_nonce_field( 'vk_seo', 'vk_seo_nonce' );
	printf(
		'<p><label for="vk_seo_title"><strong>%1$s</strong></label><input class="widefat" id="vk_seo_title" name="vk_seo_title" value="%2$s" maxlength="70" /></p><p><label for="vk_seo_description"><strong>%3$s</strong></label><textarea class="widefat" id="vk_seo_description" name="vk_seo_description" rows="2" maxlength="170">%4$s</textarea><span class="description">%5$s</span></p>',
		esc_html__( 'Titel in Google (max. ± 60 tekens)', 'valkenisse' ),
		esc_attr( (string) get_post_meta( $post->ID, 'vk_seo_title', true ) ),
		esc_html__( 'Omschrijving in Google (max. ± 155 tekens)', 'valkenisse' ),
		esc_textarea( (string) get_post_meta( $post->ID, 'vk_seo_description', true ) ),
		esc_html__( 'Leeg laten mag: dan maakt de website zelf een omschrijving.', 'valkenisse' )
	);
}

add_action( 'save_post', static function ( int $post_id ) {
	if ( ! isset( $_POST['vk_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['vk_seo_nonce'] ), 'vk_seo' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, 'vk_seo_title', sanitize_text_field( wp_unslash( $_POST['vk_seo_title'] ?? '' ) ) );
	update_post_meta( $post_id, 'vk_seo_description', sanitize_text_field( wp_unslash( $_POST['vk_seo_description'] ?? '' ) ) );
} );

/**
 * Omschrijving voor de huidige pagina.
 */
function vk_meta_description(): string {
	$desc = '';
	if ( is_singular() ) {
		$desc = (string) get_post_meta( get_queried_object_id(), 'vk_seo_description', true );
		if ( ! $desc && has_excerpt( get_queried_object_id() ) ) {
			$desc = get_the_excerpt( get_queried_object_id() );
		}
	}
	if ( ! $desc && ( is_front_page() || is_home() ) ) {
		$desc = (string) get_bloginfo( 'description' );
	}
	return wp_html_excerpt( wp_strip_all_tags( $desc ), 160, '…' );
}

/**
 * Deelafbeelding: uitgelichte afbeelding, anders het sitelogo/-icoon of de standaard uit het thema.
 */
function vk_share_image(): string {
	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		return (string) get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
	}
	$default = '';
	foreach ( array( 'hero-paviljoen.jpg', 'hero-paviljoen.webp' ) as $file ) {
		if ( file_exists( get_theme_file_path( 'assets/images/' . $file ) ) ) {
			$default = get_theme_file_uri( 'assets/images/' . $file );
			break;
		}
	}
	if ( ! $default && has_post_thumbnail( (int) get_option( 'page_on_front' ) ) ) {
		$default = (string) get_the_post_thumbnail_url( (int) get_option( 'page_on_front' ), 'large' );
	}
	if ( ! $default ) {
		$default = (string) get_site_icon_url( 512 );
	}
	return (string) apply_filters( 'valkenisse_default_share_image', $default );
}

add_filter( 'document_title_parts', static function ( array $parts ) {
	if ( vk_has_seo_plugin() || ! is_singular() ) {
		return $parts;
	}
	$custom = (string) get_post_meta( get_queried_object_id(), 'vk_seo_title', true );
	if ( $custom ) {
		return array( 'title' => $custom );
	}
	return $parts;
} );

add_action( 'wp_head', static function () {
	if ( vk_has_seo_plugin() ) {
		return;
	}
	$desc  = vk_meta_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? (string) get_permalink( get_queried_object_id() ) : home_url( add_query_arg( array() ) );

	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
	// Canonical voor losse pagina's zet WordPress zelf; voor de homepage vullen we aan.
	if ( is_front_page() ) {
		echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '" />' . "\n";
	}
	$og = array(
		'og:locale'      => str_replace( '-', '_', get_bloginfo( 'language' ) ),
		'og:type'        => is_front_page() ? 'website' : 'article',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => $title,
		'og:description' => $desc,
		'og:url'         => $url,
		'og:image'       => vk_share_image(),
	);
	foreach ( $og as $property => $content ) {
		if ( $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}, 2 );

/* -------------------------------------------------------------------------
 * Structured data
 * ---------------------------------------------------------------------- */

/**
 * Restaurant/LocalBusiness-schema. Alleen bevestigde gegevens; placeholders worden weggelaten.
 */
function vk_business_schema(): array {
	$schema = array(
		'@type'        => array( 'Restaurant', 'LocalBusiness' ),
		'@id'          => home_url( '/#paviljoen' ),
		'name'         => 'Strandpaviljoen Valkenisse',
		'alternateName' => 'Strandpaviljoen Herwegh',
		'url'          => home_url( '/' ),
		'foundingDate' => '1956',
		'description'  => (string) get_bloginfo( 'description' ),
		'image'        => vk_share_image(),
		'priceRange'   => '€€',
		'founder'      => array( '@type' => 'Person', 'name' => 'Sies Herwegh' ),
		'address'      => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Groot Valkenisse, Biggekerke',
			'addressRegion'   => 'Zeeland',
			'addressCountry'  => 'NL',
		),
		'areaServed'   => array( 'Valkenisse', 'Zoutelande', 'Biggekerke', 'Walcheren' ),
	);

	// Een prijsklasse is een inschatting; alleen tonen als de familie dat wil.
	if ( ! apply_filters( 'valkenisse_schema_price_range', false ) ) {
		unset( $schema['priceRange'] );
	}

	if ( ! vk_is_missing( vk_get( 'phone_intl' ) ) ) {
		$schema['telephone'] = (string) vk_get( 'phone_intl' );
	}
	if ( is_email( (string) vk_get( 'email' ) ) ) {
		$schema['email'] = (string) vk_get( 'email' );
	}
	if ( vk_has_coords() ) {
		$schema['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) vk_get( 'lat' ),
			'longitude' => (float) vk_get( 'lng' ),
		);
	}
	$schema['hasMap'] = vk_route_url();

	// Openingstijden alleen doorgeven als er een (gemiddelde) sluitingstijd bekend is.
	$fallback_close = (string) vk_get( 'hours_schema_close' );
	$specs          = array();
	foreach ( vk_weekdays() as $key => $day ) {
		$hours = vk_hours_for( $key );
		$close = $hours['close'] ?: $fallback_close;
		if ( empty( $hours['closed'] ) && $hours['open'] && $close ) {
			$specs[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => 'https://schema.org/' . $day[1],
				'opens'     => $hours['open'],
				'closes'    => $close,
			);
		}
	}
	if ( $specs ) {
		$schema['openingHoursSpecification'] = $specs;
	}

	// Vandaag gesloten? Dan een uitzondering voor vandaag.
	$today = vk_today();
	if ( 'closed' === $today['state'] ) {
		$date                                 = vk_now()->format( 'Y-m-d' );
		$schema['specialOpeningHoursSpecification'] = array(
			'@type'        => 'OpeningHoursSpecification',
			'validFrom'    => $date,
			'validThrough' => $date,
			'opens'        => '00:00',
			'closes'       => '00:00',
		);
	}

	if ( get_page_by_path( 'eten-drinken' ) ) {
		$schema['hasMenu'] = vk_page_url( 'eten-drinken' );
	}

	$same_as = array_values( array_filter( array( (string) vk_get( 'facebook' ), (string) vk_get( 'instagram' ) ) ) );
	if ( $same_as ) {
		$schema['sameAs'] = $same_as;
	}

	return (array) apply_filters( 'valkenisse_business_schema', $schema );
}

add_action( 'wp_head', static function () {
	$graph = array(
		array(
			'@type'      => 'WebSite',
			'@id'        => home_url( '/#website' ),
			'url'        => home_url( '/' ),
			'name'       => get_bloginfo( 'name' ),
			'inLanguage' => get_bloginfo( 'language' ),
			'publisher'  => array( '@id' => home_url( '/#paviljoen' ) ),
		),
	);

	// Het bedrijf zelf op de homepage en de contactpagina.
	if ( is_front_page() || is_page( array( 'contact', 'over-ons', 'eten-drinken' ) ) ) {
		$graph[] = vk_business_schema();
	}

	// Kruimelpad (Rank Math/Yoast doen dit zelf).
	if ( ! vk_has_seo_plugin() && is_singular() && ! is_front_page() ) {
		$list = array();
		foreach ( vk_breadcrumb_items() as $i => $item ) {
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $item['name'],
				'item'     => $item['url'],
			);
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
	}

	// Vacature.
	if ( is_singular( 'vk_vacature' ) ) {
		$job     = get_queried_object();
		$graph[] = array(
			'@type'              => 'JobPosting',
			'title'              => get_the_title( $job ),
			'description'        => wp_kses_post( apply_filters( 'the_content', $job->post_content ) ),
			'datePosted'         => get_the_date( 'c', $job ),
			'hiringOrganization' => array(
				'@type'  => 'Organization',
				'name'   => 'Strandpaviljoen Valkenisse',
				'sameAs' => home_url( '/' ),
			),
			'jobLocation'        => array(
				'@type'   => 'Place',
				'address' => array(
					'@type'           => 'PostalAddress',
					'addressLocality' => 'Biggekerke',
					'addressRegion'   => 'Zeeland',
					'addressCountry'  => 'NL',
				),
			),
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 20 );

/**
 * Sitemap: alleen echte pagina's en actieve vacatures, geen gebruikers-sitemaps.
 */
add_filter( 'wp_sitemaps_add_provider', static fn( $provider, $name ) => 'users' === $name ? false : $provider, 10, 2 );
add_filter( 'wp_sitemaps_taxonomies', static fn( $taxonomies ) => array() );
