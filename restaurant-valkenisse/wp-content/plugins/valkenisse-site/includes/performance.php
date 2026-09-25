<?php
/**
 * Snelheid en rust: geen emoji-scripts, geen reacties, geüploade JPEG/PNG-foto's
 * worden automatisch als WebP opgeslagen (als de server dat ondersteunt).
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

// Overbodige kopregels en scripts.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'emoji_svg_url', '__return_false' );

// Foto's als WebP opslaan: kleiner en sneller, met behoud van kwaliteit.
add_filter(
	'image_editor_output_format',
	static function ( array $formats ) {
		if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
			$formats['image/png']  = 'image/webp';
		}
		return $formats;
	}
);
add_filter( 'wp_editor_set_quality', static fn( $q, $mime ) => 'image/webp' === $mime ? 80 : $q, 10, 2 );

/* Reacties volledig uit: niet nodig voor een restaurantsite en voorkomt spam. */
add_action(
	'init',
	static function () {
		foreach ( get_post_types() as $type ) {
			if ( post_type_supports( $type, 'comments' ) ) {
				remove_post_type_support( $type, 'comments' );
				remove_post_type_support( $type, 'trackbacks' );
			}
		}
	},
	100
);
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_action(
	'admin_menu',
	static function () {
		remove_menu_page( 'edit-comments.php' );
	}
);
add_action(
	'wp_before_admin_bar_render',
	static function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	}
);
