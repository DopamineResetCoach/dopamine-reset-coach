/* Galerij: filterknoppen en een toegankelijke lightbox (native <dialog>, ~2 kB). */
( function () {
	document.querySelectorAll( '.valk-gallery' ).forEach( function ( gallery ) {
		var buttons = gallery.querySelectorAll( '.valk-gallery__filter button' );
		var items = Array.prototype.slice.call( gallery.querySelectorAll( '.valk-gallery__item' ) );

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var f = btn.getAttribute( 'data-filter' );
				buttons.forEach( function ( b ) { b.setAttribute( 'aria-pressed', b === btn ? 'true' : 'false' ); } );
				items.forEach( function ( item ) {
					item.hidden = f !== '*' && ( ' ' + item.getAttribute( 'data-cats' ) + ' ' ).indexOf( ' ' + f + ' ' ) === -1;
				} );
			} );
		} );

		if ( typeof HTMLDialogElement !== 'function' ) return; // Oudere browsers: link opent de foto.

		var dialog = document.createElement( 'dialog' );
		dialog.className = 'valk-lightbox';
		dialog.setAttribute( 'aria-label', 'Foto' );
		dialog.innerHTML = '<figure><img alt=""><figcaption></figcaption></figure>' +
			'<button type="button" class="valk-lightbox__close" aria-label="Sluiten">×</button>' +
			'<button type="button" class="valk-lightbox__prev" aria-label="Vorige foto">‹</button>' +
			'<button type="button" class="valk-lightbox__next" aria-label="Volgende foto">›</button>';
		document.body.appendChild( dialog );
		var img = dialog.querySelector( 'img' );
		var cap = dialog.querySelector( 'figcaption' );
		var current = 0;

		function visible() { return items.filter( function ( i ) { return ! i.hidden; } ); }
		function show( index ) {
			var list = visible();
			if ( ! list.length ) return;
			current = ( index + list.length ) % list.length;
			var link = list[ current ].querySelector( 'a' );
			var thumb = link.querySelector( 'img' );
			img.src = link.href;
			img.alt = thumb ? thumb.alt : '';
			cap.textContent = link.getAttribute( 'data-caption' ) || '';
		}

		gallery.addEventListener( 'click', function ( e ) {
			var link = e.target.closest( '.valk-gallery__item a' );
			if ( ! link ) return;
			e.preventDefault();
			show( visible().indexOf( link.parentNode ) );
			dialog.showModal();
		} );
		dialog.querySelector( '.valk-lightbox__close' ).addEventListener( 'click', function () { dialog.close(); } );
		dialog.querySelector( '.valk-lightbox__prev' ).addEventListener( 'click', function () { show( current - 1 ); } );
		dialog.querySelector( '.valk-lightbox__next' ).addEventListener( 'click', function () { show( current + 1 ); } );
		dialog.addEventListener( 'click', function ( e ) { if ( e.target === dialog ) dialog.close(); } );
		dialog.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' ) show( current - 1 );
			if ( e.key === 'ArrowRight' ) show( current + 1 );
		} );
		var startX = null;
		dialog.addEventListener( 'touchstart', function ( e ) { startX = e.touches[ 0 ].clientX; }, { passive: true } );
		dialog.addEventListener( 'touchend', function ( e ) {
			if ( startX === null ) return;
			var dx = e.changedTouches[ 0 ].clientX - startX;
			if ( Math.abs( dx ) > 40 ) show( current + ( dx < 0 ? 1 : -1 ) );
			startX = null;
		} );
	} );
} )();
