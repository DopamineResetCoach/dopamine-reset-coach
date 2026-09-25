<?php
/**
 * Title: Pagina – Strandhuisjes
 * Slug: valkenisse/page-strandhuisjes
 * Categories: valkenisse-paginas
 * Post Types: page
 * Viewport Width: 1400
 * Inserter: false
 */
echo valkenisse_page_hero( 'strandhuisjes', __( 'De strandhuisjes van Strandpaviljoen Valkenisse op het strand', 'valkenisse' ), __( 'Jouw plekje op het strand', 'valkenisse' ), __( 'Strandhuisje huren in Valkenisse', 'valkenisse' ), __( 'Per dag, per week of het hele seizoen – van begin april tot en met september.', 'valkenisse' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<!-- wp:group {"tagName":"section","align":"full","className":"vk-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vk-section"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"50%","className":"vk-reveal"} -->
<div class="wp-block-column vk-reveal" style="flex-basis:50%"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Je eigen uitvalsbasis aan zee', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vk-lead"} -->
<p class="vk-lead"><?php esc_html_e( 'Een strandhuisje bij Valkenisse is je eigen plek op het strand, met het paviljoen om de hoek voor een hapje en een drankje.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'We verhuren de strandhuisjes van begin april tot en met september. Je kunt een strandhuisje huren per dag, per week of voor het hele seizoen.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"vk-small-title"} -->
<h3 class="wp-block-heading vk-small-title"><?php esc_html_e( 'Inbegrepen bij elk strandhuisje', 'valkenisse' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:valkenisse/hut-included /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","className":"vk-reveal"} -->
<div class="wp-block-column vk-reveal" style="flex-basis:50%"><!-- wp:heading {"level":3,"className":"vk-small-title"} -->
<h3 class="wp-block-heading vk-small-title"><?php esc_html_e( 'Tarieven', 'valkenisse' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:valkenisse/hut-prices /-->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#aanvragen"><?php esc_html_e( 'Vraag een strandhuisje aan', 'valkenisse' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"vk-photo-row","layout":{"type":"grid","minimumColumnWidth":"18rem"}} -->
<div class="wp-block-group alignwide vk-photo-row"><!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"vk-reveal"} -->
<figure class="wp-block-image size-large vk-reveal"><img src="<?php echo valkenisse_photo( 'strandhuisjes' ); ?>" alt="<?php esc_attr_e( 'Een rij strandhuisjes bij Strandpaviljoen Valkenisse', 'valkenisse' ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"vk-reveal"} -->
<figure class="wp-block-image size-large vk-reveal"><img src="<?php echo valkenisse_photo( 'strandstoelen' ); ?>" alt="<?php esc_attr_e( 'Houten strandstoel op het strand van Valkenisse, met zicht op zee', 'valkenisse' ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"vk-reveal"} -->
<figure class="wp-block-image size-large vk-reveal"><img src="<?php echo valkenisse_photo( 'duinen-valkenisse' ); ?>" alt="<?php esc_attr_e( 'Het strand en de duinen van Valkenisse', 'valkenisse' ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"aanvragen","align":"full","className":"vk-section vk-request","backgroundColor":"zand","layout":{"type":"constrained","contentSize":"820px"}} -->
<section id="aanvragen" class="wp-block-group alignfull vk-section vk-request has-zand-background-color has-background"><!-- wp:paragraph {"className":"is-style-eyebrow"} -->
<p class="is-style-eyebrow"><?php esc_html_e( 'Aanvraag', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Vraag een strandhuisje aan', 'valkenisse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Laat weten wanneer je een strandhuisje wilt huren. We nemen contact met je op over de beschikbaarheid. Liever bellen of mailen? Dat kan ook.', 'valkenisse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:valkenisse/hut-request-form /--></section>
<!-- /wp:group -->
