<?php
/**
 * Een rustig, herkenbaar dashboard voor de eigenaar.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

// Menuvolgorde: eerst de dagelijkse onderdelen.
add_filter( 'custom_menu_order', '__return_true' );
add_filter(
	'menu_order',
	static fn() => array(
		'index.php',
		'valkenisse',
		'edit.php?post_type=gerecht',
		'upload.php',
		'edit.php?post_type=studio',
		'edit.php?post_type=arrangement',
		'edit.php?post_type=page',
		'edit.php',
		'edit.php?post_type=aanvraag',
		'separator1',
	)
);

// "Media" heet "Foto's".
add_action(
	'admin_menu',
	static function () {
		global $menu;
		foreach ( $menu as $i => $item ) {
			if ( 'upload.php' === ( $item[2] ?? '' ) ) {
				$menu[ $i ][0] = "Foto's"; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}
	},
	99
);

// Standaard WordPress-widgets weg, eigen startscherm erin.
add_action(
	'wp_dashboard_setup',
	static function () {
		foreach ( array( 'dashboard_primary', 'dashboard_quick_press', 'dashboard_right_now', 'dashboard_activity', 'dashboard_site_health', 'dashboard_php_nag', 'dashboard_browser_nag' ) as $id ) {
			remove_meta_box( $id, 'dashboard', 'normal' );
			remove_meta_box( $id, 'dashboard', 'side' );
		}
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
		wp_add_dashboard_widget( 'valk_start', 'Restaurant Valkenisse – wat wilt u aanpassen?', 'valkenisse_render_dashboard' );
	}
);

function valkenisse_render_dashboard(): void {
	$new_requests = (int) ( new WP_Query(
		array(
			'post_type'      => 'aanvraag',
			'post_status'    => 'private',
			'date_query'     => array( array( 'after' => '7 days ago' ) ),
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	) )->found_posts;
	$today  = Valkenisse_Hours::today_sentence( Valkenisse_Hours::day( Valkenisse_Hours::now() ) );
	$tiles  = array(
		array( 'clock', 'Openingstijden', 'Tijden per seizoen, feestdagen en afwijkende dagen.', admin_url( 'admin.php?page=valkenisse' ) ),
		array( 'food', 'Menukaart', 'Gerechten en prijzen toevoegen of wijzigen.', admin_url( 'edit.php?post_type=gerecht' ) ),
		array( 'format-gallery', "Foto's", "Foto's uploaden en in de galerij zetten.", admin_url( 'upload.php' ) ),
		array( 'admin-home', "Studio's", "Teksten, foto's en faciliteiten van de studio's.", admin_url( 'edit.php?post_type=studio' ) ),
		array( 'groups', 'Feesten & buffetten', 'Buffetten, arrangementen en prijzen.', admin_url( 'edit.php?post_type=arrangement' ) ),
		array( 'admin-page', "Pagina's & teksten", 'Teksten en foto\'s op de pagina\'s aanpassen.', admin_url( 'edit.php?post_type=page' ) ),
		array( 'megaphone', 'Mededeling', 'Korte melding bovenaan de website plaatsen.', admin_url( 'admin.php?page=valkenisse&tab=mededeling' ) ),
		array( 'edit', 'Nieuws', 'Een nieuwsbericht schrijven.', admin_url( 'post-new.php' ) ),
		array( 'email-alt', 'Aanvragen', $new_requests ? $new_requests . ' nieuw in de afgelopen 7 dagen' : 'Reserveringen en aanvragen via de website.', admin_url( 'edit.php?post_type=aanvraag' ) ),
	);
	echo '<p class="valk-dash-today"><span class="dashicons dashicons-clock"></span> Website toont nu: <strong>' . esc_html( $today ) . '</strong></p><div class="valk-tiles">';
	foreach ( $tiles as [ $icon, $title, $text, $url ] ) {
		printf( '<a class="valk-tile" href="%s"><span class="dashicons dashicons-%s"></span><strong>%s</strong><span>%s</span></a>', esc_url( $url ), esc_attr( $icon ), esc_html( $title ), esc_html( $text ) );
	}
	echo '</div><p style="margin-top:1em"><a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">Website bekijken →</a></p>';
}

// Eén kolom op het dashboard, zodat de tegels ruim staan.
add_filter( 'screen_layout_columns', static fn( $cols ) => array_merge( $cols, array( 'dashboard' => 1 ) ) );
add_filter( 'get_user_option_screen_layout_dashboard', static fn() => 1 );

add_action(
	'admin_head',
	static function () {
		?>
		<style>
			.valk-tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-top:12px}
			.valk-tile{display:flex;flex-direction:column;gap:4px;padding:16px;border:1px solid #dcdcde;border-radius:6px;text-decoration:none;color:#1d2327;background:#fbfaf7}
			.valk-tile:hover,.valk-tile:focus{border-color:#2f4a3d;background:#fff}
			.valk-tile .dashicons{color:#2f4a3d;font-size:26px;width:26px;height:26px;margin-bottom:4px}
			.valk-tile strong{font-size:15px}
			.valk-tile span:last-child{color:#50575e}
			.valk-dash-today{font-size:14px;background:#eef3ef;padding:10px 12px;border-radius:6px}
			.valk-admin .valk-lead{font-size:14px;max-width:760px}
			.valk-admin .valk-today{background:#eef3ef;padding:10px 12px;border-radius:6px;margin:16px 0;font-size:14px;max-width:760px}
			.valk-season{background:#fff;border:1px solid #dcdcde;border-radius:6px;margin:12px 0;padding:0 16px;max-width:760px}
			.valk-season>summary{cursor:pointer;padding:14px 0;font-weight:600;font-size:15px}
			.valk-season[open]>summary{border-bottom:1px solid #f0f0f1;margin-bottom:12px}
			.valk-season-grid{display:flex;flex-wrap:wrap;gap:12px 20px;margin:12px 0;align-items:flex-end}
			.valk-season-grid .valk-wide{flex:1 1 100%}
			.valk-days{max-width:760px;margin:8px 0 12px}
			.valk-days th,.valk-days td{padding:6px 10px;vertical-align:middle}
			.valk-editor-hint{background:#fff8e5;border-left:4px solid #dba617;padding:8px 12px}
		</style>
		<?php
	}
);

// Kortere voettekst.
add_filter( 'admin_footer_text', static fn() => 'Restaurant Valkenisse – beheer. Tip: begin altijd via Dashboard → "Wat wilt u aanpassen?"' );
