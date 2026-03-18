<?php
/**
 * Footer template
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;
?>
</main>

<!-- Cookie Consent Banner -->
<div id="cookie-banner" class="fixed bottom-0 inset-x-0 z-50 hidden">
  <div class="max-w-7xl px-4" style="padding-top: 0.75rem; padding-bottom: 0.75rem;">
    <div style="background: var(--color-footer); border-radius: 0.75rem; padding: 1rem; display: flex; flex-direction: column; gap: 1rem; align-items: flex-start;" class="cookie-inner shadow-lg">
      <p style="font-size: 0.875rem; color: #d1d5db; flex: 1;">
        Este sitio utiliza herramientas de análisis sin cookies para mejorar su experiencia. Al continuar navegando, acepta nuestra política de privacidad.
      </p>
      <button id="cookie-accept" class="btn-primary" style="font-size: 0.875rem; padding: 0.5rem 1rem; white-space: nowrap;">
        Aceptar
      </button>
    </div>
  </div>
</div>
<style>
  @media (min-width: 640px) {
    .cookie-inner { flex-direction: row; align-items: center; }
  }
</style>

<footer style="background-color: var(--color-footer); color: #d1d5db;">
  <div class="max-w-7xl px-4 py-12">
    <div class="footer-grid">
      <!-- Company Info -->
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logo-Blanco-1.webp'); ?>" alt="EEPPJ" style="height: 3rem; margin-bottom: 1rem;" />
        <p style="font-size: 0.875rem; line-height: 1.625;">
          Empresas Públicas de Jericó S.A E.S.P.<br />
          NIT: 800.058.016-1
        </p>
        <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
          <a href="https://www.facebook.com/EmpresasPublicasDeJerico" target="_blank" rel="noopener noreferrer" class="footer-social" aria-label="Facebook">
            <svg style="width: 1.25rem; height: 1.25rem;" fill="currentColor" viewBox="0 0 24 24"><path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/></svg>
          </a>
          <a href="https://www.instagram.com/empresaspublicasdejerico" target="_blank" rel="noopener noreferrer" class="footer-social" aria-label="Instagram">
            <svg style="width: 1.25rem; height: 1.25rem;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
          </a>
        </div>
      </div>

      <!-- Contact -->
      <div>
        <h3 style="color: #fff; font-family: var(--font-heading); font-weight: 700; font-size: 1.125rem; margin-bottom: 1rem;">Contacto</h3>
        <ul style="list-style: none; padding: 0;" class="space-y-2 text-sm">
          <li>PBX: +60 (4) 852 37 64</li>
          <li>
            <a href="mailto:contactenos@eeppj.com.co" class="footer-link">contactenos@eeppj.com.co</a>
          </li>
          <li>Calle 7 No. 2 – 68</li>
          <li>Jericó, Antioquia, Colombia</li>
          <li style="padding-top: 0.25rem;">
            Lunes a Viernes: 7:00am – 12:00m<br />
            2:00pm – 5:00pm
          </li>
        </ul>
      </div>

      <!-- Site Map -->
      <div>
        <h3 style="color: #fff; font-family: var(--font-heading); font-weight: 700; font-size: 1.125rem; margin-bottom: 1rem;">Mapa del Sitio</h3>
        <?php if (has_nav_menu('footer')) : ?>
          <?php
          wp_nav_menu([
              'theme_location' => 'footer',
              'container'      => false,
              'items_wrap'     => '<ul style="list-style: none; padding: 0;" class="space-y-2 text-sm">%3$s</ul>',
              'before'         => '',
              'after'          => '',
              'link_before'    => '',
              'link_after'     => '',
              'depth'          => 1,
          ]);
          ?>
        <?php else : ?>
          <ul style="list-style: none; padding: 0;" class="space-y-2 text-sm">
            <li><a href="<?php echo esc_url(home_url('/nuestraempresa')); ?>" class="footer-link">Institucional</a></li>
            <li><a href="<?php echo esc_url(home_url('/servicios')); ?>" class="footer-link">Servicios</a></li>
            <li><a href="<?php echo esc_url(home_url('/transparencia')); ?>" class="footer-link">Transparencia</a></li>
            <li><a href="<?php echo esc_url(home_url('/contactenos')); ?>" class="footer-link">Contáctenos</a></li>
            <li><a href="<?php echo esc_url(home_url('/blog')); ?>" class="footer-link">Noticias</a></li>
            <li><a href="<?php echo esc_url(home_url('/pqrrs')); ?>" class="footer-link">PQRRS</a></li>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Supervisory -->
      <div>
        <h3 style="color: #fff; font-family: var(--font-heading); font-weight: 700; font-size: 1.125rem; margin-bottom: 1rem;">Supervisado y Vigilado por</h3>
        <img
          src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Supervisado-y-Vigilado.webp'); ?>"
          alt="Superservicios, Procuraduría General de la Nación, Contraloría General de la República"
          style="max-height: 4rem; opacity: 0.9;"
          loading="lazy"
        />
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div style="border-top: 1px solid #374151;">
    <div class="max-w-7xl px-4" style="padding: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: space-between; gap: 0.5rem; font-size: 0.75rem; color: #6b7280;" class="footer-bottom">
      <p>&copy; <?php echo esc_html(date('Y')); ?> Empresas Públicas de Jericó S.A E.S.P. Todos los derechos reservados.</p>
      <a href="https://www.gov.co" target="_blank" rel="noopener noreferrer" style="color: #9ca3af; font-weight: 700; font-size: 0.875rem;">
        GOV.CO
      </a>
    </div>
  </div>
</footer>

<style>
  .footer-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  .footer-social { color: #9ca3af; transition: color 0.15s; }
  .footer-social:hover { color: #fff; }
  .footer-link { color: #d1d5db; transition: color 0.15s; text-decoration: none; }
  .footer-link:hover { color: #fff; }
  .footer-bottom { text-align: center; }

  /* Footer nav menu items */
  footer .menu-item a {
    color: #d1d5db;
    text-decoration: none;
    transition: color 0.15s;
  }
  footer .menu-item a:hover { color: #fff; }

  @media (min-width: 640px) {
    .footer-bottom { flex-direction: row; }
  }
  @media (min-width: 768px) {
    .footer-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (min-width: 1024px) {
    .footer-grid { grid-template-columns: repeat(4, 1fr); }
  }
</style>

<?php wp_footer(); ?>
</body>
</html>
