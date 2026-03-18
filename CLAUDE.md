# WP EEPPJ — Custom WordPress Theme

## What is this project?

Custom WordPress theme + plugins for Empresas Públicas de Jericó S.A E.S.P., a Colombian municipal utility company. This reproduces the design of the Astro static site (https://eeppjj.pages.dev) as a WordPress theme for deployment on ColombiaHosting (cPanel/LiteSpeed).

**Source design**: https://eeppjj.pages.dev (Astro site in `~/workspace/eeppjj`)
**This repo**: WordPress theme that faithfully matches that design
**Target hosting**: ColombiaHosting — cPanel, LiteSpeed, PHP 7.4+, WordPress 6.8

## Project structure

```
wp-content/
├── themes/eeppj/
│   ├── style.css              # Theme header
│   ├── functions.php          # Setup, walkers, icon helpers, auto-menu creation
│   ├── header.php             # Gov banner + sticky nav + mobile menu + search
│   ├── footer.php             # 4-col footer + cookie consent
│   ├── front-page.php         # Homepage (5 sections)
│   ├── page.php               # Generic page
│   ├── single.php             # Blog post
│   ├── index.php              # Blog listing / archive
│   ├── search.php             # Search results
│   ├── 404.php                # 404 page
│   ├── page-transparencia.php # Ley 1712 transparency (10 sections)
│   ├── page-contactenos.php   # Contact + map
│   ├── page-pqrrs.php         # PQRRS form page (sidebar + form)
│   ├── searchform.php         # Search form
│   ├── assets/
│   │   ├── css/main.css       # Full design system (tokens, typography, utilities)
│   │   ├── css/animations.css # Hero/card entrance animations
│   │   ├── js/header.js       # Mobile menu, scroll lock, search overlay
│   │   ├── js/cookie-consent.js
│   │   └── images/            # Theme images (logos, hero bg)
│   └── template-parts/
│       ├── gov-banner.php     # Gov.co compliance banner
│       ├── content-card.php   # Blog card (for grids)
│       ├── content-service.php # Service card (homepage)
│       └── document-list.php  # Document links with type icons
├── plugins/
│   ├── eeppj-pqrrs/          # PQRRS form handler plugin
│   │   ├── eeppj-pqrrs.php   # Main plugin file
│   │   ├── includes/          # PHP classes (form, handler, admin, validator, turnstile, email)
│   │   ├── assets/            # CSS + JS for form and admin
│   │   └── templates/form.php # Form HTML template
│   └── eeppj-carousel/       # Apple-style carousel Gutenberg block
│       ├── eeppj-carousel.php # Block registration + server render
│       └── build/             # Pre-built editor + frontend JS/CSS
```

## Key commands

```bash
docker compose up -d       # Start local dev (WP 6.8 + MySQL 8)
docker compose down        # Stop containers
docker compose down -v     # Stop + delete DB data
```

WP-CLI inside container:
```bash
docker compose exec wordpress wp <command> --allow-root
```

## Important conventions

- **PHP 7.4 compatible**: No union types, named arguments, match(), readonly, enums, or other PHP 8.0+ syntax. All files validated with `php:7.4-cli`.
- **No build step**: Theme CSS/JS are hand-written, not compiled. Carousel plugin uses pre-built files in `build/`.
- **Inline styles over utility classes**: Unlike the Astro/Tailwind source, this theme uses inline styles and scoped `<style>` blocks since there's no Tailwind processor. Only custom utility classes defined in `main.css` are used.
- **Spanish content**: All UI text, labels, and content is in Spanish for Colombian residents.
- **Gov.co compliance**: Blue banner at top is legally required for Colombian public entities.
- **Ley 1712 sections**: The 10 transparency sections are legally fixed — hardcoded in `page-transparencia.php`.
- **Page templates**: Transparencia, Contáctenos, and PQRRS use named templates (`Template Name:` header). Assign via Page Attributes in WP admin.
- **Nav menus**: Auto-created on theme activation via `after_switch_theme` hook. Editable in Appearance > Menus.
- **PQRRS Turnstile**: Skips verification when no keys configured (for local dev). In production, configure keys in PQRRS > Ajustes.
- **Docker**: Uses `wordpress:6.8-php8.1-apache` (closest to production). PHP 7.4 images are EOL on Docker Hub.

## Design tokens (from Astro `global.css`)

| Token | Value |
|-------|-------|
| Brand green | `#8cc04b` |
| Brand blue dark | `#1e4b75` |
| Brand blue | `#3182ce` |
| Brand red | `#bc2529` |
| Font sans | DM Sans |
| Font heading | Montserrat |
| Footer bg | `#212121` |

## Security

- PQRRS: rate limiting, honeypot, Turnstile CAPTCHA, file MIME+magic byte validation
- Admin pages: `current_user_can('manage_options')` + nonce verification
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- No inline `eval()` or `unserialize()` on user data
