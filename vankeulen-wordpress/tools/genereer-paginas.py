#!/usr/bin/env python3
"""Genereert de paginapatronen (patterns/pagina-*.php) van het thema.

Hulpmiddel voor ontwikkelaars: de eigenaar past teksten gewoon aan in de
WordPress-editor. Draai dit script alleen om de standaardinhoud te wijzigen:

    python3 tools/genereer-paginas.py
"""
import json, os

DIR = os.path.join(os.path.dirname(__file__), '..', 'wp-content', 'themes', 'vankeulen', 'patterns')
PH = '[DOOR VAN KEULEN AAN TE LEVEREN]'


def attrs(d):
    return (' ' + json.dumps(d, ensure_ascii=False, separators=(',', ':'))) if d else ''


def cls_attr(*c):
    c = ' '.join(x for x in c if x)
    return f' class="{c}"' if c else ''


def h(level, text, cls=None, size=None):
    a = {}
    if level != 2:
        a['level'] = level
    if cls:
        a['className'] = cls
    if size:
        a['fontSize'] = size
    classes = ['wp-block-heading', cls or '', f'has-{size}-font-size' if size else '']
    return f'<!-- wp:heading{attrs(a)} -->\n<h{level}{cls_attr(*classes)}>{text}</h{level}>\n<!-- /wp:heading -->'


def p(text, cls=None, size=None):
    a = {}
    if cls:
        a['className'] = cls
    if size:
        a['fontSize'] = size
    classes = [cls or '', f'has-{size}-font-size' if size else '']
    return f'<!-- wp:paragraph{attrs(a)} -->\n<p{cls_attr(*classes)}>{text}</p>\n<!-- /wp:paragraph -->'


def kicker(text):
    return p(text, 'is-style-vk-bovenkop')


def ul(items, cls='is-style-vk-vinkjes', ordered=False):
    a = {}
    if ordered:
        a['ordered'] = True
    if cls:
        a['className'] = cls
    tag = 'ol' if ordered else 'ul'
    li = '\n\n'.join(f'<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->' for i in items)
    return f'<!-- wp:list{attrs(a)} -->\n<{tag}{cls_attr("wp-block-list", cls or "")}>{li}</{tag}>\n<!-- /wp:list -->'


def blk(name, a=None):
    return f'<!-- wp:{name}{attrs(a or {})} /-->'


def knop(**a):
    return blk('vankeulen/knop', a)


def knoppen(*items):
    inner = '\n\n'.join(items)
    return ('<!-- wp:group {"className":"vk-knoppen","layout":{"type":"flex","flexWrap":"wrap"}} -->\n'
            f'<div class="wp-block-group vk-knoppen">{inner}</div>\n<!-- /wp:group -->')


def group(inner, cls, tag='div', extra=None):
    a = {'className': cls, 'layout': {'type': 'default'}}
    if extra:
        a.update(extra)
    body = '\n\n'.join(inner) if isinstance(inner, (list, tuple)) else inner
    return f'<!-- wp:group{attrs(a)} -->\n<div class="wp-block-group {cls}">{body}</div>\n<!-- /wp:group -->'


def kaart(*inner, cls=''):
    return group(list(inner), ('is-style-vk-kaart ' + cls).strip())


def sectie(inner, cls='', bg=None, anchor=None, top='60', bottom='60'):
    a = {}
    if anchor:
        a['anchor'] = anchor
    a['align'] = 'full'
    a['className'] = ('vk-sectie ' + cls).strip()
    if bg:
        a['backgroundColor'] = bg
    a['style'] = {'spacing': {'padding': {'top': f'var:preset|spacing|{top}', 'bottom': f'var:preset|spacing|{bottom}'}}}
    a['layout'] = {'type': 'constrained', 'contentSize': '1240px'}
    classes = ['wp-block-group', 'alignfull', a['className']]
    if bg:
        classes += [f'has-{bg}-background-color', 'has-background']
    idattr = f' id="{anchor}"' if anchor else ''
    style = f'padding-top:var(--wp--preset--spacing--{top});padding-bottom:var(--wp--preset--spacing--{bottom})'
    body = '\n\n'.join(inner) if isinstance(inner, (list, tuple)) else inner
    return (f'<!-- wp:group{attrs(a)} -->\n<div{idattr} class="{" ".join(classes)}" style="{style}">{body}</div>\n<!-- /wp:group -->')


def cols(*columns, center=False):
    a = {}
    if center:
        a['verticalAlignment'] = 'center'
    a['style'] = {'spacing': {'blockGap': {'left': 'var:preset|spacing|60', 'top': 'var:preset|spacing|50'}}}
    out = []
    for width, inner in columns:
        ca = {}
        if center:
            ca['verticalAlignment'] = 'center'
        ca['width'] = width
        ccls = 'wp-block-column' + (' is-vertically-aligned-center' if center else '')
        body = '\n\n'.join(inner) if isinstance(inner, (list, tuple)) else inner
        out.append(f'<!-- wp:column{attrs(ca)} -->\n<div class="{ccls}" style="flex-basis:{width}">{body}</div>\n<!-- /wp:column -->')
    wcls = 'wp-block-columns' + (' are-vertically-aligned-center' if center else '')
    return f'<!-- wp:columns{attrs(a)} -->\n<div class="{wcls}">' + '\n\n'.join(out) + '</div>\n<!-- /wp:columns -->'


def img(key, ratio='4/3', cls='is-style-vk-foto'):
    # PHP-fragmenten: de afbeelding komt uit de Mediabibliotheek (na inrichten) of het thema.
    v = f'$img_{key.replace("-", "_")}'
    return (f'<?php {v} = vankeulen_img_block( \'{key}\' ); ?>'
            f'<!-- wp:image {{<?php echo {v}[\'json\']; ?>"aspectRatio":"{ratio}","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"{cls}"}} -->\n'
            f'<figure class="wp-block-image size-large {cls}"><img src="<?php echo {v}[\'url\']; ?>" alt="<?php echo {v}[\'alt\']; ?>" class="<?php echo trim( {v}[\'class\'] ); ?>" style="aspect-ratio:{ratio};object-fit:cover"/></figure>\n'
            '<!-- /wp:image -->')


def ph(tekst=''):
    return f'<mark class="vk-aanleveren">{PH}{(" " + tekst) if tekst else ""}</mark>'


def pattern(slug, title, desc, blocks):
    head = f"""<?php
/**
 * Title: {title}
 * Slug: vankeulen/{slug}
 * Categories: vankeulen-paginas
 * Description: {desc}
 * Inserter: no
 */
?>
"""
    body = '\n\n'.join(blocks) + '\n'
    with open(os.path.join(DIR, slug + '.php'), 'w') as f:
        f.write(head + body)


def prijs_en_links(type_, andere):
    return sectie([
        cols(
            ('55%', [h(2, 'Ook iets anders te stallen?'),
                     p('Bij Van Keulen Caravanstalling kunt u terecht voor verschillende soorten stalling. Combineren kan ook, bijvoorbeeld een caravan en een aanhangwagen.'),
                     ul([f'<a href="{u}">{t}</a>' for t, u in andere], 'vk-linklijst')]),
            ('45%', [kaart(blk('vankeulen/prijzen'), cls='vk-prijskaart')]),
        )
    ], bg='zand-licht')


ALLE = [
    ('Caravanstalling', '/caravanstalling/'),
    ('Bootstalling', '/bootstalling/'),
    ('Vouwwagen &amp; aanhanger stalling', '/vouwwagen-aanhanger-stalling/'),
    ('Strandhuisjes &amp; slaaphuisjes stallen', '/strandhuisjes-stalling/'),
    ('Stalling op Walcheren', '/caravanstalling-walcheren/'),
]


def zonder(url):
    return [x for x in ALLE if x[1] != url]


# ---------------------------------------------------------------- Caravanstalling
pattern('pagina-caravanstalling', 'Pagina: Caravanstalling', 'Caravanstalling: binnen/buiten, periode, tips en prijs.', [
    sectie([cols(
        ('56%', [h(2, 'Uw caravan gestald op Walcheren'),
                 p('Een caravan neemt thuis veel ruimte in en staat buiten het seizoen het liefst droog en uit de weg. Bij Van Keulen Caravanstalling in Biggekerke stalt u uw caravan midden op Walcheren, op korte afstand van Middelburg, Vlissingen en de kust.', size='medium'),
                 p('U kiest voor binnenstalling in een van de droge, geventileerde loodsen of voor een plaats op het verharde buitenterrein. Welke plaats beschikbaar en geschikt is, hangt af van de afmetingen van uw caravan en de gewenste periode.'),
                 knoppen(knop(soort='aanvragen', type='caravan'), knop(soort='bellen', stijl='secundair'))]),
        ('44%', [img('hero-terrein', '4/3')]),
        center=True)], top='60'),
    sectie([
        h(2, 'Binnenstalling of buitenstalling'),
        group([
            kaart(h(3, 'Binnenstalling', size='large'),
                  p('In droge, geventileerde loodsen. Uw caravan staat binnen, uit de regen en de wind.'),
                  ul(['Voor caravans, vouwwagens en aanhangwagens', 'Ook voor strandhuisjes'])),
            kaart(h(3, 'Buitenstalling', size='large'),
                  p('Op een verhard terrein van ongeveer 2.000 m². Geen modder of gras onder de wielen.'),
                  ul(['Voor caravans en aanhangwagens', 'Ook voor slaaphuisjes'])),
        ], 'vk-twee'),
    ], bg='zand-licht'),
    sectie([cols(
        ('50%', [h(2, 'Winterstalling of het hele jaar'),
                 p('Stalt u uw caravan alleen in de wintermaanden, of zoekt u een vaste plaats voor het hele jaar? Geef het aan in uw aanvraag, dan hoort u wat er mogelijk is.'),
                 p('Stallingsperiodes, brengen en ophalen: ' + ph())]),
        ('50%', [h(2, 'Tips voor de winterstalling'),
                 p('Algemene tips om uw caravan goed de stalling in te laten gaan:', 'vk-klein'),
                 ul(['Laat watertank, boiler en leidingen helemaal leeglopen (vorst).',
                     'Haal etenswaren en vochtige spullen uit de caravan.',
                     'Maak de caravan schoon en laat hem goed drogen.',
                     'Laad de accu op of neem hem mee naar huis.',
                     'Controleer de bandenspanning.'])]),
    )]),
    prijs_en_links('caravan', zonder('/caravanstalling/')),
])

# ---------------------------------------------------------------- Bootstalling
pattern('pagina-bootstalling', 'Pagina: Bootstalling', 'Bootstalling op Walcheren.', [
    sectie([cols(
        ('56%', [h(2, 'Uw boot na het vaarseizoen gestald'),
                 p('Walcheren ligt midden in een mooi vaargebied, met het Veerse Meer, het Kanaal door Walcheren en de Westerschelde in de buurt. Na het seizoen zoekt u een plek voor uw boot. Bij Van Keulen Caravanstalling in Biggekerke kunt u ook uw boot stallen.', size='medium'),
                 p('Omdat iedere boot anders is, bekijken we per aanvraag wat er mogelijk is. Geef daarom het type en de afmetingen zo goed mogelijk door.'),
                 knoppen(knop(soort='aanvragen', label='Vraag bootstalling aan', type='boot'), knop(soort='bellen', stijl='secundair'))]),
        ('44%', [img('boot', '4/3')]),
        center=True)]),
    sectie([cols(
        ('50%', [h(2, 'Dit willen we van uw boot weten'),
                 ul(['Het type boot (bijvoorbeeld sloep, zeilboot of speedboot)',
                     'De lengte, inclusief trailer en eventuele buitenboordmotor',
                     'De breedte',
                     'De hoogte op de trailer, inclusief kuiptent of mast (indien van toepassing)',
                     'Of de boot op een trailer staat',
                     'Vanaf wanneer en voor welke periode u wilt stallen'])]),
        ('50%', [h(2, 'Binnen of buiten?'),
                 p('Of uw boot binnen of buiten gestald kan worden, hangt af van het formaat en de beschikbaarheid.'),
                 p('Mogelijkheden voor boten (binnen/buiten, maximale afmetingen, op trailer of bok): ' + ph())]),
    )], bg='zand-licht'),
    prijs_en_links('boot', zonder('/bootstalling/')),
])

# ---------------------------------------------------------------- Vouwwagen & aanhanger
pattern('pagina-vouwwagen-aanhanger', 'Pagina: Vouwwagen & aanhanger stalling', 'Stalling voor vouwwagens en aanhangwagens.', [
    sectie([cols(
        ('56%', [h(2, 'Geen plek thuis? Stal hem in Biggekerke'),
                 p('Een vouwwagen of aanhangwagen gebruikt u maar een paar keer per jaar, maar thuis staat hij altijd in de weg. Bij Van Keulen Caravanstalling staat hij op Walcheren tot u hem weer nodig hebt.', size='medium'),
                 knoppen(knop(soort='aanvragen', type='vouwwagen'), knop(soort='bellen', stijl='secundair'))]),
        ('44%', [img('vouwwagen', '4/3')]),
        center=True)]),
    sectie([
        group([
            kaart(h(2, 'Vouwwagen stallen', size='large'),
                  p('Uw vouwwagen staat buiten het seizoen droog in een van de geventileerde loodsen.'),
                  p('Tip: berg het tentdoek altijd helemaal droog op, zo voorkomt u schimmel en vochtplekken.', 'vk-klein'),
                  knop(soort='aanvragen', stijl='tekstlink', label='Vouwwagen aanmelden', type='vouwwagen')),
            kaart(h(2, 'Aanhangwagen stallen', size='large'),
                  p('Aanhangwagens kunnen binnen in de loods of buiten op het verharde terrein worden gestald.'),
                  p('Geef in de aanvraag de afmetingen door, inclusief dissel en eventuele opbouw of huif.', 'vk-klein'),
                  knop(soort='aanvragen', stijl='tekstlink', label='Aanhanger aanmelden', type='aanhanger')),
        ], 'vk-twee'),
    ], bg='zand-licht'),
    prijs_en_links('aanhanger', zonder('/vouwwagen-aanhanger-stalling/')),
])

# ---------------------------------------------------------------- Strandhuisjes
pattern('pagina-strandhuisjes', 'Pagina: Strandhuisjes & slaaphuisjes', 'Stalling voor strandhuisjes en slaaphuisjes van de Zeeuwse kust.', [
    sectie([cols(
        ('56%', [h(2, 'Na het strandseizoen: een goede plek voor uw strandhuisje'),
                 p('Veel strandhuisjes en slaaphuisjes staan alleen in het strandseizoen aan de Zeeuwse kust. Daarna moeten ze ergens heen. Van Keulen Caravanstalling in Biggekerke biedt stallingsruimte voor strandhuisjes en slaaphuisjes, op korte afstand van de kust van Walcheren.', size='medium'),
                 p('Dat maakt Van Keulen anders dan veel andere caravanstallingen: hier kunt u niet alleen met uw caravan of boot terecht, maar ook met uw strandhuisje of slaaphuisje.'),
                 knoppen(knop(soort='aanvragen', label='Informeer naar de mogelijkheden', type='strandhuisje'), knop(soort='bellen', stijl='secundair'))]),
        ('44%', [img('strandhuisjes', '4/3')]),
        center=True)]),
    sectie([
        group([
            kaart(h(2, 'Strandhuisjes', size='large'),
                  p('Strandhuisjes worden binnen gestald, in een van de droge, geventileerde loodsen.'),
                  knop(soort='aanvragen', stijl='tekstlink', label='Strandhuisje aanmelden', type='strandhuisje')),
            kaart(h(2, 'Slaaphuisjes', size='large'),
                  p('Slaaphuisjes kunnen buiten worden gestald, op het verharde terrein van ongeveer 2.000 m².'),
                  knop(soort='aanvragen', stijl='tekstlink', label='Slaaphuisje aanmelden', type='slaaphuisje')),
        ], 'vk-twee'),
    ], bg='zand-licht'),
    sectie([cols(
        ('50%', [h(2, 'Wat we van u willen weten'),
                 ul(['Gaat het om een strandhuisje of een slaaphuisje?',
                     'Lengte, breedte en hoogte',
                     'Staat het huisje op een onderstel of trailer?',
                     'Vanaf wanneer u het wilt stallen'])]),
        ('50%', [h(2, 'Brengen en ophalen'),
                 p('Hoe het brengen en ophalen van strandhuisjes en slaaphuisjes verloopt, en of Van Keulen daarbij kan helpen: ' + ph()),
                 p('Twijfelt u? Neem gerust contact op.', 'vk-klein')]),
    )]),
    prijs_en_links('strandhuisje', zonder('/strandhuisjes-stalling/')),
])

# ---------------------------------------------------------------- Walcheren
pattern('pagina-walcheren', 'Pagina: Stalling op Walcheren', 'Lokale pagina: caravanstalling op Walcheren.', [
    sectie([cols(
        ('56%', [h(2, 'Caravanstalling midden op Walcheren'),
                 p('Zoekt u een caravanstalling op Walcheren? Van Keulen Caravanstalling ligt in Biggekerke, aan de westkant van Walcheren tussen Middelburg en de kust bij Zoutelande. Hier stalt u uw caravan, boot, vouwwagen, aanhangwagen, strandhuisje of slaaphuisje dicht bij huis.', size='medium'),
                 p('Een stalling in de buurt is praktisch: in het voorjaar haalt u uw caravan snel op, en na de vakantie staat hij zonder lange rit weer op zijn plek.')]),
        ('44%', [img('walcheren', '4/3')]),
        center=True)]),
    sectie([cols(
        ('50%', [h(2, 'Goed bereikbaar vanuit heel Walcheren'),
                 p('Biggekerke ligt centraal op het westelijk deel van Walcheren. Vanuit onder meer deze plaatsen bent u er snel:'),
                 ul(['Middelburg en Arnemuiden', 'Vlissingen, Souburg en Koudekerke', 'Zoutelande, Meliskerke en Westkapelle', 'Domburg, Oostkapelle en Serooskerke', 'Grijpskerke en Veere'], 'vk-kolommenlijst')]),
        ('50%', [h(2, 'Voor heel Zeeland'),
                 p('Woont u elders in Zeeland, of heeft u een strandhuisje, slaaphuisje of vakantiecaravan op Walcheren? Ook dan kunt u bij Van Keulen terecht. En staat uw strandhuisje of slaaphuisje in het seizoen aan de kust van Walcheren, dan is een stalling op korte afstand van het strand extra handig.'),
                 h(3, 'Wat kunt u stallen?', size='medium'),
                 ul(['<a href="/caravanstalling/">Caravans</a> – binnen of buiten', '<a href="/bootstalling/">Boten</a>', '<a href="/vouwwagen-aanhanger-stalling/">Vouwwagens en aanhangwagens</a>', '<a href="/strandhuisjes-stalling/">Strandhuisjes en slaaphuisjes</a>'], 'vk-linklijst')]),
    )], bg='zand-licht'),
    sectie([
        h(2, 'Route naar de stalling'),
        cols(
            ('38%', [blk('vankeulen/contactgegevens', {'metNaam': True}), p('Plan uw route of open de locatie in Google Maps.', 'vk-klein')]),
            ('62%', [blk('vankeulen/kaart')]),
        ),
    ]),
])

# ---------------------------------------------------------------- Over ons
pattern('pagina-over-ons', 'Pagina: Over Van Keulen', 'Over Van Keulen Caravanstalling.', [
    sectie([cols(
        ('56%', [h(2, 'Stalling in Biggekerke, persoonlijk geregeld'),
                 p('Van Keulen Caravanstalling is een stallingsbedrijf in Biggekerke, midden op Walcheren. U kunt hier terecht voor de stalling van caravans, boten, vouwwagens en aanhangwagens, en ook voor strandhuisjes en slaaphuisjes van de Zeeuwse kust.', size='medium'),
                 p('Er zijn droge, geventileerde loodsen voor binnenstalling en een verhard buitenterrein van ongeveer 2.000 m². Vragen over een stallingsplaats bespreekt u direct met Van Keulen.')]),
        ('44%', [img('loods', '4/3')]),
        center=True)]),
    sectie([cols(
        ('50%', [h(2, 'Het verhaal van Van Keulen'),
                 p(ph('Korte geschiedenis van het bedrijf: sinds wanneer, wie is Van Keulen, wat vindt u belangrijk?')),
                 p(ph('Eventueel een foto van de eigenaar of het terrein.'))]),
        ('50%', [h(2, 'Wat u kunt stallen'),
                 ul(['<a href="/caravanstalling/">Caravanstalling</a>', '<a href="/bootstalling/">Bootstalling</a>', '<a href="/vouwwagen-aanhanger-stalling/">Vouwwagen &amp; aanhanger stalling</a>', '<a href="/strandhuisjes-stalling/">Strandhuisjes &amp; slaaphuisjes</a>'], 'vk-linklijst'),
                 knoppen(knop(soort='aanvragen'))]),
    )], bg='zand-licht'),
])

# ---------------------------------------------------------------- FAQ
pattern('pagina-veelgestelde-vragen', 'Pagina: Veelgestelde vragen', 'Alle veelgestelde vragen.', [
    sectie([cols(
        ('66%', [blk('vankeulen/faq')]),
        ('34%', [kaart(h(2, 'Staat uw vraag er niet bij?', size='large'),
                       p('Neem contact op met Van Keulen Caravanstalling of vraag direct een stallingsplaats aan.'),
                       blk('vankeulen/contactgegevens', {'metNaam': False}),
                       knoppen(knop(soort='aanvragen', label='Stallingsplaats aanvragen')), cls='vk-plakkerig')]),
    )]),
])

# ---------------------------------------------------------------- Contact
pattern('pagina-contact', 'Pagina: Contact & aanvragen', 'Contactgegevens, openingstijden, aanvraagwizard en kaart.', [
    sectie([cols(
        ('38%', [h(2, 'Contactgegevens', size='large'),
                 blk('vankeulen/contactgegevens', {'metNaam': True}),
                 h(3, 'Openingstijden en contactmomenten', size='medium'),
                 blk('vankeulen/openingstijden'),
                 knoppen(knop(soort='bellen', label='Bel Van Keulen'), knop(soort='route', stijl='secundair', label='Route plannen'), knop(soort='whatsapp', stijl='secundair'))]),
        ('62%', [group([h(2, 'Stallingsplaats aanvragen', size='large'),
                        blk('vankeulen/beschikbaarheid', {'stijl': 'blok'}),
                        blk('vankeulen/aanvraagformulier')], 'vk-formulierkaart')]),
    )]),
    sectie([h(2, 'Locatie en route'),
            p('Van Keulen Caravanstalling ligt in Biggekerke, op Walcheren. Plan uw route of bekijk de locatie in Google Maps.'),
            blk('vankeulen/kaart')], bg='zand-licht'),
])

# ---------------------------------------------------------------- Privacy
pattern('pagina-privacybeleid', 'Pagina: Privacybeleid', 'Privacybeleid (AVG) – controleer en vul aan.', [
    sectie([group([
        p('Laatst bijgewerkt: ' + ph('datum'), 'vk-klein'),
        p('[vk_bedrijfsnaam], gevestigd aan [vk_adres], is verantwoordelijk voor de verwerking van persoonsgegevens zoals weergegeven in dit privacybeleid. KvK-nummer: ' + ph() + '.'),
        h(2, 'Welke gegevens verwerken wij?', size='large'),
        p('Als u het aanvraagformulier invult, verwerken wij de gegevens die u zelf opgeeft: naam, e-mailadres, telefoonnummer, woonplaats, gegevens over het object dat u wilt stallen (soort, afmetingen, merk/type), de gewenste periode en uw eventuele opmerking.'),
        h(2, 'Waarvoor gebruiken wij uw gegevens?', size='large'),
        ul(['Om contact met u op te nemen over uw aanvraag voor een stallingsplaats.', 'Om een stallingsovereenkomst met u aan te gaan en uit te voeren, als u daarvoor kiest.'], None),
        p('De grondslag is uw toestemming en/of de voorbereiding en uitvoering van een overeenkomst.'),
        h(2, 'Hoe lang bewaren wij uw gegevens?', size='large'),
        p('Aanvragen worden automatisch verwijderd na maximaal 12 maanden, tenzij er een stallingsovereenkomst uit voortkomt. Gegevens die wij op grond van de wet (bijvoorbeeld de fiscale bewaarplicht) moeten bewaren, bewaren wij zo lang als de wet voorschrijft.'),
        h(2, 'Delen met anderen', size='large'),
        p('Wij verkopen uw gegevens niet. Wij delen gegevens alleen met partijen die nodig zijn om de website en e-mail te laten werken, zoals onze hostingpartij (' + ph('naam hostingpartij') + ') en de dienst die het aanvraagformulier naar ons doorstuurt (' + ph('bijv. Web3Forms') + '). Met deze partijen zijn afspraken gemaakt over de beveiliging van uw gegevens.'),
        h(2, 'Cookies en Google Maps', size='large'),
        p('Deze website gebruikt geen tracking- of advertentiecookies. De kaart van Google Maps wordt pas geladen als u daar zelf op klikt. Lees meer in ons <a href="/cookiebeleid/">cookiebeleid</a>.'),
        h(2, 'Uw rechten', size='large'),
        p('U heeft het recht om uw gegevens in te zien, te laten corrigeren of te laten verwijderen, en om uw toestemming in te trekken. Stuur daarvoor een e-mail naar [vk_email]. Wij reageren binnen vier weken.'),
        p('Heeft u een klacht over de verwerking van uw gegevens? Dan kunt u contact opnemen met ons, of een klacht indienen bij de Autoriteit Persoonsgegevens.'),
        h(2, 'Beveiliging', size='large'),
        p('De website maakt gebruik van een beveiligde verbinding (SSL). Wij nemen passende maatregelen om misbruik, verlies en onbevoegde toegang tot uw gegevens te voorkomen.'),
    ], 'vk-tekstpagina')]),
])

# ---------------------------------------------------------------- Cookies
pattern('pagina-cookiebeleid', 'Pagina: Cookiebeleid', 'Cookiebeleid.', [
    sectie([group([
        p('Deze website is zo gebouwd dat er zo min mogelijk gegevens van bezoekers worden verzameld.'),
        h(2, 'Geen tracking- of advertentiecookies', size='large'),
        p('Wij gebruiken geen analyse-, tracking- of advertentiecookies en er worden geen gegevens gedeeld met advertentienetwerken. Daarom ziet u op deze website ook geen cookiemelding.'),
        h(2, 'Functionele cookies', size='large'),
        p('Alleen wanneer de beheerder van de website is ingelogd, plaatst WordPress functionele cookies die nodig zijn om in te loggen. Voor gewone bezoekers worden geen cookies geplaatst.'),
        h(2, 'Google Maps', size='large'),
        p('Op de contactpagina staat een kaart. Deze wordt pas geladen nadat u op “Kaart laden” klikt. Op dat moment worden gegevens met Google uitgewisseld en kan Google cookies plaatsen. Wilt u dat niet, gebruik dan het adres of de knop “Plan uw route”.'),
        h(2, 'Wijzigingen', size='large'),
        p('Wordt er in de toekomst toch een analyse- of marketingdienst toegevoegd, dan passen wij dit cookiebeleid aan en vragen wij waar nodig eerst uw toestemming.'),
    ], 'vk-tekstpagina')]),
])

print('Paginapatronen gegenereerd in', os.path.normpath(DIR))
