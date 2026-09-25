<?php
/**
 * Lichtgewicht SEO: titels, meta-omschrijving, Open Graph, robots en sitemap.
 *
 * Per pagina zijn in de editor (zijbalk → "Zoekmachines") een SEO-titel,
 * meta-omschrijving en "niet indexeren" in te stellen. Canonical URL's en de
 * XML-sitemap (/wp-sitemap.xml) komen uit WordPress zelf.
 *
 * Wordt Yoast SEO, Rank Math of SEOPress geactiveerd, dan schakelt deze module
 * zichzelf uit om dubbele tags te voorkomen (schema.org blijft actief).
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is er een andere SEO-plugin actief?
 */
function vk_andere_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

add_action(
	'init',
	function () {
		foreach ( array( '_vk_seo_titel', '_vk_seo_omschrijving' ) as $key ) {
			register_post_meta(
				'page',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => '',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_pages' );
					},
				)
			);
		}
		register_post_meta(
			'page',
			'_vk_noindex',
			array(
				'type'          => 'boolean',
				'single'        => true,
				'default'       => false,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_pages' );
				},
			)
		);
	}
);

add_action(
	'enqueue_block_editor_assets',
	function () {
		// De zijbalk "Zoekmachines" en de blok-voorbeelden.
		wp_enqueue_script( 'vk-editor' );
		wp_add_inline_script(
			'vk-editor',
			'window.vkEditor = ' . wp_json_encode(
				array(
					'siteNaam'   => get_bloginfo( 'name' ),
					'seoActief'  => ! vk_andere_seo_plugin(),
					'objecten'   => vk_objecten(),
					'homeUrl'    => home_url( '/' ),
				)
			) . ';',
			'before'
		);
	}
);

/**
 * SEO-titel voor de huidige pagina.
 */
function vk_seo_titel() {
	if ( is_singular() ) {
		$eigen = get_post_meta( get_queried_object_id(), '_vk_seo_titel', true );
		if ( $eigen ) {
			return $eigen;
		}
	}
	if ( is_front_page() ) {
		return 'Caravanstalling Biggekerke, Walcheren | ' . vk_get( 'bedrijfsnaam' );
	}
	return '';
}

/**
 * Meta-omschrijving voor de huidige pagina.
 */
function vk_seo_omschrijving() {
	if ( is_singular() ) {
		$id    = get_queried_object_id();
		$eigen = get_post_meta( $id, '_vk_seo_omschrijving', true );
		if ( $eigen ) {
			return $eigen;
		}
		$post = get_post( $id );
		if ( $post && has_excerpt( $post ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post ) );
		}
	}
	return 'Van Keulen Caravanstalling in Biggekerke (Walcheren, Zeeland): stalling voor caravans, boten, vouwwagens, aanhangers, strandhuisjes en slaaphuisjes. Vraag een stallingsplaats aan.';
}

if ( ! vk_andere_seo_plugin() ) {

	add_filter(
		'pre_get_document_title',
		function ( $title ) {
			$eigen = vk_seo_titel();
			return $eigen ? $eigen : $title;
		}
	);

	add_filter(
		'document_title_separator',
		function () {
			return '|';
		}
	);

	add_filter(
		'wp_robots',
		function ( $robots ) {
			if ( is_singular() && get_post_meta( get_queried_object_id(), '_vk_noindex', true ) ) {
				$robots['noindex'] = true;
				$robots['follow']  = true;
			}
			if ( is_search() || is_404() || is_author() || is_date() ) {
				$robots['noindex'] = true;
			}
			if ( ! isset( $robots['noindex'] ) ) {
				$robots['max-image-preview'] = 'large';
			}
			return $robots;
		}
	);

	add_action(
		'wp_head',
		function () {
			$desc  = vk_seo_omschrijving();
			$titel = wp_get_document_title();
			$url   = is_singular() ? wp_get_canonical_url() : home_url( add_query_arg( array() ) );
			if ( is_front_page() ) {
				$url = home_url( '/' );
			}

			$img = '';
			if ( is_singular() && has_post_thumbnail() ) {
				$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
				$img = $src ? $src[0] : '';
			}
			if ( ! $img && function_exists( 'vankeulen_img' ) ) {
				$img = get_theme_file_uri( 'assets/images/og-default.jpg' );
			}

			echo "\n<!-- Van Keulen SEO -->\n";
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
			printf( '<meta property="og:locale" content="nl_NL">' . "\n" );
			printf( '<meta property="og:type" content="%s">' . "\n", is_front_page() ? 'website' : 'article' );
			printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( vk_get( 'bedrijfsnaam' ) ) );
			printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $titel ) );
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
			printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
			if ( $img ) {
				printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $img ) );
			}
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
			printf( '<meta name="geo.region" content="NL-ZE">' . "\n" );
			printf( '<meta name="geo.placename" content="%s">' . "\n", esc_attr( vk_get( 'plaats' ) ) );
			if ( vk_filled( 'lat' ) && vk_filled( 'lng' ) ) {
				printf( '<meta name="geo.position" content="%1$s;%2$s">' . "\n", esc_attr( vk_get( 'lat' ) ), esc_attr( vk_get( 'lng' ) ) );
			}
		},
		2
	);
}

/*
 * Sitemap (WordPress: /wp-sitemap.xml): alleen pagina's, zonder gebruikers,
 * categorieën of lege berichtensecties, en zonder pagina's op "niet indexeren".
 */
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return in_array( $name, array( 'users', 'taxonomies' ), true ) ? false : $provider;
	},
	10,
	2
);

add_filter(
	'wp_sitemaps_post_types',
	function ( $types ) {
		if ( isset( $types['post'] ) && ! wp_count_posts( 'post' )->publish ) {
			unset( $types['post'] );
		}
		unset( $types['attachment'] );
		return $types;
	}
);

add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array(
					'key'     => '_vk_noindex',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_vk_noindex',
					'value'   => '1',
					'compare' => '!=',
				),
			);
		}
		return $args;
	},
	10,
	2
);

/*
 * Bijlagepagina's (…/?attachment_id=…) doorsturen naar het bestand zelf.
 * Voorkomt dunne pagina's in Google.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_attachment() ) {
			$url = wp_get_attachment_url( get_queried_object_id() );
			wp_safe_redirect( $url ? $url : home_url( '/' ), 301 );
			exit;
		}
		// Auteur- en datumarchieven zijn niet nodig voor deze site.
		if ( is_author() || is_date() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);
