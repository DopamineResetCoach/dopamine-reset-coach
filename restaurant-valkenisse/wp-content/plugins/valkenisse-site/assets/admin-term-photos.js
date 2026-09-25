/* Foto's kiezen bij een menukaartcategorie (mediabibliotheek). */
jQuery( function ( $ ) {
	var frame;
	$( '#valk-fotos-kies' ).on( 'click', function ( e ) {
		e.preventDefault();
		if ( ! frame ) {
			frame = wp.media( { title: "Foto's voor deze categorie", button: { text: 'Gebruiken' }, multiple: true, library: { type: 'image' } } );
			frame.on( 'select', function () {
				var sel = frame.state().get( 'selection' ).toJSON().slice( 0, 3 );
				$( '#valk-fotos' ).val( sel.map( function ( a ) { return a.id; } ).join( ',' ) );
				$( '#valk-fotos-preview' ).empty();
				sel.forEach( function ( a ) {
					var url = ( a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail : a ).url;
					$( '<img>', { src: url, alt: '' } ).css( { width: 90, height: 90, objectFit: 'cover' } ).appendTo( '#valk-fotos-preview' );
				} );
			} );
		}
		frame.open();
	} );
	$( '#valk-fotos-leeg' ).on( 'click', function () {
		$( '#valk-fotos' ).val( '' );
		$( '#valk-fotos-preview' ).empty();
	} );
} );
