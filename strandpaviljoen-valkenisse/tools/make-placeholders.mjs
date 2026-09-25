// Genereert geïllustreerde placeholder-beelden (SVG) voor het thema.
// Elk beeld vermeldt welke ECHTE foto er moet komen. Vervang ze vóór livegang via de blokeditor.
// Gebruik: node tools/make-placeholders.mjs
import { writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const out = join( dirname( fileURLToPath( import.meta.url ) ), '../wp-content/themes/valkenisse/assets/images' );
mkdirSync( out, { recursive: true } );

const C = {
	navy: '#1C2F41', sea: '#2F6F7E', seaLight: '#BFD8DA', sand: '#EFE4CF', sandLight: '#F7F1E6',
	dune: '#D8C7A3', grass: '#6F7D45', wood: '#8B7B69', sun: '#E9A55C', white: '#FCFAF5',
};

const skies = {
	golden: [ '#F3C98B', '#F6DDB4', '#F8E9CF' ],
	day: [ '#9CC4D0', '#CFE3E6', '#F2EEE3' ],
	sunset: [ '#C9786A', '#EBA86F', '#F6D8A8' ],
	soft: [ '#DCE7E6', '#EEF1EA', '#F7F1E6' ],
};

function sky( w, h, horizon, kind ) {
	const s = skies[ kind ];
	return `<linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${ s[ 0 ] }"/><stop offset=".6" stop-color="${ s[ 1 ] }"/><stop offset="1" stop-color="${ s[ 2 ] }"/></linearGradient>
	<rect width="${ w }" height="${ horizon }" fill="url(#sky)"/>`;
}

function sun( x, y, r, kind ) {
	const col = kind === 'day' ? '#FFF6E0' : '#FFE3B0';
	return `<radialGradient id="glow"><stop offset="0" stop-color="${ col }" stop-opacity=".9"/><stop offset="1" stop-color="${ col }" stop-opacity="0"/></radialGradient>
	<circle cx="${ x }" cy="${ y }" r="${ r * 4 }" fill="url(#glow)"/><circle cx="${ x }" cy="${ y }" r="${ r }" fill="${ col }"/>`;
}

function sea( w, horizon, shore, kind ) {
	const top = kind === 'sunset' ? '#5E7F86' : kind === 'golden' ? '#4F8792' : C.sea;
	let streaks = '';
	for ( let i = 0; i < 14; i++ ) {
		const y = horizon + 6 + ( ( shore - horizon ) * ( i / 14 ) ) ** 1 ;
		const x = ( i * 397 ) % w;
		streaks += `<rect x="${ x }" y="${ y.toFixed( 0 ) }" width="${ 60 + ( i * 37 ) % 180 }" height="2" rx="1" fill="#fff" opacity="${ ( .18 + ( i % 3 ) * .06 ).toFixed( 2 ) }"/>`;
	}
	return `<linearGradient id="sea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${ top }"/><stop offset="1" stop-color="#8FB9BE"/></linearGradient>
	<rect y="${ horizon }" width="${ w }" height="${ shore - horizon }" fill="url(#sea)"/>${ streaks }`;
}

function farCoast( x, w, horizon ) {
	// De overkant van de Westerschelde: een dun, laag lijntje aan de horizon.
	return `<path d="M${ x } ${ horizon } q ${ w * .2 } -9 ${ w * .45 } -7 t ${ w * .55 } -3 v 10 h -${ w } z" fill="#7C9A9E" opacity=".55"/>`;
}

function ship( x, y, s, color = C.navy, opacity = .85 ) {
	// Containerschip / zeeschip in silhouet.
	const c = [ '#B5553F', '#2F6F7E', '#C99A4B', '#6F7D45', '#8B7B69' ];
	let boxes = '';
	for ( let i = 0; i < 9; i++ ) {
		for ( let j = 0; j < 2; j++ ) {
			boxes += `<rect x="${ 34 + i * 17 }" y="${ -22 - j * 9 }" width="16" height="8.5" fill="${ c[ ( i + j * 2 ) % 5 ] }"/>`;
		}
	}
	return `<g transform="translate(${ x } ${ y }) scale(${ s })" opacity="${ opacity }">
		${ boxes }
		<rect x="194" y="-44" width="18" height="36" fill="${ C.white }"/><rect x="190" y="-48" width="26" height="6" fill="${ color }"/><rect x="200" y="-60" width="4" height="12" fill="${ color }"/>
		<path d="M0 -6 H240 L228 10 H14 Z" fill="${ color }"/><rect x="10" y="-6" width="226" height="3" fill="#B5553F"/>
	</g>`;
}

function beach( w, h, shore, kind ) {
	const a = kind === 'golden' || kind === 'sunset' ? '#EBD3AE' : C.sand;
	return `<linearGradient id="beach" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#D9C9A8"/><stop offset=".12" stop-color="${ a }"/><stop offset="1" stop-color="#E6D2AE"/></linearGradient>
	<path d="M0 ${ shore } Q ${ w * .3 } ${ shore - 8 } ${ w * .6 } ${ shore + 4 } T ${ w } ${ shore } V ${ h } H 0 Z" fill="url(#beach)"/>
	<path d="M0 ${ shore + 3 } Q ${ w * .3 } ${ shore - 5 } ${ w * .6 } ${ shore + 7 } T ${ w } ${ shore + 3 }" stroke="#fff" stroke-width="3" fill="none" opacity=".7"/>`;
}

function dunes( w, h, top, side = 'left' ) {
	let grass = '';
	for ( let i = 0; i < 70; i++ ) {
		const x = side === 'left' ? ( i * 13 ) % ( w * .5 ) : w - ( ( i * 13 ) % ( w * .5 ) );
		const baseY = top + 40 + ( ( i * 31 ) % 90 );
		const lean = ( ( i % 5 ) - 2 ) * 4;
		grass += `<path d="M${ x } ${ baseY } q ${ lean } -20 ${ lean * 2 } -${ 28 + ( i % 4 ) * 6 }" stroke="${ i % 3 ? C.grass : '#8E9A63' }" stroke-width="2.2" fill="none" stroke-linecap="round"/>`;
	}
	const d = side === 'left'
		? `M0 ${ top } C ${ w * .15 } ${ top - 40 } ${ w * .3 } ${ top + 10 } ${ w * .5 } ${ top + 120 } L ${ w * .5 } ${ h } H 0 Z`
		: `M${ w } ${ top } C ${ w * .85 } ${ top - 40 } ${ w * .7 } ${ top + 10 } ${ w * .5 } ${ top + 120 } L ${ w * .5 } ${ h } H ${ w } Z`;
	return `<path d="${ d }" fill="#D5C29A"/><path d="${ d }" fill="#C9B386" opacity=".35" transform="translate(0 30)"/>${ grass }`;
}

function pavilion( x, y, s ) {
	// Wit paviljoen met golfplaatdak, terras op palen met glazen windschermen en parasols (naar de echte foto).
	let ribs = '';
	for ( let i = 0; i < 40; i++ ) {
		ribs += `<rect x="${ -10 + i * 11 }" y="-205" width="1.5" height="52" fill="#A9B1B6" opacity=".6"/>`;
	}
	let glass = '';
	for ( let i = 0; i < 16; i++ ) {
		glass += `<rect x="${ -200 + i * 40 }" y="-44" width="38" height="36" fill="#E3EEF0" opacity=".55" stroke="#fff" stroke-width="2"/>`;
	}
	let piles = '';
	for ( let i = 0; i < 9; i++ ) {
		piles += `<rect x="${ -196 + i * 78 }" y="0" width="9" height="70" fill="#8B7B69"/>`;
	}
	let umbrellas = '';
	[ -160, -80, 0, 80, 160, 240, 330 ].forEach( ( ux, i ) => {
		umbrellas += `<rect x="${ ux + 38 }" y="-86" width="3" height="46" fill="#6E604F"/><path d="M${ ux } -84 Q ${ ux + 40 } -112 ${ ux + 80 } -84 Z" fill="${ i % 2 ? '#fff' : '#2E6E5A' }"/><path d="M${ ux + 30 } -88 l 10 -12 l 10 12 z" fill="${ i % 2 ? '#C8412F' : '#fff' }" opacity=".8"/>`;
	} );
	return `<g transform="translate(${ x } ${ y }) scale(${ s })">
		${ piles }
		<rect x="-210" y="-8" width="660" height="14" fill="#fff"/><rect x="-210" y="6" width="660" height="6" fill="#8B7B69"/>
		<rect x="0" y="-160" width="420" height="120" fill="#F7F7F2"/>
		<path d="M-20 -150 L200 -210 L440 -150 Z" fill="#C9CFD2"/>${ ribs }
		<rect x="20" y="-135" width="380" height="60" fill="#E9C58B" opacity=".75"/>
		${ Array.from( { length: 10 }, ( _, i ) => `<rect x="${ 20 + i * 38 }" y="-135" width="3" height="60" fill="#fff"/>` ).join( '' ) }
		${ umbrellas }
		${ glass }
		<rect x="-210" y="-46" width="660" height="4" fill="#fff"/>
		<rect x="-190" y="-150" width="3" height="110" fill="#fff"/><path d="M-187 -150 l 40 10 l -40 12 z" fill="#B5553F"/>
	</g>`;
}

function huts( x, y, s, n = 7 ) {
	const cols = [ '#E8C02E', '#E3B928', '#EBC534', '#E8C02E', '#DDB322', '#E8C02E', '#EBC534' ];
	let g = '';
	for ( let i = 0; i < n; i++ ) {
		const hx = i * 118;
		const col = cols[ i % cols.length ];
		g += `<g transform="translate(${ hx } 0)">
			<rect x="0" y="-90" width="100" height="90" fill="${ col }"/>
			<rect x="-4" y="-96" width="108" height="10" fill="#2E5E4A"/>
			<rect x="0" y="-86" width="6" height="86" fill="#2E5E4A"/><rect x="94" y="-86" width="6" height="86" fill="#2E5E4A"/>
			<rect x="4" y="-86" width="92" height="3" fill="#fff" opacity=".6"/>
			<g opacity=".9"><rect x="${ 104 }" y="-24" width="4" height="24" fill="#6E604F"/></g>
		</g>`;
	}
	return `<g transform="translate(${ x } ${ y }) scale(${ s })">${ g }</g>`;
}

function chairs( x, y, s, n = 5 ) {
	let g = '';
	for ( let i = 0; i < n; i++ ) {
		const cx = i * 130;
		const c = [ C.sea, C.white, C.sun, C.navy, '#C9D0B0' ][ i % 5 ];
		g += `<g transform="translate(${ cx } 0)">
			<path d="M0 0 L22 -46 L30 -46 L8 0 Z" fill="#6E604F"/><path d="M40 0 L22 -46" stroke="#6E604F" stroke-width="4"/>
			<path d="M8 -6 L26 -44 L46 -38 L36 -4 Z" fill="${ c }"/>
			${ i % 2 ? `<rect x="58" y="-110" width="3" height="110" fill="#6E604F"/><path d="M0 -104 Q 60 -150 120 -104 Z" fill="${ i % 4 === 1 ? C.white : C.sea }"/>` : '' }
		</g>`;
	}
	return `<g transform="translate(${ x } ${ y }) scale(${ s })">${ g }</g>`;
}

function windscreen( x, y, s ) {
	let g = '';
	const stripes = [ C.sea, C.white, C.sea, C.white, C.sea, C.white, C.sea ];
	stripes.forEach( ( c, i ) => { g += `<rect x="${ i * 34 }" y="-70" width="34" height="62" fill="${ c }"/>`; } );
	for ( let i = 0; i <= 7; i++ ) { g += `<rect x="${ i * 34 - 2 }" y="-78" width="4" height="82" fill="#6E604F"/>`; }
	return `<g transform="translate(${ x } ${ y }) scale(${ s }) skewY(-3)">${ g }</g>`;
}

function table( w, h, top, kind = 'drinks' ) {
	// Tafelblad op de voorgrond met drankjes of gerechten.
	let items = '';
	if ( kind === 'drinks' || kind === 'both' ) {
		items += `<g transform="translate(${ w * .58 } ${ top + 30 })">
			<path d="M0 -150 L70 -150 L62 0 L8 0 Z" fill="#F2C36B" opacity=".92"/><path d="M0 -150 L70 -150 L68 -120 L2 -120 Z" fill="#fff" opacity=".9"/>
			<circle cx="60" cy="-155" r="16" fill="#F0DA73"/><rect x="52" y="-200" width="4" height="70" fill="#fff" transform="rotate(12 54 -165)"/>
		</g>
		<g transform="translate(${ w * .7 } ${ top + 36 })">
			<path d="M0 -120 L55 -120 L50 0 L5 0 Z" fill="#E7F0EE" opacity=".8"/><path d="M4 -80 L51 -80 L50 0 L5 0 Z" fill="#CFE3E0" opacity=".9"/>
		</g>`;
	}
	if ( kind === 'food' || kind === 'both' ) {
		items += `<g transform="translate(${ w * .3 } ${ top + 70 })">
			<ellipse cx="0" cy="0" rx="190" ry="60" fill="#fff"/><ellipse cx="0" cy="0" rx="150" ry="44" fill="#F7F4EC"/>
			<ellipse cx="-40" cy="-6" rx="70" ry="22" fill="#E3B26A"/><ellipse cx="45" cy="-2" rx="55" ry="20" fill="#9DB26A"/><circle cx="20" cy="-18" r="12" fill="#D9573F"/><circle cx="-5" cy="-20" r="9" fill="#F2E3B5"/>
		</g>
		<g transform="translate(${ w * .1 } ${ top + 40 })"><ellipse cx="0" cy="0" rx="80" ry="26" fill="#fff"/><ellipse cx="0" cy="-4" rx="50" ry="14" fill="#E9C58B"/></g>`;
	}
	return `<linearGradient id="table" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#B7A68C"/><stop offset="1" stop-color="#8B7B69"/></linearGradient>
	<path d="M0 ${ top } L${ w } ${ top - 20 } V ${ h } H 0 Z" fill="url(#table)"/>
	${ Array.from( { length: 8 }, ( _, i ) => `<path d="M0 ${ top + 20 + i * 38 } L${ w } ${ top + i * 38 }" stroke="#6E604F" stroke-width="1.5" opacity=".35"/>` ).join( '' ) }
	${ items }`;
}

function railing( w, y ) {
	let posts = '';
	for ( let x = 0; x < w; x += 40 ) { posts += `<rect x="${ x }" y="${ y }" width="5" height="160" fill="${ C.white }" opacity=".95"/>`; }
	return `<rect x="0" y="${ y - 8 }" width="${ w }" height="12" fill="${ C.white }"/>${ posts }<rect y="${ y + 150 }" width="${ w }" height="200" fill="#9C8B76"/>`;
}

function people( x, y, s ) {
	return `<g transform="translate(${ x } ${ y }) scale(${ s })" fill="${ C.navy }" opacity=".85">
		<circle cx="0" cy="-92" r="12"/><path d="M-14 -78 h28 l6 40 h-10 l-2 38 h-16 l-2 -38 h-10 z"/>
		<circle cx="42" cy="-80" r="10"/><path d="M30 -68 h24 l5 34 h-8 l-2 34 h-14 l-2 -34 h-8 z"/>
		<circle cx="74" cy="-56" r="8"/><path d="M65 -46 h18 l4 24 h-6 l-1 22 h-12 l-1 -22 h-6 z"/>
	</g>`;
}

function label( w, h, text ) {
	// Klein label rechtsboven (valt niet over koppen en knoppen heen).
	const fs = Math.max( 13, Math.round( w / 110 ) );
	const pad = Math.round( fs * 1.2 );
	const tw = text.length * fs * .55 + fs * 2;
	const x = w - tw - pad;
	const y = Math.round( h * .18 );
	return `<g font-family="Figtree, Arial, sans-serif" font-size="${ fs }" font-weight="600">
		<rect x="${ x.toFixed( 0 ) }" y="${ y }" width="${ tw.toFixed( 0 ) }" height="${ ( fs * 2.1 ).toFixed( 0 ) }" rx="${ fs * 1.05 }" fill="#1C2F41" opacity=".6"/>
		<text x="${ ( x + fs ).toFixed( 0 ) }" y="${ ( y + fs * 1.42 ).toFixed( 0 ) }" fill="#FCFAF5">${ text }</text></g>`;
}

function svg( name, w, h, body, text, extraDefs = '' ) {
	const content = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${ w } ${ h }" width="${ w }" height="${ h }" preserveAspectRatio="xMidYMid slice" role="img"><title>${ text }</title><defs>${ extraDefs }</defs>${ body }${ label( w, h, 'FOTO AANLEVEREN · ' + text ) }</svg>`;
	writeFileSync( join( out, name ), content.replace( /\s{2,}/g, ' ' ) );
}

const W = 1600, H = 1000;

// 1. Hero: paviljoen vanaf het strand, golden hour, schip op zee.
svg( 'hero-paviljoen.svg', 1920, 1200,
	sky( 1920, 560, 560, 'golden' ) + sun( 1380, 420, 46, 'golden' ) + sea( 1920, 560, 760, 'golden' ) + farCoast( 1150, 770, 560 ) +
	ship( 1250, 552, 1.25, '#34475A', .8 ) + beach( 1920, 1200, 760, 'golden' ) + dunes( 1920, 1200, 520, 'left' ) +
	huts( 20, 690, .55, 6 ) + pavilion( 520, 820, 1.35 ) + `<g transform="translate(1330 900)">${ [ 0, 1, 2 ].map( ( i ) => `<rect x="${ i * 60 }" y="${ -i * 4 }" width="52" height="70" rx="4" fill="#C8322B"/>` ).join( '' ) }</g>` + people( 1640, 1080, 1.1 ),
	'Hero: paviljoen + terras vanaf het strand, golden hour, zeeschip' );

// 2. Historie 1956 (sepia).
svg( 'historie-1956.svg', 1000, 1250,
	`<g filter="url(#sepia)">` + sky( 1000, 560, 560, 'soft' ) + sea( 1000, 560, 760, 'day' ) + beach( 1000, 1250, 760, 'day' ) + dunes( 1000, 1250, 480, 'left' ) +
	`<g transform="translate(300 820) scale(1.1)"><rect x="0" y="-120" width="300" height="120" fill="#9C8B76"/><path d="M-20 -120 L150 -170 L320 -120 Z" fill="#4A4A48"/><rect x="30" y="-90" width="60" height="40" fill="#EFE6D3"/><rect x="200" y="-90" width="60" height="40" fill="#EFE6D3"/><rect x="120" y="-70" width="50" height="70" fill="#6E604F"/></g>` +
	people( 700, 900, 1.2 ) + `</g><rect width="1000" height="1250" fill="url(#grain)" opacity=".25"/>`,
	'Historische foto (1956 of vroeg)',
	`<filter id="sepia"><feColorMatrix type="matrix" values=".39 .77 .19 0 0  .35 .69 .17 0 0  .27 .53 .13 0 0  0 0 0 1 0"/></filter>
	<pattern id="grain" width="4" height="4" patternUnits="userSpaceOnUse"><rect width="1" height="1" fill="#000"/><rect x="2" y="2" width="1" height="1" fill="#fff"/></pattern>` );

// 3. Paviljoen vandaag.
svg( 'paviljoen-vandaag.svg', 1000, 1250,
	sky( 1000, 600, 600, 'day' ) + sun( 820, 200, 34, 'day' ) + sea( 1000, 600, 780, 'day' ) + beach( 1000, 1250, 780, 'day' ) + dunes( 1000, 1250, 520, 'right' ) +
	pavilion( 180, 900, 1.1 ) + chairs( 120, 1150, .8, 5 ),
	'Het vernieuwde paviljoen vandaag' );

// 4. Eten & drinken op het terras.
svg( 'eten-terras.svg', W, H,
	sky( W, 420, 420, 'golden' ) + sun( 1200, 330, 36, 'golden' ) + sea( W, 420, 560, 'golden' ) + ship( 260, 414, .9, '#34475A', .7 ) + beach( W, H, 560, 'golden' ) +
	table( W, H, 640, 'both' ),
	'Gerechten en drankjes op het terras, zee op de achtergrond' );

// 5. Drankje.
svg( 'drankje.svg', 1000, 1250,
	sky( 1000, 560, 560, 'sunset' ) + sun( 300, 470, 40, 'sunset' ) + sea( 1000, 560, 700, 'sunset' ) + beach( 1000, 1250, 700, 'sunset' ) +
	table( 1000, 1250, 880, 'drinks' ),
	'Koud drankje op het terras' );

// 6. Uitzicht met passerend schip.
svg( 'uitzicht-schip.svg', 1920, 1100,
	sky( 1920, 600, 600, 'day' ) + sun( 400, 180, 40, 'day' ) + sea( 1920, 600, 1100, 'day' ) + farCoast( 1100, 820, 600 ) +
	ship( 520, 640, 3.2, '#22384C', .95 ) + railing( 1920, 900 ),
	'Uitzicht vanaf het terras met groot zeeschip vlak langs de kust' );

// 7. Strandhuisjes.
svg( 'strandhuisjes.svg', W, H,
	sky( W, 430, 430, 'day' ) + sun( 1350, 150, 34, 'day' ) + sea( W, 430, 560, 'day' ) + beach( W, H, 560, 'day' ) +
	huts( 60, 800, 1.55, 9 ) + chairs( 200, 950, .8, 5 ),
	'De strandhuisjes van Valkenisse' );

// 8. Strandverhuur: stoelen, ligbedden, parasols, windschermen.
svg( 'strandstoelen.svg', 1200, 1200,
	sky( 1200, 480, 480, 'day' ) + sea( 1200, 480, 620, 'day' ) + beach( 1200, 1200, 620, 'day' ) + chairs( 120, 950, 1.6, 5 ),
	'Strandstoelen' );
svg( 'ligbedden.svg', 1200, 1200,
	sky( 1200, 480, 480, 'golden' ) + sea( 1200, 480, 620, 'golden' ) + beach( 1200, 1200, 620, 'golden' ) +
	`<g transform="translate(120 920)">${ [ 0, 1, 2 ].map( ( i ) => `<g transform="translate(${ i * 330 } 0)"><path d="M0 0 L240 0 L260 -30 L20 -30 Z" fill="${ i % 2 ? C.white : C.sea }"/><path d="M200 -30 L250 -110 L270 -100 L240 -30 Z" fill="${ i % 2 ? C.white : C.sea }"/><rect x="20" y="0" width="6" height="40" fill="#6E604F"/><rect x="230" y="0" width="6" height="40" fill="#6E604F"/></g>` ).join( '' ) }</g>`,
	'Ligbedden' );
svg( 'parasols.svg', 1200, 1200,
	sky( 1200, 480, 480, 'day' ) + sun( 950, 160, 30, 'day' ) + sea( 1200, 480, 620, 'day' ) + beach( 1200, 1200, 620, 'day' ) +
	`<g>${ [ [ 250, 900, C.sea ], [ 620, 980, C.white ], [ 960, 880, C.sun ] ].map( ( [ x, y, c ] ) => `<rect x="${ x }" y="${ y - 300 }" width="8" height="300" fill="#6E604F"/><path d="M${ x - 170 } ${ y - 290 } Q ${ x + 4 } ${ y - 440 } ${ x + 178 } ${ y - 290 } Z" fill="${ c }"/>` ).join( '' ) }</g>`,
	'Parasols' );
svg( 'windschermen.svg', 1200, 1200,
	sky( 1200, 480, 480, 'soft' ) + sea( 1200, 480, 620, 'day' ) + beach( 1200, 1200, 620, 'day' ) + windscreen( 140, 960, 3.2 ) + chairs( 220, 1080, .9, 3 ),
	'Windschermen' );

// 9. Duinen / strand van Valkenisse.
svg( 'duinen-valkenisse.svg', 1920, 1100,
	sky( 1920, 520, 520, 'soft' ) + sun( 1500, 200, 36, 'day' ) + sea( 1920, 520, 700, 'day' ) + ship( 1300, 514, .8, '#34475A', .6 ) + beach( 1920, 1100, 700, 'day' ) +
	dunes( 1920, 1100, 380, 'right' ) + people( 600, 900, .9 ),
	'Het lange strand en de hoge duinen van Valkenisse' );

// 10. Zonsondergang.
svg( 'zonsondergang.svg', W, H,
	sky( W, 560, 560, 'sunset' ) + sun( 800, 520, 60, 'sunset' ) + sea( W, 560, 760, 'sunset' ) + ship( 1050, 552, 1, '#2A2F3A', .85 ) + beach( W, H, 760, 'sunset' ) + people( 300, 900, 1 ),
	'Zonsondergang vanaf het strand' );

// 11. Familie / team.
svg( 'familie.svg', 1000, 1250,
	sky( 1000, 620, 620, 'golden' ) + sea( 1000, 620, 800, 'golden' ) + beach( 1000, 1250, 800, 'golden' ) + people( 330, 1080, 3 ),
	'Familie Herwegh / het team' );

// 12. Terras met gasten.
svg( 'terras-gasten.svg', W, H,
	sky( W, 420, 420, 'golden' ) + sun( 300, 300, 34, 'golden' ) + sea( W, 420, 560, 'golden' ) + beach( W, H, 560, 'golden' ) +
	chairs( 160, 840, 1.3, 7 ) + people( 1100, 900, 1.4 ),
	'Gasten op het terras' );

console.log( 'Placeholders geschreven naar', out );
