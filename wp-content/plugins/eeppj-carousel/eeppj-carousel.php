<?php
/**
 * Plugin Name: EEPPJ Carousel
 * Description: Apple-style premium content carousel — Gutenberg block with autoplay, smooth transitions, and floating control bar.
 * Version: 1.5.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: EEPPJ
 * License: GPL v2 or later
 * Text Domain: eeppj-carousel
 */

defined('ABSPATH') || exit;

define('EEPPJ_CAROUSEL_VERSION', '1.5.0');
define('EEPPJ_CAROUSEL_PATH', plugin_dir_path(__FILE__));
define('EEPPJ_CAROUSEL_URL', plugin_dir_url(__FILE__));

/**
 * Register the Gutenberg block
 */
function eeppj_carousel_register_block() {
    // Editor script
    wp_register_script(
        'eeppj-carousel-editor',
        EEPPJ_CAROUSEL_URL . 'build/editor.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data'],
        EEPPJ_CAROUSEL_VERSION,
        true
    );

    // Editor styles
    wp_register_style(
        'eeppj-carousel-editor-style',
        EEPPJ_CAROUSEL_URL . 'build/editor.css',
        ['wp-edit-blocks'],
        EEPPJ_CAROUSEL_VERSION
    );

    // Front-end styles
    wp_register_style(
        'eeppj-carousel-style',
        EEPPJ_CAROUSEL_URL . 'build/style.css',
        [],
        EEPPJ_CAROUSEL_VERSION
    );

    // Front-end script
    wp_register_script(
        'eeppj-carousel-frontend',
        EEPPJ_CAROUSEL_URL . 'build/frontend.js',
        [],
        EEPPJ_CAROUSEL_VERSION,
        true
    );

    register_block_type('eeppj/carousel', [
        'editor_script'   => 'eeppj-carousel-editor',
        'editor_style'    => 'eeppj-carousel-editor-style',
        'style'           => 'eeppj-carousel-style',
        'script'          => 'eeppj-carousel-frontend',
        'render_callback' => 'eeppj_carousel_render',
        'attributes'      => [
            'slides' => [
                'type'    => 'array',
                'default' => [],
            ],
            'autoplayDuration' => [
                'type'    => 'number',
                'default' => 5000,
            ],
            'showPlayPause' => [
                'type'    => 'boolean',
                'default' => true,
            ],
            'blockId' => [
                'type'    => 'string',
                'default' => '',
            ],
            'theme' => [
                'type'    => 'string',
                'default' => 'dark',
            ],
        ],
    ]);
}
add_action('init', 'eeppj_carousel_register_block');

/* ====== GitHub Auto-Updater ====== */
require_once EEPPJ_CAROUSEL_PATH . 'includes/class-github-updater.php';
new EEPPJ_Carousel_GitHub_Updater(
    'eeppj-carousel/eeppj-carousel.php',
    'jgarcesres/wp_eeppj',
    EEPPJ_CAROUSEL_VERSION,
    'plugin',
    'eeppj-carousel.zip'
);

/**
 * Server-side render callback
 */
function eeppj_carousel_render($attributes) {
    $slides = $attributes['slides'] ?? [];
    $autoplay = (int) ($attributes['autoplayDuration'] ?? 5000);
    $show_play_pause = $attributes['showPlayPause'] ?? true;
    $block_id = !empty($attributes['blockId']) ? $attributes['blockId'] : 'eeppj-carousel-' . wp_unique_id();
    $theme = isset($attributes['theme']) ? $attributes['theme'] : 'dark';
    $theme_class = ($theme === 'light') ? ' eeppj-carousel--light' : '';

    if (empty($slides)) {
        return '';
    }

    $slide_count = count($slides);

    ob_start();
    ?>
    <div class="eeppj-carousel<?php echo esc_attr($theme_class); ?>"
         id="<?php echo esc_attr($block_id); ?>"
         data-autoplay="<?php echo esc_attr($autoplay); ?>"
         data-show-controls="<?php echo $show_play_pause ? '1' : '0'; ?>"
         data-count="<?php echo esc_attr($slide_count); ?>"
         role="region"
         aria-roledescription="carousel"
         aria-label="Carrusel de contenido">

      <div class="eeppj-carousel__viewport">
        <div class="eeppj-carousel__track" style="width: <?php echo esc_attr($slide_count * 100); ?>%;">
          <?php foreach ($slides as $i => $slide) :
              $headline = $slide['headline'] ?? '';
              $description = $slide['description'] ?? '';
              $mediaUrl = $slide['mediaUrl'] ?? '';
              $mediaType = $slide['mediaType'] ?? 'image';
              $is_active = ($i === 0);
          ?>
            <div class="eeppj-carousel__slide"
                 role="group"
                 aria-roledescription="slide"
                 aria-label="<?php echo esc_attr(($i + 1) . ' de ' . $slide_count); ?>"
                 style="width: <?php echo esc_attr(100 / $slide_count); ?>%;">
              <div class="eeppj-carousel__slide-inner">
                <?php if ($headline || $description) : ?>
                  <div class="eeppj-carousel__text">
                    <?php if ($headline) : ?>
                      <h3 class="eeppj-carousel__headline"><?php echo esc_html($headline); ?></h3>
                    <?php endif; ?>
                    <?php if ($description) : ?>
                      <p class="eeppj-carousel__description"><?php echo esc_html($description); ?></p>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($mediaUrl) : ?>
                  <div class="eeppj-carousel__media">
                    <?php if ($mediaType === 'video') : ?>
                      <video src="<?php echo esc_url($mediaUrl); ?>"
                             autoplay muted loop playsinline
                             class="eeppj-carousel__video"></video>
                    <?php else : ?>
                      <img src="<?php echo esc_url($mediaUrl); ?>"
                           alt="<?php echo esc_attr($headline); ?>"
                           class="eeppj-carousel__image"
                           loading="<?php echo ($i === 0) ? 'eager' : 'lazy'; ?>" />
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Control Bar -->
      <div class="eeppj-carousel__controls" role="tablist" aria-label="Navegación del carrusel">
        <div class="eeppj-carousel__dots">
          <?php for ($i = 0; $i < $slide_count; $i++) : ?>
            <button class="eeppj-carousel__dot <?php echo ($i === 0) ? 'is-active' : ''; ?>"
                    role="tab"
                    aria-selected="<?php echo ($i === 0) ? 'true' : 'false'; ?>"
                    aria-label="Ir a diapositiva <?php echo esc_attr($i + 1); ?>"
                    data-index="<?php echo esc_attr($i); ?>">
            </button>
          <?php endfor; ?>
        </div>
        <?php if ($show_play_pause) : ?>
          <div class="eeppj-carousel__divider" aria-hidden="true"></div>
          <button class="eeppj-carousel__playpause is-playing"
                  aria-label="Pausar carrusel">
            <svg class="eeppj-carousel__icon-pause" width="10" height="12" viewBox="0 0 10 12" fill="currentColor">
              <rect x="0" y="0" width="3" height="12" rx="1"/>
              <rect x="7" y="0" width="3" height="12" rx="1"/>
            </svg>
            <svg class="eeppj-carousel__icon-play" width="10" height="12" viewBox="0 0 10 12" fill="currentColor">
              <path d="M0 1.5c0-.813.886-1.32 1.592-.912l7.15 4.15a1.05 1.05 0 010 1.824l-7.15 4.15A1.05 1.05 0 010 9.8V1.5z"/>
            </svg>
          </button>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
