<?php
/**
 * PQRRS Form — shortcode [eeppj_pqrrs] and front-end assets
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Form {
    public static function init() {
        add_shortcode('eeppj_pqrrs', [__CLASS__, 'render']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets() {
        if (!is_singular()) return;

        global $post;
        $has_shortcode = has_shortcode($post->post_content ?? '', 'eeppj_pqrrs');
        $has_template  = get_page_template_slug($post) === 'page-pqrrs.php';
        if (!$has_shortcode && !$has_template) return;

        wp_enqueue_style(
            'eeppj-pqrrs-form',
            EEPPJ_PQRRS_URL . 'assets/css/pqrrs-form.css',
            [],
            EEPPJ_PQRRS_VERSION
        );

        wp_enqueue_script(
            'eeppj-pqrrs-form',
            EEPPJ_PQRRS_URL . 'assets/js/pqrrs-form.js',
            [],
            EEPPJ_PQRRS_VERSION,
            true
        );

        wp_localize_script('eeppj-pqrrs-form', 'eeppjPqrrs', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('eeppj_pqrrs_submit'),
        ]);

        // Turnstile script
        $site_key = get_option('eeppj_pqrrs_turnstile_site_key');
        if (!empty($site_key)) {
            wp_enqueue_script(
                'cf-turnstile',
                'https://challenges.cloudflare.com/turnstile/v0/api.js',
                [],
                null,
                true
            );
        }
    }

    public static function render() {
        $site_key = get_option('eeppj_pqrrs_turnstile_site_key');
        $max_upload = (int) get_option('eeppj_pqrrs_max_upload', 5);

        ob_start();
        include EEPPJ_PQRRS_PATH . 'templates/form.php';
        return ob_get_clean();
    }
}
