<?php
/**
 * Title: Pagina: Vouwwagen & aanhanger stalling
 * Slug: vankeulen/pagina-vouwwagen-aanhanger
 * Categories: vankeulen-paginas
 * Description: Stalling voor vouwwagens en aanhangwagens.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"vk-sectie","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"56%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:56%"><!-- wp:heading -->
<h2 class="wp-block-heading">Geen plek thuis? Stal hem in Biggekerke</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"medium"} -->
<p class="has-medium-font-size">Een vouwwagen of aanhangwagen gebruikt u maar een paar keer per jaar, maar thuis staat hij altijd in de weg. Bij Van Keulen Caravanstalling staat hij op Walcheren tot u hem weer nodig hebt.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group vk-knoppen"><!-- wp:vankeulen/knop {"soort":"aanvragen","type":"vouwwagen"} /-->

<!-- wp:vankeulen/knop {"soort":"bellen","stijl":"secundair"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"44%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%"><?php $img_vouwwagen = vankeulen_img_block( 'vouwwagen' ); ?><!-- wp:image {<?php echo $img_vouwwagen['json']; ?>"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"is-style-vk-foto"} -->
<figure class="wp-block-image size-large is-style-vk-foto"><img src="<?php echo $img_vouwwagen['url']; ?>" alt="<?php echo $img_vouwwagen['alt']; ?>" class="<?php echo trim( $img_vouwwagen['class'] ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"vk-sectie","backgroundColor":"zand-licht","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie has-zand-licht-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:group {"className":"vk-twee","layout":{"type":"default"}} -->
<div class="wp-block-group vk-twee"><!-- wp:group {"className":"is-style-vk-kaart","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-vk-kaart"><!-- wp:heading {"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Vouwwagen stallen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Uw vouwwagen staat buiten het seizoen droog in een van de geventileerde loodsen.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"vk-klein"} -->
<p class="vk-klein">Tip: berg het tentdoek altijd helemaal droog op, zo voorkomt u schimmel en vochtplekken.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"aanvragen","stijl":"tekstlink","label":"Vouwwagen aanmelden","type":"vouwwagen"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-vk-kaart","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-vk-kaart"><!-- wp:heading {"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Aanhangwagen stallen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Aanhangwagens kunnen binnen in de loods of buiten op het verharde terrein worden gestald.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"vk-klein"} -->
<p class="vk-klein">Geef in de aanvraag de afmetingen door, inclusief dissel en eventuele opbouw of huif.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"aanvragen","stijl":"tekstlink","label":"Aanhanger aanmelden","type":"aanhanger"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
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
<li><a href="/strandhuisjes-stalling/">Strandhuisjes &amp; slaaphuisjes stallen</a></li>
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
