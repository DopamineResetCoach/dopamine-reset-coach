/**
 * Van Keulen – statische versie: mobiel menu en verzenden van de aanvraag.
 * (In WordPress doen de kern en de plugin dit; statisch neemt dit script het over.)
 */
( function () {
	'use strict';

	/* ---- Menu -------------------------------------------------------- */
	document.querySelectorAll( '.wp-block-navigation' ).forEach( function ( nav ) {
		var open = nav.querySelector( '.wp-block-navigation__responsive-container-open' );
		var paneel = nav.querySelector( '.wp-block-navigation__responsive-container' );
		var sluit = nav.querySelector( '.wp-block-navigation__responsive-container-close' );
		if ( ! open || ! paneel ) {
			return;
		}
		function zet( aan ) {
			paneel.classList.toggle( 'is-menu-open', aan );
			paneel.classList.toggle( 'has-modal-open', aan );
			document.documentElement.classList.toggle( 'has-modal-open', aan );
			open.setAttribute( 'aria-expanded', aan ? 'true' : 'false' );
			var dialoog = paneel.querySelector( '.wp-block-navigation__responsive-dialog' );
			if ( dialoog ) {
				if ( aan ) {
					dialoog.setAttribute( 'role', 'dialog' );
					dialoog.setAttribute( 'aria-modal', 'true' );
				} else {
					dialoog.removeAttribute( 'role' );
					dialoog.removeAttribute( 'aria-modal' );
				}
			}
			( aan ? sluit : open ).focus();
		}
		open.addEventListener( 'click', function () {
			zet( true );
		} );
		if ( sluit ) {
			sluit.addEventListener( 'click', function () {
				zet( false );
			} );
		}
		paneel.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( 'a' ) && paneel.classList.contains( 'is-menu-open' ) ) {
				zet( false );
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && paneel.classList.contains( 'is-menu-open' ) ) {
				zet( false );
			}
		} );
		nav.querySelectorAll( '.wp-block-navigation-submenu__toggle' ).forEach( function ( knop ) {
			knop.addEventListener( 'click', function () {
				knop.setAttribute( 'aria-expanded', knop.getAttribute( 'aria-expanded' ) === 'true' ? 'false' : 'true' );
			} );
		} );
	} );

	/* ---- Aanvraagformulier ------------------------------------------- */
	var form = document.querySelector( '[data-vk-statisch]' );
	if ( ! form ) {
		return;
	}
	var ontvanger = 'info@vankeulencaravanstalling.nl';
	var labels = {
		type: 'Object',
		type_anders: 'Omschrijving',
		merk: 'Merk / type',
		lengte: 'Lengte (m)',
		breedte: 'Breedte (m)',
		hoogte: 'Hoogte (m)',
		vanaf: 'Vanaf',
		periode: 'Periode',
		voorkeur: 'Voorkeur',
		naam: 'Naam',
		email: 'E-mail',
		telefoon: 'Telefoon',
		woonplaats: 'Woonplaats',
		opmerking: 'Opmerking',
	};

	function waarde( naam ) {
		var gekozen = form.querySelector( '[name="' + naam + '"]:checked' );
		if ( gekozen ) {
			var span = gekozen.parentNode.querySelector( 'span' );
			return span ? span.textContent.trim() : gekozen.value;
		}
		var veld = form.querySelector( '[name="' + naam + '"]:not([type=radio])' );
		return veld ? veld.value.trim() : '';
	}

	function bedankt( tekst ) {
		var blok = form.closest( '.vk-aanvraag' );
		blok.classList.add( 'vk-aanvraag--bedankt' );
		blok.innerHTML =
			'<div class="vk-aanvraag__bedankt" role="status" tabindex="-1">' +
			'<h2>Bedankt voor uw aanvraag</h2><p>' + tekst + '</p></div>';
		blok.querySelector( '.vk-aanvraag__bedankt' ).focus();
	}

	form.addEventListener( 'submit', function ( e ) {
		if ( e.defaultPrevented ) {
			return; // Validatie in aanvraag.js heeft het verzenden al tegengehouden.
		}
		e.preventDefault();
		var knop = form.querySelector( '[type=submit]' );
		var botcheck = form.querySelector( '[name=botcheck]' );
		if ( botcheck && botcheck.value ) {
			bedankt( 'Van Keulen Caravanstalling neemt contact met u op over de mogelijkheden.' );
			return;
		}

		var endpoint = form.getAttribute( 'data-endpoint' );
		if ( endpoint ) {
			var data = new FormData( form );
			Object.keys( labels ).forEach( function ( k ) {
				if ( data.has( k ) ) {
					data.set( k, waarde( k ) );
				}
			} );
			data.set( 'subject', 'Stallingsaanvraag: ' + waarde( 'type' ) + ' – ' + waarde( 'naam' ) );
			data.set( 'replyto', waarde( 'email' ) );
			data.delete( 'redirect' );
			fetch( endpoint, { method: 'POST', body: data, headers: { Accept: 'application/json' } } )
				.then( function ( r ) {
					return r.json();
				} )
				.then( function ( res ) {
					if ( res && res.success ) {
						bedankt( 'Van Keulen Caravanstalling neemt contact met u op over de mogelijkheden.' );
					} else {
						throw new Error( 'mislukt' );
					}
				} )
				.catch( function () {
					perMail();
				} );
			return;
		}
		perMail();

		function perMail() {
			var regels = [ 'Stallingsaanvraag via de website', '' ];
			Object.keys( labels ).forEach( function ( k ) {
				var w = waarde( k );
				if ( w ) {
					regels.push( labels[ k ] + ': ' + w );
				}
			} );
			var url =
				'mailto:' + ontvanger +
				'?subject=' + encodeURIComponent( 'Stallingsaanvraag: ' + waarde( 'type' ) + ' – ' + waarde( 'naam' ) ) +
				'&body=' + encodeURIComponent( regels.join( '\n' ) );
			window.location.href = url;
			if ( knop ) {
				knop.disabled = false;
				knop.textContent = 'Verstuur mijn aanvraag';
			}
			bedankt(
				'Uw e-mailprogramma is geopend met uw aanvraag. Klik daar op <strong>Verzenden</strong> om de aanvraag af te ronden. ' +
				'Opent er niets? Mail dan naar <a href="mailto:' + ontvanger + '">' + ontvanger + '</a> of bel <a href="tel:+31118639869">0118-639869</a>.'
			);
		}
	} );
} )();
