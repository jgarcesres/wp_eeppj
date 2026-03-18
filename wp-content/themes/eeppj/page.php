<?php
/**
 * Generic page template — PageLayout port
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();
?>

<div style="background: rgba(140,192,75,0.05); border-bottom: 1px solid rgba(140,192,75,0.1);">
  <div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 2rem; margin-left: auto; margin-right: auto;" class="page-hero-inner">
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark);" class="page-hero-title">
      <?php the_title(); ?>
    </h1>
  </div>
</div>

<div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 3rem; margin-left: auto; margin-right: auto;" class="page-body">
  <div class="prose-content">
    <?php
    while (have_posts()) : the_post();
        the_content();
    endwhile;
    ?>
  </div>
</div>

<style>
  @media (min-width: 768px) {
    .page-hero-inner { padding-top: 3rem; padding-bottom: 3rem; }
    .page-hero-title { font-size: 2.25rem; }
    .page-body { padding-top: 3rem; padding-bottom: 3rem; }
  }
</style>

<?php get_footer(); ?>
