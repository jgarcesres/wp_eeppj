#!/usr/bin/env bash
set -euo pipefail

WP="npx wp-env run cli wp"

echo "==> Activating theme and plugins..."
$WP theme activate eeppj
$WP plugin activate eeppj-pqrrs
$WP plugin activate eeppj-carousel

echo "==> Setting permalink structure..."
$WP rewrite structure '/%postname%/' --hard
$WP rewrite flush --hard

echo "==> Configuring PQRRS for CI (no Turnstile keys)..."
$WP option update eeppj_pqrrs_require_turnstile 0

echo "==> Creating pages..."

# Homepage
FRONT_ID=$($WP post create --post_type=page --post_title='Inicio' \
  --post_status=publish --post_name=inicio --porcelain)
$WP option update show_on_front page
$WP option update page_on_front "$FRONT_ID"

# PQRRS — uses Template Name: header, needs meta
PQRRS_ID=$($WP post create --post_type=page --post_title='PQRRS' \
  --post_status=publish --post_name=pqrrs --porcelain)
$WP post meta update "$PQRRS_ID" _wp_page_template page-pqrrs.php

# Transparencia
TRANSP_ID=$($WP post create --post_type=page --post_title='Transparencia' \
  --post_status=publish --post_name=transparencia --porcelain)
$WP post meta update "$TRANSP_ID" _wp_page_template page-transparencia.php

# Contáctenos
CONTACT_ID=$($WP post create --post_type=page --post_title='Contáctenos' \
  --post_status=publish --post_name=contactenos --porcelain)
$WP post meta update "$CONTACT_ID" _wp_page_template page-contactenos.php

# Blog listing page
BLOG_ID=$($WP post create --post_type=page --post_title='Noticias' \
  --post_status=publish --post_name=blog --porcelain)
$WP option update page_for_posts "$BLOG_ID"

# Sample post so blog/single templates have content
$WP post create --post_type=post --post_title='Aviso de prueba' \
  --post_status=publish --post_content='Contenido de prueba para CI.'

echo "==> WordPress seeded successfully."
