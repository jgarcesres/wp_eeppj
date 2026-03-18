<?php
/**
 * 404 Page
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<div style="text-align: center; padding: 6rem 1rem; max-width: 36rem; margin: 0 auto;">
  <div style="font-size: 5rem; font-family: var(--font-heading); font-weight: 800; color: var(--color-brand-green); margin-bottom: 1rem;">404</div>
  <h1 style="font-size: 1.5rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 0.75rem;">
    Página no encontrada
  </h1>
  <p style="color: var(--color-text-muted); font-size: 1.125rem; margin-bottom: 2rem;">
    Lo sentimos, la página que busca no existe o ha sido trasladada.
  </p>
  <div style="display: flex; flex-direction: column; gap: 1rem; align-items: center;">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">
      Volver al Inicio
    </a>
    <div style="max-width: 24rem; width: 100%;">
      <?php get_search_form(); ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
