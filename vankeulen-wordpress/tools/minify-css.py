#!/usr/bin/env python3
"""Maakt theme.min.css uit theme.css (eenvoudige, veilige minificatie)."""
import os, re
d = os.path.join(os.path.dirname(__file__), '..', 'wp-content', 'themes', 'vankeulen', 'assets', 'css')
s = open(os.path.join(d, 'theme.css')).read()
s = re.sub(r'/\*.*?\*/', '', s, flags=re.S)
s = re.sub(r'\s+', ' ', s)
s = re.sub(r'\s*([{};,>])\s*', r'\1', s)
s = re.sub(r':\s+', ':', s)
s = s.replace(';}', '}')
# ':' binnen selectors zoals ":is(h1, h2)" en "a :is" blijven intact omdat alleen witruimte na ':' wegvalt.
open(os.path.join(d, 'theme.min.css'), 'w').write(s.strip() + '\n')
print(len(s), 'bytes')
