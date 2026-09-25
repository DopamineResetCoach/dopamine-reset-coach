<?php
/**
 * Snelheid: overbodige WordPress-onderdelen uit, moderne beeldformaten aan.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		// Emoji-scripts en -stijlen (± 16 kB) zijn niet nodig.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );

		// Overbodige <head>-regels.
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}
);

// Geen oEmbed-script voor het insluiten van deze site elders.
add_action(
	'wp_footer',
	function () {
		wp_dequeue_script( 'wp-embed' );
	}
);

// RSS-feeds zijn niet nodig voor een bedrijfssite zonder blog.
foreach ( array( 'do_feed', 'do_feed_rdf', 'do_feed_rss', 'do_feed_rss2', 'do_feed_atom', 'do_feed_rss2_comments', 'do_feed_atom_comments' ) as $feed ) {
	add_action(
		$feed,
		function () {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		},
		1
	);
}

/**
 * Nieuwe uploads (JPEG/PNG) automatisch opslaan als AVIF wanneer de server
 * dat ondersteunt, anders als WebP. De eigenaar kan gewoon foto's van de
 * telefoon uploaden; WordPress maakt er kleine, moderne bestanden van in alle
 * benodigde formaten (srcset).
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		$doel = 'image/webp';
		if ( wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) ) && apply_filters( 'vk_gebruik_avif', true ) ) {
			$doel = 'image/avif';
		} elseif ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			return $formats;
		}
		$formats['image/jpeg'] = $doel;
		$formats['image/png']  = $doel;
		return $formats;
	}
);

// Iets hogere compressie; visueel geen verschil voor foto's.
add_filter(
	'wp_editor_set_quality',
	function ( $q, $mime ) {
		return in_array( $mime, array( 'image/webp', 'image/avif' ), true ) ? 72 : $q;
	},
	10,
	2
);

// Heel grote foto's van telefoons/camera's begrenzen tot 2560 px (standaard WP).
add_filter(
	'big_image_size_threshold',
	function () {
		return 2560;
	}
);

/**
 * Heartbeat minder vaak in het beheer (minder serverbelasting).
 */
add_filter(
	'heartbeat_settings',
	function ( $s ) {
		$s['interval'] = 60;
		return $s;
	}
);
