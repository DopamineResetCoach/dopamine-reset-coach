/* Kaart pas laden na een klik: sneller en zonder cookies van derden vooraf. */
( function () {
	document.querySelectorAll( '[data-vk-map]' ).forEach( function ( map ) {
		var button = map.querySelector( 'button' );
		if ( ! button ) { return; }
		button.addEventListener( 'click', function () {
			var iframe = document.createElement( 'iframe' );
			iframe.src = map.getAttribute( 'data-src' );
			iframe.title = button.textContent;
			iframe.loading = 'lazy';
			iframe.referrerPolicy = 'no-referrer-when-downgrade';
			map.innerHTML = '';
			map.appendChild( iframe );
		} );
	} );
} )();
