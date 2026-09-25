// Gebruik: node tools/export-netlify.js <uitvoermap>  (vereist een draaiende lokale WordPress op http://localhost:8080 en Node 18+)
// Statische export van de lokale WordPress-site voor Netlify.
const fs = require('fs'), path = require('path');
const BASE = 'http://localhost:8080';
const OUT = process.argv[2];
const pages = ['/', '/restaurant/', '/menukaart/', '/overnachten/', '/feesten-partijen/', '/galerij/', '/contact/', '/reserveren/', '/privacybeleid/', '/cookiebeleid/', '/nieuws/'];
const assets = new Set();
const BANNER = '<div style="position:relative;z-index:200;background:#E6DAC6;color:#18271F;font:600 12px/1.4 Manrope,system-ui,sans-serif;text-align:center;padding:6px 12px;letter-spacing:.02em">Ontwerpvoorstel – dit is nog niet de officiële website van Restaurant Valkenisse. Formulieren worden in deze preview niet verzonden.</div>';
const save = (rel, buf) => { const f = path.join(OUT, decodeURIComponent(rel.split('?')[0])); fs.mkdirSync(path.dirname(f), { recursive: true }); fs.writeFileSync(f, buf); };
const localize = (s) => s.split(BASE).join('').split(BASE.replace(/\//g, '\\/')).join('');
function collect(text) {
  for (const m of text.matchAll(/(?:https?:)?(?:\\\/\\\/|\/\/)localhost:8080((?:\\\/|\/)(?:wp-content|wp-includes)[^"'\s)<>,]+)/g)) assets.add(m[1].replace(/\\\//g, '/').split('?')[0].replace(/&#0?38;|&amp;/g, '&'));
}
(async () => {
  fs.rmSync(OUT, { recursive: true, force: true });
  for (const p of pages) {
    let html = await (await fetch(BASE + p)).text();
    collect(html);
    for (const m of html.matchAll(/srcset="([^"]+)"/g)) for (const part of m[1].split(',')) collect(part.trim().split(' ')[0]);
    html = localize(html);
    // Formulieren: niet verzenden in de preview.
    html = html.replace(/action="\/wp-admin\/admin-post\.php"/g, 'action="#" onsubmit="event.preventDefault();var b=this.querySelector(\'button[type=submit]\');if(b){b.disabled=true;b.textContent=\'Preview: formulier wordt niet verzonden\';}"');
    html = html.replace(/<link[^>]+(wp-json|oembed|api\.w\.org|EditURI)[^>]*>\n?/g, '');
    html = html.replace(/<head>/, '<head><meta name="robots" content="noindex, nofollow"><link rel="icon" href="/favicon.svg" type="image/svg+xml">');
    html = html.replace(/(<body[^>]*>)/, '$1' + BANNER);
    save(p === '/' ? 'index.html' : p.slice(1) + 'index.html', html);
  }
  // 404-pagina
  let nf = localize(await (await fetch(BASE + '/bestaat-niet/')).text());
  collect(nf); save('404.html', nf.replace(/<head>/, '<head><meta name="robots" content="noindex, nofollow">'));
  // Assets (CSS kan zelf weer naar fonts/afbeeldingen verwijzen).
  const done = new Set();
  while ([...assets].some(a => !done.has(a))) {
    for (const a of [...assets]) {
      if (done.has(a)) continue; done.add(a);
      const r = await fetch(BASE + a); if (!r.ok) { console.log('MISS', r.status, a); continue; }
      let buf = Buffer.from(await r.arrayBuffer());
      if (/\.(css|js)$/.test(a)) {
        let t = buf.toString('utf8');
        for (const m of t.matchAll(/url\((['"]?)([^'")]+)\1\)/g)) { const u = m[2]; if (!u.startsWith('data:') && !u.startsWith('http') && !u.startsWith('#')) assets.add(path.posix.normalize(path.posix.join(path.posix.dirname(a), u)).split('?')[0]); }
        if (a.endsWith('pdf-viewer.js')) assets.add(path.posix.join(path.posix.dirname(a), 'vendor/pdfjs/pdf.min.js'));
        buf = Buffer.from(localize(t));
      }
      save(a.slice(1), buf);
    }
  }
  fs.writeFileSync(path.join(OUT, '_headers'), '/*\n  X-Robots-Tag: noindex, nofollow\n  X-Content-Type-Options: nosniff\n  Referrer-Policy: strict-origin-when-cross-origin\n/wp-content/*\n  Cache-Control: public, max-age=31536000, immutable\n/wp-includes/*\n  Cache-Control: public, max-age=31536000, immutable\n');
  fs.writeFileSync(path.join(OUT, 'favicon.svg'), '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#243B31"/><text x="32" y="46" text-anchor="middle" font-family="Georgia,serif" font-size="40" fill="#E6DAC6">V</text></svg>');
  fs.writeFileSync(path.join(OUT, 'favicon.ico'), '');
  fs.writeFileSync(path.join(OUT, 'robots.txt'), 'User-agent: *\nDisallow: /\n');
  fs.writeFileSync(path.join(OUT, '_redirects'), ['/ons-menu/ /menukaart/ 301','/menukaart/lunch/ /menukaart/ 301','/menukaart/diner/ /menukaart/ 301','/menukaart/pizza/ /menukaart/ 301','/menukaart/noord-afrikaans-menu/ /menukaart/ 301','/menukaart/pannenkoeken-poffertjes-wafels/ /menukaart/ 301','/menukaart/kindermenu/ /menukaart/ 301','/omgeving/ /#omgeving 301','/fotos/* /galerij/ 301'].join('\n') + '\n');
  console.log('pagina\'s', pages.length, 'assets', done.size);
})();
