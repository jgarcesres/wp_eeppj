<?php
/**
 * Homepage template — Hero, Quick Access, Services, Stats, News, CTA
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();

// Get recent posts
$recent_posts = new WP_Query([
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<!-- ====== HERO ====== -->
<section class="hero-section relative overflow-hidden" style="background-color: var(--color-brand-blue-dark);">
  <!-- Background image with overlay -->
  <div class="absolute inset-0">
    <img
      src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/jerico-hero.webp'); ?>"
      alt=""
      style="width: 100%; height: 100%; object-fit: cover;"
      loading="eager"
    />
    <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(30,75,117,0.9), rgba(30,75,117,0.7), rgba(30,75,117,0.4));"></div>
    <!-- Topographic contour overlay -->
    <div class="absolute inset-0" style="opacity: 0.04; background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22><defs><pattern id=%22t%22 patternUnits=%22userSpaceOnUse%22 width=%22200%22 height=%22200%22><circle cx=%22100%22 cy=%22100%22 r=%2230%22 fill=%22none%22 stroke=%22white%22 stroke-width=%221%22/><circle cx=%22100%22 cy=%22100%22 r=%2260%22 fill=%22none%22 stroke=%22white%22 stroke-width=%221%22/><circle cx=%22100%22 cy=%22100%22 r=%2290%22 fill=%22none%22 stroke=%22white%22 stroke-width=%221%22/></pattern></defs><rect width=%22200%22 height=%22200%22 fill=%22url(%23t)%22/></svg>');"></div>
  </div>

  <div class="relative max-w-7xl px-4 sm\:px-6 lg\:px-8" style="margin-left: auto; margin-right: auto;">
    <div style="padding-top: 4rem; padding-bottom: 8rem; max-width: 42rem;" class="hero-content">
      <!-- Accent line -->
      <div class="hero-fade" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; animation-delay: 0.1s;">
        <div style="width: 2.5rem; height: 2px; background-color: var(--color-brand-green);"></div>
        <span style="color: var(--color-brand-green); font-weight: 600; font-size: 0.875rem; letter-spacing: 0.1em; text-transform: uppercase;">Jericó, Antioquia</span>
      </div>

      <h1 class="hero-fade" style="font-size: 2.25rem; font-family: var(--font-heading); font-weight: 800; color: #fff; line-height: 1.1; margin-bottom: 1.5rem; animation-delay: 0.25s;" class="hero-title">
        Trabajando con
        <span style="position: relative; display: inline-block;">
          Entereza
          <svg style="position: absolute; bottom: -0.5rem; left: 0; width: 100%;" viewBox="0 0 200 12" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M2 8C30 3 60 2 100 5C140 8 170 4 198 6" stroke="#8cc04b" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </span>
      </h1>

      <p class="hero-fade" style="font-size: 1.125rem; color: rgba(255,255,255,0.8); margin-bottom: 2.5rem; line-height: 1.625; max-width: 36rem; animation-delay: 0.4s;">
        Comprometidos con la prestación eficiente de los servicios públicos domiciliarios para la comunidad jericoana.
      </p>

      <div class="hero-fade" style="display: flex; flex-wrap: wrap; gap: 1rem; animation-delay: 0.55s;">
        <a href="<?php echo esc_url(home_url('/servicios')); ?>" class="group btn-hero-primary">
          Nuestros Servicios
          <svg style="width: 1rem; height: 1rem; transition: transform 0.15s;" class="group-hover-nudge" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
        <a href="<?php echo esc_url(home_url('/contactenos')); ?>" class="btn-hero-outline">
          Contáctenos
        </a>
      </div>
    </div>
  </div>

  <!-- Bottom wave -->
  <div class="absolute bottom-0 left-0 right-0">
    <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width: 100%; height: 40px;" class="hero-wave">
      <path d="M0 60V30C240 5 480 0 720 15C960 30 1200 10 1440 0V60H0Z" fill="white"/>
    </svg>
  </div>
</section>

<!-- ====== QUICK ACCESS ====== -->
<section style="position: relative; margin-top: -1.5rem; z-index: 10; padding-bottom: 3rem;">
  <div class="max-w-6xl px-4 sm\:px-6" style="margin-left: auto; margin-right: auto;">
    <div class="quick-grid">
      <?php
      $quickLinks = [
          ['label' => 'Guía de Trámites', 'subtitle' => 'Documentos y formularios', 'href' => '/guia-de-documentos', 'icon' => 'clipboard'],
          ['label' => 'Transparencia', 'subtitle' => 'Información pública', 'href' => '/transparencia', 'icon' => 'eye'],
          ['label' => 'PQRRS', 'subtitle' => 'Quejas y reclamos', 'href' => '/peticiones-quejas-reclamos-y-recursos', 'icon' => 'chat'],
          ['label' => 'Pagar en Línea', 'subtitle' => 'Pagos PSE', 'href' => 'https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=13227', 'icon' => 'card', 'external' => true],
      ];
      foreach ($quickLinks as $i => $link) :
          $delay = 0.1 + $i * 0.08;
          $is_external = !empty($link['external']);
          $href = $is_external ? $link['href'] : home_url($link['href']);
      ?>
        <a href="<?php echo esc_url($href); ?>"
           <?php echo $is_external ? 'target="_blank" rel="noopener"' : ''; ?>
           class="quick-card group"
           style="animation-delay: <?php echo esc_attr($delay); ?>s; position: relative; background: #fff; border-radius: 0.75rem; padding: 1rem; box-shadow: 0 10px 15px -3px rgba(229,231,235,0.6); border: 1px solid #f3f4f6; transition: all 0.15s; text-decoration: none;">
          <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background: rgba(140,192,75,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.15s;" class="group-hover-bg">
              <?php echo eeppj_quick_icon($link['icon']); ?>
            </div>
            <div>
              <span style="font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); font-size: 0.875rem; display: block; line-height: 1.375; transition: color 0.15s;" class="group-hover-text-green"><?php echo esc_html($link['label']); ?></span>
              <span style="color: var(--color-text-muted); font-size: 0.75rem; margin-top: 0.125rem; display: block;"><?php echo esc_html($link['subtitle']); ?></span>
            </div>
          </div>
          <svg style="position: absolute; top: 1rem; right: 1rem; width: 1rem; height: 1rem; color: #d1d5db; transition: all 0.15s;" class="group-hover-text-green group-hover-nudge" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ====== SERVICES ====== -->
<section style="padding-top: 4rem; padding-bottom: 4rem; position: relative; overflow: hidden;" class="services-section">
  <div style="position: absolute; inset: 0; opacity: 0.02; background-image: radial-gradient(circle at 1px 1px, #1e4b75 1px, transparent 0); background-size: 32px 32px;"></div>

  <div class="relative max-w-7xl px-4 sm\:px-6" style="margin-left: auto; margin-right: auto;">
    <div style="max-width: 36rem; margin-bottom: 3.5rem;">
      <div class="section-accent">
        <div class="section-accent-line"></div>
        <span class="section-accent-text">Servicios</span>
      </div>
      <h2 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-blue-dark); line-height: 1.25;">
        Lo que hacemos por Jericó
      </h2>
      <p style="color: var(--color-text-muted); margin-top: 0.75rem; font-size: 1.125rem;">
        Cuatro servicios públicos esenciales para el bienestar de nuestra comunidad.
      </p>
    </div>

    <div class="services-grid">
      <?php
      $services = [
          ['title' => 'Acueducto', 'desc' => 'Agua potable de calidad para cada hogar de Jericó, desde la captación hasta su grifo.', 'href' => '/acueducto', 'icon' => 'water', 'stat' => 'Agua Potable'],
          ['title' => 'Alcantarillado', 'desc' => 'Recolección y tratamiento responsable de aguas residuales del municipio.', 'href' => '/alcantarillado', 'icon' => 'pipe', 'stat' => 'Aguas Residuales'],
          ['title' => 'Aseo', 'desc' => 'Recolección, transporte y aprovechamiento de residuos sólidos urbanos y rurales.', 'href' => '/aseo', 'icon' => 'leaf', 'stat' => 'Residuos Sólidos'],
          ['title' => 'Alumbrado Público', 'desc' => 'Iluminación eficiente de vías, parques y espacios públicos del municipio.', 'href' => '/alumbrado-publico', 'icon' => 'light', 'stat' => 'Iluminación'],
      ];
      foreach ($services as $i => $svc) :
          $delay = $i * 0.1;
      ?>
        <?php get_template_part('template-parts/content-service', null, [
            'service' => $svc,
            'delay'   => $delay,
        ]); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ====== STATS BAR ====== -->
<section style="background-color: var(--color-brand-blue-dark); position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; opacity: 0.1; background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><defs><pattern id=%22d%22 patternUnits=%22userSpaceOnUse%22 width=%22100%22 height=%22100%22><circle cx=%2250%22 cy=%2250%22 r=%2225%22 fill=%22none%22 stroke=%22white%22 stroke-width=%220.5%22/><circle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22none%22 stroke=%22white%22 stroke-width=%220.5%22/></pattern></defs><rect width=%22100%22 height=%22100%22 fill=%22url(%23d)%22/></svg>');"></div>
  <div class="relative max-w-7xl px-4 sm\:px-6" style="margin-left: auto; margin-right: auto; padding-top: 3rem; padding-bottom: 3rem;">
    <div class="stats-grid text-center">
      <div>
        <div style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-green);">+4,500</div>
        <div style="color: rgba(255,255,255,0.7); font-size: 0.875rem; margin-top: 0.25rem;">Suscriptores atendidos</div>
      </div>
      <div>
        <div style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-green);">24/7</div>
        <div style="color: rgba(255,255,255,0.7); font-size: 0.875rem; margin-top: 0.25rem;">Servicio de emergencias</div>
      </div>
      <div>
        <div style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-green);">99%</div>
        <div style="color: rgba(255,255,255,0.7); font-size: 0.875rem; margin-top: 0.25rem;">Cobertura urbana</div>
      </div>
      <div>
        <div style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-green);">IRCA</div>
        <div style="color: rgba(255,255,255,0.7); font-size: 0.875rem; margin-top: 0.25rem;">Sin riesgo — Agua apta</div>
      </div>
    </div>
  </div>
</section>

<!-- ====== RECENT NEWS ====== -->
<section style="padding-top: 4rem; padding-bottom: 6rem; background-color: var(--color-surface-muted);">
  <div class="max-w-7xl px-4 sm\:px-6" style="margin-left: auto; margin-right: auto;">
    <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2.5rem;" class="news-header">
      <div>
        <div class="section-accent">
          <div class="section-accent-line"></div>
          <span class="section-accent-text">Noticias</span>
        </div>
        <h2 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-blue-dark);">
          Últimas Novedades
        </h2>
      </div>
      <a href="<?php echo esc_url(home_url('/blog')); ?>" class="group" style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 700; color: var(--color-brand-green); transition: color 0.15s; text-decoration: none; flex-shrink: 0;">
        Ver todas las noticias
        <svg style="width: 1rem; height: 1rem; transition: transform 0.15s;" class="group-hover-nudge" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </a>
    </div>

    <?php if ($recent_posts->have_posts()) : ?>
      <div class="news-grid">
        <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
          <?php get_template_part('template-parts/content-card'); ?>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ====== CTA ====== -->
<section style="padding-top: 4rem; padding-bottom: 5rem;">
  <div class="max-w-4xl px-4 sm\:px-6 text-center" style="margin-left: auto; margin-right: auto;">
    <h2 style="font-size: 1.5rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-blue-dark); margin-bottom: 1rem;">
      ¿Tiene alguna solicitud o inquietud?
    </h2>
    <p style="color: var(--color-text-muted); font-size: 1.125rem; margin-bottom: 2rem; max-width: 42rem; margin-left: auto; margin-right: auto;">
      Puede radicar peticiones, quejas, reclamos, recursos y sugerencias a través de nuestro formulario en línea.
    </p>
    <div style="display: flex; flex-direction: column; gap: 1rem; justify-content: center;" class="cta-buttons">
      <a href="<?php echo esc_url(home_url('/peticiones-quejas-reclamos-y-recursos')); ?>" class="btn-primary" style="font-size: 1rem; justify-content: center;">
        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Radicar PQRRS
      </a>
      <a href="<?php echo esc_url(home_url('/contactenos')); ?>" class="btn-outline" style="font-size: 1rem; justify-content: center;">
        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        </svg>
        Contáctenos
      </a>
    </div>
  </div>
</section>

<style>
  /* Hero responsive */
  .btn-hero-primary {
    display: inline-flex; align-items: center; gap: 0.625rem;
    padding: 0.875rem 1.75rem; background: var(--color-brand-green); color: #fff;
    font-weight: 700; border-radius: 0.5rem; transition: all 0.15s;
    text-decoration: none;
  }
  .btn-hero-primary:hover { background: var(--color-brand-green-dark); color: #fff; box-shadow: 0 10px 15px -3px rgba(140,192,75,0.25); }
  .btn-hero-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.875rem 1.75rem; color: #fff; font-weight: 600;
    border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.25);
    transition: all 0.15s; text-decoration: none;
  }
  .btn-hero-outline:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.4); color: #fff; }

  .hero-wave { height: 40px; }
  .quick-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
  .quick-card:hover { box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-color: rgba(140,192,75,0.3); transform: translateY(-2px); }
  .services-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
  .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }
  .news-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }

  @media (min-width: 640px) {
    .hero-title, .hero-content h1 { font-size: 3rem; }
    .news-grid { grid-template-columns: repeat(2, 1fr); }
    .cta-buttons { flex-direction: row; }
    .news-header { flex-direction: row; align-items: flex-end; justify-content: space-between; }
  }
  @media (min-width: 768px) {
    .hero-content { padding-top: 6rem; padding-bottom: 10rem; }
    .hero-content h1 { font-size: 2.25rem; }
    .hero-content p { font-size: 1.25rem; }
    .hero-wave { height: 60px; }
    .quick-grid { gap: 1rem; }
    .services-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-grid { grid-template-columns: repeat(4, 1fr); gap: 3rem; }
    .stats-grid > div > div:first-child { font-size: 2.25rem; }
  }
  @media (min-width: 1024px) {
    .hero-content { padding-top: 8rem; padding-bottom: 12rem; }
    .hero-content h1 { font-size: 3.75rem; }
    .quick-grid { grid-template-columns: repeat(4, 1fr); }
    .services-grid { grid-template-columns: repeat(4, 1fr); }
    .news-grid { grid-template-columns: repeat(3, 1fr); }
  }
</style>

<?php get_footer(); ?>
