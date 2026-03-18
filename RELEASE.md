# Release Process

Each component (theme, PQRRS plugin, carousel plugin) is released independently with its own version and tag.

## Tag format

```
theme/v{VERSION}       → builds eeppj-theme.zip
pqrrs/v{VERSION}       → builds eeppj-pqrrs.zip
carousel/v{VERSION}    → builds eeppj-carousel.zip
```

## How to release a component

### 1. Bump the version in code

**Theme** — edit one file:
```
wp-content/themes/eeppj/style.css  →  Version: X.Y.Z
```

**PQRRS plugin** — edit two values in one file:
```
wp-content/plugins/eeppj-pqrrs/eeppj-pqrrs.php  →  * Version: X.Y.Z
wp-content/plugins/eeppj-pqrrs/eeppj-pqrrs.php  →  define('EEPPJ_PQRRS_VERSION', 'X.Y.Z');
```

**Carousel plugin** — edit two values in one file:
```
wp-content/plugins/eeppj-carousel/eeppj-carousel.php  →  * Version: X.Y.Z
wp-content/plugins/eeppj-carousel/eeppj-carousel.php  →  define('EEPPJ_CAROUSEL_VERSION', 'X.Y.Z');
```

### 2. Commit and push

```bash
git add <changed files>
git commit -m "Bump theme to v1.4.0"
git push
```

### 3. Tag and push

```bash
git tag theme/v1.4.0
git push origin theme/v1.4.0
```

The release workflow will:
1. Validate that the tag version matches the version in the code files
2. Build the ZIP containing only that component
3. Create a GitHub Release with the ZIP attached

### 4. Update via wp-admin

Go to **Tools > EEPPJ Updates** and click **Verificar actualizaciones**. The new version will appear. Click **Actualizar** to apply.

## CI checks

On every push/PR to `main`:
- **PHP 7.4 lint** — catches syntax incompatible with target hosting
- **Version consistency** — verifies plugin header `Version:` matches the `define()` constant
- **Smoke tests** — spins up WordPress via wp-env, activates everything, hits all pages
- **Visual screenshots** — Playwright captures desktop + mobile screenshots (uploaded as artifacts)
- **E2E tests** — form rendering, validation, submission, navigation

On tag push:
- **Version validation** — the release workflow verifies the tag version matches the code. If you forget to bump, the release fails with a clear error instead of publishing stale ZIPs.

## How the auto-updater works

Each component has a GitHub updater class that:
1. Scans the 10 most recent GitHub Releases (not just "latest")
2. Finds the first release containing its specific asset ZIP
3. Compares the release version against the installed version
4. If newer, injects the update into WordPress's built-in update mechanism

This means components can release independently — a new carousel release won't affect the theme updater, and vice versa.

## Examples

Release only the carousel after fixing a bug:
```bash
# Edit version in eeppj-carousel.php (both header and constant)
git add wp-content/plugins/eeppj-carousel/eeppj-carousel.php
git commit -m "Fix carousel media save bug, bump to v1.4.0"
git push
git tag carousel/v1.4.0
git push origin carousel/v1.4.0
```

Release only the theme after a design update:
```bash
# Edit version in style.css
git add wp-content/themes/eeppj/style.css
git commit -m "Redesign PQRRS page, bump theme to v1.5.0"
git push
git tag theme/v1.5.0
git push origin theme/v1.5.0
```

## Requirements for wp-admin updates

WordPress needs `define('FS_METHOD', 'direct');` in `wp-config.php` to apply updates in containerized environments (Docker, k8s). On traditional cPanel hosting, this is not needed — Apache runs as the file owner by default.
