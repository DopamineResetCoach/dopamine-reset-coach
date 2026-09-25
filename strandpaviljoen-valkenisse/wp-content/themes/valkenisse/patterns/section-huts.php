<?php
/**
 * Title: Home – strandhuisjes
 * Slug: valkenisse/section-huts
 * Categories: valkenisse-secties
 * Keywords: strandhuisje, huren, prijzen
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"vk-section vk-huts","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section vk-huts"><!-- wp:columns {"align":"wide","className":"vk-huts__cols"} -->
<div class="wp-block-columns alignwide vk-huts__cols"><!-- wp:column {"width":"56%","className":"vk-huts__visual"} -->
<div class="wp-block-column vk-huts__visual" style="flex-basis:56%"><!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"large","className":"vk-huts__photo vk-reveal"} -->
<figure class="wp-block-image size-large vk-huts__photo vk-reveal"><img src="<?php echo valkenisse_photo( 'strandhuisjes' ); ?>" alt="<?php esc_attr_e( 'De strandhuisjes van Strandpaviljoen Valkenisse aan de voet van de duinen', 'valkenisse' ); ?>" style="aspect-ratio:4/5;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"vk-huts__text vk-reveal"} -->
<div class="wp-block-column vk-huts__text vk-reveal"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Jouw plekje op het strand', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Een strandhuisje bij Valkenisse', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Van begin april tot en met september verhuren we strandhuisjes: per dag, per week of voor het hele seizoen. Je eigen uitvalsbasis op het strand, met het paviljoen om de hoek.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"vk-small-title"} -->
<h3 class="wp-block-heading vk-small-title"><?php esc_html_e( 'Inbegrepen bij elk strandhuisje', 'valkenisse' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:valkenisse/hut-included /-->

<!-- wp:valkenisse/hut-prices {"className":"vk-huts__prices"} /-->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/strandhuisjes/"><?php esc_html_e( 'Bekijk strandhuisjes & prijzen', 'valkenisse' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/strandhuisjes/#aanvragen"><?php esc_html_e( 'Informeer naar beschikbaarheid', 'valkenisse' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
