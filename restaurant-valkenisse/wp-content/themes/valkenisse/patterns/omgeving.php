<?php
/**
 * Title: Omgeving
 * Slug: valkenisse/omgeving
 * Categories: valkenisse, gallery
 * Description: Zee, strand en duinen: brede panoramafoto met korte tekst.
 *
 * @package Valkenisse
 */

$head = valkenisse_group(
	valkenisse_p( 'Ontdek de omgeving', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Zee, strand en duinen binnen handbereik' ) . "\n\n" .
	valkenisse_p( 'Restaurant Valkenisse ligt aan de duinen, in het bos en vlak bij het strand van Walcheren. Neem de tijd, loop een rondje en schuif daarna aan.' ),
	'vk-head vk-head--center'
);
$panorama = '<!-- wp:image {"sizeSlug":"full","linkDestination":"none","align":"wide","className":"vk-area__panorama is-style-reveal"} -->' . "\n"
	. '<figure class="wp-block-image alignwide size-full vk-area__panorama is-style-reveal"><img src="' . valkenisse_img( 'omgeving-panorama.webp' ) . '" alt="Strand, zee en duinen aan de Zeeuwse kust bij avondzon"/></figure>' . "\n"
	. '<!-- /wp:image -->';
echo valkenisse_section( 'vk-area', $head . "\n\n" . $panorama, '', 'omgeving' );
