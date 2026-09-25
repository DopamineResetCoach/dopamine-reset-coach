<?php
/**
 * Title: Pagina: Home
 * Slug: vankeulen/pagina-home
 * Categories: vankeulen-paginas
 * Description: Complete homepage: hero, introductie, stallingsmogelijkheden, strandhuisjes, voordelen, locatie, werkwijze & prijs, aanvraag en FAQ.
 * Inserter: no
 */

foreach ( array( 'hero', 'intro', 'stallen', 'strandhuisjes', 'waarom', 'locatie', 'werkwijze', 'aanvraag', 'faq-teaser' ) as $vk_sectie ) {
	include __DIR__ . '/' . $vk_sectie . '.php';
	echo "\n";
}
