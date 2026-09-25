// Zet de SVG-illustraties om naar PNG (daarna WebP/JPG via PHP GD, zie README).
// Gebruik: node render.mjs <map-met-svg> <uitvoermap>
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const [, , src, dst] = process.argv;
fs.mkdirSync(dst, { recursive: true });
const browser = await chromium.launch({ executablePath: process.env.CHROME_PATH || undefined });
const page = await browser.newPage();
for (const f of fs.readdirSync(src).filter((f) => f.endsWith('.svg'))) {
	const svg = fs.readFileSync(path.join(src, f), 'utf8');
	const [, w, h] = svg.match(/viewBox="0 0 (\d+) (\d+)"/);
	await page.setViewportSize({ width: +w, height: +h });
	await page.setContent(`<html><body style="margin:0">${svg}</body></html>`);
	await page.screenshot({ path: path.join(dst, f.replace('.svg', '.png')) });
	console.log('png', f);
}
await browser.close();
