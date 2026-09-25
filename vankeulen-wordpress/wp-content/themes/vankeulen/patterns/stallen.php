<?php
/**
 * Title: Wat kunt u stallen?
 * Slug: vankeulen/stallen
 * Categories: vankeulen
 * Description: Vier grote kaarten met foto voor caravan, boot, vouwwagen/aanhanger en strandhuisjes.
 */
?>
<!-- wp:group {"anchor":"stallen","align":"full","className":"vk-sectie vk-stallen","backgroundColor":"zand-licht","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div id="stallen" class="wp-block-group alignfull vk-sectie vk-stallen has-zand-licht-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"className":"vk-sectiekop","layout":{"type":"default"}} -->
<div class="wp-block-group vk-sectiekop"><!-- wp:paragraph {"className":"is-style-vk-bovenkop"} -->
<p class="is-style-vk-bovenkop">Stallingsmogelijkheden</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Wat kunt u stallen?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Van Keulen Caravanstalling biedt ruimte voor meer dan alleen caravans. Kies wat u wilt stallen en bekijk de mogelijkheden.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vk-diensten","layout":{"type":"default"}} -->
<div class="wp-block-group vk-diensten"><?php $img = vankeulen_img_block( 'caravan' ); ?><!-- wp:group {"className":"vk-dienst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst"><!-- wp:image {<?php echo $img['json']; ?>"aspectRatio":"3/2","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"vk-dienst__beeld"} -->
<figure class="wp-block-image size-large vk-dienst__beeld"><img src="<?php echo $img['url']; ?>" alt="<?php echo $img['alt']; ?>" class="<?php echo trim( $img['class'] ); ?>" style="aspect-ratio:3/2;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"vk-dienst__tekst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst__tekst"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/caravanstalling/">Caravanstalling</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Uw caravan binnen in een droge, geventileerde loods of buiten op het verharde terrein. Voor de winter of het hele jaar.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"link","stijl":"tekstlink","label":"Meer over caravanstalling","url":"/caravanstalling/"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<?php $img = vankeulen_img_block( 'boot' ); ?><!-- wp:group {"className":"vk-dienst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst"><!-- wp:image {<?php echo $img['json']; ?>"aspectRatio":"3/2","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"vk-dienst__beeld"} -->
<figure class="wp-block-image size-large vk-dienst__beeld"><img src="<?php echo $img['url']; ?>" alt="<?php echo $img['alt']; ?>" class="<?php echo trim( $img['class'] ); ?>" style="aspect-ratio:3/2;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"vk-dienst__tekst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst__tekst"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/bootstalling/">Bootstalling</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Na het vaarseizoen uw boot stallen op Walcheren. Geef het type en de afmetingen door en hoor wat er mogelijk is.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"link","stijl":"tekstlink","label":"Meer over bootstalling","url":"/bootstalling/"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<?php $img = vankeulen_img_block( 'aanhanger' ); ?><!-- wp:group {"className":"vk-dienst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst"><!-- wp:image {<?php echo $img['json']; ?>"aspectRatio":"3/2","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"vk-dienst__beeld"} -->
<figure class="wp-block-image size-large vk-dienst__beeld"><img src="<?php echo $img['url']; ?>" alt="<?php echo $img['alt']; ?>" class="<?php echo trim( $img['class'] ); ?>" style="aspect-ratio:3/2;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"vk-dienst__tekst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst__tekst"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/vouwwagen-aanhanger-stalling/">Vouwwagens &amp; aanhangers</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Geen plek thuis voor de vouwwagen of aanhangwagen? Bij Van Keulen staat hij tot u hem weer nodig hebt.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"link","stijl":"tekstlink","label":"Bekijk mogelijkheden","url":"/vouwwagen-aanhanger-stalling/"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<?php $img = vankeulen_img_block( 'strandhuisjes' ); ?><!-- wp:group {"className":"vk-dienst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst"><!-- wp:image {<?php echo $img['json']; ?>"aspectRatio":"3/2","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"vk-dienst__beeld"} -->
<figure class="wp-block-image size-large vk-dienst__beeld"><img src="<?php echo $img['url']; ?>" alt="<?php echo $img['alt']; ?>" class="<?php echo trim( $img['class'] ); ?>" style="aspect-ratio:3/2;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"vk-dienst__tekst","layout":{"type":"default"}} -->
<div class="wp-block-group vk-dienst__tekst"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/strandhuisjes-stalling/">Strandhuisjes &amp; slaaphuisjes</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Buiten het strandseizoen een plek voor uw strandhuisje of slaaphuisje van de Zeeuwse kust.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/knop {"soort":"link","stijl":"tekstlink","label":"Meer informatie","url":"/strandhuisjes-stalling/"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
