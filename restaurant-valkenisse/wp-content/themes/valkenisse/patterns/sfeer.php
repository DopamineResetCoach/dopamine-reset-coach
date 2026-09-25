<?php
/**
 * Title: Sfeerbeeld (volle breedte)
 * Slug: valkenisse/sfeer
 * Categories: valkenisse, banner
 * Description: Grote sfeerfoto met korte tekst en zachte parallax.
 *
 * @package Valkenisse
 */

$attrs = array(
	'url'             => valkenisse_img( 'terras.svg' ),
	'dimRatio'        => 30,
	'overlayColor'    => 'green-deep',
	'minHeight'       => 85,
	'minHeightUnit'   => 'vh',
	'contentPosition' => 'center center',
	'tagName'         => 'section',
	'align'           => 'full',
	'className'       => 'is-style-parallax-soft vk-mood',
	'layout'          => array( 'type' => 'constrained' ),
);
echo '<!-- wp:cover ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n";
echo '<section class="wp-block-cover alignfull is-style-parallax-soft vk-mood" style="min-height:85vh"><img class="wp-block-cover__image-background" alt="" src="' . valkenisse_img( 'terras.svg' ) . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-green-deep-background-color has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container">';
echo valkenisse_p( 'Restaurant Valkenisse', 'is-style-eyebrow has-text-align-center' ) . "\n\n";
echo valkenisse_h( 'Van lunch in de zon tot uitgebreid diner', 2, 'xx-large', 'has-text-align-center vk-mood__title' );
echo '</div></section>' . "\n" . '<!-- /wp:cover -->';
