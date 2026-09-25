/* Header: vaste achtergrond na scrollen, juiste hoogte, mobiel menu sluiten. (< 1 kB) */
( function () {
	var body = document.body;
	var header = document.querySelector( '.site-header' );
	var menu = document.querySelector( '.site-menu' );
	var ticking = false;

	function onScroll() {
		body.classList.toggle( 'is-scrolled', window.scrollY > 24 );
		ticking = false;
	}
	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) { window.requestAnimationFrame( onScroll ); ticking = true; }
	}, { passive: true } );
	onScroll();

	if ( header && 'ResizeObserver' in window ) {
		new ResizeObserver( function () {
			document.documentElement.style.setProperty( '--valk-header-h', header.offsetHeight + 'px' );
		} ).observe( header );
	}

	if ( menu ) {
		menu.addEventListener( 'toggle', function () { body.classList.toggle( 'menu-open', menu.open ); } );
		menu.addEventListener( 'click', function ( e ) { if ( e.target.closest( 'a' ) ) menu.open = false; } );
		document.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Escape' && menu.open ) { menu.open = false; menu.querySelector( 'summary' ).focus(); } } );
	}
} )();
