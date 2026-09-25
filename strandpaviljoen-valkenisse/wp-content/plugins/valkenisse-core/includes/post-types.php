<?php
/**
 * Inhoudstypen: menukaart, vacatures, gastreacties, strandhuisje-aanvragen en fotocategorieën.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'vk_register_post_types' );

function vk_register_post_types(): void {
	$common = array(
		'show_in_menu' => 'valkenisse',
		'show_in_rest' => true,
	);

	// Menukaart: één item per gerecht of drankje.
	register_post_type(
		'vk_menu_item',
		$common + array(
			'labels'       => array(
				'name'          => __( 'Menukaart', 'valkenisse' ),
				'singular_name' => __( 'Gerecht of drankje', 'valkenisse' ),
				'add_new'       => __( 'Nieuw item', 'valkenisse' ),
				'add_new_item'  => __( 'Nieuw gerecht of drankje', 'valkenisse' ),
				'edit_item'     => __( 'Item bewerken', 'valkenisse' ),
				'all_items'     => __( 'Menukaart', 'valkenisse' ),
				'menu_name'     => __( 'Menukaart', 'valkenisse' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'supports'     => array( 'title', 'page-attributes' ),
			'menu_icon'    => 'dashicons-food',
		)
	);
	register_taxonomy(
		'vk_menu_cat',
		'vk_menu_item',
		array(
			'labels'            => array(
				'name'          => __( 'Kaartonderdelen', 'valkenisse' ),
				'singular_name' => __( 'Kaartonderdeel', 'valkenisse' ),
				'add_new_item'  => __( 'Nieuw kaartonderdeel', 'valkenisse' ),
				'menu_name'     => __( 'Kaartonderdelen', 'valkenisse' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
		)
	);

	// Vacatures.
	register_post_type(
		'vk_vacature',
		$common + array(
			'labels'       => array(
				'name'          => __( 'Vacatures', 'valkenisse' ),
				'singular_name' => __( 'Vacature', 'valkenisse' ),
				'add_new'       => __( 'Nieuwe vacature', 'valkenisse' ),
				'add_new_item'  => __( 'Nieuwe vacature', 'valkenisse' ),
				'edit_item'     => __( 'Vacature bewerken', 'valkenisse' ),
				'all_items'     => __( 'Vacatures', 'valkenisse' ),
				'menu_name'     => __( 'Vacatures', 'valkenisse' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'vacature', 'with_front' => false ),
			'supports'     => array( 'title', 'editor', 'excerpt' ),
			'template'     => array( array( 'core/paragraph', array( 'placeholder' => __( 'Omschrijving van de functie…', 'valkenisse' ) ) ) ),
		)
	);

	// Gastreacties (alleen echte reacties, met toestemming).
	register_post_type(
		'vk_gastreactie',
		$common + array(
			'labels'       => array(
				'name'          => __( 'Gastreacties', 'valkenisse' ),
				'singular_name' => __( 'Gastreactie', 'valkenisse' ),
				'add_new'       => __( 'Nieuwe reactie', 'valkenisse' ),
				'add_new_item'  => __( 'Nieuwe gastreactie', 'valkenisse' ),
				'all_items'     => __( 'Gastreacties', 'valkenisse' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => false,
			'supports'     => array( 'title', 'page-attributes' ),
		)
	);

	// Binnengekomen aanvragen voor strandhuisjes (alleen-lezen archief).
	register_post_type(
		'vk_aanvraag',
		$common + array(
			'labels'       => array(
				'name'          => __( 'Aanvragen strandhuisjes', 'valkenisse' ),
				'singular_name' => __( 'Aanvraag', 'valkenisse' ),
				'all_items'     => __( 'Aanvragen', 'valkenisse' ),
				'edit_item'     => __( 'Aanvraag', 'valkenisse' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => false,
			'supports'     => array( 'title' ),
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);

	// Fotocategorieën voor de galerij (op afbeeldingen in de mediabibliotheek).
	register_taxonomy(
		'vk_foto_cat',
		'attachment',
		array(
			'labels'                => array(
				'name'          => __( 'Fotocategorieën', 'valkenisse' ),
				'singular_name' => __( 'Fotocategorie', 'valkenisse' ),
				'menu_name'     => __( 'Fotocategorieën', 'valkenisse' ),
			),
			'public'                => false,
			'show_ui'               => true,
			'show_in_rest'          => true,
			'hierarchical'          => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_generic_term_count',
		)
	);
}

/**
 * Standaard fotocategorieën aanmaken.
 */
function vk_seed_terms(): void {
	$terms = array(
		'paviljoen'      => __( 'Paviljoen', 'valkenisse' ),
		'terras'         => __( 'Terras', 'valkenisse' ),
		'strand'         => __( 'Strand', 'valkenisse' ),
		'eten-drinken'   => __( 'Eten & drinken', 'valkenisse' ),
		'strandhuisjes'  => __( 'Strandhuisjes', 'valkenisse' ),
		'historie'       => __( 'Historie', 'valkenisse' ),
	);
	$order = 0;
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'vk_foto_cat' ) ) {
			$result = wp_insert_term( $name, 'vk_foto_cat', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $result ) ) {
				update_term_meta( $result['term_id'], 'vk_order', $order );
			}
		}
		++$order;
	}
}

/**
 * Metavelden per inhoudstype. Eén definitie voor zowel het beheerscherm als het opslaan.
 */
function vk_meta_fields(): array {
	return array(
		'vk_menu_item'   => array(
			'title'  => __( 'Details', 'valkenisse' ),
			'fields' => array(
				'vk_price'       => array( 'label' => __( 'Prijs', 'valkenisse' ), 'type' => 'text', 'placeholder' => '€ 0,00' ),
				'vk_description' => array( 'label' => __( 'Korte omschrijving (optioneel)', 'valkenisse' ), 'type' => 'textarea' ),
				'vk_tags'        => array( 'label' => __( 'Kenmerken (optioneel)', 'valkenisse' ), 'type' => 'text', 'placeholder' => __( 'bv. vegetarisch', 'valkenisse' ) ),
				'vk_hidden'      => array( 'label' => __( 'Tijdelijk niet leverbaar (verbergen)', 'valkenisse' ), 'type' => 'checkbox' ),
			),
		),
		'vk_vacature'    => array(
			'title'  => __( 'Vacaturegegevens', 'valkenisse' ),
			'fields' => array(
				'vk_active'  => array( 'label' => __( 'Vacature is actief (zichtbaar op de website)', 'valkenisse' ), 'type' => 'checkbox' ),
				'vk_hours'   => array( 'label' => __( 'Uren', 'valkenisse' ), 'type' => 'text', 'placeholder' => __( 'bv. 16–24 uur per week', 'valkenisse' ) ),
				'vk_age'     => array( 'label' => __( 'Leeftijd (indien van toepassing)', 'valkenisse' ), 'type' => 'text' ),
				'vk_period'  => array( 'label' => __( 'Periode', 'valkenisse' ), 'type' => 'text', 'placeholder' => __( 'bv. zomerseizoen', 'valkenisse' ) ),
				'vk_contact' => array( 'label' => __( 'Contactpersoon', 'valkenisse' ), 'type' => 'text' ),
				'vk_email'   => array( 'label' => __( 'E-mail voor sollicitaties', 'valkenisse' ), 'type' => 'email' ),
			),
		),
		'vk_gastreactie' => array(
			'title'  => __( 'Reactie', 'valkenisse' ),
			'fields' => array(
				'vk_quote'   => array( 'label' => __( 'Reactie (kort, letterlijk)', 'valkenisse' ), 'type' => 'textarea' ),
				'vk_meta'    => array( 'label' => __( 'Plaats / datum (optioneel)', 'valkenisse' ), 'type' => 'text', 'placeholder' => __( 'bv. uit het gastenboek, zomer 2025', 'valkenisse' ) ),
				'vk_consent' => array( 'label' => __( 'De gast heeft toestemming gegeven om deze reactie te tonen', 'valkenisse' ), 'type' => 'checkbox' ),
			),
		),
	);
}

add_action( 'add_meta_boxes', static function () {
	foreach ( vk_meta_fields() as $type => $box ) {
		add_meta_box(
			'vk_meta_' . $type,
			$box['title'],
			static function ( WP_Post $post ) use ( $type ) {
				vk_render_meta_box( $post, $type );
			},
			$type,
			'vk_vacature' === $type ? 'side' : 'normal',
			'high'
		);
	}
	add_meta_box( 'vk_aanvraag_details', __( 'Gegevens', 'valkenisse' ), 'vk_render_request_box', 'vk_aanvraag', 'normal', 'high' );
} );

function vk_render_meta_box( WP_Post $post, string $type ): void {
	wp_nonce_field( 'vk_meta_' . $type, 'vk_meta_nonce' );
	echo '<div class="vk-meta">';
	foreach ( vk_meta_fields()[ $type ]['fields'] as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( 'vk_active' === $key && 'auto-draft' === $post->post_status ) {
			$value = 1;
		}
		echo '<p>';
		if ( 'checkbox' === $field['type'] ) {
			printf( '<label><input type="checkbox" name="%1$s" value="1" %2$s /> %3$s</label>', esc_attr( $key ), checked( (int) $value, 1, false ), esc_html( $field['label'] ) );
		} elseif ( 'textarea' === $field['type'] ) {
			printf( '<label for="%1$s"><strong>%2$s</strong></label><br /><textarea id="%1$s" name="%1$s" rows="3" class="widefat">%3$s</textarea>', esc_attr( $key ), esc_html( $field['label'] ), esc_textarea( (string) $value ) );
		} else {
			printf(
				'<label for="%1$s"><strong>%2$s</strong></label><br /><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" placeholder="%5$s" class="widefat" />',
				esc_attr( $key ),
				esc_html( $field['label'] ),
				esc_attr( $field['type'] ),
				esc_attr( (string) $value ),
				esc_attr( $field['placeholder'] ?? '' )
			);
		}
		echo '</p>';
	}
	echo '</div>';
}

add_action( 'save_post', static function ( int $post_id, WP_Post $post ) {
	$fields = vk_meta_fields();
	if ( ! isset( $fields[ $post->post_type ] ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['vk_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['vk_meta_nonce'] ), 'vk_meta_' . $post->post_type ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( $fields[ $post->post_type ]['fields'] as $key => $field ) {
		$raw = wp_unslash( $_POST[ $key ] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		switch ( $field['type'] ) {
			case 'checkbox':
				$value = empty( $raw ) ? 0 : 1;
				break;
			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				break;
			case 'email':
				$value = sanitize_email( $raw );
				break;
			default:
				$value = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, $key, $value );
	}
}, 10, 2 );

/**
 * Handige kolommen in de overzichten.
 */
add_filter( 'manage_vk_menu_item_posts_columns', static fn( $cols ) => array_slice( $cols, 0, 2 ) + array( 'vk_price' => __( 'Prijs', 'valkenisse' ) ) + $cols );
add_filter( 'manage_vk_vacature_posts_columns', static fn( $cols ) => array_slice( $cols, 0, 2 ) + array( 'vk_active' => __( 'Op de website', 'valkenisse' ) ) + $cols );
add_filter( 'manage_vk_gastreactie_posts_columns', static fn( $cols ) => array_slice( $cols, 0, 2 ) + array( 'vk_consent' => __( 'Toestemming', 'valkenisse' ) ) + $cols );
add_filter(
	'manage_vk_aanvraag_posts_columns',
	static fn( $cols ) => array(
		'cb'         => $cols['cb'],
		'title'      => __( 'Naam', 'valkenisse' ),
		'vk_type'    => __( 'Huur', 'valkenisse' ),
		'vk_period'  => __( 'Periode', 'valkenisse' ),
		'vk_count'   => __( 'Aantal', 'valkenisse' ),
		'vk_contact' => __( 'Contact', 'valkenisse' ),
		'date'       => __( 'Ontvangen', 'valkenisse' ),
	)
);

add_action( 'manage_posts_custom_column', static function ( string $column, int $post_id ) {
	switch ( $column ) {
		case 'vk_price':
			echo esc_html( (string) get_post_meta( $post_id, 'vk_price', true ) );
			break;
		case 'vk_active':
		case 'vk_consent':
			echo get_post_meta( $post_id, $column, true ) ? '✅ ' . esc_html__( 'Ja', 'valkenisse' ) : '— ' . esc_html__( 'Nee', 'valkenisse' );
			break;
		case 'vk_type':
		case 'vk_period':
		case 'vk_count':
			echo esc_html( (string) get_post_meta( $post_id, $column, true ) );
			break;
		case 'vk_contact':
			$email = (string) get_post_meta( $post_id, 'vk_email', true );
			$phone = (string) get_post_meta( $post_id, 'vk_phone', true );
			printf( '<a href="mailto:%1$s">%1$s</a><br />%2$s', esc_attr( $email ), esc_html( $phone ) );
			break;
	}
}, 10, 2 );

function vk_render_request_box( WP_Post $post ): void {
	$labels = array(
		'vk_email'   => __( 'E-mail', 'valkenisse' ),
		'vk_phone'   => __( 'Telefoon', 'valkenisse' ),
		'vk_type'    => __( 'Gewenste huur', 'valkenisse' ),
		'vk_period'  => __( 'Datum / periode', 'valkenisse' ),
		'vk_count'   => __( 'Aantal strandhuisjes', 'valkenisse' ),
		'vk_message' => __( 'Opmerking', 'valkenisse' ),
	);
	echo '<table class="form-table">';
	foreach ( $labels as $key => $label ) {
		$value = (string) get_post_meta( $post->ID, $key, true );
		if ( 'vk_email' === $key && is_email( $value ) ) {
			$value_html = '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
		} elseif ( 'vk_phone' === $key && $value ) {
			$value_html = '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $value ) ) . '">' . esc_html( $value ) . '</a>';
		} else {
			$value_html = nl2br( esc_html( $value ) );
		}
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . $value_html . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hierboven ge-escaped.
	}
	echo '</table>';
}

/**
 * Inactieve vacature direct openen → terug naar de vacaturepagina.
 */
add_action( 'template_redirect', static function () {
	if ( is_singular( 'vk_vacature' ) && ! get_post_meta( get_queried_object_id(), 'vk_active', true ) ) {
		wp_safe_redirect( vk_page_url( 'vacatures' ), 302 );
		exit;
	}
} );

/**
 * Inactieve vacatures niet in de sitemap.
 */
add_filter( 'wp_sitemaps_posts_query_args', static function ( array $args, string $post_type ) {
	if ( 'vk_vacature' === $post_type ) {
		$args['meta_query'] = array( array( 'key' => 'vk_active', 'value' => '1' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}
	return $args;
}, 10, 2 );

/**
 * Om middernacht de paginacache legen, zodat "alleen vandaag"-meldingen ook met caching netjes vervallen.
 */
add_action( 'init', static function () {
	if ( ! wp_next_scheduled( 'vk_midnight' ) ) {
		$next = vk_now()->modify( 'tomorrow 00:01' )->getTimestamp();
		wp_schedule_event( $next, 'daily', 'vk_midnight' );
	}
} );
add_action( 'vk_midnight', 'vk_flush_page_cache' );

/**
 * Wijzigingen aan menukaart, vacatures en reacties → cache legen.
 */
add_action( 'save_post', static function ( int $post_id, WP_Post $post ) {
	if ( in_array( $post->post_type, array( 'vk_menu_item', 'vk_vacature', 'vk_gastreactie' ), true ) ) {
		vk_flush_page_cache();
	}
}, 20, 2 );
