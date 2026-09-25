<?php
/**
 * Inhoudstypen: Gerechten (menukaart), Studio's, Feesten & buffetten, Aanvragen.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'valkenisse_register_post_types' );

function valkenisse_labels( string $singular, string $plural, string $menu = '' ): array {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $menu ? $menu : $plural,
		'add_new'            => 'Nieuw toevoegen',
		'add_new_item'       => $singular . ' toevoegen',
		'edit_item'          => $singular . ' bewerken',
		'new_item'           => 'Nieuw: ' . strtolower( $singular ),
		'view_item'          => $singular . ' bekijken',
		'search_items'       => 'Zoeken',
		'not_found'          => 'Niets gevonden',
		'not_found_in_trash' => 'Niets gevonden in de prullenbak',
		'all_items'          => 'Alle ' . strtolower( $plural ),
	);
}

function valkenisse_register_post_types(): void {
	register_post_type(
		'gerecht',
		array(
			'labels'          => valkenisse_labels( 'Gerecht', 'Gerechten', 'Menukaart' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-food',
			'menu_position'   => 4,
			'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'post',
		)
	);

	register_taxonomy(
		'menu_categorie',
		'gerecht',
		array(
			'labels'            => array(
				'name'          => 'Categorieën',
				'singular_name' => 'Categorie',
				'menu_name'     => 'Categorieën',
				'add_new_item'  => 'Nieuwe categorie',
				'edit_item'     => 'Categorie bewerken',
				'parent_item'   => 'Hoofdcategorie',
				'search_items'  => 'Categorieën zoeken',
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		)
	);

	register_taxonomy(
		'dieet',
		'gerecht',
		array(
			'labels'            => array(
				'name'          => 'Dieetkenmerken',
				'singular_name' => 'Dieetkenmerk',
				'menu_name'     => 'Dieetkenmerken',
				'add_new_item'  => 'Nieuw dieetkenmerk',
			),
			'hierarchical'      => true, // Toont vinkjes i.p.v. een tekstveld.
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		)
	);

	register_post_type(
		'studio',
		array(
			'labels'          => valkenisse_labels( 'Studio', "Studio's" ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-admin-home',
			'menu_position'   => 5,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'template'        => array(
				array( 'core/paragraph', array( 'placeholder' => 'Beschrijf de studio…' ) ),
				array( 'core/gallery', array( 'linkTo' => 'none' ) ),
			),
		)
	);

	register_post_type(
		'arrangement',
		array(
			'labels'          => valkenisse_labels( 'Buffet / arrangement', 'Feesten & buffetten', 'Feesten' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-groups',
			'menu_position'   => 6,
			'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'aanvraag',
		array(
			'labels'          => valkenisse_labels( 'Aanvraag', 'Aanvragen' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 7,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);

	$meta = array(
		'gerecht'     => array( 'prijs', 'omschrijving', 'allergenen' ),
		'studio'      => array( 'personen', 'faciliteiten' ),
		'arrangement' => array( 'omschrijving', 'inbegrepen', 'prijs', 'minimum' ),
	);
	foreach ( $meta as $type => $keys ) {
		foreach ( $keys as $key ) {
			register_post_meta(
				$type,
				'_valk_' . $key,
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

/* -------------------------------------------------------------------------
 * Invoervelden (eenvoudige velden zonder extra plugin)
 * ---------------------------------------------------------------------- */

function valkenisse_meta_fields(): array {
	return array(
		'gerecht'     => array(
			'title'  => 'Gerecht',
			'fields' => array(
				'prijs'        => array( 'Prijs', 'text', 'Bijvoorbeeld 14,50 of "dagprijs". Het €-teken wordt automatisch toegevoegd bij een bedrag.' ),
				'omschrijving' => array( 'Omschrijving', 'textarea', 'Korte omschrijving die onder de naam verschijnt.' ),
				'allergenen'   => array( 'Allergenen (optioneel)', 'text', 'Bijvoorbeeld "bevat noten, gluten". Kies vegetarisch/glutenvrij rechts bij "Dieetkenmerken".' ),
			),
		),
		'studio'      => array(
			'title'  => 'Studiogegevens',
			'fields' => array(
				'personen'     => array( 'Aantal personen', 'text', '' ),
				'faciliteiten' => array( 'Faciliteiten (één per regel)', 'textarea', 'Alleen faciliteiten die u echt aanbiedt.' ),
			),
		),
		'arrangement' => array(
			'title'  => 'Buffet / arrangement',
			'fields' => array(
				'omschrijving' => array( 'Omschrijving', 'textarea', '' ),
				'inbegrepen'   => array( 'Wat is inbegrepen? (één per regel)', 'textarea', '' ),
				'prijs'        => array( 'Prijs', 'text', 'Bijvoorbeeld "€ 27,50 p.p." of "op aanvraag".' ),
				'minimum'      => array( 'Minimum aantal personen', 'text', '' ),
			),
		),
	);
}

add_action(
	'add_meta_boxes',
	static function () {
		foreach ( valkenisse_meta_fields() as $type => $box ) {
			add_meta_box( 'valk_' . $type, $box['title'], 'valkenisse_render_meta_box', $type, 'normal', 'high', $box );
		}
		add_meta_box( 'valk_aanvraag', 'Aanvraag', 'valkenisse_render_request_box', 'aanvraag', 'normal', 'high' );
	}
);

function valkenisse_render_meta_box( WP_Post $post, array $box ): void {
	wp_nonce_field( 'valk_meta', 'valk_meta_nonce' );
	echo '<div class="valk-fields">';
	foreach ( $box['args']['fields'] as $key => [ $label, $type, $help ] ) {
		$value = get_post_meta( $post->ID, '_valk_' . $key, true );
		$id    = 'valk_' . $key;
		echo '<p><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'textarea' === $type ) {
			echo '<textarea id="' . esc_attr( $id ) . '" name="valk[' . esc_attr( $key ) . ']" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input id="' . esc_attr( $id ) . '" type="text" name="valk[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="large-text">';
		}
		if ( $help ) {
			echo '<span class="description">' . esc_html( $help ) . '</span>';
		}
		echo '</p>';
	}
	echo '</div>';
}

add_action(
	'save_post',
	static function ( int $post_id, WP_Post $post ) {
		$fields = valkenisse_meta_fields();
		if ( ! isset( $fields[ $post->post_type ], $_POST['valk_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_POST['valk_meta_nonce'] ), 'valk_meta' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		$input = isset( $_POST['valk'] ) && is_array( $_POST['valk'] ) ? wp_unslash( $_POST['valk'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- per veld hieronder.
		foreach ( $fields[ $post->post_type ]['fields'] as $key => $def ) {
			$value = $input[ $key ] ?? '';
			$value = 'textarea' === $def[1] ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
			update_post_meta( $post_id, '_valk_' . $key, $value );
		}
	},
	10,
	2
);

// Titelveld duidelijker maken.
add_filter(
	'enter_title_here',
	static function ( string $text, WP_Post $post ) {
		return array(
			'gerecht'     => 'Naam van het gerecht',
			'studio'      => 'Naam van de studio',
			'arrangement' => 'Naam, bv. Barbecuebuffet',
		)[ $post->post_type ] ?? $text;
	},
	10,
	2
);

/** Prijs netjes weergeven: "14.5" → "€ 14,50", tekst blijft tekst. */
function valkenisse_format_price( string $price ): string {
	$price = trim( $price );
	if ( '' === $price ) {
		return '';
	}
	$plain = str_replace( array( '€', ' ' ), '', $price );
	if ( preg_match( '/^\d+([.,]\d{1,2})?$/', $plain ) ) {
		return '€ ' . number_format( (float) str_replace( ',', '.', $plain ), 2, ',', '.' );
	}
	return $price;
}

/* -------------------------------------------------------------------------
 * Aanvragen (formulierinzendingen): alleen-lezen weergave
 * ---------------------------------------------------------------------- */

function valkenisse_render_request_box( WP_Post $post ): void {
	$data = get_post_meta( $post->ID, '_valk_data', true );
	if ( ! is_array( $data ) ) {
		echo '<p>Geen gegevens.</p>';
		return;
	}
	echo '<table class="widefat striped"><tbody>';
	foreach ( $data as $label => $value ) {
		echo '<tr><th style="width:30%">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
	$email = $data['E-mail'] ?? '';
	if ( is_email( $email ) ) {
		echo '<p><a class="button button-primary" href="' . esc_url( 'mailto:' . $email ) . '">Beantwoorden per e-mail</a></p>';
	}
}

add_filter(
	'manage_aanvraag_posts_columns',
	static fn( $cols ) => array(
		'cb'         => $cols['cb'],
		'title'      => 'Aanvraag',
		'valk_type'  => 'Soort',
		'date'       => 'Ontvangen',
	)
);
add_action(
	'manage_aanvraag_posts_custom_column',
	static function ( $col, $post_id ) {
		if ( 'valk_type' === $col ) {
			echo esc_html( get_post_meta( $post_id, '_valk_type', true ) );
		}
	},
	10,
	2
);

// Aanvragen alleen-lezen: geen bewerkbare velden behalve status/prullenbak.
add_action(
	'admin_head-post.php',
	static function () {
		if ( 'aanvraag' === get_current_screen()->post_type ) {
			echo '<style>#titlediv input{pointer-events:none;background:#f6f7f7}</style>';
		}
	}
);

/* -------------------------------------------------------------------------
 * Berichten heten "Nieuws"
 * ---------------------------------------------------------------------- */

add_action(
	'init',
	static function () {
		$obj = get_post_type_object( 'post' );
		if ( $obj ) {
			$obj->labels = (object) array_merge( (array) $obj->labels, valkenisse_labels( 'Nieuwsbericht', 'Nieuws' ) );
			$obj->menu_icon = 'dashicons-megaphone';
		}
	},
	20
);
