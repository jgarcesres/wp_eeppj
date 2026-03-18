<?php
/**
 * Template Name: Contáctenos
 *
 * Contact page with info + map
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<div style="background: rgba(140,192,75,0.05); border-bottom: 1px solid rgba(140,192,75,0.1);">
  <div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 2rem; margin-left: auto; margin-right: auto;" class="contact-hero">
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark);" class="contact-title">Contáctenos</h1>
  </div>
</div>

<div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 3rem; margin-left: auto; margin-right: auto;">
  <div class="contact-grid">
    <!-- Contact Info -->
    <div>
      <h2 style="font-size: 1.25rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 1.5rem;">
        Información de Contacto
      </h2>
      <div class="space-y-4">
        <?php
        $contacts = [
            ['icon' => 'location', 'title' => 'Dirección', 'content' => 'Calle 7 No. 2 – 68, Jericó, Antioquia, Colombia'],
            ['icon' => 'phone', 'title' => 'Teléfono', 'content' => 'PBX: +60 (4) 852 37 64'],
            ['icon' => 'email', 'title' => 'Correo Electrónico', 'content' => '<a href="mailto:contactenos@eeppj.com.co" style="color: var(--color-brand-blue);">contactenos@eeppj.com.co</a>'],
            ['icon' => 'clock', 'title' => 'Horario de Atención', 'content' => 'Lunes a Viernes: 7:00am – 12:00m / 2:00pm – 5:00pm<br />Sábados: 7:00am – 12:00m (Miércoles: Jornada continua)'],
        ];

        $icon_svgs = [
            'location' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'phone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
            'email' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];

        foreach ($contacts as $c) : ?>
          <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background: rgba(140,192,75,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg style="width: 1.25rem; height: 1.25rem; color: var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?php echo $icon_svgs[$c['icon']]; ?>
              </svg>
            </div>
            <div>
              <h3 style="font-weight: 600; color: #111827;"><?php echo esc_html($c['title']); ?></h3>
              <p style="color: var(--color-text-muted);"><?php echo wp_kses_post($c['content']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- PQRRS CTA -->
      <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(140,192,75,0.05); border-radius: 0.75rem; border: 1px solid rgba(140,192,75,0.2);">
        <h3 style="font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 0.5rem;">
          ¿Tiene una solicitud formal?
        </h3>
        <p style="color: var(--color-text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
          Para radicar Peticiones, Quejas, Reclamos, Recursos o Sugerencias, utilice nuestro formulario PQRRS.
        </p>
        <a href="<?php echo esc_url(home_url('/pqrrs')); ?>" class="btn-primary">
          Ir al formulario PQRRS
          <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
      </div>

      <!-- WP page content (if any) -->
      <?php
      while (have_posts()) : the_post();
          $content = get_the_content();
          if (trim($content)) :
      ?>
        <div class="prose-content" style="margin-top: 2rem;">
          <?php the_content(); ?>
        </div>
      <?php
          endif;
      endwhile;
      ?>
    </div>

    <!-- Map -->
    <div>
      <h2 style="font-size: 1.25rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 1.5rem;">Ubicación</h2>
      <div style="border-radius: 0.75rem; overflow: hidden; border: 1px solid #e5e7eb;">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3969.4492948034226!2d-75.78996852552312!3d5.7920344312321586!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e46594891c543b9%3A0x3e9484b14ee2c489!2sCl.%207%20%23%202-68%2C%20Jeric%C3%B3%2C%20Antioquia!5e0!3m2!1ses-419!2sco!4v1763133136444!5m2!1ses-419!2sco"
          width="100%"
          height="450"
          style="border: 0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Ubicación de EEPPJ en Jericó, Antioquia"></iframe>
      </div>
    </div>
  </div>
</div>

<style>
  .contact-grid { display: grid; grid-template-columns: 1fr; gap: 3rem; }
  @media (min-width: 768px) {
    .contact-hero { padding-top: 3rem; padding-bottom: 3rem; }
    .contact-title { font-size: 2.25rem; }
  }
  @media (min-width: 1024px) {
    .contact-grid { grid-template-columns: repeat(2, 1fr); }
  }
</style>

<?php get_footer(); ?>
