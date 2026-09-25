<?php
/**
 * Thema Valkenisse.
 *
 * Opmaak en presentatie. Alle gegevens (openingstijden, prijzen, contact) komen uit de plugin
 * "Strandpaviljoen Valkenisse – Beheer" (valkenisse-core).
 */

defined( 'ABSPATH' ) || exit;

define( 'VALKENISSE_THEME_VERSION', '1.0.0' );

add_action( 'after_setup_theme', static function () {
	load_theme_textdomain( 'valkenisse', get_template_directory() . '/languages' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_editor_style( 'assets/css/theme.css' );
} );

/**
 * Stylesheet en een klein script (header bij scrollen, rustige animaties, mobiel menu).
 */
add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_style( 'valkenisse', get_theme_file_uri( 'assets/css/theme.css' ), array(), VALKENISSE_THEME_VERSION );
	wp_enqueue_script( 'valkenisse', get_theme_file_uri( 'assets/js/theme.js' ), array(), VALKENISSE_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
} );

/**
 * Het kopletter-font vooraf laden: voorkomt verspringende koppen.
 */
add_action( 'wp_head', static function () {
	// Vroeg markeren dat JavaScript werkt (voor de rustige in-beeld-animaties, zonder flikkering).
	echo "<script>document.documentElement.classList.add('vk-js')</script>\n";
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/fraunces-var.woff2' ) ) );
}, 1 );

/**
 * Transparante header over de grote foto op de homepage en pagina's met een hero.
 */
function valkenisse_has_overlay_header(): bool {
	if ( is_front_page() ) {
		return true;
	}
	return is_page() && 'page-hero' === get_page_template_slug( get_queried_object_id() );
}

add_filter( 'body_class', static function ( array $classes ) {
	if ( valkenisse_has_overlay_header() ) {
		$classes[] = 'has-overlay-header';
	}
	return $classes;
} );

/**
 * Snelheid: eerste grote foto krijgt voorrang, de rest laadt pas als je ernaartoe scrolt.
 */
add_filter( 'render_block_core/cover', static function ( string $html ) {
	static $count = 0;
	++$count;
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( $tags->next_tag( array( 'tag_name' => 'img', 'class_name' => 'wp-block-cover__image-background' ) ) ) {
		if ( 1 === $count ) {
			$tags->remove_attribute( 'loading' );
			$tags->set_attribute( 'fetchpriority', 'high' );
		} elseif ( ! $tags->get_attribute( 'loading' ) ) {
			$tags->set_attribute( 'loading', 'lazy' );
		}
		$tags->set_attribute( 'decoding', 'async' );
		if ( ! $tags->get_attribute( 'sizes' ) ) {
			$tags->set_attribute( 'sizes', '100vw' );
		}
	}
	return $tags->get_updated_html();
} );

/**
 * Nieuwe foto's automatisch als WebP opslaan (kleiner = sneller op het strand met mobiel internet).
 * WordPress valt terug op JPEG als de server geen WebP ondersteunt.
 */
add_filter( 'image_editor_output_format', static function ( array $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );
add_filter( 'wp_editor_set_quality', static fn( $quality, $mime ) => 'image/webp' === $mime ? 78 : $quality, 10, 2 );

/**
 * Overbodige scripts/stijlen weg.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

/**
 * Patrooncategorieën en blokstijlen.
 */
add_action( 'init', static function () {
	register_block_pattern_category( 'valkenisse-secties', array( 'label' => __( 'Valkenisse – secties', 'valkenisse' ) ) );
	register_block_pattern_category( 'valkenisse-paginas', array( 'label' => __( "Valkenisse – complete pagina's", 'valkenisse' ) ) );

	register_block_style( 'core/paragraph', array( 'name' => 'eyebrow', 'label' => __( 'Kleine kop (hoofdletters)', 'valkenisse' ) ) );
	register_block_style( 'core/image', array( 'name' => 'foto-kader', 'label' => __( 'Foto met wit kader (historisch)', 'valkenisse' ) ) );
	register_block_style( 'core/group', array( 'name' => 'kaart', 'label' => __( 'Kaart (wit vlak met schaduw)', 'valkenisse' ) ) );
	register_block_style( 'core/cover', array( 'name' => 'hero', 'label' => __( 'Hero (grote foto bovenaan)', 'valkenisse' ) ) );
	register_block_style( 'core/details', array( 'name' => 'faq', 'label' => __( 'Vraag & antwoord', 'valkenisse' ) ) );
} );

/**
 * URL van een afbeelding in het thema (voor de voorbeeldfoto's in de patronen).
 */
function valkenisse_image( string $file ): string {
	return esc_url( get_theme_file_uri( 'assets/images/' . $file ) );
}

/**
 * Foto voor een patroon: gebruikt een echte foto (.webp/.jpg) als die in assets/images staat,
 * anders de geïllustreerde placeholder (.svg). Bijvoorbeeld: zet hero-paviljoen.jpg in de map
 * en de homepage gebruikt die automatisch bij het aanmaken van de pagina's.
 */
function valkenisse_photo( string $name ): string {
	foreach ( array( 'webp', 'jpg', 'jpeg', 'avif' ) as $ext ) {
		if ( file_exists( get_theme_file_path( "assets/images/{$name}.{$ext}" ) ) ) {
			return valkenisse_image( "{$name}.{$ext}" );
		}
	}
	return valkenisse_image( "{$name}.svg" );
}

/**
 * Melding als de beheerplugin ontbreekt.
 */
add_action( 'admin_notices', static function () {
	if ( ! function_exists( 'vk_get' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Het thema Valkenisse heeft de plugin "Strandpaviljoen Valkenisse – Beheer" nodig. Activeer deze onder Plugins.', 'valkenisse' ) . '</p></div>';
	}
} );

/**
 * Hero voor subpagina's (gebruikt in de paginapatronen): grote foto, kruimelpad, kleine kop, H1 en intro.
 */
function valkenisse_page_hero( string $photo, string $alt, string $eyebrow, string $title, string $lead = '', array $focal = array() ): string {
	$src = valkenisse_photo( $photo );
	$alt = esc_attr( $alt );
	// Optioneel focuspunt (0–1), bv. array( 0.5, 0.3 ) om het bovenste deel van de foto in beeld te houden.
	$fp  = $focal ? array( 'x' => (float) $focal[0], 'y' => (float) $focal[1] ) : null;
	$pos = $fp ? round( $fp['x'] * 100 ) . '% ' . round( $fp['y'] * 100 ) . '%' : '';
	ob_start();
	?>
<!-- wp:cover {"url":"<?php echo $src; ?>","alt":"<?php echo $alt; ?>","dimRatio":0,<?php echo $fp ? '"focalPoint":' . wp_json_encode( $fp ) . ',' : ''; ?>"minHeight":68,"minHeightUnit":"vh","contentPosition":"bottom left","align":"full","className":"is-style-hero vk-hero vk-hero--page"} -->
<div class="wp-block-cover alignfull has-custom-content-position is-position-bottom-left is-style-hero vk-hero vk-hero--page" style="min-height:68vh"><img class="wp-block-cover__image-background" alt="<?php echo $alt; ?>" src="<?php echo $src; ?>"<?php echo $pos ? ' style="object-position:' . esc_attr( $pos ) . '"' : ''; ?> data-object-fit="cover"<?php echo $pos ? ' data-object-position="' . esc_attr( $pos ) . '"' : ''; ?>/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"vk-hero__content vk-reveal","layout":{"type":"constrained","contentSize":"820px","justifyContent":"left"}} -->
<div class="wp-block-group vk-hero__content vk-reveal"><!-- wp:valkenisse/breadcrumbs /-->

<!-- wp:paragraph {"className":"is-style-eyebrow vk-hero__eyebrow"} -->
<p class="is-style-eyebrow vk-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"vk-hero__title"} -->
<h1 class="wp-block-heading vk-hero__title"><?php echo esc_html( $title ); ?></h1>
<!-- /wp:heading -->
<?php if ( $lead ) : ?>

<!-- wp:paragraph {"className":"vk-hero__text"} -->
<p class="vk-hero__text"><?php echo esc_html( $lead ); ?></p>
<!-- /wp:paragraph -->
<?php endif; ?></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->
	<?php
	return (string) ob_get_clean();
}
