<?php
/**
 * Beheergemak menukaart: prijs-kolom, prijs snel wijzigen, sortering per categorie
 * en importeren vanuit een CSV-bestand (handig om de huidige kaart over te zetten).
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

/* Kolommen ------------------------------------------------------------- */

add_filter(
	'manage_gerecht_posts_columns',
	static function ( array $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['valk_prijs'] = 'Prijs';
			}
		}
		unset( $new['date'] );
		return $new;
	}
);

add_action(
	'manage_gerecht_posts_custom_column',
	static function ( string $col, int $post_id ) {
		if ( 'valk_prijs' === $col ) {
			$price = (string) get_post_meta( $post_id, '_valk_prijs', true );
			echo '<span class="valk-prijs" data-prijs="' . esc_attr( $price ) . '">' . esc_html( $price ? valkenisse_format_price( $price ) : '—' ) . '</span>';
		}
	},
	10,
	2
);

// Standaard sorteren op volgorde en naam, filter op categorie.
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() && $q->is_main_query() && 'gerecht' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}
);

add_action(
	'restrict_manage_posts',
	static function ( string $post_type ) {
		if ( 'gerecht' !== $post_type ) {
			return;
		}
		wp_dropdown_categories(
			array(
				'taxonomy'        => 'menu_categorie',
				'name'            => 'menu_categorie',
				'value_field'     => 'slug',
				'show_option_all' => 'Alle categorieën',
				'selected'        => sanitize_key( $_GET['menu_categorie'] ?? '' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'hierarchical'    => true,
				'hide_empty'      => false,
			)
		);
	}
);

/* Prijs snel wijzigen (Snel bewerken) ---------------------------------- */

add_action(
	'quick_edit_custom_box',
	static function ( string $col, string $post_type ) {
		if ( 'valk_prijs' !== $col || 'gerecht' !== $post_type ) {
			return;
		}
		wp_nonce_field( 'valk_quick', 'valk_quick_nonce' );
		echo '<fieldset class="inline-edit-col-right"><div class="inline-edit-col"><label><span class="title">Prijs</span><span class="input-text-wrap"><input type="text" name="valk_quick_prijs" value=""></span></label></div></fieldset>';
	},
	10,
	2
);

add_action(
	'save_post_gerecht',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['valk_quick_nonce'], $_POST['valk_quick_prijs'] ) || ! wp_verify_nonce( sanitize_key( $_POST['valk_quick_nonce'] ), 'valk_quick' ) ) {
			return;
		}
		if ( current_user_can( 'edit_post', $post_id ) ) {
			update_post_meta( $post_id, '_valk_prijs', sanitize_text_field( wp_unslash( $_POST['valk_quick_prijs'] ) ) );
		}
	}
);

add_action(
	'admin_footer-edit.php',
	static function () {
		if ( 'gerecht' !== get_current_screen()->post_type ) {
			return;
		}
		?>
		<script>
		( function () {
			if ( ! window.inlineEditPost ) return;
			var edit = window.inlineEditPost.edit;
			window.inlineEditPost.edit = function ( id ) {
				edit.apply( this, arguments );
				var postId = typeof id === 'object' ? this.getId( id ) : id;
				var price = document.querySelector( '#post-' + postId + ' .valk-prijs' );
				var input = document.querySelector( '#edit-' + postId + ' input[name="valk_quick_prijs"]' );
				if ( price && input ) input.value = price.getAttribute( 'data-prijs' );
			};
		} )();
		</script>
		<?php
	}
);

/* Volgorde van categorieën -------------------------------------------- */

function valkenisse_term_order( WP_Term $term ): int {
	return (int) get_term_meta( $term->term_id, 'volgorde', true );
}

add_action(
	'menu_categorie_edit_form_fields',
	static function ( WP_Term $term ) {
		?>
		<tr class="form-field">
			<th scope="row"><label for="valk-volgorde">Volgorde op de menukaart</label></th>
			<td><input type="number" id="valk-volgorde" name="valk_volgorde" value="<?php echo esc_attr( (string) valkenisse_term_order( $term ) ); ?>" style="width:6em">
			<p class="description">Laag getal = hoger op de kaart. De "Beschrijving" hierboven verschijnt als korte intro boven de categorie (bv. "Alle hoofdgerechten worden geserveerd met friet").</p></td>
		</tr>
		<?php
	}
);
add_action(
	'menu_categorie_add_form_fields',
	static function () {
		echo '<div class="form-field"><label for="valk-volgorde">Volgorde op de menukaart</label><input type="number" id="valk-volgorde" name="valk_volgorde" value="0" style="width:6em"></div>';
	}
);
foreach ( array( 'created_menu_categorie', 'edited_menu_categorie' ) as $hook ) {
	add_action(
		$hook,
		static function ( int $term_id ) {
			// Nonce wordt door WordPress zelf gecontroleerd op het term-formulier.
			if ( isset( $_POST['valk_volgorde'] ) && current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				update_term_meta( $term_id, 'volgorde', (int) $_POST['valk_volgorde'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		}
	);
}

/* Sfeerfoto's per categorie ------------------------------------------ */

function valkenisse_term_photo_ids( int $term_id ): array {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) get_term_meta( $term_id, 'fotos', true ) ) ) );
	return array_values( array_filter( $ids, static fn( $id ) => wp_attachment_is_image( $id ) ) );
}

add_action(
	'menu_categorie_edit_form_fields',
	static function ( WP_Term $term ) {
		$ids = valkenisse_term_photo_ids( $term->term_id );
		?>
		<tr class="form-field">
			<th scope="row">Foto's bij deze categorie</th>
			<td>
				<input type="hidden" id="valk-fotos" name="valk_fotos" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
				<div id="valk-fotos-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
					<?php foreach ( $ids as $id ) { echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'width:90px;height:90px;object-fit:cover' ) ); } ?>
				</div>
				<button type="button" class="button" id="valk-fotos-kies">Foto's kiezen</button>
				<button type="button" class="button-link" id="valk-fotos-leeg" style="margin-left:8px">Foto's verwijderen</button>
				<p class="description">1 tot 3 sfeerfoto's die op de menukaart onder de titel van deze categorie verschijnen.</p>
			</td>
		</tr>
		<?php
	},
	20
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ) {
		if ( 'term.php' !== $hook || 'menu_categorie' !== ( $_GET['taxonomy'] ?? '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'valkenisse-term-photos', VALKENISSE_URL . 'assets/admin-term-photos.js', array( 'jquery', 'media-editor' ), VALKENISSE_VERSION, true );
	}
);

add_action(
	'edited_menu_categorie',
	static function ( int $term_id ) {
		if ( isset( $_POST['valk_fotos'] ) && current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$ids = array_slice( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['valk_fotos'] ) ) ) ) ), 0, 3 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_term_meta( $term_id, 'fotos', implode( ',', $ids ) );
		}
	}
);

/** Categorieën in kaartvolgorde. */
function valkenisse_menu_terms( int $parent = 0, array $only = array() ): array {
	$args = array(
		'taxonomy'   => 'menu_categorie',
		'hide_empty' => false,
		'parent'     => $parent,
	);
	if ( $only ) {
		unset( $args['parent'] );
		$args['slug'] = $only;
	}
	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort(
		$terms,
		static fn( $a, $b ) => array( valkenisse_term_order( $a ), $a->name ) <=> array( valkenisse_term_order( $b ), $b->name )
	);
	return $terms;
}

/* CSV-import ----------------------------------------------------------- */

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'edit.php?post_type=gerecht', 'Gerechten importeren', 'Importeren (CSV)', 'edit_posts', 'valk-import', 'valkenisse_render_import_page' );
	}
);

function valkenisse_render_import_page(): void {
	$result = null;
	if ( isset( $_POST['valk_import_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['valk_import_nonce'] ), 'valk_import' ) && ! empty( $_FILES['valk_csv']['tmp_name'] ) ) {
		$result = valkenisse_import_csv( $_FILES['valk_csv']['tmp_name'], ! empty( $_POST['valk_publish'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}
	?>
	<div class="wrap valk-admin">
		<h1>Gerechten importeren</h1>
		<?php if ( $result ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( sprintf( '%d gerechten toegevoegd, %d bijgewerkt, %d regels overgeslagen.', $result['added'], $result['updated'], $result['skipped'] ) ); ?></p></div>
		<?php endif; ?>
		<p>Handig om de huidige menukaart in één keer over te zetten. Maak in Excel of Google Sheets een bestand met deze kolommen en sla op als CSV:</p>
		<pre style="background:#fff;padding:1em;border:1px solid #dcdcde">categorie;naam;omschrijving;prijs;dieet;allergenen
Lunch;Voorbeeldgerecht;Korte omschrijving;9,50;vegetarisch;gluten
Hoofdgerechten;…;…;…;glutenvrij;</pre>
		<ul style="list-style:disc;margin-left:2em">
			<li>Scheidingsteken puntkomma (;) of komma. De eerste regel bevat de kolomnamen.</li>
			<li>Bestaat de categorie nog niet, dan wordt hij aangemaakt. Subcategorie: <code>Diner &gt; Hoofdgerechten</code>.</li>
			<li>Een gerecht met dezelfde naam in dezelfde categorie wordt bijgewerkt (handig voor nieuwe prijzen).</li>
			<li>Dieet: meerdere waarden scheiden met een komma (bv. <code>vegetarisch, glutenvrij</code>).</li>
		</ul>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'valk_import', 'valk_import_nonce' ); ?>
			<p><input type="file" name="valk_csv" accept=".csv,text/csv" required></p>
			<p><label><input type="checkbox" name="valk_publish" value="1"> Direct publiceren (anders als concept, zodat u eerst kunt controleren)</label></p>
			<?php submit_button( 'Importeren' ); ?>
		</form>
	</div>
	<?php
}

function valkenisse_import_csv( string $file, bool $publish ): array {
	$result = array( 'added' => 0, 'updated' => 0, 'skipped' => 0 );
	$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	if ( ! $handle ) {
		return $result;
	}
	$first = fgets( $handle );
	$first = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $first );
	$sep   = substr_count( $first, ';' ) >= substr_count( $first, ',' ) ? ';' : ',';
	$head  = array_map( static fn( $h ) => sanitize_key( trim( $h ) ), str_getcsv( trim( $first ), $sep, '"', '\\' ) );
	$order = 0;
	while ( ( $row = fgetcsv( $handle, 0, $sep, '"', '\\' ) ) !== false ) {
		if ( count( $row ) < 2 ) {
			++$result['skipped'];
			continue;
		}
		$row  = array_pad( $row, count( $head ), '' );
		$data = array_combine( $head, array_slice( $row, 0, count( $head ) ) );
		$name = sanitize_text_field( $data['naam'] ?? '' );
		if ( '' === $name ) {
			++$result['skipped'];
			continue;
		}
		$term_id = valkenisse_import_category( (string) ( $data['categorie'] ?? '' ) );
		$existing = get_posts(
			array(
				'post_type'      => 'gerecht',
				'post_status'    => 'any',
				'title'          => $name,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'tax_query'      => $term_id ? array( array( 'taxonomy' => 'menu_categorie', 'terms' => $term_id ) ) : array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		$post_id = $existing ? (int) $existing[0] : wp_insert_post(
			array(
				'post_type'   => 'gerecht',
				'post_title'  => $name,
				'post_status' => $publish ? 'publish' : 'draft',
				'menu_order'  => ++$order,
			)
		);
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			++$result['skipped'];
			continue;
		}
		$existing ? ++$result['updated'] : ++$result['added'];
		update_post_meta( $post_id, '_valk_omschrijving', sanitize_textarea_field( $data['omschrijving'] ?? '' ) );
		update_post_meta( $post_id, '_valk_prijs', sanitize_text_field( $data['prijs'] ?? '' ) );
		update_post_meta( $post_id, '_valk_allergenen', sanitize_text_field( $data['allergenen'] ?? '' ) );
		if ( $term_id ) {
			wp_set_object_terms( $post_id, array( $term_id ), 'menu_categorie' );
		}
		$diets = array_filter( array_map( 'trim', explode( ',', (string) ( $data['dieet'] ?? '' ) ) ) );
		if ( $diets ) {
			wp_set_object_terms( $post_id, array_map( 'sanitize_text_field', $diets ), 'dieet' );
		}
	}
	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	return $result;
}

function valkenisse_import_category( string $path ): int {
	$parent = 0;
	$id     = 0;
	foreach ( array_filter( array_map( 'trim', explode( '>', $path ) ) ) as $name ) {
		$term = term_exists( $name, 'menu_categorie', $parent );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'menu_categorie', array( 'parent' => $parent ) );
		}
		if ( is_wp_error( $term ) ) {
			return $id;
		}
		$id     = (int) $term['term_id'];
		$parent = $id;
	}
	return $id;
}
