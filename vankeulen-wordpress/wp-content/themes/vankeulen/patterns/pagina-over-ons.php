<?php
/**
 * Title: Pagina: Over Van Keulen
 * Slug: vankeulen/pagina-over-ons
 * Categories: vankeulen-paginas
 * Description: Over Van Keulen Caravanstalling.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"vk-sectie","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"56%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:56%"><!-- wp:heading -->
<h2 class="wp-block-heading">Stalling in Biggekerke, persoonlijk geregeld</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"medium"} -->
<p class="has-medium-font-size">Van Keulen Caravanstalling is een stallingsbedrijf in Biggekerke, midden op Walcheren. U kunt hier terecht voor de stalling van caravans, boten, vouwwagens en aanhangwagens, en ook voor strandhuisjes en slaaphuisjes van de Zeeuwse kust.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Er zijn droge, geventileerde loodsen voor binnenstalling en een verhard buitenterrein van ongeveer 2.000 m². Vragen over een stallingsplaats bespreekt u direct met Van Keulen.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"44%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%"><?php $img_loods = vankeulen_img_block( 'loods' ); ?><!-- wp:image {<?php echo $img_loods['json']; ?>"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"is-style-vk-foto"} -->
<figure class="wp-block-image size-large is-style-vk-foto"><img src="<?php echo $img_loods['url']; ?>" alt="<?php echo $img_loods['alt']; ?>" class="<?php echo trim( $img_loods['class'] ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"vk-sectie","backgroundColor":"zand-licht","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie has-zand-licht-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:heading -->
<h2 class="wp-block-heading">Het verhaal van Van Keulen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><mark class="vk-aanleveren">[DOOR VAN KEULEN AAN TE LEVEREN] Korte geschiedenis van het bedrijf: sinds wanneer, wie is Van Keulen, wat vindt u belangrijk?</mark></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><mark class="vk-aanleveren">[DOOR VAN KEULEN AAN TE LEVEREN] Eventueel een foto van de eigenaar of het terrein.</mark></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:heading -->
<h2 class="wp-block-heading">Wat u kunt stallen</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"vk-linklijst"} -->
<ul class="wp-block-list vk-linklijst"><!-- wp:list-item -->
<li><a href="/caravanstalling/">Caravanstalling</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/bootstalling/">Bootstalling</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/vouwwagen-aanhanger-stalling/">Vouwwagen &amp; aanhanger stalling</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/strandhuisjes-stalling/">Strandhuisjes &amp; slaaphuisjes</a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group vk-knoppen"><!-- wp:vankeulen/knop {"soort":"aanvragen"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
