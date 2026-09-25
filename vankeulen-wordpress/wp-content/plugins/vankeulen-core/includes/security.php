<?php
/**
 * Veiligheid en opschoning.
 *
 * Aanvullend op (niet in plaats van): actuele WordPress, sterke wachtwoorden
 * met tweestapsverificatie, SSL en back-ups bij de hostingpartij. Zie
 * LIVEGANG-CHECKLIST.md.
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

// Bestanden bewerken vanuit het beheer uitschakelen (thema-/plugin-editor).
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// XML-RPC is een bekend aanvalspunt en niet nodig.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	function ( $h ) {
		unset( $h['X-Pingback'] );
		return $h;
	}
);

// WordPress-versie niet prijsgeven.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Beveiligingsheaders.
add_action(
	'send_headers',
	function () {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000' );
		}
	}
);

// Gebruikersnamen niet opvraagbaar via ?author=1 of de REST API.
add_action(
	'template_redirect',
	function () {
		if ( isset( $_GET['author'] ) && ! is_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);

// Algemene foutmelding bij inloggen (verraadt niet of de gebruikersnaam bestaat).
add_filter(
	'login_errors',
	function () {
		return 'De inloggegevens zijn onjuist.';
	}
);

/*
 * Reacties volledig uit: de site heeft geen blog en reacties zijn een
 * belangrijke bron van spamlinks.
 */
add_action(
	'init',
	function () {
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
	function () {
		remove_menu_page( 'edit-comments.php' );
	}
);
add_action(
	'wp_before_admin_bar_render',
	function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	}
);
