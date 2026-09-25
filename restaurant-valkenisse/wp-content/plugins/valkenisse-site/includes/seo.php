<?php
/**
 * SEO: Schema.org (Restaurant, LodgingBusiness, BreadcrumbList) op basis van de centrale
 * gegevens, plus een lichte terugval voor meta-titel/-omschrijving en Open Graph
 * zolang er geen SEO-plugin (Rank Math of Yoast) actief is.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

function valkenisse_has_seo_plugin(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

add_action( 'init', static fn() => add_post_type_support( 'page', 'excerpt' ) );

// Gebruikers niet in de sitemap.
add_filter(
	'wp_sitemaps_add_provider',
	static fn( $provider, $name ) => 'users' === $name ? false : $provider,
	10,
	2
);

/* -------------------------------------------------------------------------
 * Terugval: titel en omschrijving per pagina ("Google-weergave")
 * ---------------------------------------------------------------------- */

add_action(
	'add_meta_boxes',
	static function () {
		if ( valkenisse_has_seo_plugin() ) {
			return;
		}
		foreach ( array( 'page', 'post' ) as $type ) {
			add_meta_box( 'valk_seo', 'Weergave in Google', 'valkenisse_render_seo_box', $type, 'side', 'low' );
		}
	}
);

add_action(
	'init',
	static function () {
		foreach ( array( 'page', 'post' ) as $type ) {
			foreach ( array( '_valk_seo_titel', '_valk_seo_omschrijving' ) as $key ) {
				register_post_meta(
					$type,
					$key,
					array(
						'type'          => 'string',
						'single'        => true,
						'show_in_rest'  => true,
						'auth_callback' => static fn() => current_user_can( 'edit_posts' ),
					)
				);
			}
		}
	}
);

function valkenisse_render_seo_box( WP_Post $post ): void {
	wp_nonce_field( 'valk_seo', 'valk_seo_nonce' );
	printf(
		'<p><label for="valk-seo-titel"><strong>Titel</strong> (± 60 tekens)</label><input id="valk-seo-titel" type="text" name="valk_seo_titel" value="%s" class="widefat" maxlength="70"></p><p><label for="valk-seo-omschrijving"><strong>Omschrijving</strong> (± 155 tekens)</label><textarea id="valk-seo-omschrijving" name="valk_seo_omschrijving" rows="4" class="widefat" maxlength="170">%s</textarea></p>',
		esc_attr( (string) get_post_meta( $post->ID, '_valk_seo_titel', true ) ),
		esc_textarea( (string) get_post_meta( $post->ID, '_valk_seo_omschrijving', true ) )
	);
}

add_action(
	'save_post',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['valk_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['valk_seo_nonce'] ), 'valk_seo' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, '_valk_seo_titel', sanitize_text_field( wp_unslash( $_POST['valk_seo_titel'] ?? '' ) ) );
		update_post_meta( $post_id, '_valk_seo_omschrijving', sanitize_textarea_field( wp_unslash( $_POST['valk_seo_omschrijving'] ?? '' ) ) );
	}
);

function valkenisse_meta_description(): string {
	$id = is_singular() ? get_queried_object_id() : ( is_home() ? (int) get_option( 'page_for_posts' ) : 0 );
	if ( $id ) {
		$desc = (string) get_post_meta( $id, '_valk_seo_omschrijving', true );
		if ( '' === $desc && has_excerpt( $id ) ) {
			$desc = get_the_excerpt( $id );
		}
		if ( '' !== $desc ) {
			return wp_strip_all_tags( $desc );
		}
	}
	return (string) get_bloginfo( 'description' );
}

add_filter(
	'pre_get_document_title',
	static function ( string $title ) {
		if ( valkenisse_has_seo_plugin() || ! is_singular() ) {
			return $title;
		}
		$custom = (string) get_post_meta( get_queried_object_id(), '_valk_seo_titel', true );
		return '' !== $custom ? $custom : $title;
	}
);

add_filter( 'document_title_separator', static fn() => '|' );

add_action(
	'wp_head',
	static function () {
		if ( valkenisse_has_seo_plugin() || is_404() ) {
			return;
		}
		$desc  = valkenisse_meta_description();
		$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		$image = '';
		if ( is_singular() && has_post_thumbnail() ) {
			$image = (string) get_the_post_thumbnail_url( null, 'large' );
		}
		if ( ! $image ) {
			$image = (string) apply_filters( 'valkenisse_default_share_image', '' );
		}
		echo "\n";
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		$og = array(
			'og:locale'      => 'nl_NL',
			'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:title'       => wp_get_document_title(),
			'og:description' => $desc,
			'og:url'         => $url,
			'og:image'       => $image,
		);
		foreach ( $og as $property => $content ) {
			if ( $content ) {
				echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '">' . "\n";
			}
		}
		echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
	},
	2
);

/* -------------------------------------------------------------------------
 * Schema.org – alleen met gegevens die in de beheeromgeving zijn ingevuld
 * ---------------------------------------------------------------------- */

function valkenisse_schema_address(): array {
	$c = valkenisse_get( 'contact' );
	return array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $c['straat'],
		'postalCode'      => $c['postcode'],
		'addressLocality' => $c['plaats'],
		'addressRegion'   => 'Zeeland',
		'addressCountry'  => 'NL',
	);
}

function valkenisse_schema_opening_hours(): array {
	$specs = array();
	$map   = array( 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday' );
	$now   = Valkenisse_Hours::now();
	foreach ( valkenisse_get( 'seizoenen', array() ) as $season ) {
		[ $from, $through ] = Valkenisse_Hours::season_range( $season, $now );
		if ( $through < $now->format( 'Y-m-d' ) ) {
			[ $from, $through ] = Valkenisse_Hours::season_range( $season, $now->modify( '+1 year' ) );
		}
		$byhours = array();
		foreach ( $season['dagen'] as $num => $d ) {
			// Alleen dagen met een bekende openings- én sluitingstijd (Schema vereist beide).
			if ( ! empty( $d['dicht'] ) || ! $d['open'] || ! $d['sluit'] ) {
				continue;
			}
			$byhours[ $d['open'] . '-' . $d['sluit'] ][] = 'https://schema.org/' . $map[ (int) $num ];
		}
		foreach ( $byhours as $range => $days ) {
			[ $opens, $closes ] = explode( '-', $range );
			$specs[]            = array(
				'@type'        => 'OpeningHoursSpecification',
				'dayOfWeek'    => $days,
				'opens'        => $opens,
				'closes'       => $closes,
				'validFrom'    => $from,
				'validThrough' => $through,
			);
		}
	}
	return $specs;
}

function valkenisse_schema_graph(): array {
	$c     = valkenisse_get( 'contact' );
	$graph = array();
	$tel   = str_replace( 'tel:', '', valkenisse_phone_href() );
	$same  = array_values( array_filter( array( $c['facebook'], $c['instagram'] ) ) );

	if ( is_front_page() || is_page( 'contact' ) ) {
		$restaurant = array(
			'@type'               => 'Restaurant',
			'@id'                 => home_url( '/#restaurant' ),
			'name'                => $c['naam'],
			'url'                 => home_url( '/' ),
			'address'             => valkenisse_schema_address(),
			'hasMap'              => valkenisse_route_url(),
			'acceptsReservations' => true,
			'hasMenu'             => valkenisse_page_url( 'menukaart' ),
		);
		if ( $tel ) {
			$restaurant['telephone'] = $tel;
		}
		if ( $c['email'] ) {
			$restaurant['email'] = $c['email'];
		}
		$hours = valkenisse_schema_opening_hours();
		if ( $hours ) {
			$restaurant['openingHoursSpecification'] = $hours;
		}
		if ( $same ) {
			$restaurant['sameAs'] = $same;
		}
		$image = is_front_page() && has_post_thumbnail( (int) get_option( 'page_on_front' ) ) ? get_the_post_thumbnail_url( (int) get_option( 'page_on_front' ), 'large' ) : '';
		if ( $image ) {
			$restaurant['image'] = $image;
		}
		$graph[] = apply_filters( 'valkenisse_schema_restaurant', $restaurant );
	}

	if ( is_page( 'overnachten' ) ) {
		$studios   = get_posts( array( 'post_type' => 'studio', 'posts_per_page' => 20 ) );
		$amenities = array();
		foreach ( $studios as $studio ) {
			foreach ( valkenisse_lines( (string) get_post_meta( $studio->ID, '_valk_faciliteiten', true ) ) as $f ) {
				$amenities[ $f ] = array( '@type' => 'LocationFeatureSpecification', 'name' => $f, 'value' => true );
			}
		}
		$lodging = array(
			'@type'   => 'LodgingBusiness',
			'@id'     => valkenisse_page_url( 'overnachten' ) . '#studios',
			'name'    => "Studio's bij " . $c['naam'],
			'url'     => valkenisse_page_url( 'overnachten' ),
			'address' => valkenisse_schema_address(),
		);
		if ( $tel ) {
			$lodging['telephone'] = $tel;
		}
		if ( $studios ) {
			$lodging['numberOfRooms'] = count( $studios );
		}
		if ( $amenities ) {
			$lodging['amenityFeature'] = array_values( $amenities );
		}
		$graph[] = apply_filters( 'valkenisse_schema_lodging', $lodging );
	}

	if ( is_page() && ! is_front_page() ) {
		$items = array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
		);
		$pos   = 2;
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title( $ancestor ), 'item' => get_permalink( $ancestor ) );
		}
		$items[] = array( '@type' => 'ListItem', 'position' => $pos, 'name' => get_the_title(), 'item' => get_permalink() );
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	}

	return $graph;
}

add_action(
	'wp_head',
	static function () {
		if ( ! valkenisse_get( 'seo.schema' ) || is_404() || is_search() ) {
			return;
		}
		$graph = valkenisse_schema_graph();
		if ( ! $graph ) {
			return;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	},
	20
);

// Nederlandse titel voor de 404-pagina (ook zonder taalpakket).
add_filter(
	'document_title_parts',
	static function ( array $parts ) {
		if ( is_404() ) {
			$parts['title'] = 'Pagina niet gevonden';
		}
		return $parts;
	}
);
