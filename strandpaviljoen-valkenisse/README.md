# Strandpaviljoen Valkenisse: WordPress-website

Nieuwe website voor **Strandpaviljoen Valkenisse, familie Herwegh, sinds 1956**.
Het is een blokthema (Gutenberg / Full Site Editing) met één kleine beheerplugin. Er is geen page builder en er is geen build-stap nodig.

> Familie. Strand. Zee. Eenvoud. Uitzicht. Vakantiegevoel. Sinds 1956.

```
wp-content/
├── themes/valkenisse/            ← het uiterlijk (kleuren, lettertypes, templates, pagina-inhoud)
│   ├── theme.json                ← globale kleuren (zand, duin, helmgras, zee, hout, marine) + typografie
│   ├── parts/                    ← header (transparant over de foto) en footer
│   ├── templates/                ← homepage, pagina met hero, gewone pagina, vacature, 404
│   ├── patterns/                 ← alle secties en complete pagina's (bewerkbaar in de editor)
│   └── assets/                   ← CSS, 1 klein script, lokaal gehoste fonts, placeholder-beelden
└── plugins/valkenisse-core/      ← de gegevens: openingstijden, tarieven, menukaart, vacatures,
                                    foto's, aanvragen, contact, SEO/structured data
tools/make-placeholders.mjs       ← genereert de voorbeeldillustraties (alleen voor ontwikkeling)
```

---

## Installatie (± 15 minuten)

1. Installeer WordPress 6.6 of hoger (PHP 8.0+), met de taal **Nederlands**.
2. Kopieer `wp-content/themes/valkenisse` en `wp-content/plugins/valkenisse-core` naar de site.
3. Activeer eerst de plugin **Strandpaviljoen Valkenisse – Beheer** en daarna het thema **Valkenisse**.
4. Ga naar **Strandpaviljoen → Installatie** en klik op **Pagina's aanmaken**. Dit doet het volgende:
   - Het maakt deze pagina's aan: `/`, `/eten-drinken/`, `/strandhuisjes/`, `/strandverhuur/`, `/over-ons/`, `/fotos/`, `/vacatures/`, `/contact/`, `/veelgestelde-vragen/`, `/privacybeleid/` en `/cookiebeleid/`.
   - Het stelt de homepage, de permalinks (`/paginanaam/`), de tijdzone Europe/Amsterdam en de SEO-titels en -omschrijvingen per pagina in.
   - Bestaande pagina's worden **nooit** overschreven, dus je kunt dit veilig vaker draaien.
5. Installeer **Rank Math** (niet ook Yoast). Zie [SEO](#seo).
6. Installeer een cacheplugin, zie [Snelheid](#snelheid).
7. Loop de lijst [Nog aan te leveren](#nog-aan-te-leveren-door-familie-herwegh) na. Alles wat nog ontbreekt staat op de site **geel gemarkeerd**, zodat het voor livegang opvalt.

**Foto's:** zet de echte hero-foto als `assets/images/hero-paviljoen.jpg` (of `.webp`) in het thema *vóór* stap 4. Dan gebruikt de homepage die meteen. Alle andere beelden vervang je gewoon in de blokeditor (klik op de foto → Vervangen).

---

## Dagelijks beheer door de familie

Alles staat in het linkermenu onder **🌴 Strandpaviljoen**. Het beheer is ook beschikbaar voor gebruikers met de rol *Redacteur*.

| Onderdeel | Waar | Wat |
|---|---|---|
| **Vandaag open of dicht** | Dashboard-widget "☀️ Vandaag op het strand", of Strandpaviljoen → Vandaag | Eén keuze: *normaal rooster / geopend vanaf … / geopend tot ± … / 🌧 gesloten vanwege het weer / gesloten / eigen tekst*. De melding bovenaan de site, de homepage, de footer en Google worden allemaal bijgewerkt. Standaard vervalt de keuze om middernacht. |
| **Tijdelijke mededeling** | Strandpaviljoen → Vandaag | Extra regel in de balk bovenaan, eventueel met link en einddatum. |
| **Openingstijden** | Strandpaviljoen → Openingstijden | Vast weekrooster. Laat "tot" leeg als de sluitingstijd wisselt. |
| **Reserveren** | Strandpaviljoen → Contactgegevens → Reserveren | De knop **Reserveer nu** (in de homepage-hero en op Eten & Drinken). Bezoekers kiezen zelf: **bellen** of **mailen**. De mail staat al klaar met onderwerp en invulvelden (naam, datum, tijd, aantal personen, lunch/diner/borrel). Je kunt de vraag erboven aanpassen of de knop uitzetten. |
| **Menukaart** | Strandpaviljoen → Menukaart | Eén item per gerecht of drankje, met prijs, omschrijving en kaartonderdeel. Een vinkje "tijdelijk niet leverbaar" verbergt een item. De kaart is een echte mobiele webpagina; een PDF is optioneel. |
| **Strandhuisjes & tarieven** | Strandpaviljoen → Strandhuisjes | Titel („Huurprijzen 2026"), periode, wat er inbegrepen is en de prijzen. Prijzen kun je groeperen (kolom *Groep*: Per dag / Per week / Per seizoen). Voor een nieuw seizoen pas je het jaartal en de prijzen aan en klik je op Opslaan. Het aanvraagformulier kan hier uit ("alles verhuurd"). |
| **Aanvragen strandhuisjes** | Strandpaviljoen → Aanvragen | Elke aanvraag komt per e-mail binnen **en** wordt hier bewaard. |
| **Verhuurprijzen** | Strandpaviljoen → Strandverhuur | Strandstoelen, ligbedden, parasols en windschermen. |
| **Foto's** | Media → Toevoegen | Kies bij elke foto een **fotocategorie** (Paviljoen, Terras, Strand, Eten & drinken, Strandhuisjes, Historie) en vul een korte **alt-tekst** in. Alleen foto's met een categorie komen in de galerij. |
| **Vacatures** | Strandpaviljoen → Vacatures | Velden: functie, omschrijving, uren, leeftijd, periode, contactpersoon, e-mail en een vinkje **Actief**. Zonder actieve vacature toont de site: *"Op dit moment hebben we geen openstaande vacatures."* |
| **Gastreacties** | Strandpaviljoen → Gastreacties | Alleen echte reacties, en alleen met het vinkje *toestemming*. Er worden er maximaal 3 getoond. Zonder reacties verdwijnt de sectie helemaal. |
| **Contactgegevens** | Strandpaviljoen → Contactgegevens | Telefoon, e-mail, postadres, **adres voor navigatie** (dat is een ander adres!), parkeren, social media, Google-reviewlink en bedrijfsnaam. Ze worden overal automatisch bijgewerkt. |

Teksten en foto's op de pagina's zelf pas je aan via **Pagina's → (pagina) → Bewerken**. Header en footer pas je aan via **Weergave → Editor**.

---

## Bronnen & feitelijke inhoud

strandpaviljoenherwegh.nl was vanuit de bouwomgeving **niet bereikbaar**. De feiten komen uit de zoekmachinesamenvattingen van die site, aangevuld met de briefing. Er is niets verzonnen: onbekende gegevens staan als `[DOOR FAMILIE HERWEGH AAN TE LEVEREN]` op de site.

Gebruikt:
- In 1956 vanaf het zand opgebouwd door Sies Herwegh, sindsdien gerund door de familie Herwegh
- Gemoedelijke sfeer, goede prijs-kwaliteitverhouding
- Ligging: duinovergang Vossenhol, Groot Valkenisse, bij Camping Meerpaal, met parkeergelegenheid in de nabijheid
- Verhuur van strandhuisjes (begin april t/m september; per dag, week of seizoen; inclusief 2 strandstoelen, 1 windscherm/luifel en een tafeltje), strandstoelen, ligbedden, parasols en windschermen
- Strandhuisjes het hele jaar te reserveren via e-mail, of telefonisch wanneer het paviljoen open is
- E-mail info@strandpaviljoenherwegh.nl, telefoon 0118 561347
- Postadres L. Simonsestraat 10, 4373 AV Biggekerke
- Open vanaf 11:00, sluitingstijd wisselend

### Nog aan te leveren door familie Herwegh

- [x] **Telefoonnummer** bevestigd door de familie: +31 (0)118 561347.
- [ ] **Postadres controleren** (L. Simonsestraat 10, 4373 AV Biggekerke).
- [ ] **Adres voor navigatie.** Vul eventueel ook de coördinaten in, dan klopt de kaart precies.
- [x] **Huurprijzen 2026** voor strandhuisjes (per dag, per week in vier periodes, per seizoen) en strandverhuur (per dag) zijn ingevoerd. Voor 2027: pas de titel en de prijzen aan onder Strandpaviljoen → Strandhuisjes / Strandverhuur.
- [ ] **Menukaart** met gerechten, dranken en prijzen.
- [ ] **Seizoen/periode** van de openingstijden, en eventueel een gemiddelde sluitingstijd voor Google.
- [ ] **Het familieverhaal in eigen woorden** (pagina Over ons) en historische foto's.
- [ ] **Officiële social-media-links.** Online bestaan meerdere Facebook-pagina's; kies de juiste.
- [ ] **Google-reviewlink** (optioneel), en echte gastenboekreacties **met toestemming** (optioneel).
- [ ] **Privacy- en cookiebeleid** (er staan concepttekst en technische informatie klaar).
- [ ] **Logo** (Weergave → Editor → Header → Sitelogo). Zonder logo verschijnt de naam als woordmerk.
- [ ] Paragliders en deltavliegers worden bewust **niet** genoemd. Voeg ze alleen toe als dat nog actueel is.

### Fotolijst (echte foto's, geen stock)

Elke placeholder vermeldt welke foto er hoort. Maak liggende foto's van minstens 2400 px breed.

1. **Hero**: het paviljoen met terras vanaf het strand, golden hour, zee en bij voorkeur een schip. Je gouden-uur-luchtfoto met duinen, trap en terras is hiervoor ideaal.
2. Een historische foto uit de begintijd, en het vernieuwde paviljoen van vandaag
3. Gerechten en drankjes op het terras, met de zee op de achtergrond
4. Het uitzicht vanaf het terras met een groot zeeschip
5. De strandhuisjes (de gele rij aan de duinvoet)
6. Strandstoelen, ligbedden, parasols en windschermen (vierkant)
7. Het strand, de duinen en een zonsondergang
8. De familie en het team, en gasten op het terras

---

## SEO

- Op elke pagina precies één H1 en een logische H2/H3-opbouw. Nette URL's en interne links tussen de pagina's.
- Voor elke pagina zijn een meta title en description ingesteld, met natuurlijk gebruik van zoektermen zoals *strandpaviljoen Valkenisse / Zoutelande*, *strandhuisje huren Valkenisse / Zoutelande / Walcheren*, *eten aan het strand Zoutelande* en *strandstoel huren Zoutelande*.
- **Structured data** komt altijd uit de plugin en rechtstreeks uit de centrale gegevens:
  - `Restaurant`/`LocalBusiness` met foundingDate 1956, telefoon, e-mail, menu-link, geo en actuele `openingHoursSpecification`. Bij "vandaag gesloten" komt er een `specialOpeningHoursSpecification` bij.
  - `BreadcrumbList` voor het kruimelpad en `JobPosting` voor actieve vacatures.
  - Placeholders worden nooit naar Google gestuurd.
- Zonder SEO-plugin verzorgt de plugin zelf de meta description, Open Graph, canonical en de XML-sitemap van WordPress (`/wp-sitemap.xml`, zonder gebruikers en zonder inactieve vacatures).
- **Met Rank Math**: de plugin schakelt dan de eigen meta-tags uit (Rank Math neemt het over). Zet in Rank Math het *Local SEO / Organization*-schema **uit** om dubbele bedrijfsgegevens te voorkomen, en zet de breadcrumbs aan (het thema gebruikt die automatisch). De installatie vult de Rank Math-titels en -omschrijvingen al in.
- Maak een Google-bedrijfsprofiel aan of werk het bij met dezelfde gegevens.

## Snelheid

- Er is geen jQuery, geen slider en geen page builder. Het thema-script is ongeveer 2 KB. De galerij- en kaartscripts worden alleen geladen op pagina's die ze gebruiken.
- De fonts (Fraunces en Figtree) worden **lokaal** gehost, het kopfont wordt vooraf geladen, en er zijn geen Google Fonts.
- Nieuwe uploads worden automatisch opgeslagen als **WebP**, met responsive `srcset`. De eerste grote foto krijgt `fetchpriority="high"`; de rest laadt pas bij scrollen (lazy loading).
- De kaart (Google Maps of OpenStreetMap) laadt pas na een klik. Dat is sneller, en er worden vooraf geen cookies van derden geplaatst.
- De emoji-scripts van WordPress zijn uitgeschakeld.
- **Caching**: installeer bijvoorbeeld WP Super Cache, LiteSpeed Cache of WP Rocket. De plugin leegt de cache automatisch na elke wijziging en elke nacht om 00:01, zodat "vandaag"-meldingen op tijd vervallen. **Let op:** stel de cache-levensduur in op maximaal 10 uur (of sluit `/strandhuisjes/` uit van caching). Het aanvraagformulier bevat een beveiligingstoken dat na 12 tot 24 uur verloopt.

## Mobiel

- Onderaan het scherm staat een vaste knoppenbalk: **Bellen | Route | Strandhuisje aanvragen | Menu**.
- De melding "vandaag open/dicht" staat bovenaan elke pagina.
- Tekstvelden zijn minstens 16 px groot, zodat de iPhone niet inzoomt. De knoppen zijn groot genoeg om met een duim te raken.
- Het hamburgermenu verschijnt tot een schermbreedte van 1080 px.

## Duitse versie (later)

Het thema is voorbereid op een Duitse versie:
- Alle vaste teksten zijn vertaalbaar (text domain `valkenisse`).
- Vrije teksten uit het beheer worden bij **Polylang** aangemeld, onder Talen → Vertalingen.
- Links zoeken automatisch de vertaalde pagina op.
- In header en footer staat al een taalkeuzeblok (NL | DE). Dat wordt pas zichtbaar zodra er een tweede taal is.

Er wordt niets automatisch vertaald. Feitelijke gegevens (telefoon, prijzen) worden één keer ingevoerd en gelden voor alle talen.

## Techniek

- **Blokken** (categorie "Strandpaviljoen Valkenisse" in de editor): Melding van vandaag, Reserveer nu (bellen of mailen), Vandaag op het strand, Openingstijden, Tarieven strandhuisjes/strandverhuur, Strandhuisje: inbegrepen, Aanvraagformulier, Menukaart, Vacatures, Fotogalerij (met filters en lightbox), Gastreacties, Contactgegevens, Knoppen bellen/route/e-mail, Kaart, Kruimelpad, Social media, Copyright, Taalkeuze en Mobiele knoppenbalk. Ze worden server-side gerenderd, met een live voorbeeld in de editor.
- **Aanvraagformulier**: werkt zonder JavaScript en zonder formulierplugin. Het is beveiligd met een nonce, een honeypot, een minimale invultijd en maximaal 5 aanvragen per uur per IP-adres. De aanvrager krijgt een ontvangstbevestiging, en de tekst maakt duidelijk dat het een aanvraag is en geen definitieve reservering.
- **ACF** is bewust niet nodig: de gratis versie heeft geen optiepagina's, dus de beheerschermen zijn in de plugin zelf gebouwd. Dat betekent één plugin minder.
- **Controle** (tijdens de bouw):
  - Alle 447 blokken in de patronen, templates en template-parts zijn gevalideerd met de officiële `@wordpress/blocks`-parser: 0 ongeldig en 0 migraties nodig.
  - Alle PHP-bestanden zijn gelint.
  - De kernlogica (status van vandaag, automatisch vervallen, validatie) is getest.
  - Er is een visuele controle gedaan op desktop (1440 px) en mobiel (390 px).
  - Er is nog **niet** getest in een echte WordPress-installatie; die was vanuit de bouwomgeving niet te downloaden. Doe daarom na de installatie een korte rondgang door de editor.
- Placeholders opnieuw genereren: `node tools/make-placeholders.mjs`.
