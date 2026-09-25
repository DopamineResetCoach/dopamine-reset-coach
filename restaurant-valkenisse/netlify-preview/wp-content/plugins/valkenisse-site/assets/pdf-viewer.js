/*
 * Menukaart-PDF als pagina's op de website (pdf.js, alleen geladen op pagina's met dit blok).
 * Pagina's worden pas getekend als ze in beeld komen; de eerste direct.
 */
import * as pdfjs from './vendor/pdfjs/pdf.min.js';

const MAX_DPR = 2;

async function renderViewer( box ) {
	const pagesEl = box.querySelector( '.valk-pdf__pages' );
	pdfjs.GlobalWorkerOptions.workerSrc = box.dataset.worker;
	let doc;
	try {
		doc = await pdfjs.getDocument( { url: box.dataset.pdf } ).promise;
	} catch ( e ) {
		box.classList.add( 'is-error' );
		return;
	}
	pagesEl.textContent = '';
	const first = await doc.getPage( 1 );
	const base = first.getViewport( { scale: 1 } );

	const draw = async ( n, fig ) => {
		const page = n === 1 ? first : await doc.getPage( n );
		const width = Math.min( fig.clientWidth || pagesEl.clientWidth, 1100 );
		const dpr = Math.min( window.devicePixelRatio || 1, MAX_DPR );
		const viewport = page.getViewport( { scale: ( width / page.getViewport( { scale: 1 } ).width ) * dpr } );
		const canvas = document.createElement( 'canvas' );
		canvas.width = Math.floor( viewport.width );
		canvas.height = Math.floor( viewport.height );
		canvas.setAttribute( 'role', 'img' );
		canvas.setAttribute( 'aria-label', 'Menukaart, pagina ' + n + ' van ' + doc.numPages );
		await page.render( { canvasContext: canvas.getContext( '2d' ), viewport } ).promise;
		fig.replaceChildren( canvas );
		fig.classList.add( 'is-ready' );
	};

	const io = 'IntersectionObserver' in window ? new IntersectionObserver( ( entries ) => {
		entries.forEach( ( entry ) => {
			if ( entry.isIntersecting ) {
				io.unobserve( entry.target );
				draw( Number( entry.target.dataset.page ), entry.target );
			}
		} );
	}, { rootMargin: '600px 0px' } ) : null;

	for ( let n = 1; n <= doc.numPages; n++ ) {
		const fig = document.createElement( 'figure' );
		fig.className = 'valk-pdf__page';
		fig.dataset.page = n;
		fig.style.aspectRatio = base.width + ' / ' + base.height;
		pagesEl.appendChild( fig );
		if ( n === 1 || ! io ) {
			draw( n, fig );
		} else {
			io.observe( fig );
		}
	}
	box.classList.add( 'is-loaded' );
}

document.querySelectorAll( '.valk-pdf[data-pdf]' ).forEach( renderViewer );
