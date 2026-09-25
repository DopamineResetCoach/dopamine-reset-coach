# Statische preview (Netlify)

`preview-site/` is een statische, klikbare preview van de WordPress-site (alle pagina's in één `index.html`,
wisselen via het menu). Hij wordt gegenereerd uit het thema en de plugin met placeholder-gegevens en is
bedoeld om te delen met de familie, niet als echte website (`noindex`).

Deployen naar Netlify:

    npx netlify-cli deploy --dir strandpaviljoen-valkenisse/preview-site --prod

of sleep de map `preview-site` naar https://app.netlify.com/drop.
