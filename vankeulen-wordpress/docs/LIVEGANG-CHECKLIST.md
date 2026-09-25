# Livegang-checklist

Werk deze lijst af vóór het omzetten van het domein. Veel punten controleert **Van Keulen → Livegang-check** automatisch.

## Schone installatie
- [ ] Nieuwe WordPress-installatie in een **lege** database en map (niet bovenop de oude site).
- [ ] Alleen deze plugins: *Van Keulen Core* + eventueel SMTP, back-up, 2FA (zie README). Verwijder *Hello Dolly*, *Akismet* e.d. als ze niet gebruikt worden.
- [ ] Alleen het thema *Van Keulen Caravanstalling* + één standaard WordPress-thema als reserve. Verwijder de rest.
- [ ] Niets overgenomen van de oude site: geen database-export, geen `wp-content`, geen `.htaccess`-regels, geen trackingcodes.
- [ ] Livegang-check → onderdeel 3 “Uitgaande links, scripts en verborgen inhoud” is groen.
- [ ] Livegang-check → “Geen PHP-bestanden in de uploadmap” is groen.

## Accounts & beveiliging
- [ ] Geen gebruiker met de naam `admin`; maximaal 1–2 beheerders.
- [ ] Sterke, unieke wachtwoorden (wachtwoordmanager) en **tweestapsverificatie**.
- [ ] Eigenaar krijgt bij voorkeur de rol **Redacteur** voor dagelijks gebruik (kan pagina's, FAQ en bedrijfsgegevens beheren) en een apart beheerdersaccount voor updates.
- [ ] Automatische updates voor WordPress-kern (minor) aan; plugins/thema maandelijks controleren.
- [ ] `wp-config.php`: `WP_DEBUG` uit, unieke salts, `DISALLOW_FILE_EDIT` (zet de plugin al).
- [ ] Bij de host: XML-RPC blokkeren op serverniveau (optioneel; de plugin schakelt de functie al uit), firewall/WAF aan als beschikbaar.
- [ ] **Back-ups**: dagelijks, minimaal 14 dagen bewaard, buiten de server. Terugzetten één keer getest.

## Domein, SSL, e-mail
- [ ] SSL-certificaat actief; `http://` en `www.` sturen door naar één adres (bijv. `https://www.vankeulencaravanstalling.nl/`).
- [ ] Instellingen → Algemeen: WordPress-adres en site-adres met `https://`.
- [ ] SMTP ingesteld; SPF, DKIM en DMARC voor het domein.
- [ ] **Testaanvraag** verstuurd: komt aan bij Van Keulen, bevestiging komt aan bij de klant, staat onder Van Keulen → Aanvragen.

## Inhoud
- [ ] Alle punten uit `AAN-TE-LEVEREN.md` verwerkt; Livegang-check → onderdeel 2 is groen (geen placeholders meer).
- [ ] Tijdelijke illustraties vervangen door echte foto's (minimaal de hero).
- [ ] Beschikbaarheid ingesteld (of bewust op “niet tonen”).
- [ ] Telefoonnummer ingevuld → belknoppen en mobiele balk tonen “Bellen”.
- [ ] Privacybeleid gecontroleerd en aangevuld.

## Google
- [ ] Instellingen → Lezen: “Zoekmachines ontmoedigen” **uit**.
- [ ] Google Search Console: domein toevoegen, sitemap `https://…/wp-sitemap.xml` indienen.
- [ ] Oude URL's die in Google staan (bijv. `/default_bestanden/…htm`) met een 301 doorsturen naar de nieuwe pagina's (via `.htaccess` of de host). Zie `SEO.md`.
- [ ] Google Bedrijfsprofiel: website-link naar de nieuwe homepage (of `/contact/`), zelfde NAP-gegevens (naam, adres, telefoon) als op de website.
- [ ] Rich Results Test (search.google.com/test/rich-results) voor home en FAQ: LocalBusiness en Breadcrumb zonder fouten.
- [ ] PageSpeed Insights (mobiel) op de live site gecontroleerd.

## Na livegang (iedere paar maanden)
- [ ] Livegang-check opnieuw draaien.
- [ ] Updates bijwerken.
- [ ] Beschikbaarheid en mededeling actueel?
- [ ] Oude aanvragen worden automatisch opgeruimd (bewaartermijn in Bedrijfsgegevens).
