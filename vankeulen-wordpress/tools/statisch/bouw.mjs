#!/usr/bin/env node
/**
 * Bouwt een statische versie van de Van Keulen-website uit een draaiende
 * WordPress-installatie (bijv. `./lokaal/start.sh`).
 *
 *   node tools/statisch/bouw.mjs --bron=http://127.0.0.1:9400 \
 *        --site-url=https://www.vankeulencaravanstalling.nl \
 *        [--formulier-sleutel=<Web3Forms access key>] [--uit=statisch] [--noindex]
 *
 * Resultaat: een map met gewone HTML/CSS/JS/afbeeldingen die op iedere
 * webhost werkt (ook in een submap), zonder PHP, database of updates.
 *
 * - Links worden relatief gemaakt; canonical, Open Graph, sitemap en
 *   structured data gebruiken --site-url.
 * - Het aanvraagformulier verstuurt via Web3Forms (als --formulier-sleutel is
 *   opgegeven) en anders via het e-mailprogramma van de bezoeker (mailto).
 * - WordPress-specifieke onderdelen (REST-links, interactiviteits-modules)
 *   worden verwijderd; het mobiele menu krijgt een eigen klein script.
 *
 * Geen externe npm-pakketten nodig (Node 20+).
 */
import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const HIER = path.dirname(fileURLToPath(import.meta.url));
const args = Object.fromEntries(
	process.argv.slice(2).map((a) => {
		const [k, ...v] = a.replace(/^--/, '').split('=');
		return [k, v.join('=') || true];
	})
);
const BRON = String(args.bron || 'http://127.0.0.1:9400').replace(/\/$/, '');
const SITE = String(args['site-url'] || 'https://www.vankeulencaravanstalling.nl').replace(/\/$/, '');
const SLEUTEL = args['formulier-sleutel'] ? String(args['formulier-sleutel']) : '';
const VOORBEELD = Boolean(args.noindex); // --noindex: voorbeeldlink, niet indexeren
const UIT = path.resolve(String(args.uit || path.join(HIER, '..', '..', 'statisch')));

const PAGINAS = [
	'/',
	'/caravanstalling/',
	'/bootstalling/',
	'/vouwwagen-aanhanger-stalling/',
	'/strandhuisjes-stalling/',
	'/caravanstalling-walcheren/',
	'/over-ons/',
	'/veelgestelde-vragen/',
	'/contact/',
	'/privacybeleid/',
	'/cookiebeleid/',
];
const NOINDEX = new Set(['/privacybeleid/', '/cookiebeleid/', '/contact/bedankt/', '/404.html']);

const bronHost = new URL(BRON).host;
// Alle schrijfwijzen waarin de bron-URL in de HTML kan voorkomen.
const bronVarianten = [BRON + '/', BRON.replace(/\//g, '\\/') + '\\/'];

const assets = new Set();

async function haal(url) {
	const r = await fetch(url, { redirect: 'manual' });
	if (r.status >= 300 && r.status < 400) {
		throw new Error(`Doorverwijzing bij ${url} → ${r.headers.get('location')} (niet ingelogd crawlen?)`);
	}
	return r;
}

function schrijf(rel, data) {
	const doel = path.join(UIT, rel);
	fs.mkdirSync(path.dirname(doel), { recursive: true });
	fs.writeFileSync(doel, data);
}

/** Pad van een pagina naar de root, bijv. '/contact/' → '../'. */
function naarRoot(pagina) {
	const diepte = pagina === '/404.html' ? 0 : pagina.split('/').filter(Boolean).length;
	return diepte ? '../'.repeat(diepte) : './';
}

/** Verwerkt de HTML van één pagina. */
function verwerk(html, pagina) {
	const root = naarRoot(pagina);
	const absoluut = SITE + (pagina === '/404.html' ? '/' : pagina);

	// 1. WordPress-onderdelen die statisch niet werken of niet nodig zijn.
	html = html
		.replace(/<link[^>]+rel=["'](?:https:\/\/api\.w\.org\/|EditURI|alternate)["'][^>]*>\s*/g, '')
		.replace(/<link[^>]+rel=["']alternate["'][^>]*type=["']application\/json["'][^>]*>\s*/g, '')
		.replace(/<script type="importmap"[\s\S]*?<\/script>\s*/g, '')
		.replace(/<script id="wp-load-polyfill-importmap">[\s\S]*?<\/script>\s*/g, '')
		.replace(/<link rel="modulepreload"[^>]*>\s*/g, '')
		.replace(/<script type="module"[^>]*><\/script>\s*/g, '')
		.replace(/<script type="module"[\s\S]*?<\/script>\s*/g, '');

	// Assets verzamelen (src, srcset, href, url(), og:image, …) vóór het herschrijven.
	const re = new RegExp(`${BRON.replace(/[.*+?^${}()|[\]\\/]/g, '\\$&')}(/wp-(?:content|includes)/[^"'\\s)>,]+)`, 'g');
	for (const m of html.matchAll(re)) {
		assets.add(m[1].replace(/&#0?38;|&amp;/g, '&'));
	}

	// 2. Absolute URL's voor zoekmachines en social media.
	const abs = (s) => bronVarianten.reduce((acc, v, i) => acc.split(v).join(i ? SITE.replace(/\//g, '\\/') + '\\/' : SITE + '/'), s);
	html = html
		.replace(/<link rel=["']canonical["'][^>]*>/, `<link rel="canonical" href="${absoluut}">`)
		.replace(/(<meta property="og:(?:url|image)" content=")([^"]+)(")/g, (m, a, b, c) => a + abs(b) + c)
		.replace(/<script type="application\/ld\+json">[\s\S]*?<\/script>/g, (m) => abs(m));
	if (!/rel=["']canonical/.test(html)) {
		html = html.replace('</head>', `<link rel="canonical" href="${absoluut}">\n</head>`);
	}
	if ((VOORBEELD || NOINDEX.has(pagina)) && !/name=['"]robots['"][^>]*noindex/.test(html)) {
		html = html.replace(/<meta name=['"]robots['"][^>]*>/, '').replace('</head>', '<meta name="robots" content="noindex, follow">\n</head>');
	}

	// 3. Resterende bron-URL's relatief maken; ?ver=… van assets verwijderen.
	html = html.replace(new RegExp(`(${BRON.replace(/[.*+?^${}()|[\]\\/]/g, '\\$&')}/wp-(?:content|includes)/[^"'\\s)>,?]+)\\?[^"'\\s)>,]*`, 'g'), '$1');
	html = html.split(BRON + '/').join(root).split(BRON).join(root.replace(/\/$/, '') || '.');

	// Root-relatieve links in de inhoud (/caravanstalling/ e.d.) relatief maken.
	html = html.replace(/(href|action)="\/(?!\/)([^"]*)"/g, (m, attr, rest) => `${attr}="${root}${rest}"`);
	// Links naar mappen: index.html niet nodig op een webserver; wel voor lokaal openen niet. Laat ze zoals ze zijn.

	// 4. Aanvraagformulier omzetten naar statische verzending.
	html = html.replace(/<form class="vk-aanvraag__form"([^>]*)action="[^"]*"([^>]*)>/, (m, a, b) => {
		const endpoint = SLEUTEL ? 'https://api.web3forms.com/submit' : '';
		return `<form class="vk-aanvraag__form"${a}action="${endpoint || 'mailto:info@vankeulencaravanstalling.nl'}"${b} data-vk-statisch data-endpoint="${endpoint}">`;
	});
	html = html
		.replace(/<input type="hidden" name="action" value="vk_aanvraag">/, SLEUTEL ? `<input type="hidden" name="access_key" value="${SLEUTEL}"><input type="hidden" name="subject" value="Nieuwe stallingsaanvraag via de website"><input type="hidden" name="from_name" value="Website Van Keulen Caravanstalling"><input type="hidden" name="redirect" value="${SITE}/contact/bedankt/">` : '')
		.replace(/<input type="hidden" name="vk_t" value="[^"]*">/, '')
		.replace(/<input type="hidden" name="vk_bron" value="[^"]*">/, '');
	// Honeypot heet bij Web3Forms "botcheck".
	html = html.replace('name="website" tabindex="-1"', 'name="botcheck" tabindex="-1"');

	// 5. Eigen scripts voor menu en formulier toevoegen.
	html = html.replace('</body>', `<script src="${root}assets/statisch.js" defer></script>\n</body>`);

	return html;
}

async function main() {
	fs.rmSync(UIT, { recursive: true, force: true });
	fs.mkdirSync(UIT, { recursive: true });

	const lijst = [...PAGINAS.map((p) => [p, p]), ['/contact/?aanvraag=bedankt', '/contact/bedankt/'], ['/deze-pagina-bestaat-niet/', '/404.html']];
	for (const [url, pagina] of lijst) {
		const r = await haal(BRON + url);
		if (!r.ok && pagina !== '/404.html') {
			throw new Error(`${url}: HTTP ${r.status}`);
		}
		let html = await r.text();
		if (pagina === '/contact/bedankt/') {
			html = html.replace(/<title>[^<]*<\/title>/, '<title>Bedankt voor uw aanvraag | Van Keulen Caravanstalling</title>');
		}
		const uit = verwerk(html, pagina);
		schrijf(pagina === '/404.html' ? '404.html' : path.join(pagina, 'index.html'), uit);
		console.log('pagina', pagina);
	}

	// CSS-bestanden kunnen zelf naar lettertypen/afbeeldingen verwijzen.
	const gezien = new Set();
	while ([...assets].some((a) => !gezien.has(a))) {
		for (const a of [...assets]) {
			if (gezien.has(a)) continue;
			gezien.add(a);
			const schoon = a.split('?')[0];
			const r = await haal(BRON + a);
			if (!r.ok) {
				console.warn('ontbreekt', a, r.status);
				continue;
			}
			let data = Buffer.from(await r.arrayBuffer());
			if (schoon.endsWith('.css')) {
				let css = data.toString('utf8');
				for (const m of css.matchAll(/url\((['"]?)([^'")]+)\1\)/g)) {
					if (m[2].startsWith('data:') || m[2].startsWith('http')) continue;
					assets.add(path.posix.normalize(path.posix.join(path.posix.dirname(schoon), m[2].split('?')[0])));
				}
				data = Buffer.from(css);
			}
			schrijf(schoon, data);
		}
	}
	console.log(assets.size, 'bestanden');

	// Versiecode (inhoudshash) achter iedere asset-link: bestanden mogen dan een
	// jaar in de browser-cache blijven, en een nieuwe foto met dezelfde
	// bestandsnaam is toch direct zichtbaar.
	const hashes = new Map();
	const versie = (rel) => {
		if (!hashes.has(rel)) {
			const bestand = path.join(UIT, rel);
			hashes.set(rel, fs.existsSync(bestand) ? crypto.createHash('sha1').update(fs.readFileSync(bestand)).digest('hex').slice(0, 10) : '');
		}
		return hashes.get(rel);
	};
	const htmlBestanden = [];
	(function zoek(map) {
		for (const f of fs.readdirSync(map, { withFileTypes: true })) {
			const vol = path.join(map, f.name);
			if (f.isDirectory()) zoek(vol);
			else if (f.name.endsWith('.html')) htmlBestanden.push(vol);
		}
	})(UIT);
	for (const bestand of htmlBestanden) {
		const html = fs.readFileSync(bestand, 'utf8').replace(/((?:\.\.\/|\.\/)*)(wp-(?:content|includes)\/[^"'\s)>,?]+\.(?:webp|avif|jpe?g|png|svg|woff2|css|js))(?![?\w])/g, (m, pre, rel) => {
			const v = versie(rel);
			return v ? `${pre}${rel}?v=${v}` : m;
		});
		fs.writeFileSync(bestand, html);
	}

	// Eigen script, sitemap, robots, redirects/headers voor Netlify/Cloudflare.
	fs.mkdirSync(path.join(UIT, 'assets'), { recursive: true });
	fs.copyFileSync(path.join(HIER, 'statisch.js'), path.join(UIT, 'assets', 'statisch.js'));
	const vandaag = new Date().toISOString().slice(0, 10);
	schrijf(
		'sitemap.xml',
		`<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${PAGINAS.filter((p) => !NOINDEX.has(p))
			.map((p) => `  <url><loc>${SITE}${p}</loc><lastmod>${vandaag}</lastmod></url>`)
			.join('\n')}\n</urlset>\n`
	);
	schrijf('robots.txt', VOORBEELD ? 'User-agent: *\nDisallow: /\n' : `User-agent: *\nAllow: /\n\nSitemap: ${SITE}/sitemap.xml\n`);
	schrijf(
		'_headers',
		`/*\n  X-Content-Type-Options: nosniff\n  X-Frame-Options: SAMEORIGIN\n  Referrer-Policy: strict-origin-when-cross-origin\n  Permissions-Policy: camera=(), microphone=(), geolocation=()\n\n/wp-content/*\n  Cache-Control: public, max-age=31536000, immutable\n/wp-includes/*\n  Cache-Control: public, max-age=31536000, immutable\n`
	);
	schrijf('_redirects', `/default_bestanden/*  /  301\n`);
	console.log('klaar →', UIT);
}

main().catch((e) => {
	console.error(e.message);
	process.exit(1);
});
