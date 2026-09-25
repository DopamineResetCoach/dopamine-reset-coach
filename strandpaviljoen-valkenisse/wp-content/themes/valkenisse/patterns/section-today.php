<?php
/**
 * Title: Home – vandaag op het strand (openingstijden)
 * Slug: valkenisse/section-today
 * Categories: valkenisse-secties
 * Keywords: openingstijden, vandaag, open, gesloten
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","anchor":"vandaag","align":"full","className":"vk-section vk-today-section","layout":{"type":"constrained"}} -->
<section id="vandaag" class="wp-block-group alignfull vk-section vk-today-section"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"55%","className":"vk-reveal"} -->
<div class="wp-block-column is-vertically-aligned-center vk-reveal" style="flex-basis:55%"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Vandaag op het strand', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:valkenisse/today {"showWeek":true} /-->

<!-- wp:valkenisse/contact-buttons {"buttons":"route,call,hours"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"large","className":"vk-today-section__photo vk-reveal"} -->
<figure class="wp-block-image size-large vk-today-section__photo vk-reveal"><img src="<?php echo valkenisse_photo( 'terras-gasten' ); ?>" alt="<?php esc_attr_e( 'Gasten op het zonnige terras van Strandpaviljoen Valkenisse', 'valkenisse' ); ?>" style="aspect-ratio:4/5;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
