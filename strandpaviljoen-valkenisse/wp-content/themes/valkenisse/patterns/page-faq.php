<?php
/**
 * Title: Pagina – Veelgestelde vragen
 * Slug: valkenisse/page-faq
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1000
 * Inserter: false
 */
$vk_faq = array(
	array(
		__( 'Hoe laat zijn jullie open?', 'valkenisse' ),
		'<!-- wp:paragraph --><p>' . esc_html__( 'We gaan open vanaf 11:00 uur; de sluitingstijd wisselt. Bovenaan elke pagina zie je of we vandaag open zijn.', 'valkenisse' ) . '</p><!-- /wp:paragraph --><!-- wp:valkenisse/opening-hours {"variant":"compact"} /-->',
	),
	array(
		__( 'Kan ik een strandhuisje reserveren?', 'valkenisse' ),
		'<!-- wp:paragraph --><p>' . esc_html__( 'Ja. Een strandhuisje kun je het hele jaar door aanvragen via e-mail, of telefonisch wanneer het paviljoen geopend is. Het makkelijkst is het aanvraagformulier op de pagina Strandhuisjes.', 'valkenisse' ) . ' <a href="/strandhuisjes/#aanvragen">' . esc_html__( 'Naar het aanvraagformulier', 'valkenisse' ) . '</a></p><!-- /wp:paragraph -->',
	),
	array(
		__( 'Wanneer en hoe lang kan ik een strandhuisje huren?', 'valkenisse' ),
		'<!-- wp:paragraph --><p>' . esc_html__( 'De strandhuisjes worden verhuurd van begin april tot en met september, per dag, per week of voor het hele seizoen.', 'valkenisse' ) . '</p><!-- /wp:paragraph -->',
	),
	array(
		__( 'Wat zit er bij een strandhuisje?', 'valkenisse' ),
		'<!-- wp:valkenisse/hut-included /-->',
	),
	array(
		__( 'Kan ik ook losse strandstoelen of een parasol huren?', 'valkenisse' ),
		'<!-- wp:paragraph --><p>' . esc_html__( 'Ja, we verhuren strandstoelen, ligbedden, parasols en windschermen.', 'valkenisse' ) . ' <a href="/strandverhuur/">' . esc_html__( 'Bekijk de prijzen', 'valkenisse' ) . '</a></p><!-- /wp:paragraph -->',
	),
	array(
		__( 'Waar ligt het paviljoen en hoe kom ik er?', 'valkenisse' ),
		'<!-- wp:paragraph --><p>' . esc_html__( 'Het paviljoen ligt bij de duinovergang Vossenhol in Groot Valkenisse, bij Camping Meerpaal. Let op: het postadres is niet het adres voor je navigatie.', 'valkenisse' ) . ' <a href="/contact/">' . esc_html__( 'Bekijk adres en route', 'valkenisse' ) . '</a></p><!-- /wp:paragraph -->',
	),
);
?>
<!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Staat je vraag er niet tussen? Bel of mail ons gerust.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->
<?php foreach ( $vk_faq as $vk_item ) : ?>

<!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php echo esc_html( $vk_item[0] ); ?></summary><?php echo $vk_item[1]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hierboven ge-escaped. ?></details>
<!-- /wp:details -->
<?php endforeach; ?>

<!-- wp:valkenisse/contact-buttons {"buttons":"call,mail,route"} /-->
