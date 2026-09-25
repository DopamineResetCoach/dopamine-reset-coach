/**
 * Kaart: Google Maps pas laden na een klik (sneller en privacyvriendelijker).
 */
( function () {
	'use strict';
	document.addEventListener( 'click', function ( e ) {
		var knop = e.target.closest && e.target.closest( '.vk-kaart__laad' );
		if ( ! knop ) {
			return;
		}
		var vlak = knop.closest( '[data-vk-kaart]' );
		var iframe = document.createElement( 'iframe' );
		iframe.src = vlak.getAttribute( 'data-vk-kaart' );
		iframe.title = 'Kaart: locatie van Van Keulen Caravanstalling';
		iframe.loading = 'lazy';
		iframe.referrerPolicy = 'no-referrer-when-downgrade';
		iframe.setAttribute( 'allowfullscreen', '' );
		vlak.innerHTML = '';
		vlak.appendChild( iframe );
		vlak.classList.add( 'is-geladen' );
	} );
} )();
