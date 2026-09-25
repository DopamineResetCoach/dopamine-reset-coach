/* Galerij: filteren per categorie + lichtgewicht lightbox (native <dialog>, geen bibliotheek). */
( function () {
	document.querySelectorAll( '[data-vk-gallery]' ).forEach( function ( gallery ) {
		var items = Array.prototype.slice.call( gallery.querySelectorAll( '.vk-gallery__item' ) );
		var dialog = gallery.querySelector( '.vk-lightbox' );
		var img = dialog.querySelector( 'img' );
		var caption = dialog.querySelector( 'figcaption' );
		var current = 0;

		gallery.querySelectorAll( '[data-filter]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var filter = button.getAttribute( 'data-filter' );
				gallery.querySelectorAll( '[data-filter]' ).forEach( function ( b ) {
					b.classList.toggle( 'is-active', b === button );
					b.setAttribute( 'aria-pressed', b === button ? 'true' : 'false' );
				} );
				items.forEach( function ( item ) {
					var cats = ( item.getAttribute( 'data-cats' ) || '' ).split( ' ' );
					item.hidden = filter !== '*' && cats.indexOf( filter ) === -1;
				} );
			} );
		} );

		function visible() {
			return items.filter( function ( item ) { return ! item.hidden; } );
		}
		function show( index ) {
			var list = visible();
			if ( ! list.length ) { return; }
			current = ( index + list.length ) % list.length;
			var link = list[ current ].querySelector( 'a' );
			var thumb = link.querySelector( 'img' );
			img.src = link.href;
			img.alt = thumb ? thumb.alt : '';
			caption.textContent = link.getAttribute( 'data-caption' ) || '';
		}

		items.forEach( function ( item ) {
			item.querySelector( 'a' ).addEventListener( 'click', function ( e ) {
				if ( typeof dialog.showModal !== 'function' ) { return; }
				e.preventDefault();
				show( visible().indexOf( item ) );
				dialog.showModal();
			} );
		} );

		dialog.querySelector( '.vk-lightbox__close' ).addEventListener( 'click', function () { dialog.close(); } );
		dialog.querySelector( '.vk-lightbox__prev' ).addEventListener( 'click', function () { show( current - 1 ); } );
		dialog.querySelector( '.vk-lightbox__next' ).addEventListener( 'click', function () { show( current + 1 ); } );
		dialog.addEventListener( 'click', function ( e ) { if ( e.target === dialog ) { dialog.close(); } } );
		dialog.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' ) { show( current - 1 ); }
			if ( e.key === 'ArrowRight' ) { show( current + 1 ); }
		} );

		// Vegen op mobiel.
		var startX = null;
		dialog.addEventListener( 'touchstart', function ( e ) { startX = e.touches[ 0 ].clientX; }, { passive: true } );
		dialog.addEventListener( 'touchend', function ( e ) {
			if ( startX === null ) { return; }
			var dx = e.changedTouches[ 0 ].clientX - startX;
			if ( Math.abs( dx ) > 50 ) { show( current + ( dx < 0 ? 1 : -1 ) ); }
			startX = null;
		} );
	} );
} )();
