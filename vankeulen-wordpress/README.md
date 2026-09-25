# Van Keulen Caravanstalling – WordPress-website

Nieuwe, schone WordPress-website voor **Van Keulen Caravanstalling**, Dorpsstraat 57A, 4373 AD Biggekerke (Walcheren, Zeeland).
Volledig opnieuw ontworpen: geen oude thema's, plugins, scripts of links overgenomen.

| Onderdeel | Map | Wat het doet |
|---|---|---|
| **Thema** `vankeulen` | `wp-content/themes/vankeulen/` | Blokthema (Gutenberg / Site-editor): kleuren, typografie, knoppen, header, footer, sjablonen en alle secties als patronen. |
| **Plugin** `vankeulen-core` | `wp-content/plugins/vankeulen-core/` | Centrale bedrijfsgegevens, aanvraagwizard met e-mail, FAQ, lokale SEO & schema.org, snelheid, beveiliging, livegang-check en één-klik-inrichting. |
| Documentatie | `docs/` | Beheerhandleiding voor de eigenaar, livegang-checklist, lijst met aan te leveren informatie, SEO-plan. |
| Hulpmiddelen | `tools/` | Generator voor paginapatronen, CSS-minificatie, generator voor tijdelijke illustraties. |

Geen page builder, geen ACF, geen jQuery, geen externe scripts of fonts. Er zijn **geen extra plugins nodig**. Aanbevolen (optioneel): één SMTP-plugin en een back-up-oplossing, zie hieronder.

---

## Lokaal bekijken (één commando)

Vereist alleen [Node.js](https://nodejs.org/) 20 of nieuwer. Er wordt een tijdelijke WordPress gestart met het thema en de plugin, en alle pagina's worden automatisch aangemaakt.

```bash
# macOS / Linux
./lokaal/start.sh

# Windows: dubbelklik op lokaal\start.cmd, of in de opdrachtprompt:
lokaal\start.cmd
```

Open daarna **http://127.0.0.1:9400**. U bent automatisch ingelogd (beheer: http://127.0.0.1:9400/wp-admin/). Stoppen met `Ctrl+C`; de testsite wordt dan weggegooid. E-mails van het formulier worden lokaal niet verstuurd, maar aanvragen staan wel onder Van Keulen → Aanvragen.

## Installatie (± 15 minuten)

1. **Hosting**: PHP 8.1+ (8.3 aanbevolen), MySQL/MariaDB, **SSL (https)**, bij voorkeur met serverside caching (LiteSpeed, Nginx FastCGI of vergelijkbaar) en dagelijkse back-ups.
2. Installeer een **verse, actuele WordPress** (6.6 of nieuwer; gebruik altijd de nieuwste versie) in het Nederlands. Neem niets over van de oude installatie.
3. Maak een beheerder aan met een **eigen gebruikersnaam (niet “admin”)** en een sterk wachtwoord.
4. Upload `wp-content/themes/vankeulen` en `wp-content/plugins/vankeulen-core` (of pak ze als zip in en upload ze via het beheer).
5. **Weergave → Thema's**: activeer *Van Keulen Caravanstalling*.
6. **Plugins**: activeer *Van Keulen Core*.
7. **Van Keulen → Site inrichten** → *Site inrichten*.
   Dit maakt alle pagina's met inhoud, de homepage, nette URL's, de veelgestelde vragen en zet de (tijdelijke) beelden in de Mediabibliotheek.
   Via WP-CLI: `wp vankeulen inrichten`.
8. **Van Keulen → Bedrijfsgegevens**: vul telefoon, openingstijden, Google Bedrijfsprofiel enz. in (zie `docs/AAN-TE-LEVEREN.md`).
9. Loop **Van Keulen → Livegang-check** en `docs/LIVEGANG-CHECKLIST.md` na.

### Pagina's en URL's

| Pagina | URL |
|---|---|
| Home | `/` |
| Caravanstalling | `/caravanstalling/` |
| Bootstalling | `/bootstalling/` |
| Vouwwagen & aanhanger stalling | `/vouwwagen-aanhanger-stalling/` |
| Strandhuisjes & slaaphuisjes | `/strandhuisjes-stalling/` |
| Stalling op Walcheren (lokale SEO-pagina) | `/caravanstalling-walcheren/` |
| Over Van Keulen | `/over-ons/` |
| Veelgestelde vragen | `/veelgestelde-vragen/` |
| Contact & stallingsplaats aanvragen | `/contact/` |
| Privacybeleid / Cookiebeleid | `/privacybeleid/`, `/cookiebeleid/` (noindex) |

Er is bewust **geen** aparte `/caravanstalling-zeeland/`-pagina: die zou grotendeels hetzelfde zeggen als de homepage en `/caravanstalling/` (die al op “Zeeland” zijn geoptimaliseerd). De Walcheren-pagina heeft wél eigen, bruikbare inhoud (ligging, bereikbaarheid vanuit de dorpen, route). Zie `docs/SEO.md`.

---

## Wat zit er in

### Beheer voor de eigenaar (menu **Van Keulen**)
- **Bedrijfsgegevens** – één scherm voor naam, adres, telefoon, WhatsApp, e-mail, KvK, Google Bedrijfsprofiel, coördinaten, openingstijden per dag, mededelingenbalk, beschikbaarheid (“Er is plaats” / “Beperkt” / “Wachtlijst”), tarieven (aan/uit) en de opties van het aanvraagformulier. Wijzigingen verschijnen overal tegelijk: header, footer, knoppen, mobiele balk, formulier en schema.org.
- **Veelgestelde vragen** – vragen toevoegen, wijzigen en sorteren.
- **Aanvragen** – back-up van iedere stallingsaanvraag (ook als een e-mail niet aankomt), automatisch verwijderd na de bewaartermijn (AVG, standaard 12 maanden).
- **Livegang-check** – controleert ontbrekende gegevens, placeholders, onbekende uitgaande links, verborgen links/scripts, SSL, indexering, beheerders, updates en PHP-bestanden in uploads.
- **Site inrichten** – eenmalige inrichting (kan veilig opnieuw; bestaande pagina's blijven staan).

Teksten en foto's wijzigt de eigenaar gewoon in de blokeditor. In de zijbalk van iedere pagina staat het paneel **Zoekmachines (Google)** voor SEO-titel, meta-omschrijving en noindex.

### Blokken (categorie “Van Keulen” in de editor)
Knop (aanvragen / bellen / WhatsApp / route / locatie / e-mail / link), Contactgegevens, Openingstijden, Beschikbaarheid, Mededelingenbalk, Prijzen, Veelgestelde vragen, Stallingsaanvraag (wizard), Kaart & route, Kruimelpad, Mobiele actiebalk, Logo, Copyright.
Allemaal server-side gerenderd uit de centrale gegevens. Ontbreekt een gegeven, dan zien bezoekers niets (bijv. geen belknop zonder nummer) en ziet de ingelogde eigenaar een gele herinnering `[DOOR VAN KEULEN AAN TE LEVEREN]`.

### Stallingsaanvraag (belangrijkste conversie)
- Wizard in 4 stappen: *Wat wilt u stallen?* → *Afmetingen* (lengte en breedte verplicht, hoogte optioneel) → *Gewenste periode* → *Contactgegevens* + privacy-akkoord → **Verstuur mijn aanvraag**.
- Werkt ook zonder JavaScript (dan staan alle stappen onder elkaar). Script ± 3 kB, alleen geladen op pagina's met het formulier.
- Knoppen als “Vraag bootstalling aan” selecteren het juiste object alvast (`?type=boot`).
- E-mail naar Van Keulen (Reply-To = klant) + ontvangstbevestiging aan de klant + opslag in WordPress.
- Spambeveiliging zonder externe dienst of cookies: honeypot, ondertekend tijdstempel (min. 4 s), max. 5 aanvragen per uur per IP, linkcontrole. Werkt ook met paginacache (geen nonces).
- Welke objecten/periodes getoond worden is instelbaar. **Camper staat standaard uit** (niet bevestigd).

### SEO
- Eén H1 per pagina (hero op home, paginatitel elders), logische H2/H3.
- Titels en meta-omschrijvingen per pagina (vooraf ingevuld op de zoekwoorden, zie `docs/SEO.md`).
- Canonical (WordPress), Open Graph + Twitter Card, `geo.*`-metatags.
- JSON-LD: `SelfStorage`/`LocalBusiness` (adres, telefoon, e-mail, openingstijden, geo, `sameAs` Google Bedrijfsprofiel, `areaServed` Walcheren/Zeeland), `WebSite`, `WebPage`, `BreadcrumbList`, `FAQPage` (alleen vragen met een definitief antwoord – nooit placeholders).
- XML-sitemap: `/wp-sitemap.xml` (alleen pagina's; geen gebruikers, categorieën of noindex-pagina's).
- Zichtbare kruimelpaden, interne links tussen alle stallingspagina's, alt-teksten op alle beelden.
- Bijlage-, auteur- en datumpagina's en RSS-feeds worden doorgestuurd (geen dunne pagina's).
- Wordt later toch Yoast/Rank Math geïnstalleerd, dan schakelt de eigen SEO-module zich automatisch uit (schema.org blijft).

### Snelheid
- Geen render-blokkerende CSS-bestanden: thema- en blokstijlen staan inline (± 25 kB thema-CSS, gzip ± 6 kB).
- Eén lokaal gehost lettertype (Archivo variabel, 35 kB WoFF2, preload, `font-display: swap`).
- Geen jQuery; JavaScript alleen voor menu (WordPress-kern), wizard (3 kB) en kaart (0,5 kB, pas na klik).
- Google Maps pas na een klik (“Kaart laden”) → geen externe verzoeken, geen cookies, geen cookiebanner nodig.
- Uploads (JPEG/PNG) worden automatisch **AVIF** (indien de server dat kan) of **WebP**; WordPress maakt responsive formaten (`srcset`), lazy loading en `fetchpriority="high"` voor de hero. Kaarten krijgen correcte `sizes`, zodat telefoons kleine bestanden laden.
- Emoji-scripts, oEmbed, RSD/WLW, shortlinks en feeds uit.

**Gemeten (Lighthouse, mobiel, lokale testserver):** Performance 98–99, Toegankelijkheid 100, SEO 100, Best Practices 96 (de enige melding kwam van ontbrekende kernbestanden in de uitgeklede testinstallatie). LCP 2,0–2,3 s, CLS 0, TBT 0 ms.

### Beveiliging
Bestandseditor uit, XML-RPC uit, WordPress-versie verborgen, gebruikersnamen niet op te vragen (`?author=`, REST), algemene loginfoutmelding, reacties/pingbacks volledig uit, beveiligingsheaders (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS op https). Zie `docs/LIVEGANG-CHECKLIST.md` voor hosting-maatregelen (2FA, back-ups, updates).

---

## Aanbevolen extra's (zo min mogelijk)

| Doel | Advies |
|---|---|
| **E-mail betrouwbaar afleveren** | SMTP van de hostingpartij, of de plugin *WP Mail SMTP* (gratis). Stel SPF/DKIM/DMARC in voor `vankeulencaravanstalling.nl`. |
| **Back-ups** | Dagelijkse back-ups door de hostingpartij, of *UpdraftPlus* naar externe opslag. |
| **Tweestapsverificatie** | Plugin *Two Factor* (van WordPress.org-bijdragers) of 2FA van de hostingpartij. |
| **Paginacache** | Serverside cache van de host. Op LiteSpeed-hosting: *LiteSpeed Cache*. |

Meer is niet nodig. Installeer geen “alles-in-één”-pakketten of page builders.

---

## Beelden

De site bevat **tijdelijke illustraties** (lichtgewicht WebP, 14–26 kB) in de huisstijl, zodat de opmaak klopt. **Vervang ze door echte foto's van Van Keulen** via Media → bestand vervangen, of per blok via “Vervangen”. Zie de fotolijst in `docs/AAN-TE-LEVEREN.md`.
Upload gewoon JPG's van telefoon of camera; WordPress zet ze om naar AVIF/WebP in alle formaten.

---

## Ontwikkelen

```bash
# Paginapatronen opnieuw genereren na wijziging van de standaardteksten
python3 tools/genereer-paginas.py

# Na wijziging van theme.css
python3 tools/minify-css.py

# Tijdelijke illustraties opnieuw maken
python3 tools/beelden/maak-svg.py
CHROME_PATH=/pad/naar/chromium node tools/beelden/render.mjs tools/beelden/svg /tmp/png
# daarna PNG → WebP (bijv. met cwebp of PHP GD) naar themes/vankeulen/assets/images/
```

Getest op een lokale WordPress (SQLite) met PHP 8.4: alle pagina's zonder PHP-meldingen, formulier end-to-end (validatie, spamfilters, e-mail, opslag, bedankmelding), wizard in Chromium (desktop en mobiel) en alle blokmarkeringen gevalideerd tegen de actuele Gutenberg-validator (0 ongeldige blokken).
