<?php
/**
 * Foto's: een categorie (Restaurant, Terras, Gerechten, …) per foto in de mediabibliotheek.
 * Foto's met een categorie verschijnen automatisch in de galerij.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'valkenisse_register_gallery_taxonomy' );

function valkenisse_register_gallery_taxonomy(): void {
	register_taxonomy(
		'fotocategorie',
		'attachment',
		array(
			'labels'                => array(
				'name'          => 'Fotocategorieën',
				'singular_name' => 'Fotocategorie',
				'menu_name'     => 'Fotocategorieën',
				'add_new_item'  => 'Nieuwe fotocategorie',
				'edit_item'     => 'Fotocategorie bewerken',
			),
			'hierarchical'          => true,
			'public'                => false,
			'show_ui'               => true,
			'show_in_rest'          => true,
			'show_admin_column'     => true,
			'show_in_quick_edit'    => false,
			'update_count_callback' => '_update_generic_term_count',
			'rewrite'               => false,
		)
	);
}

/** Vinkjes in het mediavenster (ook bij "Media toevoegen" in de editor). */
add_filter(
	'attachment_fields_to_edit',
	static function ( array $fields, WP_Post $post ) {
		if ( ! wp_attachment_is_image( $post ) ) {
			return $fields;
		}
		$terms = get_terms( array( 'taxonomy' => 'fotocategorie', 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) || ! $terms ) {
			return $fields;
		}
		$current = wp_get_object_terms( $post->ID, 'fotocategorie', array( 'fields' => 'ids' ) );
		$html    = '<input type="hidden" name="attachments[' . (int) $post->ID . '][valk_fotocat][]" value="0">';
		foreach ( $terms as $term ) {
			$html .= sprintf(
				'<label style="display:inline-block;margin:0 .8em .3em 0"><input type="checkbox" name="attachments[%d][valk_fotocat][]" value="%d" %s> %s</label>',
				(int) $post->ID,
				(int) $term->term_id,
				checked( in_array( $term->term_id, (array) $current, true ), true, false ),
				esc_html( $term->name )
			);
		}
		unset( $fields['fotocategorie'] );
		$fields['valk_fotocat'] = array(
			'label' => 'Toon in galerij',
			'input' => 'html',
			'html'  => $html,
			'helps' => 'Vink aan in welke galerijcategorie deze foto verschijnt. Vergeet de "Alternatieve tekst" niet (korte beschrijving van de foto).',
		);
		return $fields;
	},
	10,
	2
);

add_filter(
	'attachment_fields_to_save',
	static function ( array $post, array $attachment ) {
		if ( isset( $attachment['valk_fotocat'] ) && current_user_can( 'edit_post', $post['ID'] ) ) {
			$ids = array_filter( array_map( 'absint', (array) $attachment['valk_fotocat'] ) );
			wp_set_object_terms( (int) $post['ID'], $ids, 'fotocategorie' );
		}
		return $post;
	},
	10,
	2
);

/** Foto's per categorie voor de galerij. */
function valkenisse_gallery_items( array $slugs = array(), int $limit = 200 ): array {
	$args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			$slugs
				? array( 'taxonomy' => 'fotocategorie', 'field' => 'slug', 'terms' => $slugs )
				: array( 'taxonomy' => 'fotocategorie', 'operator' => 'EXISTS' ),
		),
		'no_found_rows'  => true,
	);
	return get_posts( $args );
}
