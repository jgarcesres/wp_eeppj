<?php
/**
 * Single post template — PostLayout port
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) : the_post();
    $categories = get_the_category();
    $date_iso = get_the_date('c');
    $date_display = get_the_date('j \d\e F \d\e Y');
?>

<article class="max-w-4xl px-4" style="padding-top: 2rem; padding-bottom: 3rem; margin-left: auto; margin-right: auto;" class="single-article">
  <div style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.75rem; flex-wrap: wrap;">
      <time datetime="<?php echo esc_attr($date_iso); ?>"><?php echo esc_html($date_display); ?></time>
      <?php foreach ($categories as $cat) : ?>
        <span style="background: rgba(140,192,75,0.1); color: var(--color-brand-green-dark); padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
          <?php echo esc_html($cat->name); ?>
        </span>
      <?php endforeach; ?>
    </div>
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); line-height: 1.25;" class="single-title">
      <?php echo esc_html(get_the_title()); ?>
    </h1>
  </div>

  <?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail('large', [
        'style' => 'width: 100%; border-radius: 0.75rem; margin-bottom: 2rem; max-height: 400px; object-fit: cover;',
        'loading' => 'eager',
    ]); ?>
  <?php endif; ?>

  <div class="prose-content">
    <?php the_content(); ?>
  </div>

  <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
    <a href="<?php echo esc_url(home_url('/blog')); ?>" class="btn-outline" style="font-size: 0.875rem;">
      <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Volver a Noticias
    </a>
  </div>
</article>

<?php endwhile; ?>

<style>
  @media (min-width: 768px) {
    .single-article { padding-top: 3rem; padding-bottom: 3rem; }
    .single-title { font-size: 2.25rem; }
  }
</style>

<?php get_footer(); ?>
