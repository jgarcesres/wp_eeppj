# WP EEPPJ — WordPress Theme for Empresas Públicas de Jericó

Custom WordPress theme and plugins that faithfully reproduce the [Astro site design](https://eeppjj.pages.dev) for deployment on ColombiaHosting (cPanel/LiteSpeed).

## Quick Start (Docker)

```bash
docker compose up -d
# Visit http://localhost:8080
# Admin: http://localhost:8080/wp-admin (admin / admin123)
```

First-time setup installs WordPress, activates the theme, creates nav menus, and sample content automatically via WP-CLI (see below).

## What's Included

### Theme: `eeppj` (`wp-content/themes/eeppj/`)

| Template | Purpose |
|----------|---------|
| `front-page.php` | Homepage — hero, quick access, services, stats, news, CTA |
| `page.php` | Generic pages with green header + prose content |
| `single.php` | Blog posts with metadata, featured image, back button |
| `index.php` | Blog listing / archive with card grid + pagination |
| `page-transparencia.php` | Ley 1712 transparency mega-page (10 accordion sections) |
| `page-contactenos.php` | Contact info + Google Maps embed |
| `page-pqrrs.php` | PQRRS form page with sidebar info (matches Astro layout) |
| `search.php` | Search results |
| `404.php` | 404 page |

**Design system**: All brand tokens (colors, fonts, spacing) ported from Astro's `global.css` into `assets/css/main.css`. Animations (hero fade, card entrance) in `animations.css`. No build step — pure CSS.

**Navigation**: Auto-created on theme activation with all menu items matching the Astro site. Desktop dropdowns + mobile hamburger with scroll lock.

### Plugin: `eeppj-pqrrs` (`wp-content/plugins/eeppj-pqrrs/`)

PQRRS (Peticiones, Quejas, Reclamos, Recursos, Sugerencias) form handler.

- **Shortcode**: `[eeppj_pqrrs]`
- **Turnstile CAPTCHA** (Cloudflare) — configure in PQRRS > Ajustes
- **File upload validation** — MIME + magic bytes (PDF, PNG, JPG, DOCX)
- **Rate limiting** — 5 req/min per IP via transients
- **Honeypot** field
- **Admin panel** — stats, filterable list, detail modal, delete with nonce
- **Email + webhook** notifications
- **Database** — custom `wp_eeppj_pqrrs` table (created on activation)

### Plugin: `eeppj-carousel` (`wp-content/plugins/eeppj-carousel/`)

Apple-style content carousel Gutenberg block.

- **Autoplay** with configurable duration
- **Progress bar dots** — active dot fills over autoplay duration
- **Glass-morphism control bar** with play/pause
- **Touch/swipe**, keyboard nav, pause-on-hover
- **Gutenberg editor** with dark-themed slide editor UI
- No build step — pre-built JS/CSS

## Deployment to ColombiaHosting

### 1. Upload Theme & Plugins

```
# From the repo root, create ZIPs:
cd wp-content/themes && zip -r eeppj.zip eeppj/
cd wp-content/plugins && zip -r eeppj-pqrrs.zip eeppj-pqrrs/
cd wp-content/plugins && zip -r eeppj-carousel.zip eeppj-carousel/
```

- **Theme**: WP Admin → Appearance → Themes → Add New → Upload Theme → `eeppj.zip` → Activate
- **PQRRS Plugin**: WP Admin → Plugins → Add New → Upload Plugin → `eeppj-pqrrs.zip` → Activate
- **Carousel Plugin**: WP Admin → Plugins → Add New → Upload Plugin → `eeppj-carousel.zip` → Activate

### 2. Configure Pages

1. Create pages with these slugs and assign their templates:
   | Page | Slug | Template |
   |------|------|----------|
   | Transparencia | `transparencia` | Transparencia |
   | Contáctenos | `contactenos` | Contáctenos |
   | PQRRS | `pqrrs` | PQRRS |
   | Servicios | `servicios` | (default) |
   | Inicio | `inicio` | (default — set as static front page) |
   | Noticias | `blog` | (default — set as posts page) |

2. Settings → Reading → "A static page" → Front page: Inicio, Posts page: Noticias

### 3. Set Up Cloudflare Turnstile (CAPTCHA)

The PQRRS form uses Cloudflare Turnstile to prevent spam. Without keys configured, the form accepts submissions without CAPTCHA (suitable for local dev only).

**Get your Turnstile keys:**

1. Go to [Cloudflare Dashboard](https://dash.cloudflare.com/) → sign in (or create a free account)
2. In the left sidebar, click **Turnstile**
3. Click **Add site**
4. Fill in:
   - **Site name**: `EEPPJ PQRRS` (any descriptive name)
   - **Domain**: `eeppj.com.co` (or `www.eeppj.com.co` — add both if needed)
   - **Widget type**: **Managed** (recommended — invisible when possible, shows challenge only when needed)
5. Click **Create**
6. Cloudflare will display two keys:
   - **Site Key** (public — goes in the frontend widget)
   - **Secret Key** (private — used for server-side verification)

**Configure in WordPress:**

1. WP Admin → **PQRRS** → **Ajustes**
2. Paste the **Site Key** into "Turnstile Site Key"
3. Paste the **Secret Key** into "Turnstile Secret Key"
4. Set the **notification email** where you want to receive new PQRRS alerts
5. (Optional) Add a **Discord/Slack webhook URL** for instant notifications
6. Click **Guardar Ajustes**

The PQRRS form will now show the Turnstile widget. It fails closed — if the CAPTCHA verification fails or Cloudflare is unreachable, the submission is rejected.

### 4. PQRRS Admin Workflow

Submissions flow through these statuses:

```
pendiente → en_progreso → completada
                        → descartada
```

- **Pendiente**: New submission, not yet reviewed
- **En Progreso**: Being actively worked on
- **Completada**: Resolved and closed
- **Descartada**: Dismissed (spam, duplicate, etc.)

Change status directly from the submissions table via the dropdown, or reopen completed/dismissed items. Admin notes are visible only to logged-in administrators.

### 5. Optional: Webhook Notifications

To get instant notifications in Discord or Slack:

**Discord:**
1. Server Settings → Integrations → Webhooks → New Webhook
2. Copy the webhook URL
3. Paste in PQRRS → Ajustes → Webhook URL

**Slack:**
1. Go to [Slack API](https://api.slack.com/apps) → Create New App → Incoming Webhooks
2. Activate and copy the webhook URL
3. Paste in PQRRS → Ajustes → Webhook URL

## Releasing a New Version

The theme and both plugins auto-update from GitHub Releases. To ship a new version:

1. **Bump version numbers** in the relevant files:
   - Theme: `wp-content/themes/eeppj/style.css` (the `Version:` header)
   - PQRRS plugin: `wp-content/plugins/eeppj-pqrrs/eeppj-pqrrs.php` (both the header `Version:` and the `EEPPJ_PQRRS_VERSION` constant)
   - Carousel plugin: `wp-content/plugins/eeppj-carousel/eeppj-carousel.php` (both the header `Version:` and the `EEPPJ_CAROUSEL_VERSION` constant)

2. **Commit and tag:**
   ```bash
   git add -A && git commit -m "Bump version to 1.3.0"
   git tag v1.3.0
   git push origin main --tags
   ```

3. **GitHub Actions** automatically builds ZIP files for the theme and each plugin, then creates a GitHub Release with all three ZIPs attached as assets.

4. **WordPress installations** running the theme or plugins will auto-detect the new version within 6 hours (the GitHub API response is cached via a WP transient). Admins can then update from the standard WP Admin → Updates screen.

## Tech Details

- **WordPress**: 5.6+ (tested on 6.8)
- **PHP**: 7.4+ (no PHP 8.0+ features)
- **Server**: LiteSpeed (ColombiaHosting) or Apache
- **Fonts**: Google Fonts (DM Sans + Montserrat) — loaded via CDN
- **Gov.co compliance**: Blue banner included in header
