/**
 * Stallingsaanvraag: maakt van het formulier een wizard in 4 stappen.
 * Zonder JavaScript blijft het een gewoon formulier met alle stappen onder elkaar.
 */
( function () {
	'use strict';

	var form = document.querySelector( '[data-vk-wizard]' );
	if ( ! form ) {
		return;
	}

	var stappen = Array.prototype.slice.call( form.querySelectorAll( '.vk-stap' ) );
	var voortgang = form.querySelector( '.vk-aanvraag__voortgang' );
	var info = form.querySelector( '.vk-aanvraag__stapinfo' );
	var balk = form.querySelector( '.vk-aanvraag__balk span' );
	var bolletjes = form.querySelectorAll( '.vk-aanvraag__stappen li' );
	var titels = [ 'Wat wilt u stallen?', 'Afmetingen', 'Gewenste periode', 'Uw gegevens' ];
	var huidig = 0;

	form.classList.add( 'is-wizard' );
	voortgang.hidden = false;

	// Navigatieknoppen per stap.
	stappen.forEach( function ( stap, i ) {
		var nav = document.createElement( 'div' );
		nav.className = 'vk-stap__nav';
		if ( i > 0 ) {
			var terug = document.createElement( 'button' );
			terug.type = 'button';
			terug.className = 'vk-knop vk-knop--secundair wp-element-button vk-terug';
			terug.textContent = '← Vorige';
			terug.addEventListener( 'click', function () {
				toon( i - 1 );
			} );
			nav.appendChild( terug );
		}
		if ( i < stappen.length - 1 ) {
			var verder = document.createElement( 'button' );
			verder.type = 'button';
			verder.className = 'vk-knop vk-knop--primair wp-element-button vk-verder';
			verder.textContent = 'Volgende stap →';
			verder.addEventListener( 'click', function () {
				if ( geldig( stap ) ) {
					toon( i + 1 );
				}
			} );
			nav.appendChild( verder );
			stap.appendChild( nav );
		} else {
			var submit = stap.querySelector( '[type=submit]' );
			stap.insertBefore( nav, submit );
			nav.appendChild( submit );
		}
	} );

	function geldig( stap ) {
		var velden = stap.querySelectorAll( 'input, select, textarea' );
		for ( var i = 0; i < velden.length; i++ ) {
			var v = velden[ i ];
			if ( v.closest( '[hidden]' ) ) {
				continue;
			}
			if ( ! v.checkValidity() ) {
				v.reportValidity();
				return false;
			}
		}
		return true;
	}

	function toon( n, focus ) {
		huidig = n;
		stappen.forEach( function ( s, i ) {
			s.hidden = i !== n;
		} );
		info.textContent = 'Stap ' + ( n + 1 ) + ' van ' + stappen.length + ': ' + titels[ n ];
		balk.style.width = ( ( n + 1 ) / stappen.length ) * 100 + '%';
		Array.prototype.forEach.call( bolletjes, function ( li, i ) {
			li.classList.toggle( 'is-klaar', i < n );
			li.classList.toggle( 'is-actief', i === n );
		} );
		if ( focus !== false ) {
			var legend = stappen[ n ].querySelector( 'legend' );
			legend.setAttribute( 'tabindex', '-1' );
			legend.focus( { preventScroll: true } );
			var top = form.getBoundingClientRect().top + window.scrollY - 110;
			if ( window.scrollY > top ) {
				window.scrollTo( { top: top, behavior: 'smooth' } );
			}
		}
	}

	// "Anders" toont een extra veld.
	var anders = form.querySelector( '[data-toon-bij="anders"]' );
	function checkAnders() {
		var gekozen = form.querySelector( 'input[name=type]:checked' );
		var aan = !! gekozen && gekozen.value === 'anders';
		anders.hidden = ! aan;
		anders.querySelector( 'input' ).required = aan;
	}
	if ( anders ) {
		form.addEventListener( 'change', function ( e ) {
			if ( e.target.name === 'type' ) {
				checkAnders();
			}
		} );
		checkAnders();
	}

	// Object voorselecteren via ?type=boot of een knop met data-vk-type.
	function kies( type ) {
		var r = form.querySelector( 'input[name=type][value="' + type + '"]' );
		if ( r ) {
			r.checked = true;
			checkAnders();
		}
	}
	var param = new URLSearchParams( window.location.search ).get( 'type' );
	if ( param ) {
		kies( param );
	}
	document.addEventListener( 'click', function ( e ) {
		var a = e.target.closest && e.target.closest( '[data-vk-type]' );
		if ( a ) {
			kies( a.getAttribute( 'data-vk-type' ) );
			toon( 0, false );
		}
	} );

	// Na een serverfout: open de eerste stap met een fout.
	var start = 0;
	var fout = form.querySelector( '[aria-invalid="true"]' );
	if ( fout ) {
		start = stappen.indexOf( fout.closest( '.vk-stap' ) );
	}
	toon( Math.max( 0, start ), false );

	form.addEventListener( 'submit', function ( e ) {
		for ( var i = 0; i < stappen.length; i++ ) {
			stappen[ i ].hidden = false;
			if ( ! geldig( stappen[ i ] ) ) {
				e.preventDefault();
				toon( i );
				return;
			}
		}
		var knop = form.querySelector( '[type=submit]' );
		knop.disabled = true;
		knop.textContent = 'Bezig met versturen…';
	} );

	// Bedankt / fouten in beeld brengen.
	var melding = document.querySelector( '.vk-aanvraag__fouten, .vk-aanvraag--bedankt' );
	if ( melding ) {
		melding.focus();
	}
} )();
