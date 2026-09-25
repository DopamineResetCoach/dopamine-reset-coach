<?php
/**
 * Renderfuncties van de dynamische blokken.
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Openingstijden & vandaag
 * ---------------------------------------------------------------------- */

function vk_render_notice_bar( array $attrs = array() ): string {
	$parts = array();
	$today = vk_today();

	if ( vk_get( 'notice_enabled' ) && '' !== $today['headline'] ) {
		$parts[] = sprintf(
			'<span class="vk-notice__item vk-notice__item--%1$s"><span aria-hidden="true">%2$s</span> %3$s</span>',
			esc_attr( $today['state'] ),
			esc_html( $today['icon'] ),
			esc_html( $today['notice'] )
		);
	}

	$extra = trim( vk_tr( (string) vk_get( 'notice_extra' ), 'notice_extra' ) );
	$until = (string) vk_get( 'notice_extra_until' );
	if ( '' !== $extra && ( '' === $until || $until >= vk_now()->format( 'Y-m-d' ) ) ) {
		$link    = (string) vk_get( 'notice_extra_link' );
		$parts[] = $link
			? '<a class="vk-notice__item" href="' . esc_url( $link ) . '">' . esc_html( $extra ) . '</a>'
			: '<span class="vk-notice__item">' . esc_html( $extra ) . '</span>';
	}

	if ( ! $parts ) {
		return '';
	}
	return '<div class="vk-notice vk-notice--' . esc_attr( $today['state'] ) . '" role="status">' . implode( '<span class="vk-notice__sep" aria-hidden="true">·</span>', $parts ) . '</div>';
}

function vk_render_today( array $attrs = array() ): string {
	$today = vk_today();
	$html  = '<div class="vk-today vk-today--' . esc_attr( $today['state'] ) . '">';
	$html .= '<p class="vk-today__date"><span class="vk-today__dot" aria-hidden="true"></span>' . esc_html( ucfirst( wp_date( 'l j F' ) ) ) . '</p>';
	$html .= '<p class="vk-today__headline">' . ( '' !== $today['headline'] ? esc_html( $today['headline'] ) : vk_text( VK_TODO ) ) . '</p>';

	if ( 'closed_weather' === $today['status'] ) {
		$html .= '<p class="vk-today__sub">' . esc_html__( 'Morgen weer een kans. Houd deze pagina in de gaten.', 'valkenisse' ) . '</p>';
	}

	if ( ! empty( $attrs['showWeek'] ) ) {
		$html .= vk_render_opening_hours( array( 'variant' => 'compact' ) );
	}
	$html .= '</div>';
	return $html;
}

function vk_render_opening_hours( array $attrs = array() ): string {
	$compact = ( $attrs['variant'] ?? 'table' ) === 'compact';
	$today   = vk_today_key();
	$days    = vk_weekdays();

	$html  = '<div class="vk-hours' . ( $compact ? ' vk-hours--compact' : '' ) . '">';
	$html .= '<table class="vk-hours__table"><caption class="screen-reader-text">' . esc_html__( 'Openingstijden', 'valkenisse' ) . '</caption><tbody>';

	if ( $compact ) {
		// Compact: opeenvolgende dagen met dezelfde tijden samenvoegen ("Ma – Zo  vanaf 11:00").
		$groups = array();
		foreach ( $days as $key => $day ) {
			$hours = vk_hours_for( $key );
			$label = ! empty( $hours['closed'] ) ? __( 'Gesloten', 'valkenisse' ) : ( '' === $hours['open'] ? VK_TODO : ( '' === $hours['close'] ? sprintf( __( 'vanaf %s', 'valkenisse' ), $hours['open'] ) : $hours['open'] . ' – ± ' . $hours['close'] ) );
			$last  = count( $groups ) - 1;
			if ( $last >= 0 && $groups[ $last ]['label'] === $label ) {
				$groups[ $last ]['to']     = $day[0];
				$groups[ $last ]['keys'][] = $key;
			} else {
				$groups[] = array( 'from' => $day[0], 'to' => '', 'label' => $label, 'keys' => array( $key ) );
			}
		}
		$variable = false;
		foreach ( $groups as $group ) {
			$is_today = in_array( $today, $group['keys'], true );
			$name     = 7 === count( $group['keys'] ) ? __( 'Dagelijks', 'valkenisse' ) : mb_substr( $group['from'], 0, 2 ) . ( $group['to'] ? ' – ' . mb_substr( $group['to'], 0, 2 ) : '' );
			$variable = $variable || str_starts_with( $group['label'], sprintf( __( 'vanaf %s', 'valkenisse' ), '' ) );
			$html    .= sprintf( '<tr%1$s><th scope="row">%2$s</th><td>%3$s</td></tr>', $is_today ? ' class="is-today"' : '', esc_html( $name ), vk_text( $group['label'] ) );
		}
		$html .= '</tbody></table>';
		if ( $variable ) {
			$html .= '<p class="vk-hours__variable">' . esc_html( ucfirst( vk_tr( (string) vk_get( 'hours_variable_label' ), 'hours_variable_label' ) ) ) . '</p>';
		}
		return $html . '</div>';
	}

	foreach ( $days as $key => $day ) {
		$is_today = $key === $today;
		$html    .= sprintf(
			'<tr%1$s><th scope="row">%2$s%3$s</th><td>%4$s</td></tr>',
			$is_today ? ' class="is-today"' : '',
			esc_html( $day[0] ),
			$is_today ? ' <span class="vk-hours__badge">' . esc_html__( 'vandaag', 'valkenisse' ) . '</span>' : '',
			vk_text( vk_hours_label( vk_hours_for( $key ) ) )
		);
	}
	$html .= '</tbody></table>';

	$season = (string) vk_get( 'hours_season' );
	if ( '' !== $season ) {
		$html .= '<p class="vk-hours__season"><strong>' . esc_html__( 'Seizoen:', 'valkenisse' ) . '</strong> ' . vk_text( vk_tr( $season, 'hours_season' ) ) . '</p>';
	}
	$note = (string) vk_get( 'hours_note' );
	if ( '' !== $note ) {
		$html .= '<p class="vk-hours__note">' . nl2br( vk_text( vk_tr( $note, 'hours_note' ) ) ) . '</p>';
	}
	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Tarieven
 * ---------------------------------------------------------------------- */

function vk_render_price_rows( array $rows, string $modifier ): string {
	if ( ! $rows ) {
		return '<p>' . vk_text( VK_TODO ) . '</p>';
	}
	$html  = '';
	$group = null;
	foreach ( $rows as $row ) {
		$row_group = (string) ( $row['group'] ?? '' );
		if ( $row_group !== $group ) {
			if ( null !== $group ) {
				$html .= '</ul>';
			}
			if ( '' !== $row_group ) {
				$html .= '<p class="vk-prices__group">' . esc_html( $row_group ) . '</p>';
			}
			$html .= '<ul class="vk-prices vk-prices--' . esc_attr( $modifier ) . '" role="list">';
			$group = $row_group;
		}
		$html .= '<li class="vk-prices__row">';
		$html .= '<span class="vk-prices__label">' . vk_text( $row['label'] ) . '</span>';
		$html .= '<span class="vk-prices__leader" aria-hidden="true"></span>';
		$html .= '<span class="vk-prices__price">' . vk_text( $row['price'] ) . '</span>';
		if ( ! empty( $row['note'] ) ) {
			$html .= '<span class="vk-prices__note">' . vk_text( $row['note'] ) . '</span>';
		}
		$html .= '</li>';
	}
	return $html . '</ul>';
}

/**
 * "Wilt u reserveren?": reserveringstekst + knoppen mailen, bellen en aanvraagformulier.
 */
function vk_render_hut_booking( array $attrs = array() ): string {
	$html  = '<div class="vk-booking">';
	$html .= '<h3 class="vk-booking__title">' . esc_html__( 'Wilt u reserveren?', 'valkenisse' ) . '</h3>';
	$html .= '<p class="vk-booking__text">' . vk_text( vk_tr( (string) vk_get( 'huts_booking' ), 'huts_booking' ) ) . '</p>';
	$html .= '<ul class="vk-booking__contact" role="list">';
	if ( vk_mail_url() ) {
		$html .= '<li>' . vk_icon( 'mail' ) . '<a href="' . esc_url( vk_mail_url() . '?subject=' . rawurlencode( __( 'Reservering strandhuisje', 'valkenisse' ) ) ) . '">' . esc_html( antispambot( (string) vk_get( 'email' ) ) ) . '</a></li>';
	}
	if ( vk_tel_url() ) {
		$html .= '<li>' . vk_icon( 'phone' ) . '<a href="' . esc_url( vk_tel_url() ) . '">' . esc_html( (string) vk_get( 'phone' ) ) . '</a></li>';
	}
	$html .= '<li>' . vk_icon( 'clock' ) . '<a href="' . esc_url( vk_page_url( 'contact' ) . '#openingstijden' ) . '">' . esc_html__( 'Actuele openingstijden', 'valkenisse' ) . '</a></li>';
	$html .= '</ul>';
	$html .= vk_render_contact_buttons( array( 'buttons' => 'hut,mail,call' ) );
	return $html . '</div>';
}

function vk_render_hut_prices( array $attrs = array() ): string {
	$html  = '<div class="vk-pricecard">';
	$html .= '<p class="vk-pricecard__season">' . vk_text( vk_get( 'huts_season' ) ) . '</p>';
	$html .= vk_render_price_rows( (array) vk_get( 'huts_prices' ), 'huts' );
	$html .= '<p class="vk-pricecard__period">' . vk_icon( 'sun' ) . ' ' . vk_text( vk_tr( (string) vk_get( 'huts_period' ), 'huts_period' ) ) . '</p>';
	$booking = empty( $attrs['hideBooking'] ) ? (string) vk_get( 'huts_booking' ) : '';
	if ( '' !== $booking ) {
		$html .= '<p class="vk-pricecard__booking">' . vk_text( vk_tr( $booking, 'huts_booking' ) ) . '</p>';
	}
	$html .= '</div>';
	return $html;
}

function vk_render_hut_included( array $attrs = array() ): string {
	$lines = array_filter( array_map( 'trim', explode( "\n", (string) vk_get( 'huts_included' ) ) ) );
	if ( ! $lines ) {
		return '';
	}
	$html = '<ul class="vk-included" role="list">';
	foreach ( $lines as $line ) {
		$html .= '<li>' . vk_icon( 'check' ) . '<span>' . vk_text( $line ) . '</span></li>';
	}
	return $html . '</ul>';
}

function vk_render_rental_prices( array $attrs = array() ): string {
	$html  = '<div class="vk-pricecard vk-pricecard--rental">';
	$html .= '<p class="vk-pricecard__season">' . vk_text( vk_get( 'rental_season' ) ) . '</p>';
	$html .= vk_render_price_rows( (array) vk_get( 'rental_prices' ), 'rental' );
	$note  = (string) vk_get( 'rental_note' );
	if ( '' !== $note ) {
		$html .= '<p class="vk-pricecard__period">' . vk_text( vk_tr( $note, 'rental_note' ) ) . '</p>';
	}
	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Menukaart
 * ---------------------------------------------------------------------- */

function vk_menu_categories(): array {
	$terms = get_terms( array( 'taxonomy' => 'vk_menu_cat', 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort(
		$terms,
		static function ( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, 'vk_order', true );
			$ob = (int) get_term_meta( $b->term_id, 'vk_order', true );
			return $oa <=> $ob ?: strcmp( $a->name, $b->name );
		}
	);
	return $terms;
}

function vk_render_menu( array $attrs = array() ): string {
	$items = get_posts(
		array(
			'post_type'      => 'vk_menu_item',
			'posts_per_page' => 500,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array( 'key' => 'vk_hidden', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'vk_hidden', 'value' => '1', 'compare' => '!=' ),
			),
		)
	);

	if ( ! $items ) {
		return '<div class="vk-menu vk-menu--empty"><p>' . vk_text( sprintf( __( 'Menukaart met gerechten, dranken en prijzen: %s', 'valkenisse' ), VK_TODO ) ) . '</p></div>';
	}

	// Groepeer per kaartonderdeel.
	$groups = array();
	foreach ( vk_menu_categories() as $term ) {
		$groups[ $term->term_id ] = array( 'term' => $term, 'items' => array() );
	}
	$other = array( 'term' => null, 'items' => array() );
	foreach ( $items as $item ) {
		$terms = get_the_terms( $item, 'vk_menu_cat' );
		if ( $terms && ! is_wp_error( $terms ) && isset( $groups[ $terms[0]->term_id ] ) ) {
			$groups[ $terms[0]->term_id ]['items'][] = $item;
		} else {
			$other['items'][] = $item;
		}
	}
	if ( $other['items'] ) {
		$groups['other'] = $other;
	}
	$groups = array_filter( $groups, static fn( $g ) => (bool) $g['items'] );

	$html = '<div class="vk-menu">';

	if ( ! empty( $attrs['showNav'] ) && count( $groups ) > 1 ) {
		$html .= '<nav class="vk-menu__nav" aria-label="' . esc_attr__( 'Onderdelen van de kaart', 'valkenisse' ) . '"><ul role="list">';
		foreach ( $groups as $key => $group ) {
			$name  = $group['term'] ? $group['term']->name : __( 'Overig', 'valkenisse' );
			$html .= '<li><a href="#kaart-' . esc_attr( $group['term'] ? $group['term']->slug : 'overig' ) . '">' . esc_html( $name ) . '</a></li>';
		}
		$html .= '</ul></nav>';
	}

	foreach ( $groups as $group ) {
		$slug  = $group['term'] ? $group['term']->slug : 'overig';
		$name  = $group['term'] ? $group['term']->name : __( 'Overig', 'valkenisse' );
		$html .= '<section class="vk-menu__group" id="kaart-' . esc_attr( $slug ) . '" aria-labelledby="kaart-' . esc_attr( $slug ) . '-title">';
		$html .= '<h3 class="vk-menu__title" id="kaart-' . esc_attr( $slug ) . '-title">' . esc_html( $name ) . '</h3>';
		if ( $group['term'] && $group['term']->description ) {
			$html .= '<p class="vk-menu__intro">' . esc_html( $group['term']->description ) . '</p>';
		}
		$html .= '<ul class="vk-menu__items" role="list">';
		foreach ( $group['items'] as $item ) {
			$price = (string) get_post_meta( $item->ID, 'vk_price', true );
			$desc  = (string) get_post_meta( $item->ID, 'vk_description', true );
			$tags  = (string) get_post_meta( $item->ID, 'vk_tags', true );
			$html .= '<li class="vk-menu__item"><div class="vk-prices__row">';
			$html .= '<span class="vk-prices__label">' . esc_html( get_the_title( $item ) ) . '</span>';
			$html .= '<span class="vk-prices__leader" aria-hidden="true"></span>';
			$html .= '<span class="vk-prices__price">' . esc_html( $price ) . '</span></div>';
			if ( $desc ) {
				$html .= '<p class="vk-menu__desc">' . esc_html( $desc ) . '</p>';
			}
			if ( $tags ) {
				$html .= '<p class="vk-menu__tags">' . esc_html( $tags ) . '</p>';
			}
			$html .= '</li>';
		}
		$html .= '</ul></section>';
	}

	$pdf = (string) vk_get( 'menu_pdf' );
	if ( $pdf ) {
		$html .= '<p class="vk-menu__pdf"><a href="' . esc_url( $pdf ) . '">' . esc_html__( 'Download de kaart als PDF', 'valkenisse' ) . '</a></p>';
	}
	return $html . '</div>';
}

/**
 * Volgorde-veld bij kaartonderdelen en fotocategorieën.
 */
foreach ( array( 'vk_menu_cat', 'vk_foto_cat' ) as $vk_tax ) {
	add_action(
		"{$vk_tax}_edit_form_fields",
		static function ( WP_Term $term ) {
			printf(
				'<tr class="form-field"><th scope="row"><label for="vk_order">%1$s</label></th><td><input type="number" id="vk_order" name="vk_order" value="%2$s" /><p class="description">%3$s</p></td></tr>',
				esc_html__( 'Volgorde', 'valkenisse' ),
				esc_attr( (string) get_term_meta( $term->term_id, 'vk_order', true ) ),
				esc_html__( 'Lager getal = eerder op de pagina.', 'valkenisse' )
			);
			wp_nonce_field( 'vk_term_order', 'vk_term_nonce' );
		}
	);
	add_action(
		"edited_{$vk_tax}",
		static function ( int $term_id ) {
			if ( isset( $_POST['vk_order'], $_POST['vk_term_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['vk_term_nonce'] ), 'vk_term_order' ) && current_user_can( 'manage_categories' ) ) {
				update_term_meta( $term_id, 'vk_order', (int) $_POST['vk_order'] );
			}
		}
	);
}

/* -------------------------------------------------------------------------
 * Vacatures
 * ---------------------------------------------------------------------- */

function vk_active_vacancies(): array {
	return get_posts(
		array(
			'post_type'      => 'vk_vacature',
			'posts_per_page' => 20,
			'no_found_rows'  => true,
			'meta_key'       => 'vk_active', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
}

function vk_render_vacancies( array $attrs = array() ): string {
	$jobs = vk_active_vacancies();
	if ( ! $jobs ) {
		return '<div class="vk-jobs vk-jobs--empty"><p class="vk-jobs__empty-title">' . esc_html__( 'Op dit moment hebben we geen openstaande vacatures.', 'valkenisse' ) . '</p><p>' . esc_html__( 'Kijk later nog eens: vacatures verschijnen hier zodra ze er zijn.', 'valkenisse' ) . '</p></div>';
	}

	$labels = array(
		'vk_hours'  => __( 'Uren', 'valkenisse' ),
		'vk_period' => __( 'Periode', 'valkenisse' ),
		'vk_age'    => __( 'Leeftijd', 'valkenisse' ),
	);

	$html = '<div class="vk-jobs">';
	foreach ( $jobs as $job ) {
		$email   = (string) get_post_meta( $job->ID, 'vk_email', true ) ?: (string) vk_get( 'email' );
		$contact = (string) get_post_meta( $job->ID, 'vk_contact', true );
		$subject = rawurlencode( sprintf( __( 'Sollicitatie: %s', 'valkenisse' ), get_the_title( $job ) ) );

		$html .= '<article class="vk-job">';
		$html .= '<p class="vk-job__badge">' . esc_html__( 'We zoeken', 'valkenisse' ) . '</p>';
		$html .= '<h3 class="vk-job__title"><a href="' . esc_url( get_permalink( $job ) ) . '">' . esc_html( get_the_title( $job ) ) . '</a></h3>';
		$html .= '<dl class="vk-job__meta">';
		foreach ( $labels as $key => $label ) {
			$value = (string) get_post_meta( $job->ID, $key, true );
			if ( '' !== $value ) {
				$html .= '<div><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
			}
		}
		$html .= '</dl>';
		$html .= '<div class="vk-job__text">' . wp_kses_post( has_excerpt( $job ) ? wpautop( get_the_excerpt( $job ) ) : wpautop( wp_trim_words( wp_strip_all_tags( $job->post_content ), 45 ) ) ) . '</div>';
		$html .= '<p class="vk-job__actions">';
		$html .= '<a class="vk-btn vk-btn--primary" href="' . esc_url( 'mailto:' . antispambot( $email ) . '?subject=' . $subject ) . '">' . vk_icon( 'mail' ) . esc_html__( 'Solliciteer per e-mail', 'valkenisse' ) . '</a> ';
		$html .= '<a class="vk-btn vk-btn--ghost" href="' . esc_url( get_permalink( $job ) ) . '">' . esc_html__( 'Lees meer', 'valkenisse' ) . '</a>';
		$html .= '</p>';
		if ( $contact ) {
			$html .= '<p class="vk-job__contact">' . esc_html( sprintf( __( 'Contactpersoon: %s', 'valkenisse' ), $contact ) ) . '</p>';
		}
		$html .= '</article>';
	}
	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Galerij
 * ---------------------------------------------------------------------- */

function vk_render_gallery( array $attrs = array() ): string {
	$terms = get_terms( array( 'taxonomy' => 'vk_foto_cat', 'hide_empty' => false ) );
	$terms = is_wp_error( $terms ) ? array() : $terms;
	usort( $terms, static fn( $a, $b ) => (int) get_term_meta( $a->term_id, 'vk_order', true ) <=> (int) get_term_meta( $b->term_id, 'vk_order', true ) );

	$category = sanitize_title( (string) ( $attrs['category'] ?? '' ) );
	$tax      = array(
		'taxonomy' => 'vk_foto_cat',
		'operator' => 'EXISTS',
	);
	if ( $category ) {
		$tax = array(
			'taxonomy' => 'vk_foto_cat',
			'field'    => 'slug',
			'terms'    => $category,
		);
	}

	$photos = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => max( 1, (int) ( $attrs['limit'] ?? 60 ) ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'no_found_rows'  => true,
			'tax_query'      => array( $tax ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);

	if ( ! $photos ) {
		return '<div class="vk-gallery vk-gallery--empty"><p>' . vk_text( sprintf( __( "Foto's van paviljoen, terras, strand, eten & drinken, strandhuisjes en historie: %s", 'valkenisse' ), VK_TODO ) ) . '</p><p class="vk-gallery__howto">' . esc_html__( "Tip voor beheer: upload foto's via Media en kies bij elke foto een fotocategorie.", 'valkenisse' ) . '</p></div>';
	}

	wp_enqueue_script( 'vk-gallery' );

	$used = array();
	$html = '<div class="vk-gallery" data-vk-gallery>';
	$grid = '<ul class="vk-gallery__grid" role="list">';
	foreach ( $photos as $index => $photo ) {
		$photo_terms = wp_get_object_terms( $photo->ID, 'vk_foto_cat', array( 'fields' => 'slugs' ) );
		$photo_terms = is_wp_error( $photo_terms ) ? array() : $photo_terms;
		$used        = array_merge( $used, $photo_terms );
		$full        = wp_get_attachment_image_src( $photo->ID, 'full' );
		$caption     = wp_get_attachment_caption( $photo->ID );
		$alt         = (string) get_post_meta( $photo->ID, '_wp_attachment_image_alt', true );

		$grid .= sprintf(
			'<li class="vk-gallery__item" data-cats="%1$s"><a href="%2$s" data-caption="%3$s">%4$s</a></li>',
			esc_attr( implode( ' ', $photo_terms ) ),
			esc_url( $full ? $full[0] : '' ),
			esc_attr( $caption ?: $alt ),
			wp_get_attachment_image(
				$photo->ID,
				'large',
				false,
				array(
					'loading' => $index < 4 ? 'eager' : 'lazy',
					'sizes'   => '(min-width: 1200px) 400px, (min-width: 700px) 33vw, 50vw',
					'alt'     => $alt,
				)
			)
		);
	}
	$grid .= '</ul>';

	if ( ! empty( $attrs['showFilters'] ) && ! $category ) {
		$html .= '<div class="vk-gallery__filters" role="group" aria-label="' . esc_attr__( "Filter foto's", 'valkenisse' ) . '">';
		$html .= '<button type="button" class="is-active" data-filter="*" aria-pressed="true">' . esc_html__( 'Alles', 'valkenisse' ) . '</button>';
		foreach ( $terms as $term ) {
			if ( in_array( $term->slug, $used, true ) ) {
				$html .= '<button type="button" data-filter="' . esc_attr( $term->slug ) . '" aria-pressed="false">' . esc_html( $term->name ) . '</button>';
			}
		}
		$html .= '</div>';
	}

	$html .= $grid;
	$html .= '<dialog class="vk-lightbox" aria-label="' . esc_attr__( 'Foto', 'valkenisse' ) . '"><button type="button" class="vk-lightbox__close" aria-label="' . esc_attr__( 'Sluiten', 'valkenisse' ) . '">' . vk_icon( 'close' ) . '</button><button type="button" class="vk-lightbox__prev" aria-label="' . esc_attr__( 'Vorige foto', 'valkenisse' ) . '">‹</button><figure><img alt="" /><figcaption></figcaption></figure><button type="button" class="vk-lightbox__next" aria-label="' . esc_attr__( 'Volgende foto', 'valkenisse' ) . '">›</button></dialog>';
	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Gastreacties
 * ---------------------------------------------------------------------- */

function vk_render_reviews( array $attrs = array() ): string {
	$reviews = get_posts(
		array(
			'post_type'      => 'vk_gastreactie',
			'posts_per_page' => 3,
			'no_found_rows'  => true,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'meta_key'       => 'vk_consent', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	$google = (string) vk_get( 'google_reviews' );

	// Geen echte reacties en geen Google-link? Dan tonen we niets (liever niets dan verzonnen reviews).
	if ( ! $reviews && ! $google ) {
		return '';
	}

	$html  = '<div class="vk-reviews">';
	$html .= '<p class="vk-eyebrow">' . esc_html__( 'Gastenboek', 'valkenisse' ) . '</p>';
	$html .= '<h2 class="vk-reviews__title">' . esc_html__( 'Wat gasten over Valkenisse zeggen', 'valkenisse' ) . '</h2>';
	if ( $reviews ) {
		$html .= '<div class="vk-reviews__list">';
		foreach ( $reviews as $review ) {
			$meta  = (string) get_post_meta( $review->ID, 'vk_meta', true );
			$html .= '<figure class="vk-review"><blockquote><p>' . esc_html( (string) get_post_meta( $review->ID, 'vk_quote', true ) ) . '</p></blockquote>';
			$html .= '<figcaption>' . esc_html( get_the_title( $review ) ) . ( $meta ? ' <span>· ' . esc_html( $meta ) . '</span>' : '' ) . '</figcaption></figure>';
		}
		$html .= '</div>';
	}
	if ( $google ) {
		$html .= '<p class="vk-reviews__google"><a class="vk-btn vk-btn--ghost" href="' . esc_url( $google ) . '" rel="noopener" target="_blank">' . vk_icon( 'star' ) . esc_html__( 'Lees reviews op Google', 'valkenisse' ) . '</a></p>';
	}
	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Contact
 * ---------------------------------------------------------------------- */

function vk_render_contact_details( array $attrs = array() ): string {
	$phone = (string) vk_get( 'phone' );
	$email = (string) vk_get( 'email' );

	if ( ( $attrs['variant'] ?? 'full' ) === 'compact' ) {
		$html = '<ul class="vk-contact-compact" role="list">';
		if ( vk_tel_url() ) {
			$html .= '<li>' . vk_icon( 'phone' ) . '<a href="' . esc_url( vk_tel_url() ) . '">' . esc_html( $phone ) . '</a></li>';
		}
		if ( vk_mail_url() ) {
			$html .= '<li>' . vk_icon( 'mail' ) . '<a href="' . esc_url( vk_mail_url() ) . '">' . esc_html( antispambot( $email ) ) . '</a></li>';
		}
		$html .= '<li>' . vk_icon( 'route' ) . '<a href="' . esc_url( vk_route_url() ) . '" rel="noopener" target="_blank">' . esc_html__( 'Route naar het strand', 'valkenisse' ) . '</a></li>';
		return $html . '</ul>';
	}

	$html  = '<div class="vk-contact">';
	$html .= '<section class="vk-contact__card vk-contact__card--nav"><p class="vk-eyebrow">' . esc_html__( 'Met de auto of fiets', 'valkenisse' ) . '</p><h3>' . esc_html__( 'Adres voor navigatie', 'valkenisse' ) . '</h3>';
	$html .= '<p class="vk-contact__big">' . vk_text( vk_get( 'nav_address' ) ) . '</p>';
	$html .= '<p>' . vk_text( vk_tr( (string) vk_get( 'location_desc' ), 'location_desc' ) ) . '</p>';
	$parking = (string) vk_get( 'parking' );
	if ( $parking ) {
		$html .= '<p><strong>' . esc_html__( 'Parkeren:', 'valkenisse' ) . '</strong> ' . vk_text( vk_tr( $parking, 'parking' ) ) . '</p>';
	}
	$html .= '<p><a class="vk-btn vk-btn--primary" href="' . esc_url( vk_route_url() ) . '" rel="noopener" target="_blank">' . vk_icon( 'route' ) . esc_html__( 'Route naar het strand', 'valkenisse' ) . '</a></p></section>';

	$html .= '<section class="vk-contact__card"><p class="vk-eyebrow">' . esc_html__( 'Bellen of mailen', 'valkenisse' ) . '</p><h3>' . esc_html__( 'Telefoon & e-mail', 'valkenisse' ) . '</h3>';
	if ( vk_tel_url() ) {
		$html .= '<p class="vk-contact__big"><a href="' . esc_url( vk_tel_url() ) . '">' . esc_html( $phone ) . '</a></p>';
	}
	$note = (string) vk_get( 'phone_note' );
	if ( $note ) {
		$html .= '<p>' . vk_text( vk_tr( $note, 'phone_note' ) ) . '</p>';
	}
	if ( vk_mail_url() ) {
		$html .= '<p><a href="' . esc_url( vk_mail_url() ) . '">' . esc_html( antispambot( $email ) ) . '</a></p>';
	}
	$html .= '</section>';

	$html .= '<section class="vk-contact__card"><p class="vk-eyebrow">' . esc_html__( 'Voor post', 'valkenisse' ) . '</p><h3>' . esc_html__( 'Postadres', 'valkenisse' ) . '</h3>';
	$html .= '<address><strong>' . esc_html__( 'Strandpaviljoen Valkenisse', 'valkenisse' ) . '</strong><br />' . implode( '<br />', array_map( 'vk_text', vk_postal_lines() ) ) . '</address>';
	$html .= '<p class="vk-contact__warn">' . esc_html__( 'Let op: dit is niet het adres van het paviljoen op het strand. Gebruik voor je navigatie het adres hiernaast.', 'valkenisse' ) . '</p></section>';

	return $html . '</div>';
}

function vk_render_contact_buttons( array $attrs = array() ): string {
	$style   = in_array( $attrs['style'] ?? 'solid', array( 'solid', 'light', 'header' ), true ) ? ( $attrs['style'] ?? 'solid' ) : 'solid';
	$buttons = array_map( 'trim', explode( ',', (string) ( $attrs['buttons'] ?? 'call,route,mail' ) ) );
	$html    = '<div class="vk-buttons vk-buttons--' . esc_attr( $style ) . '">';
	foreach ( $buttons as $i => $button ) {
		$class = 0 === $i ? 'vk-btn vk-btn--primary' : 'vk-btn vk-btn--ghost';
		switch ( $button ) {
			case 'call':
				if ( vk_tel_url() ) {
					$html .= '<a class="' . $class . '" href="' . esc_url( vk_tel_url() ) . '">' . vk_icon( 'phone' ) . '<span>' . esc_html__( 'Bel ons', 'valkenisse' ) . '</span></a>';
				}
				break;
			case 'route':
				$html .= '<a class="' . $class . '" href="' . esc_url( vk_route_url() ) . '" rel="noopener" target="_blank">' . vk_icon( 'route' ) . '<span>' . esc_html__( 'Route naar het strand', 'valkenisse' ) . '</span></a>';
				break;
			case 'mail':
				if ( vk_mail_url() ) {
					$html .= '<a class="' . $class . '" href="' . esc_url( vk_mail_url() ) . '">' . vk_icon( 'mail' ) . '<span>' . esc_html__( 'E-mail ons', 'valkenisse' ) . '</span></a>';
				}
				break;
			case 'hut':
				$html .= '<a class="' . $class . '" href="' . esc_url( vk_page_url( 'strandhuisjes' ) . '#aanvragen' ) . '">' . vk_icon( 'hut' ) . '<span>' . esc_html__( 'Strandhuisje aanvragen', 'valkenisse' ) . '</span></a>';
				break;
			case 'hours':
				$html .= '<a class="' . $class . '" href="' . esc_url( vk_page_url( 'contact' ) . '#openingstijden' ) . '">' . vk_icon( 'clock' ) . '<span>' . esc_html__( 'Openingstijden', 'valkenisse' ) . '</span></a>';
				break;
		}
	}
	return $html . '</div>';
}

/**
 * "Dineren, lunchen of borrelen? Reserveer nu": de bezoeker kiest zelf bellen of mailen.
 * Werkt zonder JavaScript (<details>); telefoon en e-mail komen uit de centrale contactgegevens.
 */
function vk_render_reserve( array $attrs = array() ): string {
	if ( ! vk_get( 'reserve_enabled' ) || ( ! vk_tel_url() && ! vk_mail_url() ) ) {
		return '';
	}
	$style    = 'light' === ( $attrs['style'] ?? 'solid' ) ? 'light' : 'solid';
	$question = trim( vk_tr( (string) vk_get( 'reserve_question' ), 'reserve_question' ) );
	$subject  = vk_tr( (string) vk_get( 'reserve_mail_subject' ), 'reserve_mail_subject' ) ?: __( 'Reservering', 'valkenisse' );
	$body     = implode(
		"\n",
		array(
			__( 'Naam:', 'valkenisse' ),
			__( 'Datum:', 'valkenisse' ),
			__( 'Tijd:', 'valkenisse' ),
			__( 'Aantal personen:', 'valkenisse' ),
			__( 'Lunch, diner of borrel:', 'valkenisse' ),
			__( 'Telefoonnummer:', 'valkenisse' ),
		)
	);

	$html = '<div class="vk-reserve vk-reserve--' . $style . '">';
	if ( '' !== $question ) {
		$html .= '<p class="vk-reserve__question">' . esc_html( $question ) . '</p>';
	}
	$html .= '<details class="vk-reserve__menu"><summary class="vk-btn vk-btn--primary">' . vk_icon( 'calendar' ) . '<span>' . esc_html__( 'Reserveer nu', 'valkenisse' ) . '</span></summary>';
	$html .= '<div class="vk-reserve__options" role="group" aria-label="' . esc_attr__( 'Kies hoe je wilt reserveren', 'valkenisse' ) . '">';
	$html .= '<p class="vk-reserve__label">' . esc_html__( 'Hoe wil je reserveren?', 'valkenisse' ) . '</p>';
	if ( vk_tel_url() ) {
		$html .= '<a class="vk-reserve__option" href="' . esc_url( vk_tel_url() ) . '">' . vk_icon( 'phone' ) . '<span><strong>' . esc_html__( 'Bellen', 'valkenisse' ) . '</strong><small>' . esc_html( (string) vk_get( 'phone' ) ) . '</small></span></a>';
	}
	if ( vk_mail_url() ) {
		$mailto = vk_mail_url() . '?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( $body );
		$html  .= '<a class="vk-reserve__option" href="' . esc_url( $mailto ) . '">' . vk_icon( 'mail' ) . '<span><strong>' . esc_html__( 'Mailen', 'valkenisse' ) . '</strong><small>' . esc_html( antispambot( (string) vk_get( 'email' ) ) ) . '</small></span></a>';
	}
	$note = (string) vk_get( 'phone_note' );
	if ( '' !== $note ) {
		$html .= '<p class="vk-reserve__note">' . vk_text( vk_tr( $note, 'phone_note' ) ) . '</p>';
	}
	$html .= '</div></details></div>';
	return $html;
}

function vk_render_map( array $attrs = array() ): string {
	wp_enqueue_script( 'vk-map' );

	if ( vk_has_coords() ) {
		$lat  = (float) vk_get( 'lat' );
		$lng  = (float) vk_get( 'lng' );
		$src  = sprintf(
			'https://www.openstreetmap.org/export/embed.html?bbox=%1$F,%2$F,%3$F,%4$F&layer=mapnik&marker=%5$F,%6$F',
			$lng - 0.02,
			$lat - 0.008,
			$lng + 0.02,
			$lat + 0.008,
			$lat,
			$lng
		);
		$note = __( 'Kaart van OpenStreetMap', 'valkenisse' );
	} else {
		$src  = 'https://maps.google.com/maps?q=' . rawurlencode( (string) vk_get( 'map_query' ) ) . '&z=14&output=embed';
		$note = __( 'Kaart van Google Maps', 'valkenisse' );
	}

	return sprintf(
		'<div class="vk-map" data-vk-map data-src="%1$s"><div class="vk-map__placeholder"><p>%2$s</p><button type="button" class="vk-btn vk-btn--primary">%3$s</button><p class="vk-map__note">%4$s</p><p><a href="%5$s" rel="noopener" target="_blank">%6$s</a></p></div></div>',
		esc_url( $src ),
		vk_text( vk_tr( (string) vk_get( 'location_desc' ), 'location_desc' ) ),
		esc_html__( 'Toon de kaart', 'valkenisse' ),
		esc_html( sprintf( /* translators: %s: kaartdienst */ __( '%s wordt pas geladen als je op de knop klikt.', 'valkenisse' ), $note ) ),
		esc_url( vk_route_url() ),
		esc_html__( 'Of open direct de route', 'valkenisse' )
	);
}

/* -------------------------------------------------------------------------
 * Navigatie, footer en overige
 * ---------------------------------------------------------------------- */

function vk_render_breadcrumbs( array $attrs = array() ): string {
	if ( is_front_page() ) {
		return '';
	}
	if ( function_exists( 'rank_math_get_breadcrumbs' ) ) {
		return '<div class="vk-breadcrumbs">' . rank_math_get_breadcrumbs() . '</div>';
	}
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		return (string) yoast_breadcrumb( '<div class="vk-breadcrumbs">', '</div>', false );
	}

	$items = vk_breadcrumb_items();
	$html  = '<nav class="vk-breadcrumbs" aria-label="' . esc_attr__( 'Kruimelpad', 'valkenisse' ) . '"><ol>';
	$last  = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		$html .= $i === $last
			? '<li aria-current="page">' . esc_html( $item['name'] ) . '</li>'
			: '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a></li>';
	}
	return $html . '</ol></nav>';
}

/**
 * Kruimelpad als lijst (ook gebruikt voor BreadcrumbList structured data).
 */
function vk_breadcrumb_items(): array {
	$items = array( array( 'name' => __( 'Home', 'valkenisse' ), 'url' => home_url( '/' ) ) );
	if ( is_singular( 'vk_vacature' ) ) {
		$items[] = array( 'name' => __( 'Vacatures', 'valkenisse' ), 'url' => vk_page_url( 'vacatures' ) );
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
			$items[] = array( 'name' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
		}
		$items[] = array( 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) );
	}
	return $items;
}

function vk_render_social_links( array $attrs = array() ): string {
	$links = array_filter(
		array(
			'facebook'  => (string) vk_get( 'facebook' ),
			'instagram' => (string) vk_get( 'instagram' ),
		)
	);
	if ( ! $links ) {
		return '';
	}
	$html = '<ul class="vk-social" role="list">';
	foreach ( $links as $network => $url ) {
		$html .= '<li><a href="' . esc_url( $url ) . '" rel="noopener me" target="_blank" aria-label="' . esc_attr( ucfirst( $network ) ) . '">' . vk_icon( $network ) . '</a></li>';
	}
	return $html . '</ul>';
}

function vk_render_copyright( array $attrs = array() ): string {
	$links = array();
	foreach ( array( 'privacybeleid' => __( 'Privacybeleid', 'valkenisse' ), 'cookiebeleid' => __( 'Cookiebeleid', 'valkenisse' ) ) as $slug => $label ) {
		if ( get_page_by_path( $slug ) ) {
			$links[] = '<a href="' . esc_url( vk_page_url( $slug ) ) . '">' . esc_html( $label ) . '</a>';
		}
	}
	$kvk = (string) vk_get( 'kvk' );
	return '<p class="vk-copyright">© ' . esc_html( wp_date( 'Y' ) . ' ' . vk_get( 'company' ) ) . ( $kvk ? ' · ' . esc_html( sprintf( __( 'KvK %s', 'valkenisse' ), $kvk ) ) : '' ) . ( $links ? ' · ' . implode( ' · ', $links ) : '' ) . '</p>';
}

function vk_render_language_switcher( array $attrs = array() ): string {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return ''; // Wordt pas zichtbaar zodra er een tweede taal (bv. Duits) is ingesteld.
	}
	$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
	if ( ! is_array( $languages ) || count( $languages ) < 2 ) {
		return '';
	}
	$html = '<ul class="vk-lang" role="list">';
	foreach ( $languages as $lang ) {
		$html .= sprintf(
			'<li><a href="%1$s" hreflang="%2$s" lang="%2$s"%3$s>%4$s</a></li>',
			esc_url( $lang['url'] ),
			esc_attr( $lang['locale'] ? str_replace( '_', '-', $lang['locale'] ) : $lang['slug'] ),
			$lang['current_lang'] ? ' aria-current="true"' : '',
			esc_html( strtoupper( $lang['slug'] ) )
		);
	}
	return $html . '</ul>';
}

function vk_render_mobile_bar( array $attrs = array() ): string {
	$html  = '<nav class="vk-mobilebar" aria-label="' . esc_attr__( 'Snelkoppelingen', 'valkenisse' ) . '">';
	if ( vk_tel_url() ) {
		$html .= '<a href="' . esc_url( vk_tel_url() ) . '">' . vk_icon( 'phone' ) . '<span>' . esc_html__( 'Bellen', 'valkenisse' ) . '</span></a>';
	}
	$html .= '<a href="' . esc_url( vk_route_url() ) . '" rel="noopener" target="_blank">' . vk_icon( 'route' ) . '<span>' . esc_html__( 'Route', 'valkenisse' ) . '</span></a>';
	$html .= '<a class="vk-mobilebar__primary" href="' . esc_url( vk_page_url( 'strandhuisjes' ) . '#aanvragen' ) . '">' . vk_icon( 'hut' ) . '<span>' . esc_html__( 'Strandhuisje aanvragen', 'valkenisse' ) . '</span></a>';
	$html .= '<button type="button" data-vk-open-menu>' . vk_icon( 'menu' ) . '<span>' . esc_html__( 'Menu', 'valkenisse' ) . '</span></button>';
	return $html . '</nav>';
}

function vk_render_phone( array $attrs = array() ): string {
	if ( ! vk_tel_url() ) {
		return '';
	}
	return '<a class="vk-phone" href="' . esc_url( vk_tel_url() ) . '">' . vk_icon( 'phone' ) . '<span>' . esc_html( (string) vk_get( 'phone' ) ) . '</span></a>';
}
