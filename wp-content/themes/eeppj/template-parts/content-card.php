<?php
/**
 * BlogCard — post card for grids
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

$post_id = get_the_ID();
$title = get_the_title();
$slug = get_permalink();
$date = get_the_date('j \d\e F \d\e Y');
$has_thumb = has_post_thumbnail();
$excerpt = get_the_excerpt();
?>
<article class="blog-card group" style="background: #fff; border-radius: 1rem; border: 1px solid #f3f4f6; overflow: hidden; transition: all 0.15s;">
  <a href="<?php echo esc_url($slug); ?>" style="display: block; text-decoration: none; color: inherit;">
    <?php if ($has_thumb) : ?>
      <div style="position: relative; overflow: hidden;">
        <?php the_post_thumbnail('medium_large', [
            'style' => 'width: 100%; height: 12rem; object-fit: cover; transition: transform 0.5s;',
            'class' => 'blog-card-img',
            'loading' => 'lazy',
        ]); ?>
        <div class="blog-card-overlay"></div>
      </div>
    <?php else : ?>
      <div style="width: 100%; height: 12rem; background: linear-gradient(to bottom right, rgba(140,192,75,0.08), rgba(49,130,206,0.05), rgba(140,192,75,0.1)); display: flex; align-items: center; justify-content: center; position: relative;">
        <div style="position: absolute; inset: 0; opacity: 0.03; background-image: radial-gradient(circle at 1px 1px, #1e4b75 1px, transparent 0); background-size: 24px 24px;"></div>
        <svg style="width: 2.5rem; height: 2.5rem; color: rgba(140,192,75,0.3);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/>
        </svg>
      </div>
    <?php endif; ?>

    <div style="padding: 1.25rem;">
      <time style="font-size: 0.75rem; font-weight: 500; color: rgba(104,104,104,0.8); letter-spacing: 0.025em;"><?php echo esc_html($date); ?></time>
      <h3 class="blog-card-title" style="font-size: 1rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-top: 0.5rem; line-height: 1.375; transition: color 0.15s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
        <?php echo esc_html($title); ?>
      </h3>
      <?php if ($excerpt) : ?>
        <p style="font-size: 0.875rem; color: var(--color-text-muted); margin-top: 0.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.625;">
          <?php echo esc_html($excerpt); ?>
        </p>
      <?php endif; ?>
      <span class="blog-card-read-more" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 600; color: var(--color-brand-green); margin-top: 0.75rem; opacity: 0; transition: all 0.15s;">
        Leer más
        <svg style="width: 0.75rem; height: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </span>
    </div>
  </a>
</article>

<style>
  .blog-card:hover { box-shadow: 0 10px 15px -3px rgba(229,231,235,0.6); border-color: #e5e7eb; transform: translateY(-2px); }
  .blog-card:hover .blog-card-title { color: var(--color-brand-green); }
  .blog-card:hover .blog-card-img { transform: scale(1.05); }
  .blog-card:hover .blog-card-read-more { opacity: 1; }
  .blog-card-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
    opacity: 0; transition: opacity 0.15s;
  }
  .blog-card:hover .blog-card-overlay { opacity: 1; }
</style>
