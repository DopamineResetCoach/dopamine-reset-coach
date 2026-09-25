#!/usr/bin/env python3
"""Bouwt de print-HTML voor de menukaart-PDF uit dezelfde data als de website
(wp-content/plugins/valkenisse-site/data/menukaart.csv). Render daarna met Chromium (zie README)."""
import base64, csv, html, os, sys

ROOT = os.path.join(os.path.dirname(__file__), '..')
THEME = os.path.join(ROOT, 'wp-content', 'themes', 'valkenisse', 'assets')
CSV = os.path.join(ROOT, 'wp-content', 'plugins', 'valkenisse-site', 'data', 'menukaart.csv')
OUT = sys.argv[1] if len(sys.argv) > 1 else os.path.join(ROOT, 'menukaart-pdf', 'menukaart.html')

def b64(path, mime):
    return f"data:{mime};base64,{base64.b64encode(open(path, 'rb').read()).decode()}"

font = lambda f: b64(os.path.join(THEME, 'fonts', f), 'font/woff2')
def img(f):
    # JPEG i.p.v. WebP: Chromium neemt JPEG ongewijzigd over in de PDF (veel kleiner bestand).
    import subprocess, tempfile
    tmp = os.path.join(tempfile.gettempdir(), f.rsplit('.', 1)[0] + '.jpg')
    subprocess.run(['php', '-r', '$i=imagecreatefromwebp($argv[1]); imagejpeg($i,$argv[2],84);', os.path.join(THEME, 'img', f), tmp], check=True)
    return b64(tmp, 'image/jpeg')

rows = list(csv.DictReader(open(CSV, encoding='utf-8'), delimiter=';'))
def items(cat):
    return [r for r in rows if r['categorie'] == cat]

e = html.escape
def price(p):
    return '€ ' + p if p else ''

def section(title, cat, intro='', cls=''):
    out = [f'<section class="sec {cls}"><h3>{e(title)}</h3>']
    if intro:
        out.append(f'<p class="intro">{e(intro)}</p>')
    out.append('<ul>')
    for r in items(cat):
        tag = '<span class="tag">vegetarisch</span>' if r['dieet'] == 'vegetarisch' and not cat.endswith('Vegetarisch') else ''
        desc = f'<p class="desc">{e(r["omschrijving"])}</p>' if r['omschrijving'] else ''
        out.append(f'<li><div class="row"><span class="name">{e(r["naam"])}{tag}</span><span class="dots"></span><span class="price">{price(r["prijs"])}</span></div>{desc}</li>')
    out.append('</ul></section>')
    return ''.join(out)

FOOT = 'Restaurant Valkenisse &nbsp;·&nbsp; Valkenisseweg 76, 4373 RP Biggekerke &nbsp;·&nbsp; 0118-566255 &nbsp;·&nbsp; restaurantvalkenisse.nl'
ALLERGY = 'Allergieën of dieetwensen? Meld het ons bij het bestellen. Wij serveren ook gerechten die geschikt zijn voor vegetariërs en voor een glutenvrij dieet.'

def sheet(kicker, title, body, n, cls=''):
    return f'''<div class="sheet {cls}"><header class="head"><p class="kicker">{kicker}</p><h2>{title}</h2></header>
<div class="body">{body}</div><footer class="foot"><span>{FOOT}</span><span>{n}</span></footer></div>'''

cover = f'''<div class="sheet cover">
<div class="cover-img" style="background-image:url({img('restaurant-valkenisse.webp')})"></div>
<div class="cover-text">
<p class="kicker">Genieten aan de Zeeuwse kust</p>
<h1>Restaurant<br>Valkenisse</h1>
<p class="cover-sub">Menukaart</p>
<div class="cover-index"><span>Lunch</span><span>Diner</span><span>Noord-Afrikaans menu</span><span>Pizza</span></div>
<p class="cover-addr">Valkenisseweg 76, 4373 RP Biggekerke<br>0118-566255 &nbsp;·&nbsp; restvalk@zeelandnet.nl &nbsp;·&nbsp; restaurantvalkenisse.nl</p>
</div></div>'''

lunch = sheet('Van de middag', 'Lunch',
    '<div class="cols">' + section("Tosti's en broodjes", "Lunch > Tosti's en broodjes")
    + section('12-uurtje', 'Lunch > 12-uurtje')
    + section('Pannenkoeken, poffertjes &amp; wafels'.replace('&amp;', '&'), 'Pannenkoeken, poffertjes & wafels') + '</div>'
    + f'<p class="note">{ALLERGY}</p>', 2)

diner1 = sheet('Voor de avond', 'Diner',
    f'''<div class="photos"><img src="{img('diner-vleesgerecht.webp')}" alt=""><img src="{img('diner-gamba-pasta.webp')}" alt=""></div>
<div class="cols">''' + section('Voorgerechten', 'Diner > Voorgerechten')
    + section('Vleesgerechten', 'Diner > Vleesgerechten', 'Alle vleesgerechten zijn inclusief friet.')
    + section('Visgerechten', 'Diner > Visgerechten', 'Alle visgerechten zijn inclusief friet.') + '</div>', 3)

diner2 = sheet('Diner', 'Vegetarisch, salades &amp; desserts',
    '<div class="cols">' + section('Vegetarisch', 'Diner > Vegetarisch')
    + section('Maaltijdsalades', 'Diner > Maaltijdsalades')
    + section('Bijgerechten', 'Diner > Bijgerechten')
    + section('Desserts', 'Diner > Desserts')
    + section('Kindergerechten', 'Kindermenu', 'Alle kindergerechten zijn inclusief kinderijsje en voor kinderen tot 12 jaar.') + '</div>'
    + f'<p class="note">Eén rekening per tafel &nbsp;·&nbsp; Eine Rechnung pro Tisch &nbsp;·&nbsp; One bill per table<br>{ALLERGY}</p>', 4)

na = items('Noord-Afrikaans menu')
tajines = ''.join(f'<li><div class="row"><span class="name">{e(r["naam"].replace("Menu met ", "").capitalize())}</span><span class="dots"></span><span class="price">{price(r["prijs"])}</span></div><p class="desc">{e(r["omschrijving"].replace("Tajine: ", "").capitalize())}</p></li>' for r in na)
noord = sheet('Driegangenmenu', 'Noord-Afrikaans menu', f'''
<div class="course"><p class="step">Vooraf</p><p class="dish">Gebakken sardientje op een bedje van frisse sla</p></div>
<div class="course"><p class="step">Hoofdgerecht – tajine naar keuze</p><p class="dish-intro">Tajine: Noord-Afrikaanse stoofpot met diverse groenten.</p><ul class="tajines">{tajines}</ul></div>
<div class="course"><p class="step">Tot besluit</p><p class="dish">Een kannetje verse muntthee</p></div>
<p class="note center">De prijs geldt voor het complete driegangenmenu.<br>Wij serveren ook gerechten die geschikt zijn voor vegetariërs en voor een glutenvrij dieet.</p>''', 5, 'na')

pizza = sheet('Uit de oven', 'Pizza',
    '<div class="cols">' + section('Pizza’s', 'Pizza').replace('<h3>Pizza’s</h3>', '') + '</div>', 6, 'pizza')

css = f'''
@font-face{{font-family:"Cormorant Garamond";font-weight:500;src:url({font('cormorant-garamond-latin-500-normal.woff2')}) format("woff2")}}
@font-face{{font-family:"Cormorant Garamond";font-weight:500;font-style:italic;src:url({font('cormorant-garamond-latin-500-italic.woff2')}) format("woff2")}}
@font-face{{font-family:"Cormorant Garamond";font-weight:600;src:url({font('cormorant-garamond-latin-600-normal.woff2')}) format("woff2")}}
@font-face{{font-family:Manrope;font-weight:200 800;src:url({font('manrope-latin-wght-normal.woff2')}) format("woff2")}}
@page{{size:A4;margin:0}}
:root{{--off:#F8F5EF;--sand:#E6DAC6;--sandl:#F1EADF;--dune:#C8B495;--earth:#8A6649;--green:#243B31;--deep:#18271F;--ink:#222623;--serif:"Cormorant Garamond",Garamond,serif;--sans:Manrope,system-ui,sans-serif}}
*{{box-sizing:border-box}}
html,body{{margin:0;background:#888}}
body{{font-family:var(--sans);color:var(--ink);-webkit-print-color-adjust:exact;print-color-adjust:exact}}
.sheet{{width:210mm;height:297mm;background:var(--off);position:relative;overflow:hidden;page-break-after:always;padding:20mm 18mm 26mm;display:flex;flex-direction:column}}
@media screen{{.sheet{{margin:10mm auto;box-shadow:0 4px 30px rgba(0,0,0,.25)}}}}
.kicker{{font-size:7.5pt;letter-spacing:.24em;text-transform:uppercase;color:var(--earth);font-weight:600;margin:0}}
.head{{border-bottom:.6pt solid rgba(34,38,35,.2);padding-bottom:6mm;margin-bottom:7mm}}
.head h2{{font-family:var(--serif);font-weight:500;font-size:40pt;line-height:1;margin:2mm 0 0;letter-spacing:-.01em}}
.body{{flex:1;min-height:0}}
.cols{{column-count:2;column-gap:12mm}}
.sec{{break-inside:avoid;margin:0 0 7mm}}
.sec h3{{font-family:var(--serif);font-weight:600;font-size:16pt;color:var(--green);margin:0 0 1.5mm}}
.intro{{font-size:8pt;font-style:italic;color:#5b605c;margin:0 0 2mm}}
ul{{list-style:none;margin:0;padding:0}}
li{{padding:2mm 0;break-inside:avoid}}
.row{{display:flex;align-items:baseline;gap:2mm}}
.name{{font-size:10pt;font-weight:600}}
.dots{{flex:1;border-bottom:.8pt dotted rgba(34,38,35,.35);transform:translateY(-1mm);min-width:4mm}}
.price{{font-size:10pt;font-weight:600;font-variant-numeric:tabular-nums;white-space:nowrap}}
.desc{{font-size:8.4pt;color:#5b605c;margin:.6mm 0 0;line-height:1.4;padding-right:14mm}}
.tag{{font-size:6.2pt;letter-spacing:.08em;text-transform:uppercase;color:var(--green);border:.6pt solid rgba(36,59,49,.45);border-radius:6pt;padding:.3mm 1.4mm;margin-left:2mm;vertical-align:1pt;font-weight:600}}
.note{{font-size:7.8pt;color:#5b605c;border-top:.6pt solid rgba(34,38,35,.2);padding-top:3mm;margin:2mm 0 0;line-height:1.5}}
.note.center{{text-align:center;border:0;margin-top:8mm}}
.foot{{position:absolute;left:18mm;right:18mm;bottom:11mm;display:flex;justify-content:space-between;font-size:6.8pt;color:#7a7f7b;letter-spacing:.02em}}
.photos{{display:grid;grid-template-columns:1fr 1fr;gap:3mm;margin:0 0 7mm}}
.photos img{{width:100%;height:52mm;object-fit:cover;display:block}}
/* omslag */
.cover{{padding:0;background:var(--deep);color:var(--sandl)}}
.cover-img{{height:170mm;background-size:cover;background-position:center 60%}}
.cover-text{{padding:12mm 18mm 0}}
.cover .kicker{{color:var(--dune)}}
.cover h1{{font-family:var(--serif);font-weight:500;font-size:48pt;line-height:.95;margin:3mm 0 4mm;color:#fff}}
.cover-sub{{font-family:var(--serif);font-style:italic;font-size:20pt;margin:0 0 7mm;color:var(--sand)}}
.cover-index{{display:flex;gap:6mm;font-size:8pt;letter-spacing:.16em;text-transform:uppercase;border-top:.6pt solid rgba(255,255,255,.2);border-bottom:.6pt solid rgba(255,255,255,.2);padding:3mm 0;margin-bottom:7mm}}
.cover-addr{{font-size:8.2pt;line-height:1.7;color:rgba(241,234,223,.8);margin:0}}
/* Noord-Afrikaans */
.na .body{{display:flex;flex-direction:column;align-items:center;text-align:center;padding-top:6mm}}
.course{{width:128mm;padding:6mm 0;border-bottom:.6pt solid rgba(34,38,35,.15)}}
.course:last-of-type{{border-bottom:0}}
.step{{font-size:7.5pt;letter-spacing:.2em;text-transform:uppercase;color:var(--earth);font-weight:600;margin:0 0 2.5mm}}
.dish{{font-family:var(--serif);font-size:17pt;margin:0;line-height:1.25}}
.dish-intro{{font-size:8.4pt;color:#5b605c;margin:0 0 3mm}}
.tajines{{text-align:left}}
.tajines .name{{font-family:var(--serif);font-size:14pt;font-weight:600}}
.tajines .price{{font-size:10pt}}
.tajines .desc{{padding-right:0}}
.pizza .cols{{column-gap:12mm}}
.pizza .sec{{break-inside:auto}}
.pizza li{{padding:2.6mm 0}}
'''
doc = f'<!doctype html><html lang="nl"><head><meta charset="utf-8"><title>Menukaart Restaurant Valkenisse</title><style>{css}</style></head><body>{cover}{lunch}{diner1}{diner2}{noord}{pizza}</body></html>'
open(OUT, 'w', encoding='utf-8').write(doc)
print(OUT, round(len(doc) / 1024), 'kB')
