<?php
/**
 * Title: Pagina: Home
 * Slug: valkenisse/page-home
 * Categories: valkenisse-paginas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Complete homepage.
 *
 * @package Valkenisse
 */

foreach ( array( 'hero-home', 'intro', 'eten-drinken', 'sfeer', 'overnachten-home', 'feesten-home', 'omgeving', 'bezoek' ) as $valkenisse_part ) {
	include __DIR__ . '/' . $valkenisse_part . '.php';
	echo "\n\n";
}
