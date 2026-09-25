<?php
/**
 * Title: Pagina: Strandhuisjes & slaaphuisjes
 * Slug: vankeulen/pagina-strandhuisjes
 * Categories: vankeulen-paginas
 * Description: Stalling voor strandhuisjes en slaaphuisjes van de Zeeuwse kust.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"vk-sectie","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"56%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:56%"><!-- wp:heading -->
<h2 class="wp-block-heading">Na het strandseizoen: een goede plek voor uw strandhuisje</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"medium"} -->
<p class="has-medium-font-size">Veel strandhuisjes en slaaphuisjes staan alleen in het strandseizoen aan de Zeeuwse kust. Daarna moeten ze ergens heen. Van Keulen Caravanstalling in Biggekerke biedt stallingsruimte voor strandhuisjes en slaaphuisjes, op korte afstand van de kust van Walcheren.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Dat maakt Van Keulen anders dan veel andere caravanstallingen: hier kunt u niet alleen met uw caravan of boot terecht, maar ook met uw strandhuisje of slaaphuisje.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group vk-knoppen"><!-- wp:vankeulen/knop {"soort":"aanvragen","label":"Informeer naar de mogelijkheden","type":"strandhuisje"} /-->

<!-- wp:vankeulen/knop {"soort":"bellen","stijl":"secundair"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"44%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%"><?php $img_strandhuisjes = vankeulen_img_block( 'strandhuisjes' ); ?><!-- wp:image {<?php echo $img_strandhuisjes['json']; ?>"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"is-style-vk-foto"} -->
<figure class="wp-block-image size-large is-style-vk-foto"><img src="<?php echo $img_strandhuisjes['url']; ?>" alt="<?php echo $img_strandhuisjes['alt']; ?>" class="<?php echo trim( $img_strandhuisjes['class'] ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"vk-sectie","backgroundColor":"zand-licht","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie has-zand-licht-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:group {"className":"vk-twee","layout":{"type":"default"}} -->
<div class="wp-block-group vk-twee"><!-- wp:group {"className":"is-style-vk-kaart","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-vk-kaart"><!-- wp:heading {"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Strandhuisjes</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Strandhuisjes worden binnen gestald, in een van de droge, geventileerde loodsen.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"aanvragen","stijl":"tekstlink","label":"Strandhuisje aanmelden","type":"strandhuisje"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-vk-kaart","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-vk-kaart"><!-- wp:heading {"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Slaaphuisjes</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Slaaphuisjes kunnen buiten worden gestald, op het verharde terrein van ongeveer 2.000 m².</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"aanvragen","stijl":"tekstlink","label":"Slaaphuisje aanmelden","type":"slaaphuisje"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"vk-sectie","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:heading -->
<h2 class="wp-block-heading">Wat we van u willen weten</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"is-style-vk-vinkjes"} -->
<ul class="wp-block-list is-style-vk-vinkjes"><!-- wp:list-item -->
<li>Gaat het om een strandhuisje of een slaaphuisje?</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Lengte, breedte en hoogte</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Staat het huisje op een onderstel of trailer?</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Vanaf wanneer u het wilt stallen</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:heading -->
<h2 class="wp-block-heading">Brengen en ophalen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Hoe het brengen en ophalen van strandhuisjes en slaaphuisjes verloopt, en of Van Keulen daarbij kan helpen: <mark class="vk-aanleveren">[DOOR VAN KEULEN AAN TE LEVEREN]</mark></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"vk-klein"} -->
<p class="vk-klein">Twijfelt u? Neem gerust contact op.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"vk-sectie","backgroundColor":"zand-licht","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie has-zand-licht-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%"><!-- wp:heading -->
<h2 class="wp-block-heading">Ook iets anders te stallen?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bij Van Keulen Caravanstalling kunt u terecht voor verschillende soorten stalling. Combineren kan ook, bijvoorbeeld een caravan en een aanhangwagen.</p>
<!-- /wp:paragraph -->

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
<li><a href="/caravanstalling-walcheren/">Stalling op Walcheren</a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:group {"className":"is-style-vk-kaart vk-prijskaart","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-vk-kaart vk-prijskaart"><!-- wp:vankeulen/prijzen /--></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
