# Dopamine Reset Coach — Support Site

This is the public marketing + legal site for the iOS app, served via **GitHub Pages**.

## Why this exists

Apple requires every App Store submission to have a publicly reachable:
- **Support URL** — where users get help
- **Privacy Policy URL** — where users read what data you collect
- **Marketing URL** (optional but recommended)

These URLs must be stable for the lifetime of the app — if they break, Apple can pull the app from sale.

Hosting on **GitHub Pages** means:
- ✅ Free forever (GitHub doesn't charge for public Pages sites)
- ✅ Tied to the repo's lifecycle — if the app exists, the site exists
- ✅ Custom domain ready (just add a `CNAME` file)
- ✅ No build step — pure HTML/CSS, deploys instantly on push
- ✅ HTTPS by default

## One-time setup (3 min, only once)

Already done if you can see this site live. Otherwise:

1. **Push your repo** to GitHub (if not already)
2. Open your GitHub repo → **Settings** tab → **Pages** in the left sidebar
3. Under "Build and deployment":
   - **Source**: Deploy from a branch
   - **Branch**: `main` (or whatever your default branch is)
   - **Folder**: `/docs`
4. Click **Save**
5. Wait ~1 min — GitHub Pages will publish at:
   **`https://dopamineresetcoach.github.io/dopamine-reset-coach/`**
6. Verify all 4 pages load:
   - `/` (index)
   - `/support.html`
   - `/privacy.html`
   - `/terms.html`

## Updating content

Just edit the HTML files in this folder, commit + push. GitHub Pages republishes within ~30 seconds.

Files:
- `index.html` — Landing page
- `support.html` — FAQ + contact info
- `privacy.html` — Privacy Policy
- `terms.html` — Terms of Use (EULA)
- `style.css` — Shared styles

## Want a custom domain? (Optional)

If you own e.g. `dopaminereset.coach`:

1. Add a file `CNAME` in this `docs/` folder containing just the domain:
   ```
   dopaminereset.coach
   ```
2. In your domain registrar's DNS panel:
   - Create a `CNAME` record from `www.dopaminereset.coach` → `dopamineresetcoach.github.io`
   - For apex (no `www`), create A records pointing to GitHub Pages IPs:
     - `185.199.108.153`
     - `185.199.109.153`
     - `185.199.110.153`
     - `185.199.111.153`
3. GitHub repo → Settings → Pages → enter the custom domain → Save
4. Wait for DNS propagation (~5 min to 24h)
5. Update fastlane URLs in `ios/fastlane/metadata/<locale>/*_url.txt` to use the new domain.

## Why these specific URLs in App Store Connect

The fastlane metadata at `ios/fastlane/metadata/<locale>/*_url.txt` all point here:
- `support_url.txt` → `…/support.html`
- `privacy_url.txt` → `…/privacy.html`
- `marketing_url.txt` → `…/` (root)

If you change the URL structure (e.g. add a `/v2/` prefix), update those metadata files too — otherwise Apple gets 404s during review.

## Email

The site uses `rebuildwithinofficial@gmail.com` as the support email across `index.html`, `support.html`, `privacy.html`, and `terms.html`. If you ever switch to a custom domain (e.g. `support@dopaminereset.coach`), find-and-replace `rebuildwithinofficial@gmail.com` across those 4 files.
