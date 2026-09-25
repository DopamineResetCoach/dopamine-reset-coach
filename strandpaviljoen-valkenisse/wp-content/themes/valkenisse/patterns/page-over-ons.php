<?php
/**
 * Title: Pagina – Over ons
 * Slug: valkenisse/page-over-ons
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1400
 * Inserter: false
 */
echo valkenisse_page_hero( 'historie-1956', __( 'Historische foto van het strandpaviljoen', 'valkenisse' ), __( 'Familie Herwegh', 'valkenisse' ), __( 'Sinds 1956', 'valkenisse' ), __( 'Het verhaal van een strandpaviljoen dat vanaf het zand werd opgebouwd.', 'valkenisse' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","layout":{"type":"constrained","contentSize":"720px"}} -->
<section class="wp-block-group alignfull vk-section"><!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Strandpaviljoen Valkenisse werd in 1956 vanaf het zand opgebouwd door Sies Herwegh. Sindsdien wordt het paviljoen door de familie Herwegh geëxploiteerd.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php echo wp_kses_post( '<mark class="vk-todo">' . esc_html__( '[DOOR FAMILIE HERWEGH AAN TE LEVEREN: het verhaal in eigen woorden – bijvoorbeeld een herinnering aan Sies, wie het paviljoen vandaag runt en wat de familie zo mooi vindt aan deze plek.]', 'valkenisse' ) . '</mark>' ); ?></p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section vk-timeline-section","backgroundColor":"zand-licht","layout":{"type":"constrained","contentSize":"820px"}} -->
<section class="wp-block-group alignfull vk-section vk-timeline-section has-zand-licht-background-color has-background"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Van toen naar nu', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"vk-timeline","layout":{"type":"default"}} -->
<div class="wp-block-group vk-timeline"><!-- wp:group {"className":"vk-timeline__item vk-reveal","layout":{"type":"default"}} -->
<div class="wp-block-group vk-timeline__item vk-reveal"><!-- wp:paragraph {"className":"vk-timeline__year"} -->
<p class="vk-timeline__year">1956</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Sies Herwegh bouwt het strandpaviljoen vanaf het zand op.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vk-timeline__item vk-reveal","layout":{"type":"default"}} -->
<div class="wp-block-group vk-timeline__item vk-reveal"><!-- wp:paragraph {"className":"vk-timeline__year"} -->
<p class="vk-timeline__year"><?php esc_html_e( 'Familie Herwegh', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Het paviljoen blijft binnen de familie en wordt van generatie op generatie voortgezet.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vk-timeline__item vk-reveal","layout":{"type":"default"}} -->
<div class="wp-block-group vk-timeline__item vk-reveal"><!-- wp:paragraph {"className":"vk-timeline__year"} -->
<p class="vk-timeline__year"><?php esc_html_e( 'Vandaag', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Een vernieuwd strandpaviljoen, met dezelfde gemoedelijke sfeer.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"large","className":"vk-reveal"} -->
<figure class="wp-block-image size-large vk-reveal"><img src="<?php echo valkenisse_photo( 'familie' ); ?>" alt="<?php esc_attr_e( 'De familie Herwegh bij het strandpaviljoen', 'valkenisse' ); ?>" style="aspect-ratio:4/5;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","className":"vk-reveal"} -->
<div class="wp-block-column is-vertically-aligned-center vk-reveal"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Wat ons bijzonder maakt', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Familie. Strand. Zee.', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"vk-checklist"} -->
<ul class="wp-block-list vk-checklist"><!-- wp:list-item -->
<li><?php esc_html_e( 'Een familiebedrijf sinds 1956', 'valkenisse' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Een gemoedelijke sfeer', 'valkenisse' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Een goede prijs-kwaliteitverhouding', 'valkenisse' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Direct aan zee, met uitzicht over de Westerschelde en de Noordzee', 'valkenisse' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Strandhuisjes en strandverhuur', 'valkenisse' ); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","backgroundColor":"zand-licht","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section has-zand-licht-background-color has-background"><!-- wp:heading {"align":"wide","className":"vk-h2-small"} -->
<h2 class="wp-block-heading alignwide vk-h2-small"><?php esc_html_e( "Foto's van vroeger", 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:valkenisse/gallery {"category":"historie","limit":24,"showFilters":false,"align":"wide"} /--></section>
<!-- /wp:group -->
