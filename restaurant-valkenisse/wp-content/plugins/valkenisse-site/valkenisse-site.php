<?php
/**
 * Plugin Name:       Restaurant Valkenisse – Website
 * Description:       Menukaart, openingstijden, studio's, feesten & buffetten, fotogalerij, aanvraagformulieren en SEO voor Restaurant Valkenisse. Werkt samen met het thema "Valkenisse".
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Restaurant Valkenisse
 * License:           GPL-2.0-or-later
 * Text Domain:       valkenisse
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

define( 'VALKENISSE_VERSION', '1.0.0' );
define( 'VALKENISSE_FILE', __FILE__ );
define( 'VALKENISSE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VALKENISSE_URL', plugin_dir_url( __FILE__ ) );

require_once VALKENISSE_DIR . 'includes/settings.php';
require_once VALKENISSE_DIR . 'includes/hours.php';
require_once VALKENISSE_DIR . 'includes/post-types.php';
require_once VALKENISSE_DIR . 'includes/menu-admin.php';
require_once VALKENISSE_DIR . 'includes/gallery.php';
require_once VALKENISSE_DIR . 'includes/blocks.php';
require_once VALKENISSE_DIR . 'includes/forms.php';
require_once VALKENISSE_DIR . 'includes/seo.php';
require_once VALKENISSE_DIR . 'includes/redirects.php';
require_once VALKENISSE_DIR . 'includes/performance.php';
require_once VALKENISSE_DIR . 'includes/admin.php';
require_once VALKENISSE_DIR . 'includes/setup.php';

register_activation_hook(
	__FILE__,
	static function () {
		valkenisse_register_post_types();
		valkenisse_register_gallery_taxonomy();
		flush_rewrite_rules();
		if ( ! wp_next_scheduled( 'valkenisse_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'valkenisse_daily_cleanup' );
		}
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		wp_clear_scheduled_hook( 'valkenisse_daily_cleanup' );
		flush_rewrite_rules();
	}
);
