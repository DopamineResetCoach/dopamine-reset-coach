<?php
/**
 * Title: Feesten & partijen (homepage)
 * Slug: valkenisse/feesten-home
 * Categories: valkenisse, call-to-action
 * Description: Uitnodigende sectie voor feesten, buffetten en catering.
 *
 * @package Valkenisse
 */

$attrs = array(
	'url'             => valkenisse_img( 'feest.svg' ),
	'dimRatio'        => 50,
	'overlayColor'    => 'green-deep',
	'minHeight'       => 80,
	'minHeightUnit'   => 'vh',
	'contentPosition' => 'center left',
	'tagName'         => 'section',
	'align'           => 'full',
	'className'       => 'vk-party',
	'layout'          => array( 'type' => 'constrained' ),
);
echo '<!-- wp:cover ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n";
echo '<section class="wp-block-cover alignfull has-custom-content-position is-position-center-left vk-party" style="min-height:80vh"><img class="wp-block-cover__image-background" alt="" src="' . valkenisse_img( 'feest.svg' ) . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-green-deep-background-color has-background-dim"></span><div class="wp-block-cover__inner-container">';
echo valkenisse_group(
	valkenisse_p( 'Iets te vieren?', 'is-style-eyebrow' ) . "\n\n" .
	valkenisse_h( 'Samen maken we er iets bijzonders van' ) . "\n\n" .
	valkenisse_p( 'Een verjaardag, bruiloft of ander feest? Vertel ons wat u voor ogen heeft. Wij denken graag mee over de ruimte, een buffet of catering.', 'is-style-lead' ) . "\n\n" .
	valkenisse_p( 'Verjaardagen · Bruiloften · Feesten · Buffetten · Catering', 'vk-party__list' ) . "\n\n" .
	valkenisse_buttons( array( array( 'Bekijk de mogelijkheden', '/feesten-partijen/', 'light' ) ) ),
	'vk-party__content'
);
echo '</div></section>' . "\n" . '<!-- /wp:cover -->';
