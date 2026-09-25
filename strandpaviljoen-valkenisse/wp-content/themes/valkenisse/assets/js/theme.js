/* Valkenisse – klein script: header bij scrollen, rustige animaties, menuknop in de mobiele balk. */
( function () {
	var doc = document.documentElement;
	var body = document.body;
	var header = document.querySelector( '.vk-site-header' );

	// Header krijgt een lichte achtergrond na scrollen.
	var ticking = false;
	function onScroll() {
		body.classList.toggle( 'is-scrolled', window.scrollY > 40 );
		ticking = false;
	}
	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			window.requestAnimationFrame( onScroll );
			ticking = true;
		}
	}, { passive: true } );
	onScroll();

	// Hoogte van de header beschikbaar maken voor CSS (bv. het snelmenu van de kaart).
	if ( header && 'ResizeObserver' in window ) {
		new ResizeObserver( function () {
			doc.style.setProperty( '--vk-header-h', header.offsetHeight + 'px' );
		} ).observe( header );
	}

	// Elementen rustig laten verschijnen.
	var items = document.querySelectorAll( '.vk-reveal' );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					io.unobserve( entry.target );
				}
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );
		items.forEach( function ( el ) { io.observe( el ); } );
	} else {
		items.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
	}

	// "Menu" in de mobiele knoppenbalk opent het navigatiemenu.
	document.addEventListener( 'click', function ( e ) {
		var trigger = e.target.closest( '[data-vk-open-menu]' );
		if ( ! trigger ) { return; }
		var open = document.querySelector( '.vk-nav .wp-block-navigation__responsive-container-open' );
		if ( open && open.offsetParent !== null ) {
			open.click();
		} else {
			window.scrollTo( { top: 0, behavior: 'smooth' } );
		}
	} );
} )();
