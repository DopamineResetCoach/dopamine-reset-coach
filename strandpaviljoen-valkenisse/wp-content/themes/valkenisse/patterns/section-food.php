<?php
/**
 * Title: Home – eten & drinken
 * Slug: valkenisse/section-food
 * Categories: valkenisse-secties
 * Keywords: eten, drinken, terras, kaart
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"vk-section vk-food","backgroundColor":"zand-licht","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section vk-food has-zand-licht-background-color has-background"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"40%","className":"vk-reveal"} -->
<div class="wp-block-column is-vertically-aligned-center vk-reveal" style="flex-basis:40%"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Aan tafel', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Met uitzicht smaakt alles beter', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Schuif aan voor een hapje en een drankje in de gemoedelijke sfeer van ons familiepaviljoen. Met het zand aan je voeten en de zee recht voor je.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Eten aan het strand bij Zoutelande, zonder poespas en voor een eerlijke prijs.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/eten-drinken/"><?php esc_html_e( 'Bekijk onze kaart', 'valkenisse' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"60%","className":"vk-food__photos"} -->
<div class="wp-block-column is-vertically-aligned-center vk-food__photos" style="flex-basis:60%"><!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"vk-food__main vk-reveal"} -->
<figure class="wp-block-image size-large vk-food__main vk-reveal"><img src="<?php echo valkenisse_photo( 'eten-terras' ); ?>" alt="<?php esc_attr_e( 'Eten en drinken op het terras van Strandpaviljoen Valkenisse met uitzicht op zee', 'valkenisse' ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"medium","className":"vk-food__small vk-reveal"} -->
<figure class="wp-block-image size-medium vk-food__small vk-reveal"><img src="<?php echo valkenisse_photo( 'drankje' ); ?>" alt="<?php esc_attr_e( 'Een koud drankje op het terras aan zee', 'valkenisse' ); ?>" style="aspect-ratio:4/5;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
