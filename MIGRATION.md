# Migration Plan: EEPPJ WordPress Theme Cutover

**Target**: Replace the current `starter-blog` theme and 7 third-party plugins with our custom `eeppj` theme + `eeppj-pqrrs` + `eeppj-carousel` plugins on ColombiaHosting (cPanel/LiteSpeed).

**Current production inventory** (https://www.eeppj.com.co):
- WordPress 6.8.5 on LiteSpeed
- Theme: `starter-blog`
- Plugins (7): `contact-form-7`, `gutenberg`, `image-hover-effects-ultimate`, `kadence-blocks`, `menu-image`, `ml-slider`, `shared-files`
- Content: 34 pages, 50 posts, 916 media files, 2 categories, 1 admin user (`admineeppj`)

---

## Pre-Migration (Before Touching Production)

### 1. Full Backup via cPanel

**Do this first. Everything else depends on having a rollback.**

1. Log into cPanel at ColombiaHosting
2. **File backup**: File Manager → select `public_html/` → Compress → Download the ZIP
3. **Database backup**: phpMyAdmin → select the WP database → Export → Quick → SQL format → Download
4. Store both files somewhere safe (local machine, Google Drive, etc.)

### 2. Note Current Settings

While logged into WP Admin (https://www.eeppj.com.co/wp-admin):

1. **Settings → General**: Site title, tagline, timezone
2. **Settings → Reading**: What's the front page? (static page or latest posts), posts per page
3. **Settings → Permalinks**: Current permalink structure (should be `/%postname%/`)
4. **Appearance → Menus**: Screenshot the current menu structure (our theme auto-creates menus, but good to have as reference)
5. **Plugins**: Screenshot the plugins page for reference
6. **Users**: Note any additional users beyond `admineeppj`

### 3. Identify Content That Uses Plugin-Specific Shortcodes

Some plugins inject shortcodes into page/post content. These will break when the plugins are removed. Check for:

| Plugin | Shortcode to search for | Replacement |
|--------|------------------------|-------------|
| Contact Form 7 | `[contact-form-7 ...]` | Replace with `[eeppj_pqrrs]` on the PQRRS page, or remove from other pages |
| MetaSlider | `[metaslider id=...]` | Remove or replace with our carousel Gutenberg block |
| Shared Files | `[shared_files ...]` | Replace with direct links to uploaded documents |
| Image Hover Effects | `[jejejeobji ...]` or shortcodes from this plugin | Replace with standard images |
| Kadence Blocks | No shortcodes, but block markup | Blocks will render as plain HTML when plugin is removed — mostly safe |

**How to find them**: In WP Admin → use the Search box in Posts and Pages to search for `[contact-form`, `[metaslider`, `[shared_files`, etc. Note which pages contain them.

---

## Migration Steps

### Phase 1: Install New Theme & Plugins (Non-Destructive)

These steps don't change the live site's appearance yet.

1. **Upload theme**: Appearance → Themes → Add New → Upload Theme → upload `eeppj-theme.zip` from [GitHub Releases](https://github.com/jgarcesres/wp_eeppj/releases/latest)
2. **Upload plugins**:
   - Plugins → Add New → Upload Plugin → `eeppj-pqrrs.zip` → **Activate**
   - Plugins → Add New → Upload Plugin → `eeppj-carousel.zip` → **Activate**
3. **Configure PQRRS**:
   - Go to PQRRS → Ajustes
   - Set Turnstile Site Key and Secret Key ([see Turnstile setup in README](https://github.com/jgarcesres/wp_eeppj#3-set-up-cloudflare-turnstile-captcha))
   - Set notification email
   - Optional: Set webhook URL
4. **Do NOT activate the theme yet** — the old theme stays live while we prepare

### Phase 2: Prepare Page Templates

While the old theme is still active, create/update the pages that need our custom templates:

1. **Create PQRRS page** (if it doesn't exist):
   - Pages → Add New
   - Title: `PQRRS`
   - Slug: `pqrrs`
   - Content: Add a heading + `[eeppj_pqrrs]` shortcode (or leave empty — our template has the sidebar built in)
   - Publish (don't worry about template assignment yet — that happens after theme switch)

2. **Also create the long-slug alias** (matches the Astro site URL):
   - Pages → Add New
   - Title: `Peticiones, Quejas, Reclamos y Recursos`
   - Slug: `peticiones-quejas-reclamos-y-recursos`
   - Content: `[eeppj_pqrrs]`
   - Publish

3. **Verify these pages exist** with correct slugs:
   - `transparencia`
   - `contactenos`
   - `servicios`
   - `pqrrs`

   If any are missing, create them now. Our theme uses `page-{slug}.php` template matching, so the slug is what matters.

### Phase 3: Switch Theme (The Cutover)

**This is the moment the site changes. Best done during low-traffic hours (e.g., 6am–7am Colombia time).**

1. **Activate the theme**: Appearance → Themes → EEPPJ → Activate
   - This triggers `after_switch_theme` which auto-creates both navigation menus (primary + footer) with all the correct links

2. **Assign page templates** (must be done AFTER activation):
   - Pages → Transparencia → Edit → Page Attributes → Template: `Transparencia` → Update
   - Pages → Contáctenos → Edit → Page Attributes → Template: `Contáctenos` → Update
   - Pages → PQRRS → Edit → Page Attributes → Template: `PQRRS` → Update
   - Pages → Peticiones... → Edit → Page Attributes → Template: `PQRRS` → Update

3. **Set front page**:
   - Settings → Reading → "A static page"
   - Front page: select the homepage (or create one titled "Inicio" with slug `inicio`)
   - Posts page: select "Noticias" or "Blog" (or create one with slug `blog`)
   - Save

4. **Set permalink structure** (if not already):
   - Settings → Permalinks → Post name (`/%postname%/`)
   - Save

5. **Upload the logo**:
   - Appearance → Customize → Site Identity → Logo → Upload `Logo-EPJ.webp`
   - (Our theme includes a fallback logo, but the Customizer one takes priority)

### Phase 4: Clean Up Old Plugin Content

Now that the new theme is active, fix any broken shortcodes:

1. **Search for `[contact-form-7`** in Pages/Posts:
   - Remove the shortcode or replace with `[eeppj_pqrrs]` if it's a contact/PQRRS page
   - For simple contact forms, our Contáctenos template already has the contact info built in

2. **Search for `[metaslider`**:
   - Remove the shortcode
   - If the slider was important, replace with our Carousel Gutenberg block or a simple image

3. **Search for `[shared_files`**:
   - Replace with direct `<a href="/documents/filename.pdf">` links
   - Our Transparencia template already links to all the key documents

4. **Review pages visually**: Click through the top 10 most important pages to verify they render correctly:
   - Homepage (`/`)
   - Servicios (`/servicios`)
   - Acueducto (`/acueducto`)
   - Transparencia (`/transparencia`)
   - Contáctenos (`/contactenos`)
   - PQRRS (`/pqrrs`)
   - Blog (`/blog`)
   - A single blog post
   - Nuestra Empresa (`/nuestraempresa`)

### Phase 5: Deactivate & Remove Old Plugins

**Only after verifying the site works correctly with the new theme.**

1. Plugins → Deactivate each old plugin one at a time, checking the site after each:
   - `contact-form-7` — replaced by `eeppj-pqrrs`
   - `ml-slider` (MetaSlider) — replaced by `eeppj-carousel` or not needed
   - `kadence-blocks` — not needed (our theme + theme.json handle block styling)
   - `image-hover-effects-ultimate` — not needed (CSS hover effects built into theme)
   - `menu-image` — not needed (our nav walker handles menu rendering)
   - `shared-files` — not needed (documents linked directly)
   - `gutenberg` — not needed (WP 6.8 has Gutenberg built in; this plugin was for beta features)

2. After all are deactivated and site is verified: Plugins → Delete each old plugin

3. **Remove old theme**: Appearance → Themes → `starter-blog` → Theme Details → Delete

### Phase 6: Post-Migration Cleanup

1. **Test the PQRRS form**: Submit a test PQRRS on `/pqrrs/`, verify it appears in WP Admin → PQRRS
2. **Test mobile**: Check hamburger menu, scroll lock, responsive layout on a phone
3. **Check Gov.co banner**: Blue banner should appear at the top on all pages
4. **Verify search**: Use the search icon in the header
5. **Check 404 page**: Visit a non-existent URL like `/asdfasdf`
6. **Remove unused media** (optional): Media Library may have old slider images or plugin assets that are no longer referenced

---

## Rollback Plan

If something goes wrong during migration:

### Quick rollback (< 5 minutes):
1. Appearance → Themes → `starter-blog` → Activate
2. Plugins → Reactivate the old plugins
3. Site is back to previous state

### Full rollback (if database is corrupted):
1. cPanel → phpMyAdmin → Drop all tables in the WP database
2. Import the SQL backup taken in Pre-Migration step 1
3. cPanel → File Manager → Upload and extract the file backup
4. Site is fully restored to pre-migration state

---

## Post-Migration: Ongoing Updates

After migration, the theme and plugins auto-update from GitHub:

1. We push code changes to `github.com/jgarcesres/wp_eeppj`
2. We tag a release (e.g., `git tag v1.2.0 && git push origin v1.2.0`)
3. GitHub Actions builds ZIPs and creates a release
4. Within 6 hours, WP Admin shows "Update available" for the theme/plugins
5. Admin clicks Update — done

---

## Timeline Estimate

| Phase | Duration | Downtime? |
|-------|----------|-----------|
| Pre-migration (backup + notes) | 30 min | No |
| Phase 1 (install theme + plugins) | 10 min | No |
| Phase 2 (prepare pages) | 15 min | No |
| Phase 3 (switch theme) | 10 min | **~5 min visual disruption** |
| Phase 4 (fix shortcodes) | 20 min | No (cosmetic issues only) |
| Phase 5 (remove old plugins) | 10 min | No |
| Phase 6 (verify) | 15 min | No |
| **Total** | **~2 hours** | **~5 min** |
