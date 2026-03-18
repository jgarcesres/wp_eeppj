<?php
/**
 * Template Name: PQRRS
 *
 * Faithfully ports the Astro PQRRS page layout:
 * Blue gradient hero → 3-col layout (sidebar info + form)
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<!-- Hero banner — blue gradient with decorative circles -->
<div style="background: linear-gradient(to bottom right, var(--color-brand-blue-dark), var(--color-brand-blue), var(--color-brand-blue-light)); position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; opacity: 0.1;">
    <div style="position: absolute; top: 0; right: 0; width: 24rem; height: 24rem; background: var(--color-brand-green); border-radius: 9999px; transform: translate(33%, -50%);"></div>
    <div style="position: absolute; bottom: 0; left: 0; width: 16rem; height: 16rem; background: #fff; border-radius: 9999px; transform: translate(-25%, 50%);"></div>
  </div>
  <div class="max-w-7xl px-4" style="margin: 0 auto; padding-top: 2.5rem; padding-bottom: 2.5rem; position: relative;" class="pqrrs-hero-inner">
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: #fff; margin-bottom: 0.75rem;" class="pqrrs-hero-title">PQRRS</h1>
    <p style="color: #bfdbfe; font-size: 1.125rem; max-width: 42rem;">
      Peticiones, Quejas, Reclamos, Recursos y Sugerencias. Este espacio nos permite mejorar, facilitar y agilizar nuestros procesos al servicio de la comunidad jericoana.
    </p>
  </div>
</div>

<div class="max-w-7xl px-4" style="margin: 0 auto; padding-top: 2.5rem; padding-bottom: 3.5rem;">
  <div class="pqrrs-layout">
    <!-- Left sidebar -->
    <div class="pqrrs-sidebar" style="display: flex; flex-direction: column; gap: 1.5rem;">

      <!-- Request types card -->
      <div style="background: #fff; border-radius: 1rem; border: 1px solid #f3f4f6; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 1.5rem;">
        <h2 style="font-size: 1.125rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 1rem;">Tipos de solicitud</h2>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <?php
          $types = [
              ['letter' => 'P', 'name' => 'Petición', 'desc' => 'Solicitud de información o servicio', 'bg' => 'rgba(140,192,75,0.1)', 'color' => 'var(--color-brand-green)'],
              ['letter' => 'Q', 'name' => 'Queja', 'desc' => 'Inconformidad con la atención recibida', 'bg' => '#fffbeb', 'color' => '#d97706'],
              ['letter' => 'R', 'name' => 'Reclamo', 'desc' => 'Inconformidad con el servicio prestado', 'bg' => '#fef2f2', 'color' => '#dc2626'],
              ['letter' => 'R', 'name' => 'Recurso', 'desc' => 'Solicitud de revisión de una decisión', 'bg' => '#eff6ff', 'color' => '#2563eb'],
              ['letter' => 'S', 'name' => 'Sugerencia', 'desc' => 'Propuesta para mejorar el servicio', 'bg' => '#faf5ff', 'color' => '#9333ea'],
          ];
          foreach ($types as $t) : ?>
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
              <span style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: <?php echo $t['bg']; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: <?php echo $t['color']; ?>; font-weight: 700; font-size: 0.875rem;"><?php echo $t['letter']; ?></span>
              <div>
                <p style="font-weight: 600; color: #111827; font-size: 0.875rem;"><?php echo esc_html($t['name']); ?></p>
                <p style="font-size: 0.75rem; color: var(--color-text-muted);"><?php echo esc_html($t['desc']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Other channels card -->
      <div style="background: var(--color-surface-subtle); border-radius: 1rem; padding: 1.5rem;">
        <h2 style="font-size: 1.125rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 1rem;">Otros canales</h2>
        <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.875rem;">
          <li style="display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width:1rem;height:1rem;color:var(--color-brand-green);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span style="color: var(--color-text);">Calle 7 No. 2 – 68, Jericó</span>
          </li>
          <li style="display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width:1rem;height:1rem;color:var(--color-brand-green);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span style="color: var(--color-text);">PBX: +60 (4) 852 37 64</span>
          </li>
          <li style="display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width:1rem;height:1rem;color:var(--color-brand-green);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <a href="mailto:contactenos@eeppj.com.co" style="color: var(--color-brand-blue);">contactenos@eeppj.com.co</a>
          </li>
          <li style="display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width:1rem;height:1rem;color:var(--color-brand-green);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span style="color: var(--color-text);">L–V 7am–12m / 2pm–5pm</span>
          </li>
        </ul>
      </div>

      <!-- Reports card -->
      <div style="background: #fff; border-radius: 1rem; border: 1px solid #f3f4f6; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 1.5rem;">
        <h2 style="font-size: 1.125rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 1rem;">Informes PQRSD</h2>
        <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
          <?php
          $reports = [
              ['label' => '1er Trimestre 2025', 'url' => '/documents/PQRSD-1-TRIMESTRE-2025.pdf'],
              ['label' => '2do Trimestre 2025', 'url' => '/documents/PQRSD-2-TRIMESTRE-2025.pdf'],
              ['label' => '1er Semestre 2024', 'url' => '/documents/PQRSD-1-SEMESTRE-2024.pdf'],
          ];
          foreach ($reports as $r) : ?>
            <li>
              <a href="<?php echo esc_url(home_url($r['url'])); ?>" style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-brand-blue); text-decoration: none;">
                <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <?php echo esc_html($r['label']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <p style="font-size: 0.75rem; color: var(--color-text-muted); font-style: italic; padding: 0 0.5rem;">
        Protección de Datos: Sus datos personales serán tratados conforme a la Ley 1581 de 2012.
      </p>
    </div>

    <!-- Right: Form -->
    <div class="pqrrs-form-col">
      <div style="background: #fff; border-radius: 1rem; border: 1px solid #f3f4f6; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 1.5rem;" class="pqrrs-form-card">
        <h2 style="font-size: 1.25rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 0.25rem;">Radicar solicitud</h2>
        <p style="color: var(--color-text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">Los campos marcados con * son obligatorios.</p>

        <?php echo do_shortcode('[eeppj_pqrrs]'); ?>
      </div>
    </div>
  </div>
</div>

<style>
  .pqrrs-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }
  @media (min-width: 768px) {
    .pqrrs-hero-inner { padding-top: 3.5rem; padding-bottom: 3.5rem; }
    .pqrrs-hero-title { font-size: 2.25rem; }
    .pqrrs-form-card { padding: 2rem; }
  }
  @media (min-width: 1024px) {
    .pqrrs-layout {
      grid-template-columns: 1fr 2fr;
    }
  }

  /* Override the default prose-content styling on this page */
  .pqrrs-form-col .pqrrs-form-wrapper { max-width: none; }
</style>

<?php get_footer(); ?>
