/* Houdt "Vandaag geopend" juist, ook als de pagina uit een cache komt. */
( function () {
	var d = new Date();
	var key = d.getFullYear() + '-' + String( d.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( d.getDate() ).padStart( 2, '0' );
	document.querySelectorAll( '.valk-today[data-upcoming]' ).forEach( function ( box ) {
		var list;
		try { list = JSON.parse( box.getAttribute( 'data-upcoming' ) ); } catch ( e ) { return; }
		var today = list && list[ key ];
		if ( ! today || today.s === box.getAttribute( 'data-status' ) && box.querySelector( '.valk-today__text' ).textContent === today.t ) return;
		box.setAttribute( 'data-status', today.s );
		box.querySelector( '.valk-today__text' ).textContent = today.t;
		box.querySelector( '.valk-today__kitchen' ).textContent = today.k;
		var next = box.querySelector( '.valk-today__next' );
		if ( next ) next.remove();
	} );
} )();
