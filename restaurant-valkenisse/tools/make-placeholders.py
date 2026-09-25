#!/usr/bin/env python3
"""Genereert sfeervolle SVG-placeholders (duinen, zee, bos, bord, studio) in de huisstijl.
Ze worden gebruikt totdat de eigenaar echte foto's plaatst. Elk bestand bevat een klein label
"Foto vervangen: …" zodat duidelijk is dat het een tijdelijke afbeelding is."""
import math, os, random, sys

OUT = os.path.join(os.path.dirname(__file__), '..', 'wp-content', 'themes', 'valkenisse', 'assets', 'img')
os.makedirs(OUT, exist_ok=True)

GRAIN = '''<filter id="grain" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency=".9" numOctaves="2" stitchTiles="stitch"/><feColorMatrix values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 .07 0"/><feComposite in2="SourceGraphic" operator="in"/></filter>'''

def dune(rng, w, h, base, amp, n=5, seed=0):
    r = random.Random(seed)
    pts = []
    steps = 48
    phase = [r.random() * 6.28 for _ in range(n)]
    freq = [0.6 + r.random() * 1.6 for _ in range(n)]
    for i in range(steps + 1):
        x = w * i / steps
        y = base
        for k in range(n):
            y -= amp / (k + 1) * math.sin(x / w * math.pi * freq[k] * (k + 1) + phase[k])
        pts.append((x, y))
    d = f'M0 {h} L' + ' L'.join(f'{x:.1f} {y:.1f}' for x, y in pts) + f' L{w} {h} Z'
    return d

def label(w, h, text, dark=True):
    fill = '#18271F' if dark else '#F8F5EF'
    return (f'<g opacity=".55" font-family="Helvetica, Arial, sans-serif" font-size="{max(16, w//90)}" letter-spacing="2" fill="{fill}">'
            f'<text x="{w-40}" y="{h-36}" text-anchor="end">FOTO VERVANGEN · {text.upper()}</text></g>')

def grass(rng, w, y0, count, color, seed):
    r = random.Random(seed)
    out = []
    for _ in range(count):
        x = r.random() * w
        hgt = 20 + r.random() * 70
        lean = (r.random() - .5) * 40
        out.append(f'<path d="M{x:.0f} {y0 + r.random()*30:.0f} q{lean/2:.0f} {-hgt/2:.0f} {lean:.0f} {-hgt:.0f}" stroke="{color}" stroke-width="2" fill="none" opacity=".55"/>')
    return ''.join(out)

def svg(name, w, h, body, defs=''):
    s = f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}" preserveAspectRatio="xMidYMid slice"><defs>{GRAIN}{defs}</defs>{body}<rect width="{w}" height="{h}" filter="url(#grain)"/></svg>'
    with open(os.path.join(OUT, name + '.svg'), 'w') as f:
        f.write(s)

def coast(name, w, h, sky, sun, sea, dunes, lbl, sun_y=.46, grass_col='#3b4a3a', lbl_dark=False, seed=1):
    defs = (f'<linearGradient id="sky-{name}" x1="0" y1="0" x2="0" y2="1">' + ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in sky) + '</linearGradient>'
            f'<radialGradient id="sun-{name}" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="{sun}" stop-opacity=".95"/><stop offset=".35" stop-color="{sun}" stop-opacity=".35"/><stop offset="1" stop-color="{sun}" stop-opacity="0"/></radialGradient>')
    horizon = h * .58
    body = f'<rect width="{w}" height="{h}" fill="url(#sky-{name})"/>'
    body += f'<circle cx="{w*.64:.0f}" cy="{h*sun_y:.0f}" r="{h*.42:.0f}" fill="url(#sun-{name})"/>'
    body += f'<circle cx="{w*.64:.0f}" cy="{h*sun_y:.0f}" r="{h*.045:.0f}" fill="{sun}" opacity=".9"/>'
    body += f'<rect y="{horizon:.0f}" width="{w}" height="{h-horizon:.0f}" fill="{sea}"/>'
    r = random.Random(seed)
    for i in range(26):
        y = horizon + 6 + i * i * 0.9
        x = w * .64 - (40 + i * 18) * (0.6 + r.random())
        body += f'<rect x="{x:.0f}" y="{y:.0f}" width="{(80 + i*36)*(0.6+r.random()*.8):.0f}" height="{1.5 + i*.12:.1f}" fill="{sun}" opacity="{max(.08, .5 - i*.018):.2f}"/>'
    for i, (col, base, amp) in enumerate(dunes):
        body += f'<path d="{dune(r, w, h, h*base, h*amp, seed=seed*10+i)}" fill="{col}"/>'
    body += grass(r, w, h * dunes[-1][1] - h * .05, 140, grass_col, seed)
    body += label(w, h, lbl, lbl_dark)
    svg(name, w, h, body, defs)

# Hero: gouden uur boven zee en duinen.
coast('hero-kust', 2400, 1500,
      [(0, '#2a3b3a'), (.38, '#8b8a78'), (.62, '#e7c9a0'), (.75, '#efd8b5')], '#fbe9c8', '#6f7f78',
      [('#b69c78', .72, .05), ('#8f7a5b', .80, .045), ('#3c4d3f', .90, .05), ('#24382f', .97, .04)], 'hero: terras of kust', sun_y=.5)
# Terras / restaurant sfeer: warm middaglicht.
coast('terras', 1800, 1200,
      [(0, '#c7d0c8'), (.55, '#efe4d0'), (.8, '#f6ecdc')], '#fff4de', '#9fb0a8',
      [('#dac7a6', .70, .04), ('#c3ab86', .80, .05), ('#6f7a5e', .92, .04)], 'terras', sun_y=.34, grass_col='#55604a', lbl_dark=True, seed=3)
# Omgeving: strand en zee.
coast('omgeving-strand', 1800, 1200,
      [(0, '#9fb3b4'), (.5, '#dfe3dc'), (.8, '#efe8dc')], '#ffffff', '#7e9896',
      [('#e8dcc6', .78, .015), ('#d9c7a6', .86, .02)], 'strand en zee', sun_y=.28, grass_col='#8a7a5a', lbl_dark=True, seed=5)
# Omgeving: duinen.
coast('omgeving-duinen', 1800, 1200,
      [(0, '#b9c3bd'), (.6, '#e9e2d2')], '#fff8ea', '#a4b3ae',
      [('#d8c7a6', .58, .10), ('#bda683', .72, .08), ('#7b7a58', .86, .06), ('#4d5a44', .95, .04)], 'duinen', sun_y=.22, grass_col='#46503d', lbl_dark=True, seed=7)

# Bos: stilistische dennen.
def forest(name, w, h, lbl):
    r = random.Random(11)
    body = f'<rect width="{w}" height="{h}" fill="#dfe0d3"/>'
    for layer, (col, scale, y) in enumerate([('#b7bca9', .7, .55), ('#7e8a73', .85, .68), ('#40533f', 1, .82), ('#243b31', 1.2, .98)]):
        x = -50
        while x < w + 100:
            th = h * .35 * scale * (0.7 + r.random() * .6)
            tw = th * .32
            base = h * y
            body += f'<path d="M{x:.0f} {base:.0f} L{x+tw/2:.0f} {base-th:.0f} L{x+tw:.0f} {base:.0f} Z" fill="{col}"/>'
            x += tw * (0.55 + r.random() * .5)
        body += f'<rect y="{h*y:.0f}" width="{w}" height="{h*(1-y)+2:.0f}" fill="{col}"/>'
    body += label(w, h, lbl, False)
    svg(name, w, h, body)
forest('omgeving-bos', 1800, 1200, 'bos en wandelpaden')

# Gerecht: bord van bovenaf op linnen.
def plate(name, w, h, lbl, bg, accent, seed):
    r = random.Random(seed)
    cx, cy, R = w * .5, h * .52, h * .36
    body = f'<rect width="{w}" height="{h}" fill="{bg}"/>'
    for i in range(0, w, 14):
        body += f'<rect x="{i}" width="1" height="{h}" fill="#000" opacity=".025"/>'
    body += f'<ellipse cx="{cx+18:.0f}" cy="{cy+26:.0f}" rx="{R*1.02:.0f}" ry="{R:.0f}" fill="#000" opacity=".08"/>'
    body += f'<circle cx="{cx:.0f}" cy="{cy:.0f}" r="{R:.0f}" fill="#f7f3ec"/><circle cx="{cx:.0f}" cy="{cy:.0f}" r="{R*.72:.0f}" fill="#efe8dc"/>'
    for _ in range(9):
        a = r.random() * 6.28; d = r.random() * R * .4
        body += f'<circle cx="{cx+math.cos(a)*d:.0f}" cy="{cy+math.sin(a)*d:.0f}" r="{R*(.1+r.random()*.14):.0f}" fill="{r.choice(accent)}" opacity=".92"/>'
    for _ in range(26):
        a = r.random() * 6.28; d = r.random() * R * .55
        body += f'<ellipse cx="{cx+math.cos(a)*d:.0f}" cy="{cy+math.sin(a)*d:.0f}" rx="{R*.05:.0f}" ry="{R*.022:.0f}" transform="rotate({r.random()*180:.0f} {cx+math.cos(a)*d:.0f} {cy+math.sin(a)*d:.0f})" fill="#4f6b3a" opacity=".85"/>'
    body += f'<rect x="{cx+R*1.25:.0f}" y="{cy-R*.9:.0f}" width="{R*.07:.0f}" height="{R*1.8:.0f}" rx="6" fill="#b9a98f"/>'
    body += f'<rect x="{cx-R*1.32:.0f}" y="{cy-R*.9:.0f}" width="{R*.07:.0f}" height="{R*1.8:.0f}" rx="6" fill="#b9a98f"/>'
    body += label(w, h, lbl, True)
    svg(name, w, h, body)
plate('gerecht-lunch', 1400, 1400, 'lunch', '#e6dac6', ['#d9a55b', '#e8c77a', '#b5553a', '#f1e1b8'], 21)
plate('gerecht-diner', 1400, 1400, 'diner', '#2b3a33', ['#8a4a33', '#c77b4a', '#e2c38f', '#6e2f23'], 22)
plate('gerecht-pizza', 1400, 1400, 'pizza', '#c8b495', ['#b8412d', '#e8c270', '#f4e3b5', '#9e2f21'], 23)
plate('gerecht-noord-afrikaans', 1400, 1400, 'noord-afrikaans menu', '#8a6649', ['#c86f35', '#e0a24d', '#7b8a3f', '#f0cf8a'], 24)

# Studio: raam met uitzicht.
def studio(name, w, h, lbl):
    body = f'<rect width="{w}" height="{h}" fill="#efe8dc"/>'
    body += f'<rect x="{w*.12:.0f}" y="{h*.14:.0f}" width="{w*.46:.0f}" height="{h*.62:.0f}" fill="#cfd6cf"/>'
    body += f'<path d="{dune(None, w*.46, h*.62, h*.62*.72, h*.62*.08, seed=41)}" transform="translate({w*.12:.0f} {h*.14:.0f})" fill="#b9a585"/>'
    body += f'<path d="{dune(None, w*.46, h*.62, h*.62*.86, h*.62*.06, seed=42)}" transform="translate({w*.12:.0f} {h*.14:.0f})" fill="#5c6b50"/>'
    body += f'<rect x="{w*.12:.0f}" y="{h*.14:.0f}" width="{w*.46:.0f}" height="{h*.62:.0f}" fill="none" stroke="#f8f5ef" stroke-width="26"/>'
    body += f'<line x1="{w*.35:.0f}" y1="{h*.14:.0f}" x2="{w*.35:.0f}" y2="{h*.76:.0f}" stroke="#f8f5ef" stroke-width="16"/>'
    body += f'<rect y="{h*.8:.0f}" width="{w}" height="{h*.2:.0f}" fill="#d9ccb6"/>'
    body += f'<rect x="{w*.52:.0f}" y="{h*.62:.0f}" width="{w*.44:.0f}" height="{h*.2:.0f}" rx="10" fill="#f8f5ef"/>'
    body += f'<rect x="{w*.52:.0f}" y="{h*.58:.0f}" width="{w*.44:.0f}" height="{h*.06:.0f}" rx="10" fill="#e6dac6"/>'
    body += f'<rect x="{w*.56:.0f}" y="{h*.52:.0f}" width="{w*.12:.0f}" height="{h*.08:.0f}" rx="16" fill="#8a6649"/>'
    body += f'<rect x="{w*.74:.0f}" y="{h*.52:.0f}" width="{w*.12:.0f}" height="{h*.08:.0f}" rx="16" fill="#243b31"/>'
    body += f'<rect x="{w*.66:.0f}" y="{h*.2:.0f}" width="{w*.001+3:.0f}" height="{h*.16:.0f}" fill="#8a6649"/><circle cx="{w*.66:.0f}" cy="{h*.2:.0f}" r="{h*.05:.0f}" fill="#e6c98f" opacity=".7"/>'
    body += label(w, h, lbl, True)
    svg(name, w, h, body)
studio('studio-interieur', 1800, 1200, "interieur studio")
studio('studio-detail', 1200, 1500, "studio")

# Feest: lichtsnoer boven een lange tafel in de avond.
def party(name, w, h, lbl):
    r = random.Random(51)
    body = f'<rect width="{w}" height="{h}" fill="#18271f"/>'
    for k in range(2):
        y0 = h * (.12 + k * .1)
        pts = [(x, y0 + math.sin(x / w * math.pi) * h * .12) for x in range(0, w + 1, 60)]
        body += '<path d="M' + ' L'.join(f'{x} {y:.0f}' for x, y in pts) + '" stroke="#57665a" stroke-width="2" fill="none"/>'
        for x, y in pts[1::2]:
            body += f'<circle cx="{x}" cy="{y+14:.0f}" r="{h*.03:.0f}" fill="#f3d9a0" opacity=".08"/><circle cx="{x}" cy="{y+14:.0f}" r="9" fill="#ffe7b0"/>'
    body += f'<rect x="{w*.08:.0f}" y="{h*.7:.0f}" width="{w*.84:.0f}" height="{h*.05:.0f}" fill="#e6dac6"/>'
    for i in range(14):
        x = w * .11 + i * w * .058
        body += f'<rect x="{x:.0f}" y="{h*.62:.0f}" width="10" height="{h*.08:.0f}" fill="#c8b495" opacity=".8"/><circle cx="{x+5:.0f}" cy="{h*.6:.0f}" r="7" fill="#ffd98a" opacity=".9"/>'
    body += f'<rect x="{w*.1:.0f}" y="{h*.75:.0f}" width="{w*.8:.0f}" height="{h*.25:.0f}" fill="#243b31"/>'
    body += label(w, h, lbl, False)
    svg(name, w, h, body)
party('feest', 1800, 1200, 'feest of buffet')

print('ok', sorted(os.listdir(OUT)))
