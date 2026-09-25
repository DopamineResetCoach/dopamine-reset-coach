<?php
/**
 * Title: Home – uitzicht op de scheepvaart
 * Slug: valkenisse/section-view
 * Categories: valkenisse-secties
 * Keywords: uitzicht, schepen, westerschelde
 * Viewport Width: 1400
 */
$vk_img = valkenisse_photo( 'uitzicht-schip' );
$vk_alt = esc_attr__( 'Een groot containerschip vaart vlak langs de kust, gezien vanaf de duintrap naar het strand en Strandpaviljoen Valkenisse', 'valkenisse' );
?>
<!-- wp:cover {"url":"<?php echo $vk_img; ?>","alt":"<?php echo $vk_alt; ?>","dimRatio":0,"focalPoint":{"x":0.62,"y":0.08},"minHeight":88,"minHeightUnit":"vh","contentPosition":"bottom left","align":"full","className":"vk-view"} -->
<div class="wp-block-cover alignfull has-custom-content-position is-position-bottom-left vk-view" style="min-height:88vh"><img class="wp-block-cover__image-background" alt="<?php echo $vk_alt; ?>" src="<?php echo $vk_img; ?>" style="object-position:62% 8%" data-object-fit="cover" data-object-position="62% 8%"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"vk-view__content vk-reveal","layout":{"type":"constrained","contentSize":"640px","justifyContent":"left"}} -->
<div class="wp-block-group vk-view__content vk-reveal"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Eerste rang aan de Westerschelde', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"vk-view__title"} -->
<h2 class="wp-block-heading vk-view__title"><?php esc_html_e( 'Hier komt de wereld voorbij', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Vanaf het terras kijk je uit over de Noordzee en de monding van de Westerschelde. De zeeschepen varen hier vlak langs de kust voorbij, op weg naar en van de havens verderop. Een schouwspel dat nooit went.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->
