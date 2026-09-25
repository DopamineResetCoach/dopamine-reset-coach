#!/usr/bin/env python3
"""Tijdelijke illustraties (tot er echte foto's van Van Keulen zijn).

Genereert SVG's; tools/beelden/render.mjs zet ze om naar WebP/JPG.
"""
import os
OUT = os.path.join(os.path.dirname(__file__), 'svg')
os.makedirs(OUT, exist_ok=True)

MARINE = '#14284B'; MARINE_D = '#0B1830'; GROEN = '#2D6A43'; ZAND = '#E6DAC3'
ZAND_L = '#F1EBDF'; ZAND_D = '#B89D6E'; WIT = '#F7F5F0'; ANTR = '#2B2F33'


def svg(w, h, body, defs=''):
    return f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}"><defs>{defs}</defs>{body}</svg>'


def lucht(w, h, top='#B9CAD6', bottom='#F3EADB', id_='l'):
    return (f'<linearGradient id="{id_}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{top}"/><stop offset="1" stop-color="{bottom}"/></linearGradient>',
            f'<rect width="{w}" height="{h}" fill="url(#{id_})"/>')


def wolk(x, y, s=1, o=.7):
    return (f'<g opacity="{o}" fill="#fff" transform="translate({x} {y}) scale({s})">'
            '<ellipse cx="0" cy="0" rx="90" ry="26"/><ellipse cx="50" cy="-14" rx="60" ry="30"/><ellipse cx="-45" cy="-8" rx="50" ry="22"/></g>')


def bomen(x0, x1, y, h=60, kleur='#3F5F48', stap=38, seed=3):
    out = []
    i = 0
    x = x0
    while x < x1:
        hh = h * (0.7 + ((i * seed * 7919) % 10) / 22)
        out.append(f'<ellipse cx="{x}" cy="{y - hh / 2}" rx="{stap * .75}" ry="{hh / 2}" fill="{kleur}"/>')
        x += stap
        i += 1
    return ''.join(out)


def kerk(x, y, s=1, kleur='#556B63'):
    return (f'<g transform="translate({x} {y}) scale({s})" fill="{kleur}">'
            '<rect x="-14" y="-120" width="28" height="120"/><path d="M-14 -120 L0 -175 L14 -120Z"/>'
            '<rect x="14" y="-55" width="70" height="55"/><path d="M14 -55 L49 -85 L84 -55Z"/></g>')


def caravan(x, y, s=1, streep=MARINE, body=WIT):
    """x,y = linkeronderhoek van de opbouw (zonder wiel)."""
    return (f'<g transform="translate({x} {y}) scale({s})">'
            f'<rect x="-6" y="-8" width="330" height="10" rx="3" fill="#00000022"/>'
            f'<path d="M0 0 V-150 Q0 -170 20 -172 H250 Q300 -170 312 -120 L320 -40 V0 Z" fill="{body}" stroke="#D3CBBB" stroke-width="3"/>'
            f'<rect x="0" y="-62" width="320" height="14" fill="{streep}"/>'
            f'<rect x="0" y="-44" width="320" height="5" fill="{ZAND_D}"/>'
            f'<rect x="28" y="-135" width="80" height="48" rx="8" fill="#2B3A55"/>'
            f'<rect x="200" y="-135" width="78" height="48" rx="8" fill="#2B3A55"/>'
            f'<rect x="138" y="-150" width="44" height="130" rx="5" fill="#E5DFD3" stroke="#CFC6B6" stroke-width="3"/>'
            f'<rect x="172" y="-90" width="5" height="16" rx="2" fill="#8A8F95"/>'
            f'<path d="M-2 -8 L-90 -2" stroke="{ANTR}" stroke-width="9" stroke-linecap="round"/>'
            f'<rect x="-110" y="-10" width="28" height="14" rx="4" fill="{ANTR}"/>'
            f'<circle cx="215" cy="4" r="30" fill="{ANTR}"/><circle cx="215" cy="4" r="13" fill="#9AA0A6"/>'
            f'<rect x="-86" y="-2" width="8" height="30" fill="#6B7075"/>'
            '</g>')


def loods(x, y, w, h, kleur='#3C4A44', deur=True):
    lines = ''.join(f'<rect x="{x + i}" y="{y - h}" width="2" height="{h}" fill="#00000022"/>' for i in range(10, int(w), 18))
    d = ''
    if deur:
        dw = w * .38
        dx = x + w * .31
        d = (f'<rect x="{dx}" y="{y - h * .7}" width="{dw}" height="{h * .7}" fill="#1E2623"/>'
             f'<rect x="{dx}" y="{y - h * .7}" width="{dw}" height="10" fill="#00000055"/>')
    return (f'<path d="M{x - 12} {y - h} L{x + w / 2} {y - h - h * .32} L{x + w + 12} {y - h}Z" fill="#2A3430"/>'
            f'<rect x="{x}" y="{y - h}" width="{w}" height="{h}" fill="{kleur}"/>{lines}{d}')


def terrein(w, y0, h, kleur='#CFC6B6'):
    lijnen = ''.join(f'<path d="M{-200 + i * 160} {y0 + h} L{i * 160 + 40} {y0}" stroke="#BFB5A2" stroke-width="3"/>' for i in range(0, int(w / 160) + 3))
    return f'<rect x="0" y="{y0}" width="{w}" height="{h}" fill="{kleur}"/>{lijnen}'


def hek(x0, x1, y, h=46, kleur='#7F8A84'):
    posts = ''.join(f'<rect x="{x}" y="{y - h}" width="5" height="{h}" fill="{kleur}"/>' for x in range(int(x0), int(x1), 60))
    return f'{posts}<rect x="{x0}" y="{y - h}" width="{x1 - x0}" height="4" fill="{kleur}"/><rect x="{x0}" y="{y - h / 2}" width="{x1 - x0}" height="3" fill="{kleur}"/>'


def polder(w, y, kleur1='#9DB597', kleur2='#86A383'):
    return (f'<rect x="0" y="{y}" width="{w}" height="80" fill="{kleur1}"/>'
            f'<rect x="0" y="{y + 30}" width="{w}" height="60" fill="{kleur2}"/>')


# ---------------------------------------------------------------- hero
def hero():
    W, H = 2400, 1400
    d, sky = lucht(W, H)
    b = sky + wolk(1500, 230, 1.6) + wolk(2050, 380, 1.1, .6) + wolk(700, 300, 1.2, .5)
    b += polder(W, 820)
    b += bomen(0, 700, 840, 70, '#56735E', 44) + kerk(1880, 840, 1.25) + bomen(1960, 2400, 840, 60, '#56735E', 40)
    b += loods(80, 1010, 760, 260)
    b += terrein(W, 900, 500)
    b += hek(840, 2400, 910, 40)
    for i, x in enumerate([980, 1420, 1860]):
        b += caravan(x, 1150 + i * 18, 1.18, streep=[MARINE, GROEN, '#8C6D3F'][i])
    b += caravan(1250, 1390, 1.45, streep=MARINE)
    return svg(W, H, b, d)


def scene_caravan():
    W, H = 1600, 1200
    d, sky = lucht(W, H, '#C4D2DB', '#F3EADB')
    b = sky + wolk(1200, 200, 1.3) + wolk(400, 260, 1, .5)
    b += polder(W, 640) + bomen(0, 600, 660, 60, '#56735E', 40) + kerk(1350, 660, 1)
    b += loods(900, 820, 640, 230)
    b += terrein(W, 700, 500)
    b += caravan(330, 1060, 2.1, streep=MARINE)
    return svg(W, H, b, d)


def scene_boot():
    W, H = 1600, 1200
    d, sky = lucht(W, H, '#B7CCD9', '#EEF1EE')
    b = sky + wolk(1250, 220, 1.2) + wolk(350, 180, .9, .5)
    b += f'<rect x="0" y="640" width="{W}" height="120" fill="#7C9DB0"/><rect x="0" y="700" width="{W}" height="4" fill="#ffffff66"/>'
    b += polder(W, 740, '#9DB597', '#86A383') + bomen(1000, 1600, 760, 55, '#56735E', 38)
    b += terrein(W, 800, 400)
    # trailer
    b += f'<rect x="260" y="1000" width="1080" height="16" rx="6" fill="{ANTR}"/><path d="M260 1008 L120 1020" stroke="{ANTR}" stroke-width="12" stroke-linecap="round"/>'
    b += f'<circle cx="640" cy="1030" r="40" fill="{ANTR}"/><circle cx="640" cy="1030" r="17" fill="#9AA0A6"/><circle cx="780" cy="1030" r="40" fill="{ANTR}"/><circle cx="780" cy="1030" r="17" fill="#9AA0A6"/>'
    # romp
    b += f'<path d="M230 900 L1400 880 Q1420 882 1400 920 L1300 1000 L360 1000 Q260 980 230 900Z" fill="{WIT}" stroke="#D3CBBB" stroke-width="3"/>'
    b += f'<path d="M250 930 L1390 915 L1370 935 L262 948Z" fill="{MARINE}"/>'
    b += f'<path d="M560 890 L600 790 L980 790 L1060 880Z" fill="#E5DFD3" stroke="#CFC6B6" stroke-width="3"/><rect x="640" y="810" width="330" height="50" rx="6" fill="#2B3A55"/>'
    b += f'<rect x="1360" y="870" width="36" height="120" rx="8" fill="{ANTR}"/>'
    return svg(W, H, b, d)


def scene_aanhanger():
    W, H = 1600, 1200
    d, sky = lucht(W, H, '#C4D2DB', '#F3EADB')
    b = sky + wolk(1100, 200, 1.2) + polder(W, 620) + bomen(0, 1600, 640, 55, '#56735E', 42, 5)
    b += loods(80, 820, 560, 220)
    b += terrein(W, 700, 500)
    # vouwwagen (gesloten)
    b += (f'<rect x="220" y="870" width="520" height="150" rx="14" fill="{WIT}" stroke="#D3CBBB" stroke-width="3"/>'
          f'<rect x="220" y="850" width="520" height="34" rx="10" fill="{MARINE}"/><rect x="220" y="960" width="520" height="10" fill="{ZAND_D}"/>'
          f'<path d="M220 1000 L90 1012" stroke="{ANTR}" stroke-width="12" stroke-linecap="round"/>'
          f'<circle cx="480" cy="1030" r="40" fill="{ANTR}"/><circle cx="480" cy="1030" r="17" fill="#9AA0A6"/>')
    # aanhangwagen (open bak)
    b += (f'<rect x="900" y="900" width="560" height="120" rx="6" fill="{GROEN}"/>'
          f'<rect x="900" y="900" width="560" height="12" fill="#1F4B30"/>'
          + ''.join(f'<rect x="{x}" y="912" width="4" height="108" fill="#00000022"/>' for x in range(940, 1460, 70)) +
          f'<path d="M900 1000 L780 1012" stroke="{ANTR}" stroke-width="12" stroke-linecap="round"/>'
          f'<circle cx="1180" cy="1030" r="40" fill="{ANTR}"/><circle cx="1180" cy="1030" r="17" fill="#9AA0A6"/>')
    return svg(W, H, b, d)


def strandhuisje(x, y, w, h, wand, dak, deur):
    return (f'<g><path d="M{x - 10} {y - h} L{x + w / 2} {y - h - w * .42} L{x + w + 10} {y - h}Z" fill="{dak}"/>'
            f'<rect x="{x}" y="{y - h}" width="{w}" height="{h}" fill="{wand}"/>'
            + ''.join(f'<rect x="{x}" y="{yy}" width="{w}" height="2" fill="#00000014"/>' for yy in range(int(y - h + 14), int(y), 16)) +
            f'<rect x="{x + w * .3}" y="{y - h * .75}" width="{w * .4}" height="{h * .75}" fill="{deur}"/>'
            f'<rect x="{x - 8}" y="{y}" width="{w + 16}" height="12" fill="#9C8866"/></g>')


def scene_strandhuisjes():
    W, H = 1600, 1200
    d, sky = lucht(W, H, '#A9C3D6', '#F4ECDD')
    b = sky + wolk(1250, 220, 1.3) + wolk(300, 170, 1, .6)
    b += f'<rect x="0" y="600" width="{W}" height="90" fill="#6F97AE"/><rect x="0" y="640" width="{W}" height="3" fill="#ffffff80"/>'
    b += f'<path d="M0 690 Q400 660 800 690 T1600 680 V1200 H0Z" fill="#EADBB8"/>'
    b += f'<path d="M0 760 Q300 720 700 760 T1600 750 V1200 H0Z" fill="#E1CFA6"/>'
    kleuren = [(WIT, MARINE, '#5F86A3'), ('#DDE8DD', GROEN, '#F7F5F0'), (WIT, '#8C6D3F', MARINE), ('#E9D3B0', MARINE, '#F7F5F0'), (WIT, GROEN, '#C95B44'), ('#D7E1EA', MARINE, '#F7F5F0')]
    for i, (w1, d1, dr) in enumerate(kleuren):
        b += strandhuisje(90 + i * 245, 930, 190, 200, w1, d1, dr)
    b += f'<path d="M0 1000 Q500 960 1000 1000 T1600 990 V1200 H0Z" fill="#D8C393"/>'
    for x in range(40, 1600, 90):
        b += f'<path d="M{x} 1010 l-10 -50 M{x + 8} 1010 l2 -60 M{x + 16} 1010 l14 -46" stroke="#9A8A55" stroke-width="4" stroke-linecap="round"/>'
    return svg(W, H, b, d)


def scene_walcheren():
    W, H = 1600, 1200
    d, sky = lucht(W, H, '#B3C7D4', '#F4ECDD')
    b = sky + wolk(1150, 240, 1.4) + wolk(400, 180, 1, .6)
    b += f'<rect x="0" y="640" width="{W}" height="560" fill="#A5BB9C"/>'
    b += bomen(0, 520, 660, 70, '#4E6B57', 44) + kerk(800, 660, 1.4, '#4F6159') + bomen(900, 1600, 660, 60, '#4E6B57', 40, 7)
    for i in range(7):
        y = 700 + i * 72
        b += f'<rect x="0" y="{y}" width="{W}" height="36" fill="{["#93AE8B", "#B7C79B", "#8EAA88", "#C4CFA0"][i % 4]}"/>'
    b += f'<path d="M0 1080 Q800 1020 1600 1080 V1200 H0Z" fill="#7F9C7A"/>'
    b += f'<path d="M-50 1200 Q700 1000 1650 1120" stroke="#E4DCCB" stroke-width="44" fill="none"/>'
    return svg(W, H, b, d)


def scene_loods():
    W, H = 1600, 1200
    d, sky = lucht(W, H, '#C4D2DB', '#F3EADB')
    b = sky + wolk(1300, 180, 1.1) + polder(W, 520) + bomen(1300, 1600, 540, 60, '#56735E', 40)
    # grote loods met open deuren
    x, y, w, h = 120, 1000, 1200, 470
    b += f'<path d="M{x - 20} {y - h} L{x + w / 2} {y - h - 170} L{x + w + 20} {y - h}Z" fill="#2A3430"/>'
    b += f'<rect x="{x}" y="{y - h}" width="{w}" height="{h}" fill="#3C4A44"/>'
    b += ''.join(f'<rect x="{x + i}" y="{y - h}" width="3" height="{h}" fill="#00000022"/>' for i in range(14, w, 26))
    b += f'<rect x="{x + 250}" y="{y - 390}" width="700" height="390" fill="#252C29"/>'
    b += f'<rect x="{x + 250}" y="{y - 390}" width="700" height="14" fill="#00000066"/>'
    b += caravan(x + 330, y - 20, 1.1, streep=MARINE, body='#E9E5DC')
    b += caravan(x + 650, y - 10, .95, streep=GROEN, body='#DCD7CC')
    b += terrein(W, y, 200)
    return svg(W, H, b, d)


def og():
    W, H = 1200, 630
    d, sky = lucht(W, H)
    b = sky + wolk(900, 120, 1) + polder(W, 360) + bomen(0, 400, 380, 45, '#56735E', 30) + kerk(1040, 380, .8)
    b += terrein(W, 420, 210) + caravan(560, 560, .95, streep=MARINE)
    b += f'<rect x="0" y="0" width="560" height="{H}" fill="{MARINE_D}" opacity=".9"/>'
    b += (f'<text x="56" y="250" font-family="Archivo, Arial, sans-serif" font-size="30" font-weight="700" fill="{ZAND}" letter-spacing="4">VAN KEULEN</text>'
          f'<text x="56" y="315" font-family="Archivo, Arial, sans-serif" font-size="54" font-weight="800" fill="#fff">Caravanstalling</text>'
          f'<text x="56" y="370" font-family="Archivo, Arial, sans-serif" font-size="28" font-weight="500" fill="{ZAND_L}">Biggekerke · Walcheren · Zeeland</text>')
    return svg(W, H, b, d)


for naam, f in [('hero-terrein', hero), ('caravan', scene_caravan), ('boot', scene_boot), ('aanhanger', scene_aanhanger),
                ('strandhuisjes', scene_strandhuisjes), ('walcheren', scene_walcheren), ('loods', scene_loods), ('og-default', og)]:
    with open(os.path.join(OUT, naam + '.svg'), 'w') as fh:
        fh.write(f())
print('ok')
