<?php
/**
 * Plugin Name:       Strandpaviljoen Valkenisse – Beheer
 * Description:       Centrale gegevens, openingstijden, tarieven, menukaart, vacatures, foto's, strandhuisje-aanvragen en SEO voor Strandpaviljoen Valkenisse. Werkt samen met het thema "Valkenisse".
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Familie Herwegh
 * Text Domain:       valkenisse
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'VK_VERSION', '1.0.0' );
define( 'VK_FILE', __FILE__ );
define( 'VK_DIR', plugin_dir_path( __FILE__ ) );
define( 'VK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Tekst die op de site verschijnt zolang de familie iets nog niet heeft aangeleverd.
 * Wordt op de voorkant gemarkeerd zodat het vóór livegang opvalt.
 */
define( 'VK_TODO', '[DOOR FAMILIE HERWEGH AAN TE LEVEREN]' );

require_once VK_DIR . 'includes/helpers.php';
require_once VK_DIR . 'includes/settings.php';
require_once VK_DIR . 'includes/admin.php';
require_once VK_DIR . 'includes/post-types.php';
require_once VK_DIR . 'includes/blocks.php';
require_once VK_DIR . 'includes/render.php';
require_once VK_DIR . 'includes/forms.php';
require_once VK_DIR . 'includes/seo.php';
require_once VK_DIR . 'includes/i18n.php';
require_once VK_DIR . 'includes/installer.php';

add_action( 'plugins_loaded', static function () {
	load_plugin_textdomain( 'valkenisse', false, dirname( plugin_basename( VK_FILE ) ) . '/languages' );
} );

register_activation_hook( VK_FILE, static function () {
	vk_register_post_types();
	vk_seed_terms();
	flush_rewrite_rules();
} );

register_deactivation_hook( VK_FILE, 'flush_rewrite_rules' );
