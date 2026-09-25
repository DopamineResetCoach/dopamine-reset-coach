# SEO-plan Van Keulen Caravanstalling

Doel: gevonden worden door mensen op Walcheren en in Zeeland die een stalling zoeken, en ze direct laten aanvragen.
Uitgangspunt: **kwaliteit boven hoeveelheid** – geen tientallen bijna identieke dorpspagina's.

## Zoekwoorden per pagina

| Pagina | Hoofdzoekwoorden | Ondersteunend |
|---|---|---|
| Home `/` | caravanstalling Biggekerke, caravanstalling Walcheren | caravanstalling Zeeland, stalling strandhuisjes |
| `/caravanstalling/` | caravanstalling Zeeland, caravan stallen Zeeland | winterstalling caravan Zeeland, binnenstalling/buitenstalling caravan |
| `/bootstalling/` | bootstalling Walcheren, bootstalling Zeeland | boot stallen Walcheren, winterstalling boot |
| `/vouwwagen-aanhanger-stalling/` | vouwwagen stalling Zeeland, aanhanger stalling Zeeland | aanhangwagen stallen Walcheren |
| `/strandhuisjes-stalling/` | strandhuisje stallen Zeeland, strandhuisje winterstalling Zeeland | slaaphuisje stalling Zeeland |
| `/caravanstalling-walcheren/` | caravanstalling Walcheren, caravan stallen Walcheren | Middelburg, Vlissingen, Domburg, Zoutelande (bereikbaarheid) |
| `/veelgestelde-vragen/` | long-tail vragen (wat kost…, kan ik…) | FAQ-structured data |
| `/contact/` | Van Keulen Caravanstalling contact / adres / route | Dorpsstraat 57A Biggekerke |

Titels en meta-omschrijvingen zijn vooraf ingevuld (editor → zijbalk **Zoekmachines**). Houd titels onder ± 60 en omschrijvingen onder ± 155 tekens (de teller in de zijbalk helpt).

## Waarom geen `/caravanstalling-zeeland/`
Zo'n pagina zou dezelfde informatie herhalen als de homepage en `/caravanstalling/`, die al op “Zeeland” zijn geoptimaliseerd. Google waardeert dat niet (dunne/dubbele inhoud). Pas een Zeeland-pagina toe als er echt unieke inhoud is (bijv. klanten uit heel Zeeland met verhalen/foto's, aanvoerroutes vanaf Schouwen of Zeeuws-Vlaanderen).

## Lokale SEO
1. **Google Bedrijfsprofiel** is belangrijker dan welke pagina ook:
   - Categorie: *Caravanstalling* (hoofd), eventueel *Opslagfaciliteit* / *Bootopslag*.
   - Exact dezelfde NAP als de website: *Van Keulen Caravanstalling, Dorpsstraat 57A, 4373 AD Biggekerke*, zelfde telefoonnummer.
   - Website-link naar de homepage; afsprakenlink naar `/contact/#aanvraag`.
   - Echte foto's van terrein, loodsen, strandhuisjes (minimaal 10).
   - Openingstijden/“op afspraak” gelijk aan de website.
   - Vraag tevreden klanten om een review (geen reviews verzinnen of kopen).
2. Plak de profiellink in **Bedrijfsgegevens → Google Bedrijfsprofiel**: die gaat als `sameAs` in de LocalBusiness-structured data en wordt de “Bekijk locatie”-knop.
3. Vul de **coördinaten** in voor exacte `geo`-data en routeplanning.
4. Vermeldingen in bedrijvengidsen (De Telefoongids, Goudengids, caravanstallingen.nl, KampeerKenner): controleer dat naam/adres/telefoon overal gelijk zijn en de nieuwe website noemen.

## Technisch (al ingebouwd)
Eén H1 per pagina · logische H2/H3 · canonical · Open Graph · XML-sitemap `/wp-sitemap.xml` · `SelfStorage`/`LocalBusiness`, `BreadcrumbList`, `FAQPage` (alleen definitieve antwoorden) · kruimelpaden · interne links tussen alle stallingspagina's · alt-teksten · snelle, indexeerbare HTML zonder JavaScript-afhankelijkheid · noindex op privacy/cookies · geen dunne archiefpagina's.

> Let op: Google toont FAQ-rich-results sinds 2023 vooral voor overheids- en gezondheidssites. De FAQ-structured data blijft nuttig voor begrip van de pagina, maar verwacht geen uitklapvragen in de zoekresultaten.

## Oude URL's doorsturen
De oude site gebruikte adressen als `/default_bestanden/Page378.htm`. Zoek in Google `site:vankeulencaravanstalling.nl` welke nog geïndexeerd zijn en stuur ze door, bijvoorbeeld in `.htaccess` (Apache):

```apache
RedirectMatch 301 ^/default_bestanden/.*$ /
# of per pagina, bijv.:
# Redirect 301 /default_bestanden/Page378.htm /strandhuisjes-stalling/
```
