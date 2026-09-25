<?php
/**
 * Dynamische blokken. De inhoud komt uit de centrale gegevens (openingstijden, menukaart,
 * studio's, …), zodat de eigenaar iets één keer wijzigt en het overal klopt.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'valkenisse_register_blocks' );

function valkenisse_register_blocks(): void {
	wp_register_style( 'valkenisse-blocks', VALKENISSE_URL . 'assets/blocks.css', array(), VALKENISSE_VERSION );
	wp_register_script(
		'valkenisse-blocks-editor',
		VALKENISSE_URL . 'assets/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data' ),
		VALKENISSE_VERSION,
		true
	);
	wp_register_script( 'valkenisse-hours', VALKENISSE_URL . 'assets/hours.js', array(), VALKENISSE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'valkenisse-gallery', VALKENISSE_URL . 'assets/gallery.js', array(), VALKENISSE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	if ( function_exists( 'wp_register_script_module' ) ) {
		wp_register_script_module( 'valkenisse-pdf-viewer', VALKENISSE_URL . 'assets/pdf-viewer.js', array(), VALKENISSE_VERSION );
	}
	wp_register_script( 'valkenisse-forms', VALKENISSE_URL . 'assets/forms.js', array(), VALKENISSE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	$common = array(
		'api_version'          => 3,
		'category'             => 'valkenisse',
		'editor_script_handles' => array( 'valkenisse-blocks-editor' ),
		'style_handles'        => array( 'valkenisse-blocks' ),
		'supports'             => array( 'html' => false, 'align' => array( 'wide', 'full' ), 'anchor' => true ),
	);

	$blocks = array(
		'openingstijden' => array(
			'title'       => 'Openingstijden',
			'description' => 'Toont automatisch de actuele openingstijden. Aanpassen via menu "Openingstijden".',
			'icon'        => 'clock',
			'attributes'  => array(
				'weergave' => array( 'type' => 'string', 'default' => 'vandaag' ),
				'toonLink' => array( 'type' => 'boolean', 'default' => true ),
			),
			'render_callback' => 'valkenisse_render_hours_block',
		),
		'contact'        => array(
			'title'       => 'Contactgegevens',
			'description' => 'Adres, telefoon en e-mail uit "Openingstijden → Contact & reserveren".',
			'icon'        => 'location',
			'attributes'  => array(
				'weergave' => array( 'type' => 'string', 'default' => 'volledig' ),
			),
			'render_callback' => 'valkenisse_render_contact_block',
		),
		'menukaart'      => array(
			'title'       => 'Menukaart',
			'description' => 'Toont de gerechten uit "Menukaart". Prijzen en gerechten past u daar aan.',
			'icon'        => 'food',
			'attributes'  => array(
				'categorieen' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'string' ) ),
				'navigatie'   => array( 'type' => 'boolean', 'default' => true ),
				'fotos'       => array( 'type' => 'boolean', 'default' => true ),
			),
			'render_callback' => 'valkenisse_render_menu_block',
		),
		'menukaart-pdf'  => array(
			'title'       => 'Menukaart (PDF)',
			'description' => 'Toont de menukaart-PDF als pagina\'s op de website, met downloadknop. Nieuwe kaart? Kies hier een nieuwe PDF.',
			'icon'        => 'media-document',
			'attributes'  => array(
				'pdfId'      => array( 'type' => 'number', 'default' => 0 ),
				'tekstversie' => array( 'type' => 'boolean', 'default' => true ),
			),
			'render_callback' => 'valkenisse_render_menu_pdf_block',
		),
		'studios'        => array(
			'title'       => "Studio's",
			'description' => "Toont de studio's uit het menu \"Studio's\".",
			'icon'        => 'admin-home',
			'attributes'  => array(),
			'render_callback' => 'valkenisse_render_studios_block',
		),
		'buffetten'      => array(
			'title'       => 'Buffetten & arrangementen',
			'description' => 'Toont de buffetten uit het menu "Feesten".',
			'icon'        => 'groups',
			'attributes'  => array(
				'titel' => array( 'type' => 'string', 'default' => 'Buffetten' ),
				'intro' => array( 'type' => 'string', 'default' => '' ),
			),
			'render_callback' => 'valkenisse_render_buffets_block',
		),
		'galerij'        => array(
			'title'       => 'Fotogalerij',
			'description' => 'Foto\'s uit de mediabibliotheek met een fotocategorie. Klik op een foto voor een grote weergave.',
			'icon'        => 'format-gallery',
			'attributes'  => array(
				'categorieen' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'string' ) ),
				'filter'      => array( 'type' => 'boolean', 'default' => true ),
				'maximum'     => array( 'type' => 'number', 'default' => 60 ),
			),
			'render_callback' => 'valkenisse_render_gallery_block',
		),
		'formulier'      => array(
			'title'       => 'Aanvraagformulier',
			'description' => 'Reservering, studio-aanvraag, feestaanvraag of contact. Inzendingen komen per e-mail binnen en onder "Aanvragen".',
			'icon'        => 'feedback',
			'attributes'  => array(
				'soort' => array( 'type' => 'string', 'default' => 'contact' ),
			),
			'render_callback' => 'valkenisse_render_form_block',
		),
		'actiebalk'      => array(
			'title'       => 'Mobiele actiebalk',
			'description' => 'Vaste balk onderin op mobiel: Bellen, Route, Menukaart, Reserveren.',
			'icon'        => 'smartphone',
			'attributes'  => array(),
			'render_callback' => 'valkenisse_render_actionbar_block',
			'supports'    => array( 'html' => false ),
		),
		'mededeling'     => array(
			'title'       => 'Mededeling',
			'description' => 'Toont de mededeling uit "Openingstijden → Mededeling" (alleen als die aan staat).',
			'icon'        => 'megaphone',
			'attributes'  => array(),
			'render_callback' => 'valkenisse_render_notice_block',
			'supports'    => array( 'html' => false ),
		),
	);

	foreach ( $blocks as $name => $args ) {
		register_block_type( 'valkenisse/' . $name, array_replace_recursive( $common, $args ) );
	}
}

add_filter(
	'block_categories_all',
	static function ( array $cats ) {
		array_unshift( $cats, array( 'slug' => 'valkenisse', 'title' => 'Restaurant Valkenisse', 'icon' => null ) );
		return $cats;
	}
);

// Gegevens voor de editor (keuzelijsten).
add_action(
	'enqueue_block_editor_assets',
	static function () {
		$terms = static function ( string $tax ): array {
			$list = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
			return is_wp_error( $list ) ? array() : array_map( static fn( $t ) => array( 'value' => $t->slug, 'label' => $t->name ), $list );
		};
		wp_add_inline_script(
			'valkenisse-blocks-editor',
			'window.valkenisseData = ' . wp_json_encode(
				array(
					'menu'    => $terms( 'menu_categorie' ),
					'fotos'   => $terms( 'fotocategorie' ),
					'admin'   => admin_url( 'admin.php?page=valkenisse' ),
				)
			) . ';',
			'before'
		);
	}
);

/* -------------------------------------------------------------------------
 * Hulpfuncties
 * ---------------------------------------------------------------------- */

function valkenisse_wrapper( string $class, string $inner, string $tag = 'div', array $extra = array() ): string {
	$attrs = get_block_wrapper_attributes( array_merge( array( 'class' => $class ), $extra ) );
	return sprintf( '<%1$s %2$s>%3$s</%1$s>', $tag, $attrs, $inner );
}

function valkenisse_icon( string $name ): string {
	$paths = array(
		'phone'  => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z"/>',
		'route'  => '<path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>',
		'menu'   => '<path d="M7 2v9a3 3 0 0 0 2 2.8V22h2v-8.2A3 3 0 0 0 13 11V2h-1.5v7h-1V2h-1.5v7h-1V2zm10 0c-1.7 0-3 2.2-3 5v6h2v9h2V2z"/>',
		'agenda' => '<path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2zm-2 8h14v10H5zm2 2v2h2v-2zm4 0v2h2v-2zm4 0v2h2v-2z"/>',
		'mail'   => '<path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 7.2L4.4 7H4v.8l8 5.5 8-5.5V7h-.4z"/>',
		'clock'  => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10.4 3.5 2.1-.8 1.3L11 13V6.5h2z"/>',
	);
	return '<svg class="valk-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="currentColor">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

function valkenisse_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/* Navigatieblokken zonder eigen menu gebruiken altijd het Hoofdmenu. */
add_filter(
	'render_block_data',
	static function ( array $block ) {
		if ( 'core/navigation' === $block['blockName'] && empty( $block['attrs']['ref'] ) && empty( $block['innerBlocks'] ) ) {
			$nav_id = (int) get_option( 'valkenisse_nav_id' );
			if ( $nav_id && 'publish' === get_post_status( $nav_id ) ) {
				$block['attrs']['ref'] = $nav_id;
			}
		}
		return $block;
	}
);

/* -------------------------------------------------------------------------
 * Speciale links: "#reserveren", "#bellen", "#route", "#mailen", "#boeken"
 * werken in elke knop of link en volgen de centrale gegevens.
 * ---------------------------------------------------------------------- */

function valkenisse_token_urls(): array {
	$booking = (string) valkenisse_get( 'contact.booking_url' );
	return array(
		'#reserveren' => valkenisse_reserve_url(),
		'#bellen'     => valkenisse_phone_href(),
		'#route'      => valkenisse_route_url(),
		'#mailen'     => 'mailto:' . valkenisse_get( 'contact.email' ),
		'#boeken'     => $booking ? $booking : valkenisse_page_url( 'overnachten' ) . '#aanvraag',
	);
}

add_filter(
	'render_block',
	static function ( string $html ) {
		if ( ! str_contains( $html, 'href="#' ) ) {
			return $html;
		}
		$tokens = valkenisse_token_urls();
		$p      = new WP_HTML_Tag_Processor( $html );
		while ( $p->next_tag( 'a' ) ) {
			$href = (string) $p->get_attribute( 'href' );
			if ( isset( $tokens[ $href ] ) && $tokens[ $href ] ) {
				$p->set_attribute( 'href', $tokens[ $href ] );
				if ( '#route' === $href || ( '#boeken' === $href && valkenisse_get( 'contact.booking_url' ) ) || ( '#reserveren' === $href && 'extern' === valkenisse_get( 'contact.reserveer_modus' ) ) ) {
					$p->set_attribute( 'target', '_blank' );
					$p->set_attribute( 'rel', 'noopener' );
				}
			}
		}
		return $p->get_updated_html();
	}
);

/* -------------------------------------------------------------------------
 * Openingstijden
 * ---------------------------------------------------------------------- */

function valkenisse_render_hours_block( array $attrs ): string {
	$view = $attrs['weergave'] ?? 'vandaag';
	$now  = Valkenisse_Hours::now();
	$day  = Valkenisse_Hours::day( $now );
	$link = ! empty( $attrs['toonLink'] ) ? '<a class="valk-hours__link" href="' . esc_url( valkenisse_page_url( 'contact' ) ) . '#openingstijden">Bekijk alle openingstijden</a>' : '';

	if ( 'overzicht' === $view ) {
		return valkenisse_wrapper( 'valk-hours valk-hours--full', valkenisse_hours_today_html( $day, $now, false ) . valkenisse_hours_table_html( $now ) );
	}
	if ( 'seizoen' === $view ) {
		$season = Valkenisse_Hours::season_for( $now );
		$inner  = valkenisse_hours_today_html( $day, $now, false );
		if ( $season ) {
			$inner .= valkenisse_hours_rows_html( $season );
		}
		return valkenisse_wrapper( 'valk-hours valk-hours--compact', $inner . $link );
	}
	return valkenisse_wrapper( 'valk-hours valk-hours--today', valkenisse_hours_today_html( $day, $now, true ) . $link );
}

function valkenisse_hours_today_html( array $day, DateTimeImmutable $now, bool $with_next ): string {
	wp_enqueue_script( 'valkenisse-hours' );
	$status  = $day['status'];
	$kitchen = Valkenisse_Hours::kitchen_sentence( $day );
	$extra   = '';
	if ( $with_next && 'open' !== $status ) {
		$next = Valkenisse_Hours::next_open( $now );
		if ( $next ) {
			$extra = 'Weer open ' . wp_date( 'l j F', $next['date']->getTimestamp() ) . ' ' . Valkenisse_Hours::format_range( $next['day']['open'], $next['day']['sluit'] );
		}
	}
	return sprintf(
		'<div class="valk-today" data-status="%1$s" data-upcoming="%2$s"><span class="valk-today__dot" aria-hidden="true"></span><div><p class="valk-today__text">%3$s</p><p class="valk-today__kitchen">%4$s</p>%5$s</div></div>',
		esc_attr( $status ),
		esc_attr( wp_json_encode( Valkenisse_Hours::upcoming() ) ),
		esc_html( Valkenisse_Hours::today_sentence( $day ) ),
		esc_html( $kitchen ),
		$extra ? '<p class="valk-today__next">' . esc_html( $extra ) . '</p>' : ''
	);
}

function valkenisse_hours_rows_html( array $season ): string {
	$rows = '';
	foreach ( Valkenisse_Hours::grouped_days( $season ) as $g ) {
		$rows .= sprintf( '<tr class="%s"><th scope="row">%s</th><td>%s</td></tr>', $g['dicht'] ? 'is-closed' : '', esc_html( $g['dagen'] ), esc_html( $g['tijden'] ) );
	}
	if ( $season['keuken_van'] || $season['keuken_tot'] ) {
		$rows .= '<tr class="is-kitchen"><th scope="row">Keuken</th><td>' . esc_html( Valkenisse_Hours::format_range( $season['keuken_van'], $season['keuken_tot'] ) ) . '</td></tr>';
	}
	return '<table class="valk-hours__table"><tbody>' . $rows . '</tbody></table>';
}

function valkenisse_hours_table_html( DateTimeImmutable $now ): string {
	$current = Valkenisse_Hours::season_for( $now );
	$html    = '<div class="valk-hours__seasons">';
	foreach ( valkenisse_get( 'seizoenen', array() ) as $season ) {
		$is_now = $current && $current['label'] === $season['label'] && $current['van'] === $season['van'];
		$html  .= sprintf(
			'<section class="valk-season%s"><h3 class="valk-season__title">%s%s</h3><p class="valk-season__dates">%s</p>%s%s</section>',
			$is_now ? ' is-current' : '',
			esc_html( $season['label'] ),
			$is_now ? ' <span class="valk-badge">Nu</span>' : '',
			esc_html( Valkenisse_Hours::season_dates_label( $season ) ),
			valkenisse_hours_rows_html( $season ),
			$season['opmerking'] ? '<p class="valk-season__note">' . esc_html( $season['opmerking'] ) . '</p>' : ''
		);
	}
	$html .= '</div>';
	if ( valkenisse_get( 'buiten_seizoen' ) ) {
		$html .= '<p class="valk-hours__off">' . esc_html( valkenisse_get( 'buiten_seizoen' ) ) . '</p>';
	}
	$upcoming = array_filter( valkenisse_get( 'uitzonderingen', array() ), static fn( $e ) => $e['datum'] >= $now->format( 'Y-m-d' ) );
	if ( $upcoming ) {
		$html .= '<h3 class="valk-season__title">Afwijkende openingstijden</h3><ul class="valk-hours__exceptions">';
		foreach ( $upcoming as $e ) {
			$closed = $e['dicht'] || ( ! $e['open'] && ! $e['sluit'] );
			$html  .= sprintf(
				'<li><strong>%s</strong> %s%s</li>',
				esc_html( wp_date( 'l j F Y', strtotime( $e['datum'] . ' 12:00' ) ) ),
				esc_html( $closed ? 'gesloten' : Valkenisse_Hours::format_range( $e['open'], $e['sluit'] ) ),
				$e['opmerking'] ? ' – ' . esc_html( $e['opmerking'] ) : ''
			);
		}
		$html .= '</ul>';
	}
	return $html;
}

/* -------------------------------------------------------------------------
 * Contact
 * ---------------------------------------------------------------------- */

function valkenisse_render_contact_block( array $attrs ): string {
	$c     = valkenisse_get( 'contact' );
	$view  = $attrs['weergave'] ?? 'volledig';
	$tel   = valkenisse_phone_href();
	$btns  = '<div class="valk-contact__buttons">'
		. ( $tel ? '<a class="valk-btn valk-btn--primary" href="' . esc_url( $tel ) . '">' . valkenisse_icon( 'phone' ) . 'Bel direct</a>' : '' )
		. '<a class="valk-btn" href="' . esc_url( valkenisse_route_url() ) . '" target="_blank" rel="noopener">' . valkenisse_icon( 'route' ) . 'Route plannen</a>'
		. '<a class="valk-btn" href="' . esc_url( valkenisse_reserve_url() ) . '">' . valkenisse_icon( 'agenda' ) . 'Reserveer een tafel</a>'
		. '</div>';

	if ( 'knoppen' === $view ) {
		return valkenisse_wrapper( 'valk-contact valk-contact--buttons', $btns );
	}

	$address = sprintf(
		'<address class="valk-contact__address"><strong>%s</strong><br>%s<br>%s</address>',
		esc_html( $c['naam'] ),
		esc_html( $c['straat'] ),
		esc_html( trim( $c['postcode'] . ' ' . $c['plaats'] ) )
	);
	$lines = '<ul class="valk-contact__lines">'
		. ( $tel ? '<li>' . valkenisse_icon( 'phone' ) . '<a href="' . esc_url( $tel ) . '">' . esc_html( $c['telefoon'] ) . '</a></li>' : '' )
		. ( $c['email'] ? '<li>' . valkenisse_icon( 'mail' ) . '<a href="' . esc_url( 'mailto:' . $c['email'] ) . '">' . esc_html( $c['email'] ) . '</a></li>' : '' )
		. '<li>' . valkenisse_icon( 'route' ) . '<a href="' . esc_url( valkenisse_route_url() ) . '" target="_blank" rel="noopener">Route plannen</a></li>'
		. '</ul>';
	$social = '';
	foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram' ) as $key => $label ) {
		if ( $c[ $key ] ) {
			$social .= '<a href="' . esc_url( $c[ $key ] ) . '" rel="noopener" target="_blank">' . esc_html( $label ) . '</a>';
		}
	}
	$social = $social ? '<p class="valk-contact__social">' . $social . '</p>' : '';

	if ( 'adres' === $view ) {
		return valkenisse_wrapper( 'valk-contact valk-contact--compact', $address . $lines . $social );
	}
	return valkenisse_wrapper( 'valk-contact', $address . $lines . $social . $btns );
}

/* -------------------------------------------------------------------------
 * Menukaart
 * ---------------------------------------------------------------------- */

function valkenisse_dishes( int $term_id ): array {
	return get_posts(
		array(
			'post_type'      => 'gerecht',
			'posts_per_page' => 300,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array( array( 'taxonomy' => 'menu_categorie', 'terms' => $term_id, 'include_children' => false ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'  => true,
		)
	);
}

function valkenisse_render_menu_block( array $attrs ): string {
	$only   = array_filter( (array) ( $attrs['categorieen'] ?? array() ) );
	$terms  = valkenisse_menu_terms( 0, $only );
	$editor = current_user_can( 'edit_posts' );
	$photos = ! empty( $attrs['fotos'] );
	$nav    = '';
	$body   = '';

	foreach ( $terms as $term ) {
		$section = valkenisse_menu_section_html( $term, 2, $photos, $editor );
		if ( '' === $section ) {
			continue;
		}
		$nav  .= '<li><a href="#' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</a></li>';
		$body .= $section;
	}

	if ( '' === $body ) {
		return $editor
			? valkenisse_wrapper( 'valk-menu valk-menu--empty', '<p class="valk-editor-hint">De menukaart is nog leeg. Voeg gerechten toe via <strong>Menukaart → Nieuw toevoegen</strong> of importeer ze via <strong>Menukaart → Importeren (CSV)</strong>.</p>' )
			: '';
	}
	$nav = ( ! empty( $attrs['navigatie'] ) && ! $only ) ? '<nav class="valk-menu__nav" aria-label="Categorieën menukaart"><ul>' . $nav . '</ul></nav>' : '';
	return valkenisse_wrapper( 'valk-menu', $nav . '<div class="valk-menu__body">' . $body . '</div>' );
}

function valkenisse_menu_section_html( WP_Term $term, int $level, bool $photos, bool $editor ): string {
	$items    = '';
	foreach ( valkenisse_dishes( $term->term_id ) as $dish ) {
		$items .= valkenisse_dish_html( $dish, $photos );
	}
	$children = '';
	foreach ( valkenisse_menu_terms( $term->term_id ) as $child ) {
		$children .= valkenisse_menu_section_html( $child, min( $level + 1, 4 ), $photos, $editor );
	}
	if ( '' === $items && '' === $children ) {
		if ( ! $editor ) {
			return '';
		}
		$items = '<p class="valk-editor-hint">Nog geen gerechten in “' . esc_html( $term->name ) . '”. (Deze melding ziet alleen u als beheerder.)</p>';
	}
	$intro = $term->description ? '<p class="valk-menu__intro">' . esc_html( $term->description ) . '</p>' : '';
	return sprintf(
		'<section class="valk-menu__section valk-menu__section--l%1$d" id="%2$s"><h%1$d class="valk-menu__title">%3$s</h%1$d>%4$s%5$s%6$s</section>',
		$level,
		esc_attr( $term->slug ),
		esc_html( $term->name ),
		$intro . valkenisse_menu_photos_html( $term ),
		$items ? '<ul class="valk-menu__list">' . $items . '</ul>' : '',
		$children
	);
}

/** Sfeerfoto's bij een categorie (in te stellen bij Menukaart → Categorieën). */
function valkenisse_menu_photos_html( WP_Term $term ): string {
	$ids = valkenisse_term_photo_ids( $term->term_id );
	if ( ! $ids ) {
		return '';
	}
	$html = '';
	foreach ( $ids as $id ) {
		$html .= '<figure class="valk-menu__photo">' . wp_get_attachment_image(
			$id,
			'large',
			false,
			array(
				'loading' => 'lazy',
				'sizes'   => count( $ids ) > 1 ? '(min-width: 782px) 50vw, 100vw' : '(min-width: 1320px) 1320px, 100vw',
			)
		) . '</figure>';
	}
	return '<div class="valk-menu__photos valk-menu__photos--' . min( count( $ids ), 3 ) . '">' . $html . '</div>';
}

function valkenisse_dish_html( WP_Post $dish, bool $photos ): string {
	$price = valkenisse_format_price( (string) get_post_meta( $dish->ID, '_valk_prijs', true ) );
	$desc  = (string) get_post_meta( $dish->ID, '_valk_omschrijving', true );
	$allerg = (string) get_post_meta( $dish->ID, '_valk_allergenen', true );
	$diets = get_the_terms( $dish, 'dieet' );
	$tags  = '';
	if ( $diets && ! is_wp_error( $diets ) ) {
		foreach ( $diets as $d ) {
			$tags .= '<span class="valk-diet">' . esc_html( $d->name ) . '</span>';
		}
	}
	$img = ( $photos && has_post_thumbnail( $dish ) ) ? get_the_post_thumbnail( $dish, 'thumbnail', array( 'class' => 'valk-dish__img', 'loading' => 'lazy', 'alt' => '' ) ) : '';
	return sprintf(
		'<li class="valk-dish%1$s">%2$s<div class="valk-dish__body"><div class="valk-dish__head"><h4 class="valk-dish__name">%3$s</h4>%4$s</div>%5$s%6$s</div></li>',
		$img ? ' has-img' : '',
		$img,
		esc_html( get_the_title( $dish ) ),
		$price ? '<span class="valk-dish__price">' . esc_html( $price ) . '</span>' : '',
		$desc ? '<p class="valk-dish__desc">' . esc_html( $desc ) . '</p>' : '',
		( $tags || $allerg ) ? '<p class="valk-dish__meta">' . $tags . ( $allerg ? '<span class="valk-dish__allergens">Allergenen: ' . esc_html( $allerg ) . '</span>' : '' ) . '</p>' : ''
	);
}

/* -------------------------------------------------------------------------
 * Menukaart als PDF
 * ---------------------------------------------------------------------- */

function valkenisse_render_menu_pdf_block( array $attrs ): string {
	$id  = (int) ( $attrs['pdfId'] ?? 0 );
	$url = $id ? (string) wp_get_attachment_url( $id ) : '';
	if ( ! $url ) {
		return current_user_can( 'edit_posts' )
			? valkenisse_wrapper( 'valk-pdf', '<p class="valk-editor-hint">Nog geen menukaart-PDF gekozen. Selecteer dit blok en kies rechts <strong>PDF kiezen</strong>.</p>' )
			: '';
	}
	if ( function_exists( 'wp_enqueue_script_module' ) ) {
		wp_enqueue_script_module( 'valkenisse-pdf-viewer' );
	}
	$size   = size_format( (int) filesize( get_attached_file( $id ) ), 1 );
	$bar    = '<div class="valk-pdf__bar">'
		. '<a class="valk-btn valk-btn--primary" href="' . esc_url( $url ) . '" download>' . valkenisse_icon( 'menu' ) . 'Download de menukaart <span class="valk-pdf__meta">(PDF' . ( $size ? ', ' . esc_html( $size ) : '' ) . ')</span></a>'
		. '<a class="valk-btn" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Openen in nieuw venster</a>'
		. '</div>';
	$pages  = '<div class="valk-pdf__pages"><p class="valk-pdf__loading">De menukaart wordt geladen… Lukt dat niet? <a href="' . esc_url( $url ) . '">Open de menukaart als PDF</a>.</p></div>';
	$text   = '';
	if ( ! empty( $attrs['tekstversie'] ) ) {
		$menu = valkenisse_render_menu_block( array( 'categorieen' => array(), 'navigatie' => false, 'fotos' => false ) );
		if ( $menu ) {
			$text = '<details class="valk-pdf__text"><summary>Tekstversie van de menukaart</summary>' . $menu . '</details>';
		}
	}
	return valkenisse_wrapper(
		'valk-pdf',
		$bar . $pages . $text,
		'div',
		array(
			'data-pdf'    => esc_url( $url ),
			'data-worker' => esc_url( VALKENISSE_URL . 'assets/vendor/pdfjs/pdf.worker.min.js' ),
		)
	);
}

/* -------------------------------------------------------------------------
 * Studio's en buffetten
 * ---------------------------------------------------------------------- */

function valkenisse_lines( string $text ): array {
	return array_values( array_filter( array_map( 'trim', explode( "\n", $text ) ) ) );
}

function valkenisse_render_studios_block(): string {
	$studios = get_posts( array( 'post_type' => 'studio', 'posts_per_page' => 20, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	if ( ! $studios ) {
		return current_user_can( 'edit_posts' ) ? valkenisse_wrapper( 'valk-studios', '<p class="valk-editor-hint">Nog geen studio\'s. Voeg ze toe via het menu Studio\'s.</p>' ) : '';
	}
	$html = '';
	foreach ( $studios as $i => $studio ) {
		$facilities = valkenisse_lines( (string) get_post_meta( $studio->ID, '_valk_faciliteiten', true ) );
		$persons    = (string) get_post_meta( $studio->ID, '_valk_personen', true );
		$list       = '';
		foreach ( $facilities as $f ) {
			$list .= '<li>' . esc_html( $f ) . '</li>';
		}
		$img = has_post_thumbnail( $studio ) ? get_the_post_thumbnail( $studio, 'large', array( 'class' => 'valk-studio__img', 'sizes' => '(min-width: 900px) 50vw, 100vw' ) ) : '<div class="valk-studio__img valk-placeholder" role="img" aria-label="Foto studio volgt"></div>';
		$html      .= sprintf(
			'<article class="valk-studio%1$s">%2$s<div class="valk-studio__body"><p class="valk-eyebrow">%3$s</p><h3 class="valk-studio__title">%4$s</h3><div class="valk-studio__text">%5$s</div>%6$s</div></article>',
			$i % 2 ? ' is-reversed' : '',
			$img,
			esc_html( $persons ? $persons . ' personen' : 'Studio' ),
			esc_html( get_the_title( $studio ) ),
			apply_filters( 'the_content', $studio->post_content ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core-filter.
			$list ? '<ul class="valk-checklist">' . $list . '</ul>' : ''
		);
	}
	return valkenisse_wrapper( 'valk-studios', $html );
}

function valkenisse_render_buffets_block( array $attrs = array() ): string {
	$items = get_posts( array( 'post_type' => 'arrangement', 'posts_per_page' => 30, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	if ( ! $items ) {
		return current_user_can( 'edit_posts' ) ? valkenisse_wrapper( 'valk-buffets', '<p class="valk-editor-hint">Nog geen buffetten. Voeg ze toe via het menu Feesten.</p>' ) : '';
	}
	$html = '';
	foreach ( $items as $item ) {
		$incl = '';
		foreach ( valkenisse_lines( (string) get_post_meta( $item->ID, '_valk_inbegrepen', true ) ) as $line ) {
			$incl .= '<li>' . esc_html( $line ) . '</li>';
		}
		$price   = (string) get_post_meta( $item->ID, '_valk_prijs', true );
		$minimum = (string) get_post_meta( $item->ID, '_valk_minimum', true );
		$desc    = (string) get_post_meta( $item->ID, '_valk_omschrijving', true );
		$img     = has_post_thumbnail( $item ) ? get_the_post_thumbnail( $item, 'medium_large', array( 'class' => 'valk-buffet__img', 'loading' => 'lazy' ) ) : '';
		$html   .= sprintf(
			'<article class="valk-buffet">%1$s<div class="valk-buffet__body"><h3 class="valk-buffet__title">%2$s</h3>%3$s%4$s<p class="valk-buffet__meta">%5$s%6$s</p></div></article>',
			$img,
			esc_html( get_the_title( $item ) ),
			$desc ? '<p>' . nl2br( esc_html( $desc ) ) . '</p>' : '',
			$incl ? '<ul class="valk-checklist">' . $incl . '</ul>' : '',
			$price ? '<span class="valk-buffet__price">' . esc_html( $price ) . '</span>' : '',
			$minimum ? '<span>Vanaf ' . esc_html( $minimum ) . ' personen</span>' : ''
		);
	}
	$head = ( $attrs['titel'] ?? '' ) ? '<h2 class="valk-buffets__title">' . esc_html( $attrs['titel'] ) . '</h2>' : '';
	$head .= ( $attrs['intro'] ?? '' ) ? '<p class="valk-buffets__intro">' . esc_html( $attrs['intro'] ) . '</p>' : '';
	return valkenisse_wrapper( 'valk-buffets-wrap', $head . '<div class="valk-buffets">' . $html . '</div>' );
}

/* -------------------------------------------------------------------------
 * Galerij met lightbox
 * ---------------------------------------------------------------------- */

function valkenisse_render_gallery_block( array $attrs ): string {
	$only  = array_filter( (array) ( $attrs['categorieen'] ?? array() ) );
	$items = valkenisse_gallery_items( $only, max( 1, (int) ( $attrs['maximum'] ?? 60 ) ) );
	if ( ! $items ) {
		return current_user_can( 'edit_posts' )
			? valkenisse_wrapper( 'valk-gallery', '<p class="valk-editor-hint">Nog geen foto\'s in de galerij. Upload foto\'s via <strong>Media</strong> en vink bij "Toon in galerij" een categorie aan.</p>' )
			: '';
	}
	wp_enqueue_script( 'valkenisse-gallery' );

	$used  = array();
	$grid  = '';
	foreach ( $items as $i => $item ) {
		$cats = wp_get_object_terms( $item->ID, 'fotocategorie' );
		$slugs = array();
		foreach ( $cats as $cat ) {
			$slugs[]             = $cat->slug;
			$used[ $cat->slug ] = $cat;
		}
		$full = wp_get_attachment_image_src( $item->ID, 'large' );
		$alt  = trim( (string) get_post_meta( $item->ID, '_wp_attachment_image_alt', true ) );
		$grid .= sprintf(
			'<li class="valk-gallery__item" data-cats="%1$s"><a href="%2$s" data-caption="%3$s">%4$s</a></li>',
			esc_attr( implode( ' ', $slugs ) ),
			esc_url( $full ? $full[0] : wp_get_attachment_url( $item->ID ) ),
			esc_attr( wp_get_attachment_caption( $item->ID ) ? wp_get_attachment_caption( $item->ID ) : $alt ),
			wp_get_attachment_image(
				$item->ID,
				'medium_large',
				false,
				array(
					'loading' => $i < 4 ? 'eager' : 'lazy',
					'sizes'   => '(min-width: 1000px) 33vw, (min-width: 600px) 50vw, 100vw',
					'alt'     => $alt,
				)
			)
		);
	}

	$filter = '';
	if ( ! empty( $attrs['filter'] ) && count( $used ) > 1 ) {
		uasort( $used, static fn( $a, $b ) => strcmp( $a->name, $b->name ) );
		$filter = '<div class="valk-gallery__filter" role="group" aria-label="Filter foto\'s"><button type="button" aria-pressed="true" data-filter="*">Alles</button>';
		foreach ( $used as $slug => $term ) {
			$filter .= '<button type="button" aria-pressed="false" data-filter="' . esc_attr( $slug ) . '">' . esc_html( $term->name ) . '</button>';
		}
		$filter .= '</div>';
	}
	return valkenisse_wrapper( 'valk-gallery', $filter . '<ul class="valk-gallery__grid">' . $grid . '</ul>' );
}

/* -------------------------------------------------------------------------
 * Mobiele actiebalk en mededeling
 * ---------------------------------------------------------------------- */

function valkenisse_render_actionbar_block(): string {
	$tel   = valkenisse_phone_href();
	$items = array();
	if ( $tel ) {
		$items[] = '<a href="' . esc_url( $tel ) . '">' . valkenisse_icon( 'phone' ) . '<span>Bellen</span></a>';
	}
	$items[] = '<a href="' . esc_url( valkenisse_route_url() ) . '" target="_blank" rel="noopener">' . valkenisse_icon( 'route' ) . '<span>Route</span></a>';
	$items[] = '<a href="' . esc_url( valkenisse_page_url( 'menukaart' ) ) . '">' . valkenisse_icon( 'menu' ) . '<span>Menukaart</span></a>';
	$items[] = '<a class="is-primary" href="' . esc_url( valkenisse_reserve_url() ) . '">' . valkenisse_icon( 'agenda' ) . '<span>Reserveren</span></a>';
	return '<nav class="valk-actionbar" aria-label="Snelle acties">' . implode( '', $items ) . '</nav>';
}

function valkenisse_render_notice_block(): string {
	$m = valkenisse_get( 'mededeling' );
	if ( empty( $m['actief'] ) || '' === $m['tekst'] ) {
		return '';
	}
	if ( $m['tot'] && Valkenisse_Hours::now()->format( 'Y-m-d' ) > $m['tot'] ) {
		return '';
	}
	$link = $m['link'] ? ' <a href="' . esc_url( $m['link'] ) . '">' . esc_html( $m['link_tekst'] ? $m['link_tekst'] : 'Lees meer' ) . '</a>' : '';
	return '<div class="valk-notice" role="status"><p>' . esc_html( $m['tekst'] ) . $link . '</p></div>';
}
