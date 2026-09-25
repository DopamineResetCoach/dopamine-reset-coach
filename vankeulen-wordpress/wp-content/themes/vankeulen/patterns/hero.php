<?php
/**
 * Title: Hero – caravanstalling in Biggekerke
 * Slug: vankeulen/hero
 * Categories: vankeulen
 * Description: Grote openingsfoto met kop, introductie, knoppen en beschikbaarheid.
 * Keywords: hero, header, foto, kop
 */

$img = vankeulen_img_block( 'hero-terrein' );
?>
<!-- wp:cover {"url":"<?php echo $img['url']; ?>",<?php echo $img['json']; ?>"dimRatio":100,"gradient":"hero","focalPoint":{"x":0.6,"y":0.55},"minHeight":86,"minHeightUnit":"vh","contentPosition":"center left","isDark":true,"align":"full","className":"vk-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-cover alignfull has-custom-content-position is-position-center-left vk-hero" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);min-height:86vh"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient has-hero-gradient-background"></span><img class="wp-block-cover__image-background<?php echo $img['class']; ?>" alt="<?php echo $img['alt']; ?>" src="<?php echo $img['url']; ?>" style="object-position:60% 55%" data-object-fit="cover" data-object-position="60% 55%"/><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"vk-hero__tekst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-hero__tekst"><!-- wp:paragraph {"className":"is-style-vk-bovenkop"} -->
<p class="is-style-vk-bovenkop">Caravanstalling in Biggekerke</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"textColor":"wit"} -->
<h1 class="wp-block-heading has-wit-color has-text-color">Uw caravan veilig gestald op Walcheren</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-hero__intro","fontSize":"medium"} -->
<p class="vk-hero__intro has-medium-font-size">Van caravan en vouwwagen tot boot, aanhanger en strandhuisje. Informeer naar de mogelijkheden bij Van Keulen Caravanstalling.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group vk-knoppen"><!-- wp:vankeulen/knop {"soort":"aanvragen","label":"Vraag een stallingsplaats aan"} /-->

<!-- wp:vankeulen/knop {"soort":"link","stijl":"licht","label":"Bekijk de mogelijkheden","url":"#stallen"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vk-hero__onder","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
<div class="wp-block-group vk-hero__onder"><!-- wp:paragraph {"className":"vk-hero__plaats"} -->
<p class="vk-hero__plaats">Biggekerke • Walcheren • Zeeland</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/beschikbaarheid /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->
