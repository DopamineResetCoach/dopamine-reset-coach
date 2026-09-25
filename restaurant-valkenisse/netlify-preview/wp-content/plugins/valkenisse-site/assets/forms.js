/* Formulieren: datum niet in het verleden, vertrek na aankomst, geen dubbele verzending, focus op melding. */
( function () {
	var d = new Date();
	var today = d.getFullYear() + '-' + String( d.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( d.getDate() ).padStart( 2, '0' );
	document.querySelectorAll( '.valk-form input[data-future]' ).forEach( function ( input ) { input.min = today; } );

	document.querySelectorAll( '.valk-form__form' ).forEach( function ( form ) {
		var arrive = form.querySelector( '[name="valk[aankomst]"]' );
		var leave = form.querySelector( '[name="valk[vertrek]"]' );
		if ( arrive && leave ) {
			arrive.addEventListener( 'change', function () {
				if ( ! arrive.value ) return;
				var next = new Date( arrive.value + 'T12:00:00' );
				next.setDate( next.getDate() + 1 );
				leave.min = next.toISOString().slice( 0, 10 );
				if ( leave.value && leave.value < leave.min ) leave.value = '';
			} );
		}
		form.addEventListener( 'submit', function () {
			var btn = form.querySelector( 'button[type="submit"]' );
			if ( btn ) { btn.disabled = true; btn.textContent = 'Bezig met verzenden…'; }
		} );
	} );

	var msg = document.querySelector( '.valk-form__success, .valk-form__error' );
	if ( msg ) msg.focus();
} )();
