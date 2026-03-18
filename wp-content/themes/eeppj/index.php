<?php
/**
 * Blog listing / archive template
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<div style="background: rgba(140,192,75,0.05); border-bottom: 1px solid rgba(140,192,75,0.1);">
  <div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 2rem; margin-left: auto; margin-right: auto;">
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark);" class="archive-title">
      <?php
      if (is_category()) {
          single_cat_title();
      } elseif (is_tag()) {
          single_tag_title();
      } elseif (is_search()) {
          printf(esc_html__('Resultados para: %s', 'eeppj'), get_search_query());
      } else {
          echo 'Noticias';
      }
      ?>
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

    <?php
    $pagination = paginate_links([
        'prev_text' => '&laquo; Anterior',
        'next_text' => 'Siguiente &raquo;',
        'type'      => 'list',
    ]);
    if ($pagination) :
    ?>
      <nav class="pagination" style="margin-top: 3rem;" aria-label="Paginación">
        <?php echo $pagination; ?>
      </nav>
    <?php endif; ?>
  <?php else : ?>
    <p style="color: var(--color-text-muted); font-size: 1.125rem;">No se encontraron publicaciones.</p>
  <?php endif; ?>
</div>

<style>
  .archive-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
  .pagination .page-numbers { list-style: none; display: flex; gap: 0.5rem; justify-content: center; padding: 0; flex-wrap: wrap; }
  .pagination .page-numbers li { display: inline; }
  .pagination .page-numbers a,
  .pagination .page-numbers span {
    display: inline-block; padding: 0.5rem 1rem; border-radius: 0.5rem;
    font-size: 0.875rem; text-decoration: none; transition: all 0.15s;
    border: 1px solid #e5e7eb; color: var(--color-text);
  }
  .pagination .page-numbers .current { background: var(--color-brand-green); color: #fff; border-color: var(--color-brand-green); }
  .pagination .page-numbers a:hover { background: rgba(140,192,75,0.05); border-color: var(--color-brand-green); }

  @media (min-width: 640px) { .archive-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (min-width: 1024px) { .archive-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (min-width: 768px) { .archive-title { font-size: 2.25rem; } }
</style>

<?php get_footer(); ?>
