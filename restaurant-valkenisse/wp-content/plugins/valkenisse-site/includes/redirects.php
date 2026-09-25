<?php
/**
 * 301-doorverwijzingen van adressen van de oude website naar de nieuwe structuur,
 * zodat bestaande Google-posities en links blijven werken.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

function valkenisse_redirect_map(): array {
	return apply_filters(
		'valkenisse_redirects',
		array(
			// Exacte paden (zonder domein, met slash aan het eind).
			'/ons-menu/'                                   => '/menukaart/',
			'/menukaart/lunch/'                            => '/menukaart/#lunch',
			'/menukaart/diner/'                            => '/menukaart/#diner',
			'/menukaart/pizza/'                            => '/menukaart/#pizza',
			'/menukaart/noord-afrikaans-menu/'             => '/menukaart/#noord-afrikaans',
			'/menukaart/pannenkoeken-poffertjes-wafels/'   => '/menukaart/#pannenkoeken-poffertjes-wafels',
			'/menukaart/kindermenu/'                       => '/menukaart/#kindermenu',
			'/omgeving/'                                   => '/#omgeving',
			'/fotos/'                                      => '/galerij/',
			// Voorvoegsels (alles eronder).
			'/fotos/*'                                     => '/galerij/',
			'/product-categorie/*'                         => '/feesten-partijen/',
			'/product/*'                                   => '/feesten-partijen/',
		)
	);
}

add_action(
	'template_redirect',
	static function () {
		if ( ! is_404() ) {
			return;
		}
		$path = wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! $path ) {
			return;
		}
		$path = trailingslashit( strtolower( $path ) );
		foreach ( valkenisse_redirect_map() as $from => $to ) {
			$match = str_ends_with( $from, '*' ) ? str_starts_with( $path, rtrim( $from, '*' ) ) : $path === $from;
			if ( $match ) {
				wp_safe_redirect( home_url( $to ), 301, 'Valkenisse' );
				exit;
			}
		}
	},
	1
);
