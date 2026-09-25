<?php
/**
 * Plugin Name:       Van Keulen Core
 * Description:       Centrale bedrijfsgegevens, stallingsaanvraag-wizard, veelgestelde vragen, lokale SEO (schema.org, Open Graph, sitemap) en veiligheid voor Van Keulen Caravanstalling.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.0
 * Author:            Van Keulen Caravanstalling
 * License:           GPL-2.0-or-later
 * Text Domain:       vankeulen-core
 *
 * @package VanKeulenCore
 */

defined( 'ABSPATH' ) || exit;

define( 'VK_CORE_VERSION', '1.0.0' );
define( 'VK_CORE_FILE', __FILE__ );
define( 'VK_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VK_CORE_URL', plugin_dir_url( __FILE__ ) );

/** Tekst die overal verschijnt waar de eigenaar nog informatie moet aanleveren. */
define( 'VK_PLACEHOLDER', '[DOOR VAN KEULEN AAN TE LEVEREN]' );

require_once VK_CORE_DIR . 'includes/helpers.php';
require_once VK_CORE_DIR . 'includes/settings.php';
require_once VK_CORE_DIR . 'includes/blocks.php';
require_once VK_CORE_DIR . 'includes/faq.php';
require_once VK_CORE_DIR . 'includes/aanvraag.php';
require_once VK_CORE_DIR . 'includes/seo.php';
require_once VK_CORE_DIR . 'includes/schema.php';
require_once VK_CORE_DIR . 'includes/performance.php';
require_once VK_CORE_DIR . 'includes/security.php';
require_once VK_CORE_DIR . 'includes/livegang-check.php';
require_once VK_CORE_DIR . 'includes/setup.php';

register_activation_hook(
	__FILE__,
	function () {
		if ( false === get_option( 'vk_settings' ) ) {
			add_option( 'vk_settings', vk_default_settings() );
		}
		vk_register_post_types();
		if ( ! wp_next_scheduled( 'vk_opschonen_aanvragen' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vk_opschonen_aanvragen' );
		}
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		wp_clear_scheduled_hook( 'vk_opschonen_aanvragen' );
	}
);
