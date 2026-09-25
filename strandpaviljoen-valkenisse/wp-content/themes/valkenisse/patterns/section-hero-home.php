<?php
/**
 * Title: Home – hero (grote foto)
 * Slug: valkenisse/section-hero-home
 * Categories: valkenisse-secties
 * Keywords: hero, welkom, foto
 * Viewport Width: 1400
 */
$vk_hero     = valkenisse_photo( 'hero-paviljoen' );
$vk_hero_alt = esc_attr__( 'Strandpaviljoen Valkenisse met het terras op het strand, de duinen en de zee in het licht van de late middag', 'valkenisse' );
?>
<!-- wp:cover {"url":"<?php echo $vk_hero; ?>","alt":"<?php echo $vk_hero_alt; ?>","dimRatio":0,"focalPoint":{"x":0.5,"y":0.55},"minHeight":94,"minHeightUnit":"vh","contentPosition":"bottom left","align":"full","className":"is-style-hero vk-hero"} -->
<div class="wp-block-cover alignfull has-custom-content-position is-position-bottom-left is-style-hero vk-hero" style="min-height:94vh"><img class="wp-block-cover__image-background" alt="<?php echo $vk_hero_alt; ?>" src="<?php echo $vk_hero; ?>" style="object-position:50% 55%" data-object-fit="cover" data-object-position="50% 55%"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"vk-hero__content vk-reveal","layout":{"type":"constrained","contentSize":"820px","justifyContent":"left"}} -->
<div class="wp-block-group vk-hero__content vk-reveal"><!-- wp:paragraph {"className":"is-style-eyebrow vk-hero__eyebrow"} -->
<p class="is-style-eyebrow vk-hero__eyebrow"><?php esc_html_e( 'Welkom op het strand', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"vk-hero__title"} -->
<h1 class="wp-block-heading vk-hero__title"><?php esc_html_e( 'Strandpaviljoen Valkenisse', 'valkenisse' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-hero__lead"} -->
<p class="vk-hero__lead"><?php esc_html_e( 'Sinds 1956 een vertrouwde plek aan zee.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"vk-hero__text"} -->
<p class="vk-hero__text"><?php esc_html_e( 'Geniet van een hapje en drankje met uitzicht over zee, direct vanaf het strand van Valkenisse.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"vk-hero__buttons"} -->
<div class="wp-block-buttons vk-hero__buttons"><!-- wp:button {"className":"vk-btn-light"} -->
<div class="wp-block-button vk-btn-light"><a class="wp-block-button__link wp-element-button" href="/eten-drinken/"><?php esc_html_e( 'Bekijk eten & drinken', 'valkenisse' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline vk-btn-on-photo"} -->
<div class="wp-block-button is-style-outline vk-btn-on-photo"><a class="wp-block-button__link wp-element-button" href="/strandhuisjes/"><?php esc_html_e( 'Ontdek onze strandhuisjes', 'valkenisse' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"vk-hero__scroll"} -->
<p class="vk-hero__scroll"><a href="#ontdek"><?php esc_html_e( '↓ Ontdek Valkenisse', 'valkenisse' ); ?></a></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->
