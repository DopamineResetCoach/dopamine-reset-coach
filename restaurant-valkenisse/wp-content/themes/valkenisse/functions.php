<?php
/**
 * Thema Valkenisse.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

define( 'VALKENISSE_THEME_VERSION', '1.0.0' );

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( array( 'assets/css/theme.css' ) );
		remove_theme_support( 'core-block-patterns' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'valkenisse', get_theme_file_uri( 'assets/css/theme.css' ), array(), VALKENISSE_THEME_VERSION );
		wp_enqueue_script( 'valkenisse-header', get_theme_file_uri( 'assets/js/header.js' ), array(), VALKENISSE_THEME_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

// Belangrijkste lettertypen vooraf laden (kop in de hero + broodtekst).
add_action(
	'wp_head',
	static function () {
		foreach ( array( 'cormorant-garamond-latin-500-normal.woff2', 'manrope-latin-wght-normal.woff2' ) as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) ) );
		}
	},
	1
);

/**
 * Pagina's die beginnen met een grote foto (hero) krijgen een transparante header.
 */
add_filter(
	'body_class',
	static function ( array $classes ) {
		if ( is_singular() ) {
			$blocks = parse_blocks( (string) get_post_field( 'post_content', get_queried_object_id() ) );
			$first  = null;
			foreach ( $blocks as $block ) {
				if ( $block['blockName'] ) {
					$first = $block;
					break;
				}
			}
			if ( $first && 'core/cover' === $first['blockName'] && str_contains( (string) ( $first['attrs']['className'] ?? '' ), 'is-style-hero' ) ) {
				$classes[] = 'has-hero';
			}
		}
		return $classes;
	}
);

/* Blokstijlen die de eigenaar in de editor kan kiezen. */
add_action(
	'init',
	static function () {
		register_block_style( 'core/cover', array( 'name' => 'hero', 'label' => 'Hero (bovenaan pagina)' ) );
		register_block_style( 'core/paragraph', array( 'name' => 'eyebrow', 'label' => 'Klein label' ) );
		register_block_style( 'core/paragraph', array( 'name' => 'lead', 'label' => 'Intro (groot)' ) );
		register_block_style( 'core/image', array( 'name' => 'reveal', 'label' => 'Rustig invliegen' ) );
		register_block_style( 'core/cover', array( 'name' => 'parallax-soft', 'label' => 'Zachte parallax' ) );
		register_block_style( 'core/group', array( 'name' => 'tile-link', 'label' => 'Fototegel met link' ) );
		register_block_style( 'core/button', array( 'name' => 'light', 'label' => 'Licht (op foto)' ) );
		register_block_style( 'core/list', array( 'name' => 'checklist', 'label' => 'Vinkjes' ) );

		register_block_pattern_category( 'valkenisse-paginas', array( 'label' => "Valkenisse – complete pagina's" ) );
		register_block_pattern_category( 'valkenisse', array( 'label' => 'Valkenisse – secties' ) );
	}
);

/** URL van een afbeelding in het thema (gebruikt in patronen). */
function valkenisse_img( string $file ): string {
	return esc_url( get_theme_file_uri( 'assets/img/' . $file ) );
}

// Standaard deelafbeelding (Open Graph) als een pagina geen uitgelichte afbeelding heeft.
add_filter(
	'valkenisse_default_share_image',
	static function ( string $url ) {
		if ( $url ) {
			return $url;
		}
		$front = (int) get_option( 'page_on_front' );
		return $front && has_post_thumbnail( $front ) ? (string) get_the_post_thumbnail_url( $front, 'large' ) : '';
	}
);

/* -------------------------------------------------------------------------
 * Bouwstenen voor patronen (geven gewone blok-HTML terug die in de editor
 * volledig bewerkbaar is).
 * ---------------------------------------------------------------------- */

/** Knoppenrij. $buttons = [ [tekst, link, stijl], … ] met stijl '', 'light' of 'outline'. */
function valkenisse_buttons( array $buttons, string $class = '' ): string {
	$inner = array();
	foreach ( $buttons as [ $text, $url, $style ] ) {
		$cls     = $style ? 'is-style-' . $style : '';
		$attrs   = $cls ? ' ' . wp_json_encode( array( 'className' => $cls ) ) : '';
		$inner[] = '<!-- wp:button' . $attrs . ' -->' . "\n" . '<div class="wp-block-button' . ( $cls ? ' ' . $cls : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_attr( $url ) . '">' . esc_html( $text ) . '</a></div>' . "\n" . '<!-- /wp:button -->';
	}
	$attrs = $class ? ' ' . wp_json_encode( array( 'className' => $class ) ) : '';
	return '<!-- wp:buttons' . $attrs . ' -->' . "\n" . '<div class="wp-block-buttons' . ( $class ? ' ' . $class : '' ) . '">' . implode( "\n\n", $inner ) . '</div>' . "\n" . '<!-- /wp:buttons -->';
}

function valkenisse_p( string $html, string $class = '' ): string {
	$attrs = $class ? ' ' . wp_json_encode( array( 'className' => $class ) ) : '';
	return '<!-- wp:paragraph' . $attrs . ' -->' . "\n" . '<p' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . $html . '</p>' . "\n" . '<!-- /wp:paragraph -->';
}

function valkenisse_h( string $text, int $level = 2, string $size = '', string $class = '' ): string {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	if ( $size ) {
		$attrs['fontSize'] = $size;
	}
	$classes = trim( 'wp-block-heading ' . $class . ( $size ? ' has-' . $size . '-font-size' : '' ) );
	return '<!-- wp:heading' . ( $attrs ? ' ' . wp_json_encode( $attrs ) : '' ) . ' -->' . "\n" . '<h' . $level . ' class="' . esc_attr( $classes ) . '">' . $text . '</h' . $level . '>' . "\n" . '<!-- /wp:heading -->';
}

/** Afbeelding uit het thema (tijdelijke placeholder) met vaste verhouding. */
function valkenisse_image( string $file, string $alt, string $ratio = '', string $class = '', string $href = '' ): string {
	$attrs = array();
	if ( $ratio ) {
		$attrs['aspectRatio'] = $ratio;
		$attrs['scale']       = 'cover';
	}
	$attrs['sizeSlug'] = 'large';
	if ( $href ) {
		$attrs['linkDestination'] = 'custom';
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$style = $ratio ? ' style="aspect-ratio:' . esc_attr( $ratio ) . ';object-fit:cover"' : '';
	$img   = '<img src="' . valkenisse_img( $file ) . '" alt="' . esc_attr( $alt ) . '"' . $style . '/>';
	if ( $href ) {
		$img = '<a href="' . esc_attr( $href ) . '">' . $img . '</a>';
	}
	return '<!-- wp:image ' . wp_json_encode( $attrs ) . ' -->' . "\n" . '<figure class="wp-block-image size-large' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">' . $img . '</figure>' . "\n" . '<!-- /wp:image -->';
}

/** Hero bovenaan een pagina (fotoblok met titel). */
function valkenisse_hero( string $eyebrow, string $title, string $lead, string $image, int $height = 64, string $extra = '', string $class = 'is-style-hero' ): string {
	$attrs = array(
		'url'            => valkenisse_img( $image ),
		'dimRatio'       => 40,
		'overlayColor'   => 'green-deep',
		'minHeight'      => $height,
		'minHeightUnit'  => 'vh',
		'contentPosition' => 'bottom left',
		'tagName'        => 'section',
		'align'          => 'full',
		'className'      => $class,
		'layout'         => array( 'type' => 'constrained' ),
	);
	$inner  = valkenisse_p( esc_html( $eyebrow ), 'is-style-eyebrow' ) . "\n\n";
	$inner .= valkenisse_h( $title, 1, $height >= 85 ? 'display' : 'xx-large', 'hero__title' ) . "\n\n";
	$inner .= $lead ? valkenisse_p( $lead, 'is-style-lead' ) . "\n\n" : '';
	$inner .= $extra;
	return '<!-- wp:cover ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n"
		. '<section class="wp-block-cover alignfull has-custom-content-position is-position-bottom-left ' . esc_attr( $class ) . '" style="min-height:' . $height . 'vh"><img class="wp-block-cover__image-background" alt="" src="' . valkenisse_img( $image ) . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-green-deep-background-color has-background-dim-40 has-background-dim"></span><div class="wp-block-cover__inner-container">'
		. $inner
		. '</div></section>' . "\n" . '<!-- /wp:cover -->';
}

/** Sectie (groep) over de volle breedte met ruime witruimte. */
function valkenisse_section( string $class, string $inner, string $bg = '', string $anchor = '' ): string {
	$attrs = array( 'tagName' => 'section' );
	if ( $anchor ) {
		$attrs['anchor'] = $anchor;
	}
	$attrs['align']     = 'full';
	$attrs['className'] = 'vk-section ' . $class;
	if ( $bg ) {
		$attrs['backgroundColor'] = $bg;
	}
	$attrs['layout'] = array( 'type' => 'constrained' );
	$classes = 'wp-block-group alignfull vk-section ' . $class . ( $bg ? ' has-' . $bg . '-background-color has-background' : '' );
	return '<!-- wp:group ' . wp_json_encode( $attrs ) . ' -->' . "\n" . '<section' . ( $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . ' class="' . esc_attr( $classes ) . '">' . $inner . '</section>' . "\n" . '<!-- /wp:group -->';
}

/** Groep binnen een sectie. */
function valkenisse_group( string $inner, string $class = '', string $align = '', array $layout = array( 'type' => 'default' ) ): string {
	$attrs = array();
	if ( $align ) {
		$attrs['align'] = $align;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	if ( 'default' !== ( $layout['type'] ?? '' ) ) {
		$attrs['layout'] = $layout;
	}
	$classes = trim( 'wp-block-group' . ( $align ? ' align' . $align : '' ) . ' ' . $class );
	return '<!-- wp:group' . ( $attrs ? ' ' . wp_json_encode( $attrs ) : '' ) . ' -->' . "\n" . '<div class="' . esc_attr( $classes ) . '">' . $inner . '</div>' . "\n" . '<!-- /wp:group -->';
}

/** Kolommen: $cols = [ [breedte of '', inhoud], … ]. */
function valkenisse_columns( array $cols, string $class = '', string $valign = '' ): string {
	$attrs = array();
	if ( $valign ) {
		$attrs['verticalAlignment'] = $valign;
	}
	$attrs['align'] = 'wide';
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$html = array();
	foreach ( $cols as [ $width, $inner ] ) {
		$cattrs = $width ? ' ' . wp_json_encode( array( 'width' => $width ) ) : '';
		$html[] = '<!-- wp:column' . $cattrs . ' -->' . "\n" . '<div class="wp-block-column"' . ( $width ? ' style="flex-basis:' . esc_attr( $width ) . '"' : '' ) . '>' . $inner . '</div>' . "\n" . '<!-- /wp:column -->';
	}
	$classes = 'wp-block-columns alignwide' . ( $class ? ' ' . $class : '' ) . ( $valign ? ' are-vertically-aligned-' . $valign : '' );
	return '<!-- wp:columns ' . wp_json_encode( $attrs ) . ' -->' . "\n" . '<div class="' . esc_attr( $classes ) . '">' . implode( "\n\n", $html ) . '</div>' . "\n" . '<!-- /wp:columns -->';
}

function valkenisse_list( array $items, string $class = '' ): string {
	$li = array();
	foreach ( $items as $item ) {
		$li[] = '<!-- wp:list-item -->' . "\n" . '<li>' . $item . '</li>' . "\n" . '<!-- /wp:list-item -->';
	}
	$attrs = $class ? ' ' . wp_json_encode( array( 'className' => $class ) ) : '';
	return '<!-- wp:list' . $attrs . ' -->' . "\n" . '<ul class="wp-block-list' . ( $class ? ' ' . $class : '' ) . '">' . implode( "\n\n", $li ) . '</ul>' . "\n" . '<!-- /wp:list -->';
}

/** Dynamisch blok van de plugin. */
function valkenisse_block( string $name, array $attrs = array() ): string {
	return '<!-- wp:valkenisse/' . $name . ( $attrs ? ' ' . wp_json_encode( $attrs ) : '' ) . ' /-->';
}
