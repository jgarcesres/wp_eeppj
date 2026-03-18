<?php
/**
 * Search results template
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<div style="background: rgba(140,192,75,0.05); border-bottom: 1px solid rgba(140,192,75,0.1);">
  <div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 2rem; margin-left: auto; margin-right: auto;">
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark);">
      Resultados para: <span style="color: var(--color-brand-green);"><?php echo esc_html(get_search_query()); ?></span>
    </h1>
  </div>
</div>

<div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 4rem; margin-left: auto; margin-right: auto;">
  <?php if (have_posts()) : ?>
    <div class="archive-grid">
      <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('template-parts/content-card'); ?>
      <?php endwhile; ?>
    </div>
  <?php else : ?>
    <div style="text-align: center; padding: 3rem 0;">
      <p style="color: var(--color-text-muted); font-size: 1.125rem; margin-bottom: 1.5rem;">No se encontraron resultados.</p>
      <div style="max-width: 28rem; margin: 0 auto;">
        <?php get_search_form(); ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<style>
  .archive-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
  @media (min-width: 640px) { .archive-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (min-width: 1024px) { .archive-grid { grid-template-columns: repeat(3, 1fr); } }
</style>

<?php get_footer(); ?>
