<?php
/**
 * Beheeromgeving: één overzichtelijk menu "Strandpaviljoen" met alles wat de familie zelf aanpast.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wie mag de gegevens aanpassen? Standaard iedereen met de rol Redacteur of hoger.
 */
function vk_capability(): string {
	return (string) apply_filters( 'valkenisse_capability', 'edit_pages' );
}

add_action( 'admin_menu', static function () {
	$cap = vk_capability();

	add_menu_page(
		__( 'Strandpaviljoen', 'valkenisse' ),
		__( 'Strandpaviljoen', 'valkenisse' ),
		$cap,
		'valkenisse',
		'vk_admin_overview',
		'dashicons-palmtree',
		2
	);
	add_submenu_page( 'valkenisse', __( 'Overzicht', 'valkenisse' ), __( 'Overzicht', 'valkenisse' ), $cap, 'valkenisse', 'vk_admin_overview' );

	foreach ( vk_settings_schema() as $slug => $page ) {
		add_submenu_page(
			'valkenisse',
			$page['title'],
			$page['title'],
			$cap,
			'valkenisse-' . $slug,
			static function () use ( $slug ) {
				vk_admin_settings_page( $slug );
			}
		);
	}

	add_submenu_page( 'valkenisse', __( "Foto's", 'valkenisse' ), __( "Foto's", 'valkenisse' ), 'upload_files', 'upload.php?mode=grid' );
}, 9 );

add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	wp_enqueue_style( 'vk-admin', VK_URL . 'assets/css/admin.css', array(), VK_VERSION );
} );

/**
 * Overzichtspagina: grote knoppen naar alle onderdelen.
 */
function vk_admin_overview(): void {
	$today = vk_today();
	$tiles = array(
		array( 'admin.php?page=valkenisse-vandaag', 'dashicons-megaphone', __( 'Vandaag open of dicht', 'valkenisse' ), $today['headline'] ?: __( 'Normaal rooster', 'valkenisse' ) ),
		array( 'admin.php?page=valkenisse-openingstijden', 'dashicons-clock', __( 'Openingstijden', 'valkenisse' ), __( 'Het vaste weekrooster', 'valkenisse' ) ),
		array( 'edit.php?post_type=vk_menu_item', 'dashicons-food', __( 'Menukaart', 'valkenisse' ), __( 'Gerechten, dranken en prijzen', 'valkenisse' ) ),
		array( 'admin.php?page=valkenisse-strandhuisjes', 'dashicons-admin-home', __( 'Strandhuisjes', 'valkenisse' ), __( 'Tarieven en aanvraagformulier', 'valkenisse' ) ),
		array( 'edit.php?post_type=vk_aanvraag', 'dashicons-email-alt', __( 'Aanvragen strandhuisjes', 'valkenisse' ), __( 'Binnengekomen aanvragen', 'valkenisse' ) ),
		array( 'admin.php?page=valkenisse-strandverhuur', 'dashicons-palmtree', __( 'Verhuurprijzen', 'valkenisse' ), __( 'Stoelen, ligbedden, parasols, windschermen', 'valkenisse' ) ),
		array( 'upload.php?mode=grid', 'dashicons-format-gallery', __( "Foto's", 'valkenisse' ), __( 'Toevoegen en een categorie kiezen', 'valkenisse' ) ),
		array( 'edit.php?post_type=vk_vacature', 'dashicons-groups', __( 'Vacatures', 'valkenisse' ), __( 'Aan- of uitzetten', 'valkenisse' ) ),
		array( 'edit.php?post_type=vk_gastreactie', 'dashicons-format-quote', __( 'Gastreacties', 'valkenisse' ), __( 'Max. 3 op de homepage, alleen met toestemming', 'valkenisse' ) ),
		array( 'admin.php?page=valkenisse-contact', 'dashicons-phone', __( 'Contactgegevens', 'valkenisse' ), __( 'Telefoon, e-mail, adressen, social', 'valkenisse' ) ),
	);
	echo '<div class="wrap vk-admin"><h1>' . esc_html__( 'Strandpaviljoen Valkenisse', 'valkenisse' ) . '</h1>';
	echo '<p class="vk-admin__lead">' . esc_html__( 'Kies wat je wilt aanpassen. Wijzigingen staan direct op de website.', 'valkenisse' ) . '</p>';
	echo '<div class="vk-tiles">';
	foreach ( $tiles as $tile ) {
		printf(
			'<a class="vk-tile" href="%1$s"><span class="dashicons %2$s" aria-hidden="true"></span><strong>%3$s</strong><span>%4$s</span></a>',
			esc_url( admin_url( $tile[0] ) ),
			esc_attr( $tile[1] ),
			esc_html( $tile[2] ),
			esc_html( $tile[3] )
		);
	}
	echo '</div></div>';
}

/**
 * Generieke instellingenpagina op basis van het schema.
 */
function vk_admin_settings_page( string $slug ): void {
	$schema = vk_settings_schema();
	if ( ! isset( $schema[ $slug ] ) || ! current_user_can( vk_capability() ) ) {
		return;
	}
	$page = $schema[ $slug ];

	echo '<div class="wrap vk-admin">';
	echo '<h1>' . esc_html( $page['title'] ) . '</h1>';
	if ( ! empty( $_GET['vk-saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Opgeslagen. De website is bijgewerkt.', 'valkenisse' ) . '</p></div>';
	}
	if ( ! empty( $page['intro'] ) ) {
		echo '<p class="vk-admin__lead">' . esc_html( $page['intro'] ) . '</p>';
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="vk_save_settings" />';
	echo '<input type="hidden" name="vk_page" value="' . esc_attr( $slug ) . '" />';
	wp_nonce_field( 'vk_save_' . $slug );

	foreach ( $page['sections'] as $section ) {
		echo '<div class="vk-card"><h2>' . esc_html( $section['title'] ) . '</h2><table class="form-table" role="presentation">';
		foreach ( $section['fields'] as $field ) {
			vk_admin_field( $field );
		}
		echo '</table></div>';
	}

	submit_button( __( 'Opslaan', 'valkenisse' ), 'primary large' );
	echo '</form></div>';
}

/**
 * Eén formulierveld renderen.
 */
function vk_admin_field( array $field ): void {
	$key   = $field['key'];
	$name  = 'vk[' . $key . ']';
	$id    = 'vk-' . $key;
	$value = vk_get( $key );

	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

	switch ( $field['type'] ) {
		case 'textarea':
			printf( '<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ) );
			break;
		case 'checkbox':
			printf( '<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>', esc_attr( $id ), esc_attr( $name ), checked( (int) $value, 1, false ), esc_html__( 'Ja', 'valkenisse' ) );
			break;
		case 'radio':
			echo '<fieldset class="vk-radio">';
			foreach ( $field['options'] as $opt => $label ) {
				printf(
					'<label><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label>',
					esc_attr( $name ),
					esc_attr( $opt ),
					checked( (string) $value, $opt, false ),
					esc_html( $label )
				);
			}
			echo '</fieldset>';
			break;
		case 'hours':
			$value = wp_parse_args( (array) $value, array( 'open' => '', 'close' => '', 'closed' => 0 ) );
			printf(
				'<span class="vk-hours"><label>%1$s <input type="time" id="%2$s" name="%3$s[open]" value="%4$s" /></label> <label>%5$s <input type="time" name="%3$s[close]" value="%6$s" /></label> <label><input type="checkbox" name="%3$s[closed]" value="1" %7$s /> %8$s</label></span>',
				esc_html__( 'vanaf', 'valkenisse' ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value['open'] ),
				esc_html__( 'tot ±', 'valkenisse' ),
				esc_attr( $value['close'] ),
				checked( (int) $value['closed'], 1, false ),
				esc_html__( 'gesloten', 'valkenisse' )
			);
			break;
		case 'rows':
			$rows   = array_values( (array) $value );
			$groups = ! empty( $field['groups'] );
			echo '<table class="vk-rows widefat striped"><thead><tr>';
			if ( $groups ) {
				echo '<th>' . esc_html__( 'Groep (bv. Per week)', 'valkenisse' ) . '</th>';
			}
			echo '<th>' . esc_html__( 'Omschrijving', 'valkenisse' ) . '</th><th>' . esc_html__( 'Prijs', 'valkenisse' ) . '</th><th>' . esc_html__( 'Toelichting (optioneel)', 'valkenisse' ) . '</th></tr></thead><tbody>';
			$count = max( (int) $field['rows'], count( $rows ) + 2 );
			for ( $i = 0; $i < $count; $i++ ) {
				$row = wp_parse_args( $rows[ $i ] ?? array(), array( 'group' => '', 'label' => '', 'price' => '', 'note' => '' ) );
				echo '<tr>';
				if ( $groups ) {
					printf( '<td><input type="text" name="%1$s[%2$d][group]" value="%3$s" /></td>', esc_attr( $name ), (int) $i, esc_attr( $row['group'] ) );
				}
				printf(
					'<td><input type="text" name="%1$s[%2$d][label]" value="%3$s" class="regular-text" /></td><td><input type="text" name="%1$s[%2$d][price]" value="%4$s" placeholder="€ 0,00" /></td><td><input type="text" name="%1$s[%2$d][note]" value="%5$s" class="regular-text" /></td></tr>',
					esc_attr( $name ),
					(int) $i,
					esc_attr( $row['label'] ),
					esc_attr( $row['price'] ),
					esc_attr( $row['note'] )
				);
			}
			echo '</tbody></table>';
			echo '<p class="description">' . esc_html__( 'Maak de omschrijving leeg om een regel te verwijderen. Regels met dezelfde groep worden onder één kopje getoond.', 'valkenisse' ) . '</p>';
			break;
		default:
			$type = in_array( $field['type'], array( 'email', 'url', 'time', 'date' ), true ) ? $field['type'] : 'text';
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" />', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
	}

	if ( ! empty( $field['description'] ) ) {
		echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
	}
	echo '</td></tr>';
}

/**
 * Opslaan van een instellingenpagina.
 */
add_action( 'admin_post_vk_save_settings', static function () {
	$slug   = sanitize_key( $_POST['vk_page'] ?? '' );
	$schema = vk_settings_schema();
	if ( ! isset( $schema[ $slug ] ) || ! current_user_can( vk_capability() ) ) {
		wp_die( esc_html__( 'Geen toegang.', 'valkenisse' ) );
	}
	check_admin_referer( 'vk_save_' . $slug );

	$input  = wp_unslash( $_POST['vk'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- per veld ontsmet hieronder.
	$values = array();
	foreach ( $schema[ $slug ]['sections'] as $section ) {
		foreach ( $section['fields'] as $field ) {
			$values[ $field['key'] ] = vk_sanitize_field( $field, $input[ $field['key'] ] ?? '' );
		}
	}
	if ( 'vandaag' === $slug ) {
		$values['today_status_date'] = vk_now()->format( 'Y-m-d' );
	}
	vk_update( $values );

	wp_safe_redirect( add_query_arg( 'vk-saved', 1, admin_url( 'admin.php?page=valkenisse-' . $slug ) ) );
	exit;
} );

/**
 * Dashboard-widget: status van vandaag met één klik aanpassen.
 */
add_action( 'wp_dashboard_setup', static function () {
	if ( ! current_user_can( vk_capability() ) ) {
		return;
	}
	wp_add_dashboard_widget( 'vk_today_widget', __( '☀️ Vandaag op het strand', 'valkenisse' ), 'vk_dashboard_widget' );

	// Widget bovenaan zetten.
	global $wp_meta_boxes;
	$widget = $wp_meta_boxes['dashboard']['normal']['core']['vk_today_widget'] ?? null;
	if ( $widget ) {
		unset( $wp_meta_boxes['dashboard']['normal']['core']['vk_today_widget'] );
		$wp_meta_boxes['dashboard']['normal']['high']['vk_today_widget'] = $widget; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	// Rust in het dashboard: overbodige standaardwidgets weg.
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
} );

function vk_dashboard_widget(): void {
	$today  = vk_today();
	$field  = vk_settings_fields()['today_status'];
	$status = 'auto' === $today['status'] ? 'auto' : (string) vk_get( 'today_status' );

	echo '<p class="vk-widget__now"><strong>' . esc_html__( 'Nu op de website:', 'valkenisse' ) . '</strong> ' . esc_html( $today['icon'] . ' ' . ( $today['headline'] ?: '—' ) ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="vk_quick_status" />';
	wp_nonce_field( 'vk_quick_status' );
	echo '<fieldset class="vk-radio">';
	foreach ( $field['options'] as $opt => $label ) {
		if ( 'custom' === $opt ) {
			continue;
		}
		printf( '<label><input type="radio" name="status" value="%1$s" %2$s /> %3$s</label>', esc_attr( $opt ), checked( $status, $opt, false ), esc_html( $label ) );
	}
	echo '</fieldset>';
	printf(
		'<p class="vk-hours"><label>%1$s <input type="time" name="open" value="%2$s" /></label> <label>%3$s <input type="time" name="close" value="%4$s" /></label></p>',
		esc_html__( 'Vanaf', 'valkenisse' ),
		esc_attr( (string) vk_get( 'today_open' ) ),
		esc_html__( 'Tot ±', 'valkenisse' ),
		esc_attr( (string) vk_get( 'today_close' ) )
	);
	submit_button( __( 'Zet op de website', 'valkenisse' ), 'primary', 'submit', false );
	echo ' <a href="' . esc_url( admin_url( 'admin.php?page=valkenisse-vandaag' ) ) . '">' . esc_html__( 'Meer opties', 'valkenisse' ) . '</a>';
	echo '</form>';
}

add_action( 'admin_post_vk_quick_status', static function () {
	if ( ! current_user_can( vk_capability() ) ) {
		wp_die( esc_html__( 'Geen toegang.', 'valkenisse' ) );
	}
	check_admin_referer( 'vk_quick_status' );
	$fields = vk_settings_fields();
	vk_update(
		array(
			'today_status'      => vk_sanitize_field( $fields['today_status'], wp_unslash( $_POST['status'] ?? 'auto' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'today_open'        => vk_sanitize_field( $fields['today_open'], wp_unslash( $_POST['open'] ?? '' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'today_close'       => vk_sanitize_field( $fields['today_close'], wp_unslash( $_POST['close'] ?? '' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'today_status_date' => vk_now()->format( 'Y-m-d' ),
		)
	);
	wp_safe_redirect( admin_url( 'index.php?vk-saved=1' ) );
	exit;
} );

/**
 * "Vandaag"-status ook in de admin-balk bovenaan, zodat altijd zichtbaar is wat bezoekers zien.
 */
add_action( 'admin_bar_menu', static function ( WP_Admin_Bar $bar ) {
	if ( ! current_user_can( vk_capability() ) ) {
		return;
	}
	$today = vk_today();
	$bar->add_node(
		array(
			'id'    => 'vk-today',
			'title' => esc_html( $today['icon'] . ' ' . ( $today['open'] ? __( 'Vandaag open', 'valkenisse' ) : __( 'Vandaag', 'valkenisse' ) ) ),
			'href'  => admin_url( 'admin.php?page=valkenisse-vandaag' ),
		)
	);
}, 80 );

/**
 * Na opslaan: paginacache legen (werkt met de meest gebruikte cacheplugins).
 */
add_action( 'valkenisse_settings_updated', 'vk_flush_page_cache' );
function vk_flush_page_cache(): void {
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache(); // WP Super Cache.
	}
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain(); // WP Rocket.
	}
	if ( class_exists( 'LiteSpeed\Purge' ) ) {
		do_action( 'litespeed_purge_all' );
	}
	do_action( 'valkenisse_flush_cache' );
}
