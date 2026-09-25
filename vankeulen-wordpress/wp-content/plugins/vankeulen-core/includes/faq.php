<?php
/**
 * Veelgestelde vragen (eigen berichttype) en aanvragen (privé berichttype).
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'vk_register_post_types' );

/**
 * Berichttypen registreren.
 */
function vk_register_post_types() {
	register_post_type(
		'vk_faq',
		array(
			'labels'          => array(
				'name'          => 'Veelgestelde vragen',
				'singular_name' => 'Vraag',
				'add_new'       => 'Nieuwe vraag',
				'add_new_item'  => 'Nieuwe vraag toevoegen',
				'edit_item'     => 'Vraag bewerken',
				'all_items'     => 'Veelgestelde vragen',
				'menu_name'     => 'Veelgestelde vragen',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'vk-gegevens',
			'show_in_rest'    => true,
			'supports'        => array( 'title', 'editor', 'page-attributes', 'revisions' ),
			'capability_type' => 'page',
			'map_meta_cap'    => true,
			'template'        => array( array( 'core/paragraph', array( 'placeholder' => 'Schrijf hier het antwoord…' ) ) ),
		)
	);

	register_post_type(
		'vk_aanvraag',
		array(
			'labels'          => array(
				'name'          => 'Aanvragen',
				'singular_name' => 'Aanvraag',
				'all_items'     => 'Aanvragen',
				'edit_item'     => 'Aanvraag bekijken',
				'menu_name'     => 'Aanvragen',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'vk-gegevens',
			'show_in_rest'    => false,
			'supports'        => array( 'title' ),
			'capability_type' => 'page',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}

// Vragen op volgorde (veld "Volgorde" in de zijbalk), ook in het beheer.
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'vk_faq' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'menu_order title' );
			$q->set( 'order', 'ASC' );
		}
	}
);

// Klassieke editor voor vragen is niet nodig; titel-placeholder aanpassen.
add_filter(
	'enter_title_here',
	function ( $t, $post ) {
		return 'vk_faq' === $post->post_type ? 'Stel hier de vraag, bijv. “Kan ik ook een boot stallen?”' : $t;
	},
	10,
	2
);

/**
 * Gepubliceerde vragen.
 *
 * @param int $aantal 0 = alle.
 * @return WP_Post[]
 */
function vk_faq_items( $aantal = 0 ) {
	return get_posts(
		array(
			'post_type'        => 'vk_faq',
			'post_status'      => 'publish',
			'posts_per_page'   => $aantal > 0 ? (int) $aantal : 100,
			'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'suppress_filters' => false,
		)
	);
}

/**
 * Antwoord als HTML.
 *
 * @param WP_Post $p Vraag.
 */
function vk_faq_antwoord_html( $p ) {
	$html = do_blocks( $p->post_content );
	$html = do_shortcode( wpautop( $html ) );
	$html = str_replace( esc_html( VK_PLACEHOLDER ), vk_placeholder_html(), $html );
	return wp_kses_post( $html );
}

/**
 * Is een antwoord definitief (geen placeholder)? Alleen die gaan in de
 * FAQPage structured data.
 *
 * @param WP_Post $p Vraag.
 */
function vk_faq_is_definitief( $p ) {
	return false === strpos( $p->post_content, VK_PLACEHOLDER ) && '' !== trim( wp_strip_all_tags( $p->post_content ) );
}

/**
 * Blok: veelgestelde vragen als uitklapbare lijst (zonder JavaScript).
 *
 * @param array $a Attributen.
 */
function vk_render_faq( $a ) {
	$items = vk_faq_items( (int) ( $a['aantal'] ?? 0 ) );
	if ( ! $items ) {
		return vk_toon_ontbrekend() ? '<p class="vk-aanleveren">Nog geen vragen. Voeg ze toe via Van Keulen → Veelgestelde vragen.</p>' : '';
	}
	$out = '<div class="' . esc_attr( vk_cls( $a, 'vk-faq' ) ) . '">';
	foreach ( $items as $i => $p ) {
		$out .= sprintf(
			'<details class="vk-faq__item"%s><summary class="vk-faq__vraag"><h3>%s</h3></summary><div class="vk-faq__antwoord">%s</div></details>',
			0 === $i ? ' open' : '',
			esc_html( get_the_title( $p ) ),
			vk_faq_antwoord_html( $p )
		);
	}
	return $out . '</div>';
}
