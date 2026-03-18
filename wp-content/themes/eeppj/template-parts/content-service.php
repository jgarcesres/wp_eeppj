<?php
/**
 * Service card for homepage
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

$service = $args['service'] ?? [];
$delay = $args['delay'] ?? 0;

if (empty($service)) return;
?>
<a href="<?php echo esc_url(home_url($service['href'])); ?>"
   class="service-card group"
   style="position: relative; background: #fff; border-radius: 1rem; border: 1px solid #f3f4f6; padding: 1.5rem; transition: all 0.15s; text-decoration: none; display: block; animation-delay: <?php echo esc_attr($delay); ?>s;">

  <!-- Icon -->
  <div style="width: 3rem; height: 3rem; border-radius: 0.75rem; background: linear-gradient(to bottom right, rgba(140,192,75,0.15), rgba(49,130,206,0.1)); display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; transition: all 0.15s;" class="service-icon-wrap">
    <?php echo eeppj_service_icon($service['icon']); ?>
  </div>

  <!-- Label chip -->
  <div style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; background: var(--color-surface-subtle); font-size: 0.75rem; font-weight: 500; color: var(--color-text-muted); margin-bottom: 0.75rem;">
    <?php echo esc_html($service['stat']); ?>
  </div>

  <h3 class="service-card-title" style="font-size: 1.125rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark); margin-bottom: 0.5rem; transition: color 0.15s;">
    <?php echo esc_html($service['title']); ?>
  </h3>

  <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.625; margin-bottom: 1rem;">
    <?php echo esc_html($service['desc']); ?>
  </p>

  <span class="service-card-link" style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; font-weight: 600; color: var(--color-brand-green); opacity: 0; transition: all 0.15s; transform: translateY(4px);">
    Conocer más
    <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
    </svg>
  </span>
</a>

<style>
  .service-card:hover { border-color: rgba(140,192,75,0.4); box-shadow: 0 10px 15px -3px rgba(140,192,75,0.05); transform: translateY(-4px); }
  .service-card:hover .service-card-title { color: var(--color-brand-green); }
  .service-card:hover .service-card-link { opacity: 1; transform: translateY(0); }
  .service-card:hover .service-icon-wrap { background: linear-gradient(to bottom right, rgba(140,192,75,0.25), rgba(49,130,206,0.15)); }
</style>
