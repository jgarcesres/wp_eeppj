<?php
/**
 * Template Name: PQRRS
 *
 * Faithfully ports the Astro PQRRS page layout:
 * Blue gradient hero → sidebar info + form
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<!-- Hero banner — blue gradient with decorative shapes -->
<div class="pqrrs-hero">
  <div class="pqrrs-hero-bg" aria-hidden="true">
    <div class="pqrrs-hero-circle pqrrs-hero-circle--green"></div>
    <div class="pqrrs-hero-circle pqrrs-hero-circle--white"></div>
  </div>
  <div class="max-w-7xl px-4 pqrrs-hero-inner">
    <p class="pqrrs-hero-eyebrow">Empresas Públicas de Jericó</p>
    <h1 class="pqrrs-hero-title">PQRRS</h1>
    <p class="pqrrs-hero-desc">
      Peticiones, Quejas, Reclamos, Recursos y Sugerencias. Este espacio nos permite mejorar, facilitar y agilizar nuestros procesos al servicio de la comunidad jericoana.
    </p>
  </div>
</div>

<div class="max-w-7xl px-4 pqrrs-content">
  <div class="pqrrs-layout">

    <!-- Form (main content — shows first on mobile) -->
    <div class="pqrrs-form-col">
      <div class="pqrrs-form-card">
        <div class="pqrrs-form-card-header">
          <div class="pqrrs-form-card-icon" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </div>
          <div>
            <h2 class="pqrrs-form-card-title">Radicar solicitud</h2>
            <p class="pqrrs-form-card-subtitle">Los campos marcados con * son obligatorios.</p>
          </div>
        </div>

        <?php echo do_shortcode('[eeppj_pqrrs]'); ?>
      </div>
    </div>

    <!-- Sidebar (supplementary info — shows second on mobile) -->
    <div class="pqrrs-sidebar">

      <!-- Request types card -->
      <div class="pqrrs-info-card">
        <h2 class="pqrrs-info-card-title">Tipos de solicitud</h2>
        <div class="pqrrs-types-list">
          <?php
          $types = [
              ['letter' => 'P', 'name' => 'Petición', 'desc' => 'Solicitud de información o servicio', 'bg' => 'rgba(140,192,75,0.1)', 'color' => 'var(--color-brand-green)'],
              ['letter' => 'Q', 'name' => 'Queja', 'desc' => 'Inconformidad con la atención recibida', 'bg' => '#fffbeb', 'color' => '#d97706'],
              ['letter' => 'R', 'name' => 'Reclamo', 'desc' => 'Inconformidad con el servicio prestado', 'bg' => '#fef2f2', 'color' => '#dc2626'],
              ['letter' => 'R', 'name' => 'Recurso', 'desc' => 'Solicitud de revisión de una decisión', 'bg' => '#eff6ff', 'color' => '#2563eb'],
              ['letter' => 'S', 'name' => 'Sugerencia', 'desc' => 'Propuesta para mejorar el servicio', 'bg' => '#faf5ff', 'color' => '#9333ea'],
          ];
          foreach ($types as $t) : ?>
            <div class="pqrrs-type-item">
              <span class="pqrrs-type-letter" style="background: <?php echo $t['bg']; ?>; color: <?php echo $t['color']; ?>;"><?php echo $t['letter']; ?></span>
              <div>
                <p class="pqrrs-type-name"><?php echo esc_html($t['name']); ?></p>
                <p class="pqrrs-type-desc"><?php echo esc_html($t['desc']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Other channels card -->
      <div class="pqrrs-info-card pqrrs-info-card--muted">
        <h2 class="pqrrs-info-card-title">Otros canales</h2>
        <ul class="pqrrs-channels-list">
          <li class="pqrrs-channel-item">
            <svg class="pqrrs-channel-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Calle 7 No. 2 – 68, Jericó</span>
          </li>
          <li class="pqrrs-channel-item">
            <svg class="pqrrs-channel-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>PBX: +60 (4) 852 37 64</span>
          </li>
          <li class="pqrrs-channel-item">
            <svg class="pqrrs-channel-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <a href="mailto:contactenos@eeppj.com.co">contactenos@eeppj.com.co</a>
          </li>
          <li class="pqrrs-channel-item">
            <svg class="pqrrs-channel-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>L–V 7am–12m / 2pm–5pm</span>
          </li>
        </ul>
      </div>

      <!-- Reports card -->
      <div class="pqrrs-info-card">
        <h2 class="pqrrs-info-card-title">Informes PQRSD</h2>
        <ul class="pqrrs-reports-list">
          <?php
          $reports = [
              ['label' => '1er Trimestre 2025', 'url' => '/documents/PQRSD-1-TRIMESTRE-2025.pdf'],
              ['label' => '2do Trimestre 2025', 'url' => '/documents/PQRSD-2-TRIMESTRE-2025.pdf'],
              ['label' => '1er Semestre 2024', 'url' => '/documents/PQRSD-1-SEMESTRE-2024.pdf'],
          ];
          foreach ($reports as $r) : ?>
            <li>
              <a href="<?php echo esc_url(home_url($r['url'])); ?>" class="pqrrs-report-link">
                <svg class="pqrrs-report-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <?php echo esc_html($r['label']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <p class="pqrrs-privacy-note">
        Protección de Datos: Sus datos personales serán tratados conforme a la Ley 1581 de 2012.
      </p>
    </div>

  </div>
</div>

<style>
  /* ====== PQRRS Hero ====== */
  .pqrrs-hero {
    background: linear-gradient(135deg, var(--color-brand-blue-dark) 0%, var(--color-brand-blue) 50%, var(--color-brand-blue-light) 100%);
    position: relative;
    overflow: hidden;
  }
  .pqrrs-hero-bg {
    position: absolute;
    inset: 0;
    opacity: 0.08;
  }
  .pqrrs-hero-circle {
    position: absolute;
    border-radius: 9999px;
  }
  .pqrrs-hero-circle--green {
    top: 0; right: 0;
    width: 28rem; height: 28rem;
    background: var(--color-brand-green);
    transform: translate(30%, -45%);
  }
  .pqrrs-hero-circle--white {
    bottom: 0; left: 0;
    width: 18rem; height: 18rem;
    background: #fff;
    transform: translate(-20%, 45%);
  }
  .pqrrs-hero-inner {
    margin: 0 auto;
    padding-top: 2.5rem;
    padding-bottom: 2.5rem;
    position: relative;
  }
  .pqrrs-hero-eyebrow {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(191, 219, 254, 0.7);
    margin-bottom: 0.5rem;
  }
  .pqrrs-hero-title {
    font-size: 2rem;
    font-family: var(--font-heading);
    font-weight: 800;
    color: #fff;
    margin-bottom: 0.75rem;
    letter-spacing: -0.02em;
  }
  .pqrrs-hero-desc {
    color: #bfdbfe;
    font-size: 1rem;
    max-width: 40rem;
    line-height: 1.6;
  }

  @media (min-width: 768px) {
    .pqrrs-hero-inner {
      padding-top: 3.5rem;
      padding-bottom: 3.5rem;
    }
    .pqrrs-hero-title {
      font-size: 2.5rem;
    }
    .pqrrs-hero-desc {
      font-size: 1.125rem;
    }
  }

  /* ====== Content area ====== */
  .pqrrs-content {
    margin: 0 auto;
    padding-top: 2rem;
    padding-bottom: 3.5rem;
  }

  /* ====== Grid Layout ====== */
  .pqrrs-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  /* Mobile: form first, sidebar second */
  .pqrrs-form-col { order: 1; }
  .pqrrs-sidebar { order: 2; }

  @media (min-width: 1024px) {
    .pqrrs-layout {
      grid-template-columns: 22rem 1fr;
      gap: 2.5rem;
      align-items: start;
    }
    /* Desktop: sidebar left (narrow), form right (wide) */
    .pqrrs-form-col { order: 2; }
    .pqrrs-sidebar {
      order: 1;
      position: sticky;
      top: 6rem;
    }
  }

  @media (min-width: 1280px) {
    .pqrrs-layout {
      grid-template-columns: 24rem 1fr;
      gap: 3rem;
    }
  }

  /* ====== Form Card ====== */
  .pqrrs-form-card {
    background: #fff;
    border-radius: 1rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    padding: 1.5rem;
  }

  @media (min-width: 640px) {
    .pqrrs-form-card {
      padding: 2rem;
    }
  }

  @media (min-width: 1024px) {
    .pqrrs-form-card {
      padding: 2.5rem;
    }
  }

  .pqrrs-form-card-header {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #f3f4f6;
  }

  .pqrrs-form-card-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.625rem;
    background: linear-gradient(135deg, var(--color-brand-blue-dark), var(--color-brand-blue));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .pqrrs-form-card-title {
    font-size: 1.25rem;
    font-family: var(--font-heading);
    font-weight: 700;
    color: var(--color-brand-blue-dark);
    margin-bottom: 0.125rem;
  }

  .pqrrs-form-card-subtitle {
    color: var(--color-text-muted);
    font-size: 0.8125rem;
  }

  /* Override wrapper constraint */
  .pqrrs-form-col .pqrrs-form-wrapper { max-width: none; }

  /* ====== Sidebar Cards ====== */
  .pqrrs-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .pqrrs-info-card {
    background: #fff;
    border-radius: 0.875rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    padding: 1.25rem;
  }

  .pqrrs-info-card--muted {
    background: var(--color-surface-subtle);
    border-color: transparent;
    box-shadow: none;
  }

  .pqrrs-info-card-title {
    font-size: 0.9375rem;
    font-family: var(--font-heading);
    font-weight: 700;
    color: var(--color-brand-blue-dark);
    margin-bottom: 0.875rem;
    padding-bottom: 0.625rem;
    border-bottom: 2px solid var(--color-brand-green);
    display: inline-block;
  }

  /* Type items */
  .pqrrs-types-list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
  }

  .pqrrs-type-item {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
  }

  .pqrrs-type-letter {
    width: 1.875rem;
    height: 1.875rem;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 700;
    font-size: 0.8125rem;
  }

  .pqrrs-type-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.8125rem;
    line-height: 1.3;
  }

  .pqrrs-type-desc {
    font-size: 0.6875rem;
    color: var(--color-text-muted);
    line-height: 1.4;
  }

  /* Channel items */
  .pqrrs-channels-list {
    list-style: none;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    font-size: 0.8125rem;
  }

  .pqrrs-channel-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-text);
  }

  .pqrrs-channel-item a {
    color: var(--color-brand-blue);
  }

  .pqrrs-channel-icon {
    width: 0.9375rem;
    height: 0.9375rem;
    color: var(--color-brand-green);
    flex-shrink: 0;
  }

  /* Reports */
  .pqrrs-reports-list {
    list-style: none;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    font-size: 0.8125rem;
  }

  .pqrrs-report-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-brand-blue);
    text-decoration: none;
    padding: 0.375rem 0.5rem;
    border-radius: 0.375rem;
    margin: 0 -0.5rem;
    transition: background-color 0.15s;
  }

  .pqrrs-report-link:hover {
    background: rgba(49, 130, 206, 0.06);
  }

  .pqrrs-report-icon {
    width: 0.9375rem;
    height: 0.9375rem;
    flex-shrink: 0;
  }

  .pqrrs-privacy-note {
    font-size: 0.6875rem;
    color: var(--color-text-muted);
    font-style: italic;
    padding: 0 0.25rem;
    line-height: 1.5;
  }

  /* ====== Mobile: sidebar as horizontal scroll cards ====== */
  @media (max-width: 639px) {
    .pqrrs-sidebar {
      gap: 1rem;
    }
  }

  /* ====== Tablet: sidebar as 2-col grid ====== */
  @media (min-width: 640px) and (max-width: 1023px) {
    .pqrrs-sidebar {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .pqrrs-privacy-note {
      grid-column: 1 / -1;
    }
  }
</style>

<?php get_footer(); ?>
