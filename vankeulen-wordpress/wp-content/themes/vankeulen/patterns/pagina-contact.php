<?php
/**
 * Title: Pagina: Contact & aanvragen
 * Slug: vankeulen/pagina-contact
 * Categories: vankeulen-paginas
 * Description: Contactgegevens, openingstijden, aanvraagwizard en kaart.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"vk-sectie","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"38%"} -->
<div class="wp-block-column" style="flex-basis:38%"><!-- wp:heading {"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Contactgegevens</h2>
<!-- /wp:heading -->

<!-- wp:vankeulen/contactgegevens {"metNaam":true} /-->

<!-- wp:heading {"level":3,"fontSize":"medium"} -->
<h3 class="wp-block-heading has-medium-font-size">Openingstijden en contactmomenten</h3>
<!-- /wp:heading -->

<!-- wp:vankeulen/openingstijden /-->

<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group vk-knoppen"><!-- wp:vankeulen/knop {"soort":"bellen","label":"Bel Van Keulen"} /-->

<!-- wp:vankeulen/knop {"soort":"route","stijl":"secundair","label":"Route plannen"} /-->

<!-- wp:vankeulen/knop {"soort":"whatsapp","stijl":"secundair"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"62%"} -->
<div class="wp-block-column" style="flex-basis:62%"><!-- wp:group {"className":"vk-formulierkaart","layout":{"type":"default"}} -->
<div class="wp-block-group vk-formulierkaart"><!-- wp:heading {"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Stallingsplaats aanvragen</h2>
<!-- /wp:heading -->

<!-- wp:vankeulen/beschikbaarheid {"stijl":"blok"} /-->

<!-- wp:vankeulen/aanvraagformulier /--></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"vk-sectie","backgroundColor":"zand-licht","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull vk-sectie has-zand-licht-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:heading -->
<h2 class="wp-block-heading">Locatie en route</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Van Keulen Caravanstalling ligt in Biggekerke, op Walcheren. Plan uw route of bekijk de locatie in Google Maps.</p>
<!-- /wp:paragraph -->

<!-- wp:vankeulen/kaart /--></div>
<!-- /wp:group -->
