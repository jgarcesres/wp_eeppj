<?php
/**
 * Plugin Name: EEPPJ PQRRS
 * Plugin URI: https://github.com/jgarcesres/wp_eeppj
 * Description: PQRRS (Peticiones, Quejas, Reclamos, Recursos, Sugerencias) form handler for Empresas Públicas de Jericó. Provides form shortcode, Turnstile CAPTCHA, file upload validation, admin panel, and email notifications.
 * Version: 1.5.0
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: EEPPJ
 * License: GPL v2 or later
 * Text Domain: eeppj-pqrrs
 */

defined('ABSPATH') || exit;

define('EEPPJ_PQRRS_VERSION', '1.5.0');
define('EEPPJ_PQRRS_PATH', plugin_dir_path(__FILE__));
define('EEPPJ_PQRRS_URL', plugin_dir_url(__FILE__));

// Autoload classes
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-crypto.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-validator.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-turnstile.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-handler.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-form.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-admin.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-email.php';
require_once EEPPJ_PQRRS_PATH . 'includes/class-pqrrs-reports.php';

/* ====== Activation: create DB table ====== */
function eeppj_pqrrs_activate() {
    global $wpdb;
    $table = $wpdb->prefix . 'eeppj_pqrrs';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        submission_id VARCHAR(8) NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        cedula VARCHAR(255) DEFAULT '',
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

    // Generate encryption key for PII fields
    if (!EEPPJ_PQRRS_Crypto::generate_key()) {
        error_log('EEPPJ PQRRS WARNING: Encryption key not generated during activation. Cedulas will be stored unencrypted.');
    }

    // Schedule retention cron
    if (!wp_next_scheduled('eeppj_pqrrs_retention_cron')) {
        wp_schedule_event(time(), 'daily', 'eeppj_pqrrs_retention_cron');
    }
}
register_activation_hook(__FILE__, 'eeppj_pqrrs_activate');

/* ====== Deactivation: clear cron ====== */
function eeppj_pqrrs_deactivate() {
    $timestamp = wp_next_scheduled('eeppj_pqrrs_retention_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'eeppj_pqrrs_retention_cron');
    }
}
register_deactivation_hook(__FILE__, 'eeppj_pqrrs_deactivate');

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
        $installed = '1.2.0';
    }

    if (version_compare($installed, '1.5.0', '<')) {
        global $wpdb;
        if (!isset($table)) {
            $table = $wpdb->prefix . 'eeppj_pqrrs';
        }

        // Widen cedula column for encrypted values (~120 chars)
        $wpdb->query("ALTER TABLE $table MODIFY cedula VARCHAR(255) DEFAULT ''");
        if ($wpdb->last_error) {
            error_log('EEPPJ PQRRS CRITICAL: Failed to widen cedula column: ' . $wpdb->last_error);
            return; // Do NOT mark migration complete — encrypted values would be truncated
        }

        // Generate encryption key if not present
        EEPPJ_PQRRS_Crypto::generate_key();

        // Encrypt existing plaintext cedulas in batches
        if (EEPPJ_PQRRS_Crypto::is_configured()) {
            $batch_size = 50;
            $max_iterations = 1000;
            $iteration = 0;
            do {
                if (++$iteration > $max_iterations) {
                    error_log('EEPPJ PQRRS: Migration batch encryption exceeded ' . $max_iterations . ' iterations. Some cedulas may remain unencrypted.');
                    break;
                }
                $rows = $wpdb->get_results(
                    "SELECT id, cedula FROM $table WHERE cedula != '' AND cedula NOT LIKE '%:%:%' AND cedula != '[ANONIMIZADO]' LIMIT $batch_size"
                );
                foreach ($rows as $row) {
                    $encrypted = EEPPJ_PQRRS_Crypto::encrypt($row->cedula);
                    // If encrypt returned plaintext (no ':'), stop to avoid infinite loop
                    if (strpos($encrypted, ':') === false) {
                        error_log('EEPPJ PQRRS: Encryption failed during migration for record ID ' . $row->id . '. Stopping batch.');
                        break 2;
                    }
                    $wpdb->update($table, array('cedula' => $encrypted), array('id' => $row->id), array('%s'), array('%d'));
                }
            } while (count($rows) === $batch_size);
        }

        // Schedule retention cron for existing installs
        if (!wp_next_scheduled('eeppj_pqrrs_retention_cron')) {
            wp_schedule_event(time(), 'daily', 'eeppj_pqrrs_retention_cron');
        }

        update_option('eeppj_pqrrs_db_version', '1.5.0');
    }
}
add_action('admin_init', 'eeppj_pqrrs_check_db_upgrade');

/* ====== Data retention cron handler ====== */
function eeppj_pqrrs_run_retention() {
    if (get_option('eeppj_pqrrs_retention_enabled', '1') !== '1') {
        return;
    }

    $days = (int) get_option('eeppj_pqrrs_retention_days', 730);
    if ($days < 30) {
        $days = 730;
    }

    $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

    global $wpdb;
    $table = $wpdb->prefix . 'eeppj_pqrrs';

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, archivo_id FROM $table
         WHERE status IN ('completada', 'descartada')
         AND created_at < %s
         AND nombre != '[ANONIMIZADO]'
         LIMIT 100",
        $cutoff
    ));

    if (empty($rows)) {
        return;
    }

    $count = 0;
    $failed = 0;
    foreach ($rows as $row) {
        // Delete attachment if exists
        if ($row->archivo_id) {
            $deleted = wp_delete_attachment($row->archivo_id, true);
            if (!$deleted) {
                error_log('EEPPJ PQRRS: Retention cron failed to delete attachment ID ' . $row->archivo_id . ' for record ID ' . $row->id);
            }
        }

        $result = $wpdb->update(
            $table,
            array(
                'nombre'     => '[ANONIMIZADO]',
                'cedula'     => '[ANONIMIZADO]',
                'email'      => '[ANONIMIZADO]',
                'telefono'   => '',
                'ip_address' => '0.0.0.0',
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $row->id),
            array('%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            error_log('EEPPJ PQRRS: Retention cron FAILED to anonymize record ID ' . $row->id . ': ' . $wpdb->last_error);
            $failed++;
            continue;
        }

        // Null out archivo_id separately (wpdb->update can't mix NULL with format strings)
        $wpdb->query($wpdb->prepare("UPDATE $table SET archivo_id = NULL WHERE id = %d", $row->id));
        $count++;
    }

    if ($count > 0 || $failed > 0) {
        error_log('EEPPJ PQRRS: Retention cron completed — anonymized ' . $count . ', failed ' . $failed . '.');
    }
}
add_action('eeppj_pqrrs_retention_cron', 'eeppj_pqrrs_run_retention');

/* ====== Initialize plugin components ====== */
function eeppj_pqrrs_init() {
    EEPPJ_PQRRS_Form::init();
    EEPPJ_PQRRS_Handler::init();
    EEPPJ_PQRRS_Admin::init();
    EEPPJ_PQRRS_Reports::init();
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
new EEPPJ_PQRRS_GitHub_Updater(
    'eeppj-pqrrs/eeppj-pqrrs.php',
    'jgarcesres/wp_eeppj',
    EEPPJ_PQRRS_VERSION,
    'plugin',
    'eeppj-pqrrs.zip'
);
