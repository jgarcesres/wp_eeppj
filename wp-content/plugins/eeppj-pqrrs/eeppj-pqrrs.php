<?php
/**
 * Plugin Name: EEPPJ PQRRS
 * Plugin URI: https://github.com/jgarcesres/wp_eeppj
 * Description: PQRRS (Peticiones, Quejas, Reclamos, Recursos, Sugerencias) form handler for Empresas Públicas de Jericó. Provides form shortcode, Turnstile CAPTCHA, file upload validation, admin panel, and email notifications.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: EEPPJ
 * License: GPL v2 or later
 * Text Domain: eeppj-pqrrs
 */

defined('ABSPATH') || exit;

define('EEPPJ_PQRRS_VERSION', '1.3.0');
define('EEPPJ_PQRRS_PATH', plugin_dir_path(__FILE__));
define('EEPPJ_PQRRS_URL', plugin_dir_url(__FILE__));

// Autoload classes
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-validator.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-turnstile.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-handler.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-form.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-admin.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-email.php';

/* ====== Activation: create DB table ====== */
function eeppj_pqrrs_activate() {
    global $wpdb;
    $table = $wpdb->prefix . 'eeppj_pqrrs';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        submission_id VARCHAR(8) NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        cedula VARCHAR(20) DEFAULT '',
        email VARCHAR(255) NOT NULL,
        telefono VARCHAR(20) DEFAULT '',
        tipo VARCHAR(20) NOT NULL,
        asunto VARCHAR(500) NOT NULL,
        mensaje TEXT NOT NULL,
        archivo_id BIGINT UNSIGNED DEFAULT NULL,
        ip_address VARCHAR(45) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pendiente',
        admin_notes TEXT DEFAULT '',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL,
        INDEX idx_tipo (tipo),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('eeppj_pqrrs_db_version', EEPPJ_PQRRS_VERSION);
}
register_activation_hook(__FILE__, 'eeppj_pqrrs_activate');

/* ====== DB migration for existing installs ====== */
function eeppj_pqrrs_check_db_upgrade() {
    $installed = get_option('eeppj_pqrrs_db_version', '0');
    if (version_compare($installed, '1.2.0', '<')) {
        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        // Add status column if missing
        $col = $wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'status'");
        if (!$col) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pendiente' AFTER ip_address");
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_status (status)");
        }

        // Add admin_notes column if missing
        $col2 = $wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'admin_notes'");
        if (!$col2) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN admin_notes TEXT DEFAULT '' AFTER status");
        }

        // Add updated_at column if missing
        $col3 = $wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'updated_at'");
        if (!$col3) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER created_at");
        }

        update_option('eeppj_pqrrs_db_version', '1.2.0');
    }
}
add_action('admin_init', 'eeppj_pqrrs_check_db_upgrade');

/* ====== Initialize plugin components ====== */
function eeppj_pqrrs_init() {
    EEPPJ_PQRRS_Form::init();
    EEPPJ_PQRRS_Handler::init();
    EEPPJ_PQRRS_Admin::init();
}
add_action('init', 'eeppj_pqrrs_init');

/* ====== Settings link on plugins page ====== */
function eeppj_pqrrs_settings_link($links) {
    $settings = '<a href="' . admin_url('admin.php?page=eeppj-pqrrs-settings') . '">Ajustes</a>';
    array_unshift($links, $settings);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'eeppj_pqrrs_settings_link');

/* ====== GitHub Auto-Updater ====== */
require_once EEPPJ_PQRRS_PATH . 'includes/class-github-updater.php';
new EEPPJ_GitHub_Updater(
    'eeppj-pqrrs/eeppj-pqrrs.php',
    'jgarcesres/wp_eeppj',
    EEPPJ_PQRRS_VERSION,
    'plugin',
    'eeppj-pqrrs.zip'
);
