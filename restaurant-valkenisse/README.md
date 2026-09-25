# Restaurant Valkenisse – WordPress-website

Maatwerk WordPress-website voor Restaurant Valkenisse (Biggekerke, Zeeuwse kust).
Gebouwd als **block theme** (Gutenberg, geen pagebuilder) plus één **functionaliteitsplugin**.
Er zijn geen andere plugins nodig.

```
restaurant-valkenisse/
├── wp-content/themes/valkenisse/        Thema: vormgeving, templates, header/footer, patronen
├── wp-content/plugins/valkenisse-site/  Plugin: menukaart, openingstijden, studio's, feesten,
│                                        galerij, formulieren, SEO/Schema, redirects, dashboard
├── tools/make-placeholders.py           Genereert de tijdelijke SVG-illustraties
├── HANDLEIDING.md                       Beheerhandleiding voor de eigenaar (zonder techniek)
└── BRONNEN-EN-CONTROLE.md               Welke gegevens van de huidige site komen + wat nog moet
```

> **Belangrijk:** de huidige website kon tijdens de bouw niet rechtstreeks worden geopend
> (netwerkbeperking). Bedrijfsgegevens zijn overgenomen uit zoekresultaten van
> restaurantvalkenisse.nl. Alles wat niet bevestigd kon worden, staat als duidelijke
> **[placeholder]** op de site. Zie `BRONNEN-EN-CONTROLE.md` vóór livegang.

---

## Installatie

Vereist: WordPress 6.6 of hoger (getest op **7.1.2**), PHP 8.1+.

1. Kopieer `wp-content/themes/valkenisse` en `wp-content/plugins/valkenisse-site` naar de
   WordPress-installatie.
2. **Instellingen → Algemeen:** Sitetaal *Nederlands*, tijdzone *Amsterdam*.
3. Activeer het thema **Valkenisse** en de plugin **Restaurant Valkenisse – Website**.
4. Klik in het dashboard op **Website inrichten** (of `wp valkenisse setup`). Dit maakt:
   - pagina's: Home, Restaurant, Menukaart, Overnachten, Feesten & Partijen, Galerij, Contact,
     Reserveren, Nieuws, Privacybeleid, Cookiebeleid (met SEO-titels en -omschrijvingen);
   - het hoofdmenu, de menukaartcategorieën, fotocategorieën en dieetlabels;
   - twee studio's, één gerecht (Tajine, zonder prijs) en vier buffetten **als concept**.

   Bestaande pagina's worden nooit overschreven; het is veilig om dit opnieuw te draaien.
5. **Instellingen → Permalinks:** "Berichtnaam" (`/%postname%/`) – wordt door de setup al gezet.
6. Werk de controlelijst in `BRONNEN-EN-CONTROLE.md` af en vervang de tijdelijke illustraties
   door echte foto's.

### Aanbevolen aanvullingen op de server

| Onderdeel | Advies |
|---|---|
| SEO-plugin | **Rank Math** (gratis) *of* Yoast – niet allebei. De thema-SEO schakelt zich dan automatisch uit, behalve de Schema.org-data (uitzetten kan onder *Openingstijden → Formulieren & SEO* als Rank Math al een Restaurant-schema levert). |
| E-mail | SMTP-plugin (bv. *FluentSMTP*) met het e-mailaccount van het restaurant, zodat formulier-e-mails betrouwbaar aankomen. |
| Caching | De cache van de hosting (LiteSpeed Cache, WP Super Cache of servercache). Het blok "Vandaag geopend" blijft ook in gecachte pagina's juist (de browser kiest de dag opnieuw). |
| Foto's | Upload JPEG's; de plugin slaat ze automatisch als **WebP** op (kwaliteit 80). |

---

## Opbouw en keuzes

### Beheer zonder techniek
- **Openingstijden** (eigen menu): periodes per seizoen (ook "vanaf Pasen"), per dag open/sluit/gesloten,
  keukentijden, afwijkende dagen (feestdagen, besloten feest) en een tekst voor dagen buiten de periodes.
  Eén keer wijzigen → overal bijgewerkt: hero, header-menu, footer, contactpagina, reserveerpagina,
  dashboard en Schema.org.
- **Contact & reserveren**: adres, telefoon, e-mail, en waar álle "Reserveer een tafel"-knoppen naartoe gaan
  (formulier / alleen bellen / eigen extern systeem).
- **Mededeling**: korte melding boven elke pagina met automatische einddatum.
- **Menukaart** = inhoudstype *Gerecht* (naam, omschrijving, prijs, categorie, dieetlabels, allergenen,
  optionele foto). Prijs snel wijzigen via *Snel bewerken* in het overzicht. CSV-import voor de volledige
  kaart. Categorieën met volgorde en intro-tekst.
- **Studio's**, **Feesten** (buffetten/arrangementen) en **Aanvragen** als herkenbare menu's.
- **Foto's**: in de mediabibliotheek vinkt u per foto een galerijcategorie aan → foto verschijnt in de galerij.
- **Nieuws** = WordPress-berichten.
- Dashboard vervangen door één scherm "Wat wilt u aanpassen?" met tegels.

### Speciale links (werken in elke knop)
| Link | Gaat naar |
|---|---|
| `#reserveren` | reserveringspagina, telefoon of extern systeem (volgens instelling) |
| `#bellen` | `tel:`-link met het ingestelde nummer |
| `#route` | Google Maps-route (opent pas na klik; geen cookies vooraf) |
| `#mailen` | `mailto:` met het ingestelde adres |
| `#boeken` | boekingslink studio's (indien ingevuld), anders het aanvraagformulier |

### Blokken van de plugin (categorie "Restaurant Valkenisse" in de editor)
Openingstijden (vandaag / seizoen / volledig), Contactgegevens, Menukaart, Studio's, Buffetten,
Fotogalerij (filters + lightbox), Aanvraagformulier (reservering / studio / feest / contact),
Mobiele actiebalk, Mededeling. Alle blokken worden op de server opgebouwd; geen build-stap nodig.

### Formulieren
Eigen, lichte oplossing in de plugin (geen Fluent/Gravity Forms nodig):
honeypot + ondertekend tijdstoken (min. 3 s) + limiet van 5 inzendingen per 10 min per bezoeker (IP alleen
gehasht), verplichte AVG-toestemming met link naar het privacybeleid, server- en browservalidatie,
duidelijke succes-/foutmeldingen (ingevulde velden blijven bewaard), e-mail naar het restaurant met
*Reply-To* van de gast, optionele ontvangstbevestiging, kopie onder **Aanvragen** die na de ingestelde
bewaartermijn (standaard 90 dagen) automatisch wordt verwijderd. Werkt zonder JavaScript en met paginacache.
Wilt u toch Fluent Forms gebruiken, vervang dan het blok *Aanvraagformulier* door het Fluent Forms-blok.

### SEO
- Eén H1 per pagina (in de hero), logische H2/H3.
- Per pagina SEO-titel en -omschrijving (vak "Weergave in Google"; bij Rank Math/Yoast neemt die plugin het over).
- Canonical URL's en XML-sitemap (`/wp-sitemap.xml`) via WordPress zelf; gebruikers uitgesloten.
- Open Graph / Twitter-card als terugval zonder SEO-plugin.
- Schema.org: `Restaurant` (home + contact, met openingstijden per seizoen incl. `validFrom/validThrough`,
  alleen dagen met een bekende sluitingstijd), `LodgingBusiness` (overnachten, faciliteiten uit de studio's),
  `BreadcrumbList`. Geen reviews, ratings of prijsklasse (niet beschikbaar).
- **301-redirects** van oude adressen: `/ons-menu/`, `/menukaart/lunch/` e.d. → `/menukaart/#…`,
  `/fotos/*` → `/galerij/`, `/omgeving/` → `/#omgeving`, `/product-categorie/*` en `/product/*` → `/feesten-partijen/`.
  Aan te vullen via het filter `valkenisse_redirects`.
- Lokale zoektermen (Valkenisse, Biggekerke, Zoutelande, Walcheren, Zeeuwse kust, overnachten, feestlocatie)
  zijn natuurlijk verwerkt in titels, omschrijvingen en teksten. Er zijn bewust **geen aparte
  SEO-landingspagina's** per plaatsnaam gemaakt: daarvoor is (nog) te weinig unieke, bevestigde inhoud
  en dunne pagina's schaden eerder dan ze helpen.

### Snelheid
- Geen pagebuilder, geen jQuery op de voorkant. JavaScript op een gewone pagina: `header.js` (<1 kB) en
  `hours.js` (<1 kB); formulier- en galerijscript alleen op pagina's die ze gebruiken.
- Lokale lettertypen (Cormorant Garamond + Manrope variable, WOFF2, ~95 kB totaal), met `preload` en `font-display: swap`.
- Responsieve afbeeldingen, lazy loading en `fetchpriority` via WordPress; uploads als WebP.
- Animaties (hero, rustige image-reveal, zachte parallax) zijn pure CSS, respecteren
  `prefers-reduced-motion` en worden alleen gebruikt waar de browser *scroll-driven animations* ondersteunt.
- Geen Google Maps-embed, geen externe scripts, geen trackingcookies.

### Mobiel
- Eerste scherm: naam, "Reserveer een tafel", "Bekijk de menukaart", openingstijden van vandaag.
- Vaste actiebalk onderin: **Bellen · Route · Menukaart · Reserveren** (min. 60 px hoog).
- Menu zonder JavaScript (`<details>`), schermvullend, met reserveerknop, tijden en contact.
- Menukaart met horizontaal scrollbare categorieknoppen; tegels "Eten & drinken" als swipe-carrousel.
- Formuliervelden 16 px (geen inzoomen op iPhone), grote knoppen, juiste toetsenborden (`tel`, `email`, `date`).

---

## Getest

Lokaal op WordPress 7.1.2 (SQLite) met Chromium (Playwright):
- alle 11 pagina's, alle templates en header/footer valideren in de blok-editor (geen "ongeldige blokken");
- geen JS-fouten of mislukte verzoeken; geen horizontale scroll op 390 px; exact één H1 per pagina;
- formulier: spamtoken blokkeert te snel verzenden (velden blijven ingevuld), geldige aanvraag → e-mail naar
  restaurant + bevestiging aan gast + opgeslagen onder Aanvragen;
- openingstijden: seizoenen incl. Pasen (2026-04-05, 2027-03-28), gesloten dagen en dagen buiten de periodes;
- instellingen opslaan per tabblad verliest geen gegevens;
- CSV-import en oude-URL-redirects.

## Ontwikkeling

```bash
python3 tools/make-placeholders.py   # SVG-placeholders opnieuw genereren
wp valkenisse setup                  # pagina's/menu/categorieën aanmaken
```
Wijzigingen in `patterns/*.php` werken alleen door in **nieuwe** pagina's (bestaande pagina-inhoud staat in
de database). In de editor zijn alle patronen terug te vinden onder *Patronen → Valkenisse*.
