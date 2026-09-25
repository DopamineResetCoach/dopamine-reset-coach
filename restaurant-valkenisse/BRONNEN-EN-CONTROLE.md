# Bronnen en controlelijst vóór livegang

## Hoe de gegevens zijn verzameld

De opdracht was om restaurantvalkenisse.nl als enige feitelijke bron te gebruiken. Tijdens de bouw was die
website **niet rechtstreeks bereikbaar** vanuit de bouwomgeving (netwerkbeleid). De gegevens hieronder komen
uit zoekresultaten die uitsluitend naar pagina's op **restaurantvalkenisse.nl** verwezen. Deze samenvattingen
waren deels in het Engels vertaald. **Controleer daarom alles op de huidige website voordat de nieuwe site live gaat.**

Gevonden pagina's van de huidige site: `/`, `/contact/`, `/ons-menu/`, `/menukaart/lunch/`, `/menukaart/diner/`,
`/menukaart/pizza/`, `/menukaart/noord-afrikaans-menu/`, `/menukaart/pannenkoeken-poffertjes-wafels/`,
`/menukaart/kindermenu/`, `/overnachten/`, `/omgeving/`, `/fotos/binnen/`, `/fotos/gerechten/`,
`/product-categorie/koude-schotels/` ("5. Koude Schotels" – lijkt op een catering-/bestelmodule).
Voor al deze adressen zijn 301-doorverwijzingen ingesteld.

## Overgenomen (uit bron, graag bevestigen)

| Gegeven | Waarde | Waar gebruikt |
|---|---|---|
| Naam | Restaurant Valkenisse | overal |
| Adres | Valkenisseweg 76, 4373 RP Biggekerke | instellingen → overal |
| Telefoon | 0118-566255 | instellingen → overal |
| E-mail | restvalk@zeelandnet.nl | instellingen, formulieren |
| Ligging | "aan de duinen, in het bos, vlak bij het strand", "de beste vis- en vleesspecialiteiten" | teksten |
| Dieet | gerechten voor vegetariërs en glutenvrij dieet | teksten, dieetlabels |
| Menu-onderdelen | Lunch, Diner, Pizza, Noord-Afrikaans menu, Pannenkoeken/poffertjes/wafels, Kindermenu | categorieën |
| Diner | vlees- en visgerechten worden geserveerd met friet | intro categorie Hoofdgerechten |
| Noord-Afrikaans | tajine met kip, vis of garnalen en diverse groenten, met brood | categorie Noord-Afrikaans menu (3 tajine-menu's met prijs) |
| Reserveren | gasten wordt gevraagd vooraf te reserveren | tekst "Kom langs" |
| Openingstijden april–juni (vanaf Pasen) | 7 dagen vanaf 10:30, keuken 12:00–22:00 | instellingen |
| Juli en augustus | vanaf 10:00, keuken 12:00–22:00 | instellingen |
| September | di t/m zo 11:00–21:00, keuken 12:00–21:00 (ma gesloten) | instellingen |
| Oktober | do t/m zo 11:00–21:00, keuken 12:00–21:00 (ma–wo gesloten) | instellingen |
| Studio's | 2 studio's boven het restaurant, 2 personen, eigen ingang, kitchenette, zithoek, slaapkamer met boxsprings, douche en toilet, bedlinnen, koffie/thee, gratis wifi | studio's, overnachten |
| Parkeren | gratis parkeren naast het restaurant bij boeking studio | overnachten, contact |
| Huisregels | niet roken, geen huisdieren | overnachten |
| Kosten | excl. toeristenbelasting € 2,75 p.p.p.n. en € 100 borg | overnachten |
| Boeken studio | telefonisch of via Booking.com | overnachten (Booking-link nog niet ingevuld) |

## Nog aanvullen of controleren (staat als [placeholder] op de site)

- [ ] **Openingstijden november – Pasen**: niet gevonden. Nu toont de site "Voor de openingstijden in deze
      periode kunt u ons het beste even bellen." Vul periodes in onder *Openingstijden* als die er zijn.
- [ ] **Sluitingstijd april–augustus**: alleen "vanaf 10:30/10:00" gevonden. Nu "vanaf …". Vul een sluitingstijd in
      als u die wilt tonen (dan komt hij ook in Google).
- [ ] **Feesten & Partijen**: capaciteit (circa 100 personen?), verjaardagen/bruiloften/feesten/catering-teksten.
- [ ] **Buffetten** (barbecuebuffet, steengrill, Marokkaans buffet, warm en koud buffet): staan als **concept**.
      Alleen publiceren als ze nog actueel zijn, met omschrijving en prijs. "Koude schotels" (catering) toevoegen indien actueel.
- [ ] **Studio's**: namen (nu "Studio 1/2"), foto's, tarieven per nacht, aankomst-/vertrektijden.
      In de zoekresultaten werd ook **"La Casa de Mina"** genoemd (overdekt hoekterras met zithoek, schommelstoel,
      twee oude fietsen, minimaal 4 nachten) – controleer of dit een van de studio's is en neem het dan op.
- [ ] Toeristenbelasting en borg: kloppen de bedragen nog voor het huidige jaar?
- [ ] **Booking.com-link** voor de studio's invullen onder *Openingstijden → Contact & reserveren* (optioneel).
- [ ] **Reserveren**: gebruikt het restaurant een online reserveringssysteem? Zo ja: link invullen en
      "Extern reserveringssysteem" kiezen. Zo nee: formulier (standaard) of "alleen telefonisch".
- [ ] **Social media**-links (indien aanwezig).
- [ ] **Privacybeleid**: KvK-nummer, hostingpartij, datum; laten controleren.
- [ ] **Terras**: de teksten noemen het terras (lunch binnen of op het terras) – klopt dit?
- [x] **Hoofdfoto homepage**: foto van het restaurant met terras (aangeleverd door de opdrachtgever, `assets/img/restaurant-valkenisse.webp`).
      De setup zet hem in de mediabibliotheek, plaatst hem in de hero,
      gebruikt hem als deelafbeelding (Open Graph) en zet hem in de galerij (Restaurant, Terras).
- [x] **Dinerkaart**: twee gerechtfoto's (vleesgerecht met jus; gamba's met spaghetti) aangeleverd door de
      opdrachtgever. De setup zet ze in de mediabibliotheek en toont ze onder de titel "Diner" op de menukaart.
- [x] **Menukaart** (lunch, diner, Noord-Afrikaans menu, pizza): tekst en prijzen aangeleverd door de opdrachtgever,
      vastgelegd in `wp-content/plugins/valkenisse-site/data/menukaart.csv` (66 gerechten). De setup importeert deze
      kaart; dezelfde data wordt gebruikt voor de PDF in `menukaart-pdf/`. Alleen spelling en hoofdletters zijn
      rechtgezet (bijv. "Vitello tonnato", "Pizzabaguette", "Biefstuk van de grill – met pepersaus").
- [ ] **Noord-Afrikaans menu**: in de aangeleverde tekst stond bij de tajines met kip en vis "wordt geserveerd met brood of"
      – de zin houdt op. Nu staat er "geserveerd met brood". Aanvullen als er een keuze is (bijv. brood of couscous).
- [ ] **Dranken**: nog geen drankenkaart aangeleverd (categorie staat klaar en blijft verborgen zolang hij leeg is).
- [x] **Hoofdfoto Overnachten**: foto van een entree met terras (aangeleverd door de opdrachtgever,
      `assets/img/overnachten-studio.webp`), bovenaan de pagina Overnachten en in de galerij (Studio's).
- [x] **Studiofoto's**: gang, badkamer en inloopdouche (aangeleverd) in de fotogalerij op Overnachten en in de galerij (Studio's).
- [x] **Eten & drinken**: de dinerfoto (vleesgerecht) staat ook groot onder "Van zonnige lunch tot lange avond aan tafel" op de homepage en Restaurant.
- [x] **Omgeving**: panoramafoto van strand, zee en duinen (aangeleverd) onder "Zee, strand en duinen binnen handbereik" op de homepage en Overnachten, en in de galerij (Omgeving).
- [ ] **Controleren**: op de hoofdfoto van Overnachten staat huisnummer 62 en een entree op de begane grond, terwijl de huidige site
      vermeldt dat de studio's *boven* het restaurant (Valkenisseweg 76) liggen. Klopt de foto bij de studio's,
      of moeten tekst of foto worden aangepast?
- [ ] **Overige foto's**: de illustraties met "FOTO VERVANGEN" vervangen door echte foto's (restaurant, terras,
      gerechten, studio's, feest, omgeving) en de galerij verder vullen. Download de bestaande foto's van de huidige site
      (`/fotos/binnen/`, `/fotos/gerechten/`) als uitgangspunt.
- [ ] **Logo**: indien aanwezig uploaden via *Weergave → Editor → Patronen → Header → Sitelogo*. Zonder logo
      toont de site de naam als woordmerk.

## Bewust níet toegevoegd
Reviews, sterren, awards, keurmerken, historie ("sinds …"), aantallen gasten, garanties, reserveringspartners,
prijsklasse, Google Maps-embed en trackingcookies.
