<?php
/**
 * Title: Uitgelicht – strandhuisjes en slaaphuisjes
 * Slug: vankeulen/strandhuisjes
 * Categories: vankeulen
 * Description: Grote sectie met foto over stalling van strandhuisjes en slaaphuisjes.
 */

$img = vankeulen_img_block( 'strandhuisjes' );
?>
<!-- wp:group {"align":"full","className":"vk-sectie vk-uitgelicht","backgroundColor":"marine","textColor":"zand-licht","style":{"spacing":{"padding":{"top":"0","bottom":"0"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull vk-sectie vk-uitgelicht has-zand-licht-color has-marine-background-color has-text-color has-background" style="padding-top:0;padding-bottom:0"><!-- wp:media-text {"align":"full",<?php echo str_replace( '"id":', '"mediaId":', $img['json'] ); ?>"mediaType":"image","mediaWidth":52,"imageFill":true,"focalPoint":{"x":0.5,"y":0.55},"className":"vk-uitgelicht__mt"} -->
<div class="wp-block-media-text alignfull is-stacked-on-mobile is-image-fill-element vk-uitgelicht__mt" style="grid-template-columns:52% auto"><figure class="wp-block-media-text__media"><img src="<?php echo $img['url']; ?>" alt="<?php echo $img['alt']; ?>" class="<?php echo trim( ltrim( $img['class'] ) . ' size-full' ); ?>" style="object-position:50% 55%"/></figure><div class="wp-block-media-text__content"><!-- wp:paragraph {"className":"is-style-vk-bovenkop"} -->
<p class="is-style-vk-bovenkop">Ook voor strandhuisjes</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textColor":"wit"} -->
<h2 class="wp-block-heading has-wit-color has-text-color">Van de Zeeuwse kust naar een goede winterstalling</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>In de zomer staan ze langs de Zeeuwse kust, maar na het seizoen moeten strandhuisjes en slaaphuisjes ergens heen. Bij Van Keulen Caravanstalling in Biggekerke is er stallingsruimte voor strandhuisjes en slaaphuisjes, op korte afstand van de kust van Walcheren.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-vk-vinkjes"} -->
<ul class="wp-block-list is-style-vk-vinkjes"><!-- wp:list-item -->
<li>Stalling voor strandhuisjes en slaaphuisjes</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Strandhuisjes binnen in de loods, slaaphuisjes op het verharde buitenterrein</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Geef de afmetingen door, dan hoort u de mogelijkheden</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group vk-knoppen"><!-- wp:vankeulen/knop {"soort":"aanvragen","label":"Informeer naar de mogelijkheden","type":"strandhuisje"} /-->

<!-- wp:vankeulen/knop {"soort":"link","stijl":"licht","label":"Meer over strandhuisjes","url":"/strandhuisjes-stalling/"} /--></div>
<!-- /wp:group --></div></div>
<!-- /wp:media-text --></div>
<!-- /wp:group -->
