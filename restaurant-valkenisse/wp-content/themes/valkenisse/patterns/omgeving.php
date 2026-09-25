<?php
/**
 * Title: Omgeving
 * Slug: valkenisse/omgeving
 * Categories: valkenisse, gallery
 * Description: Zee, strand en duinen: fotomozaïek met korte tekst.
 *
 * @package Valkenisse
 */

$head = valkenisse_group(
	valkenisse_p( 'Ontdek de omgeving', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Zee, strand en duinen binnen handbereik' ) . "\n\n" .
	valkenisse_p( 'Restaurant Valkenisse ligt aan de duinen, in het bos en vlak bij het strand van Walcheren. Neem de tijd, loop een rondje en schuif daarna aan.' ),
	'vk-head vk-head--center'
);
$mosaic = valkenisse_columns(
	array(
		array( '58%', valkenisse_image( 'omgeving-strand.svg', 'Strand en zee bij Valkenisse', '4/5', 'vk-area__big is-style-reveal' ) ),
		array( '', valkenisse_image( 'omgeving-duinen.svg', 'De duinen bij Valkenisse', '3/2', 'is-style-reveal' ) . "\n\n" . valkenisse_image( 'omgeving-bos.svg', 'Het bos rond Valkenisse', '3/2', 'is-style-reveal' ) ),
	),
	'vk-mosaic'
);
echo valkenisse_section( 'vk-area', $head . "\n\n" . $mosaic, '', 'omgeving' );
