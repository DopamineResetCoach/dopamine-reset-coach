<?php
/**
 * Dynamische blokken. Ze tonen altijd de actuele gegevens uit "Strandpaviljoen" in het beheer,
 * dus een prijs of telefoonnummer hoeft maar op één plek aangepast te worden.
 *
 * De blokken zijn server-side gerenderd en hebben geen build-stap nodig.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Definitie van alle blokken: naam => [titel, icoon, render-functie, attributen].
 */
function vk_block_definitions(): array {
	return array(
		'notice-bar'       => array( __( 'Melding van vandaag', 'valkenisse' ), 'megaphone', 'vk_render_notice_bar', array() ),
		'today'            => array(
			__( 'Vandaag op het strand', 'valkenisse' ),
			'clock',
			'vk_render_today',
			array(
				'showWeek' => array( 'type' => 'boolean', 'default' => true, 'label' => __( 'Weekrooster tonen', 'valkenisse' ) ),
			),
		),
		'opening-hours'    => array(
			__( 'Openingstijden', 'valkenisse' ),
			'calendar',
			'vk_render_opening_hours',
			array(
				'variant' => array( 'type' => 'string', 'default' => 'table', 'enum' => array( 'table', 'compact' ), 'label' => __( 'Weergave', 'valkenisse' ) ),
			),
		),
		'hut-prices'       => array( __( 'Tarieven strandhuisjes', 'valkenisse' ), 'money-alt', 'vk_render_hut_prices', array() ),
		'hut-included'     => array( __( 'Strandhuisje: inbegrepen', 'valkenisse' ), 'yes-alt', 'vk_render_hut_included', array() ),
		'hut-request-form' => array( __( 'Aanvraagformulier strandhuisje', 'valkenisse' ), 'email-alt', 'vk_render_hut_request_form', array() ),
		'rental-prices'    => array( __( 'Tarieven strandverhuur', 'valkenisse' ), 'money-alt', 'vk_render_rental_prices', array() ),
		'menu'             => array(
			__( 'Menukaart', 'valkenisse' ),
			'food',
			'vk_render_menu',
			array(
				'showNav' => array( 'type' => 'boolean', 'default' => true, 'label' => __( 'Snelmenu met kaartonderdelen', 'valkenisse' ) ),
			),
		),
		'vacancies'        => array( __( 'Vacatures', 'valkenisse' ), 'groups', 'vk_render_vacancies', array() ),
		'gallery'          => array(
			__( "Fotogalerij", 'valkenisse' ),
			'format-gallery',
			'vk_render_gallery',
			array(
				'category'    => array( 'type' => 'string', 'default' => '', 'label' => __( 'Alleen categorie (slug, leeg = alles)', 'valkenisse' ) ),
				'limit'       => array( 'type' => 'number', 'default' => 60, 'label' => __( "Maximaal aantal foto's", 'valkenisse' ) ),
				'showFilters' => array( 'type' => 'boolean', 'default' => true, 'label' => __( 'Filterknoppen tonen', 'valkenisse' ) ),
			),
		),
		'reviews'          => array( __( 'Gastreacties', 'valkenisse' ), 'format-quote', 'vk_render_reviews', array() ),
		'contact-details'  => array(
			__( 'Contactgegevens & adressen', 'valkenisse' ),
			'location',
			'vk_render_contact_details',
			array(
				'variant' => array( 'type' => 'string', 'default' => 'full', 'enum' => array( 'full', 'compact' ), 'label' => __( 'Weergave', 'valkenisse' ) ),
			),
		),
		'contact-buttons'  => array(
			__( 'Knoppen: bellen, route, e-mail', 'valkenisse' ),
			'phone',
			'vk_render_contact_buttons',
			array(
				'buttons' => array( 'type' => 'string', 'default' => 'call,route,mail', 'label' => __( 'Knoppen (call, route, mail, hut, hours)', 'valkenisse' ) ),
				'style'   => array( 'type' => 'string', 'default' => 'solid', 'enum' => array( 'solid', 'light', 'header' ), 'label' => __( 'Stijl', 'valkenisse' ) ),
			),
		),
		'map'              => array( __( 'Kaart met locatie', 'valkenisse' ), 'location-alt', 'vk_render_map', array() ),
		'breadcrumbs'      => array( __( 'Kruimelpad', 'valkenisse' ), 'arrow-right-alt2', 'vk_render_breadcrumbs', array() ),
		'social-links'     => array( __( 'Social media', 'valkenisse' ), 'share', 'vk_render_social_links', array() ),
		'copyright'        => array( __( 'Copyright-regel', 'valkenisse' ), 'editor-code', 'vk_render_copyright', array() ),
		'language-switcher' => array( __( 'Taalkeuze (NL | DE)', 'valkenisse' ), 'translation', 'vk_render_language_switcher', array() ),
		'mobile-bar'       => array( __( 'Mobiele knoppenbalk', 'valkenisse' ), 'smartphone', 'vk_render_mobile_bar', array() ),
		'phone'            => array(
			__( 'Telefoonnummer (tekst)', 'valkenisse' ),
			'phone',
			'vk_render_phone',
			array(),
		),
	);
}

add_action( 'init', static function () {
	wp_register_script(
		'vk-blocks-editor',
		VK_URL . 'assets/js/blocks-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		VK_VERSION,
		true
	);

	$client = array();
	foreach ( vk_block_definitions() as $name => $def ) {
		list( $title, $icon, $callback, $attributes ) = $def;

		$server_attributes = array();
		foreach ( $attributes as $key => $attr ) {
			$server_attributes[ $key ] = array_intersect_key( $attr, array_flip( array( 'type', 'default', 'enum' ) ) );
		}

		register_block_type(
			'valkenisse/' . $name,
			array(
				'api_version'     => 3,
				'title'           => $title,
				'category'        => 'valkenisse',
				'attributes'      => $server_attributes + array(
					'className' => array( 'type' => 'string', 'default' => '' ),
					'align'     => array( 'type' => 'string', 'default' => '' ),
				),
				'supports'        => array( 'html' => false, 'align' => array( 'wide', 'full' ) ),
				'render_callback' => static function ( $attrs ) use ( $callback ) {
					$html = call_user_func( $callback, is_array( $attrs ) ? $attrs : array() );
					$classes = trim( ( $attrs['className'] ?? '' ) . ( ! empty( $attrs['align'] ) ? ' align' . $attrs['align'] : '' ) );
					if ( '' !== $html && '' !== $classes ) {
						$html = '<div class="' . esc_attr( $classes ) . '">' . $html . '</div>';
					}
					return $html;
				},
				'editor_script_handles' => array( 'vk-blocks-editor' ),
			)
		);

		$client[] = array(
			'name'       => 'valkenisse/' . $name,
			'title'      => $title,
			'icon'       => $icon,
			'attributes' => $attributes,
		);
	}

	wp_add_inline_script( 'vk-blocks-editor', 'window.vkBlocks = ' . wp_json_encode( $client ) . ';', 'before' );
} );

add_filter( 'block_categories_all', static function ( array $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'valkenisse',
			'title' => __( 'Strandpaviljoen Valkenisse', 'valkenisse' ),
			'icon'  => null,
		)
	);
	return $categories;
} );

/**
 * Stylesheet voor de dynamische blokken (klein, alleen op de voorkant + editor).
 */
add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_style( 'vk-blocks', VK_URL . 'assets/css/blocks.css', array(), VK_VERSION );
	wp_register_script( 'vk-gallery', VK_URL . 'assets/js/gallery.js', array(), VK_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_register_script( 'vk-map', VK_URL . 'assets/js/map.js', array(), VK_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
} );
add_action( 'enqueue_block_assets', static function () {
	if ( is_admin() ) {
		wp_enqueue_style( 'vk-blocks', VK_URL . 'assets/css/blocks.css', array(), VK_VERSION );
	}
} );
