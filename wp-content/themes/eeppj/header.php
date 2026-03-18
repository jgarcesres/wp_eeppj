<?php
/**
 * Header template — Gov Banner + Sticky Header + Nav + Mobile Menu
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php wp_head(); ?>
</head>
<body <?php body_class('flex flex-col'); ?> style="min-height: 100vh;">

<?php get_template_part('template-parts/gov-banner'); ?>

<header class="bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl px-4">
    <div style="display: flex; align-items: center; justify-content: space-between; height: 4rem;" class="header-inner">
      <!-- Logo -->
      <a href="<?php echo esc_url(home_url('/')); ?>" style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
        <?php if (has_custom_logo()) : ?>
          <?php
          $logo_id = get_theme_mod('custom_logo');
          $logo_url = wp_get_attachment_image_url($logo_id, 'full');
          ?>
          <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="header-logo" />
        <?php else : ?>
          <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logo-EPJ.webp'); ?>" alt="EEPPJ Logo" class="header-logo" />
        <?php endif; ?>
      </a>

      <!-- Desktop Nav -->
      <nav class="desktop-nav" aria-label="Navegación principal">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'walker'         => new EEPPJ_Nav_Walker(),
            'fallback_cb'    => false,
        ]);
        ?>
      </nav>

      <!-- Search + PSE + Mobile Toggle -->
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <button id="search-toggle" class="header-icon-btn" aria-label="Buscar">
          <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>

        <a href="https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=13227"
           target="_blank" rel="noopener"
           class="btn-primary pse-btn header-pse-desktop"
           style="font-size: 0.875rem; padding: 0.5rem 1rem;">
          Pagar en Línea
        </a>

        <button id="mobile-menu-toggle" class="header-icon-btn mobile-only" aria-label="Menú" aria-expanded="false">
          <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Search overlay -->
  <div id="search-overlay" class="hidden" style="background: #fff; border-top: 1px solid #f3f4f6; padding: 1rem;">
    <div class="max-w-2xl">
      <?php get_search_form(); ?>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden mobile-menu-panel">
    <nav class="max-w-7xl px-4" style="padding-top: 1rem; padding-bottom: 2rem;" aria-label="Navegación móvil">
      <div class="space-y-1">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'walker'         => new EEPPJ_Mobile_Nav_Walker(),
            'fallback_cb'    => false,
        ]);
        ?>
        <a href="https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=13227"
           target="_blank" rel="noopener"
           class="btn-primary" style="display: block; width: 100%; text-align: center; margin-top: 1rem;">
          Pagar en Línea
        </a>
      </div>
    </nav>
  </div>
</header>

<main class="site-main" id="contenido-principal" style="flex: 1;">

<style>
  .header-logo { height: 2.5rem; width: auto; }
  .header-inner { height: 4rem; }
  .header-icon-btn {
    padding: 0.5rem;
    color: #6b7280;
    border-radius: 0.5rem;
    border: none;
    background: none;
    cursor: pointer;
    transition: color 0.15s, background-color 0.15s;
  }
  .header-icon-btn:hover { color: var(--color-brand-green); background-color: #f9fafb; }

  /* Desktop nav layout */
  .desktop-nav { display: none; align-items: center; gap: 0.25rem; }
  .desktop-nav .nav-item { position: relative; }
  .desktop-nav .nav-link {
    display: inline-block;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: color 0.15s, background-color 0.15s;
    text-decoration: none;
  }
  .text-brand-green-5 { background-color: rgba(140, 192, 75, 0.05); }
  .bg-brand-green-5 { background-color: rgba(140, 192, 75, 0.05); }
  .hover-text-brand-green:hover { color: var(--color-brand-green) !important; }
  .hover-bg-gray-50:hover { background-color: #f9fafb !important; }
  .hover-bg-brand-green-5:hover { background-color: rgba(140, 192, 75, 0.05) !important; }

  /* Dropdown */
  .nav-dropdown { display: none; position: absolute; left: 0; top: 100%; padding-top: 0.25rem; z-index: 60; }
  .nav-dropdown a { display: block; white-space: nowrap; text-decoration: none; }
  .nav-item:hover .nav-dropdown { display: block; }
  .nav-dropdown-link:hover { background: rgba(140,192,75,0.05) !important; color: var(--color-brand-green) !important; }

  /* Mobile */
  .mobile-only { display: block; }
  .mobile-menu-panel {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    background: #fff;
    border-top: 1px solid #f3f4f6;
    overflow-y: auto;
    z-index: 40;
  }
  .header-pse-desktop { display: none; }

  @media (min-width: 768px) {
    .header-logo { height: 3.5rem; }
    .header-inner { height: 5rem; }
    .header-pse-desktop { display: inline-flex; }
  }
  @media (min-width: 1024px) {
    .desktop-nav { display: flex; }
    .mobile-only { display: none; }
  }
</style>
