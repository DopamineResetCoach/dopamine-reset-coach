# Fastlane — App Store Connect Uploads

This folder ships **all** the App Store Connect metadata and screenshots for
Dopamine Reset Coach in 12 languages. One command uploads everything.

## What's in here

- `metadata/<locale>/` — Per-locale text fields (12 locales × 9 files)
  - `name.txt` · `subtitle.txt` · `description.txt` · `keywords.txt`
  - `promotional_text.txt` · `release_notes.txt`
  - `support_url.txt` · `marketing_url.txt` · `privacy_url.txt`
- `metadata/review_information/` — Notes for Apple's review team (sandbox login, paywall access, IAP info)
- `screenshots/<locale>/iPhone67-N-name.png` — 5 screenshots per locale (1290×2796)
- `Fastfile` — Three lanes: `verify`, `metadata`, `submit`
- `Appfile` — App identifier + Apple ID
- `Deliverfile` — deliver action defaults

## One-time install (10 min)

```bash
# 1. Install Homebrew if you don't have it
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# 2. Install fastlane via brew
brew install fastlane

# 3. Verify
fastlane --version    # should print 2.x
```

## One-time App Store Connect API key (5 min)

1. Open [appstoreconnect.apple.com/access/api](https://appstoreconnect.apple.com/access/api)
2. Click **Generate API Key** (Team Keys tab)
3. Give it a name like "Fastlane Local", role **App Manager**
4. Download the `.p8` file — **save it somewhere safe**, you can only download it once
5. Note the **Key ID** (next to your key, ~10 chars) and **Issuer ID** (top of page, looks like UUID)
6. Drop the `.p8` file into this `fastlane/` folder (it's git-ignored)
7. Add to your shell (e.g. `~/.zshrc`) or run before each session:

```bash
export ASC_KEY_ID="ABC1234567"
export ASC_ISSUER_ID="69a6de70-..."
export ASC_KEY_FILEPATH="$HOME/dopamine-reset-coach/ios/fastlane/AuthKey_ABC1234567.p8"
```

## Three lanes

From `ios/` directory:

```bash
# Dry-run — checks files locally, doesn't talk to App Store
fastlane verify

# Upload metadata + screenshots, no submission (creates/updates draft v1.1)
fastlane metadata

# Upload + Submit for review
fastlane submit
```

**Recommended first run:** `fastlane verify` → `fastlane metadata` → review draft
in App Store Connect web UI → `fastlane submit` when ready.

## Updating for future releases

1. Edit `metadata/<locale>/release_notes.txt` for each language (or generate via an AI translator agent)
2. If screenshots changed: regenerate via `~/dopamine-screenshots/screenshots.mjs` and copy into `screenshots/<locale>/`
3. Bump `CFBundleShortVersionString` + `CFBundleVersion` in Xcode
4. Archive + upload IPA in Xcode (or via `fastlane gym` for full automation)
5. `fastlane submit`

Total time per release after first setup: ~5 min for metadata + ~10 min for the build/archive in Xcode.

## Notes

- The Apple Team ID is commented out in `Appfile` — fill it in if fastlane prompts you. Find it at developer.apple.com/account → Membership.
- `Deliverfile` sets `Health & Fitness` as primary category. Change if needed.
- The `.p8` file and any `.env` are git-ignored.
