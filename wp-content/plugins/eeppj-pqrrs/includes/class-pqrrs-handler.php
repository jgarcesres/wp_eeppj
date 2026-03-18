<?php
/**
 * PQRRS form submission handler (AJAX endpoint)
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Handler {
    public static function init() {
        add_action('wp_ajax_eeppj_pqrrs_submit', [__CLASS__, 'handle']);
        add_action('wp_ajax_nopriv_eeppj_pqrrs_submit', [__CLASS__, 'handle']);
    }

    public static function handle() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['eeppj_pqrrs_nonce'] ?? '', 'eeppj_pqrrs_submit')) {
            wp_send_json_error(['message' => 'Solicitud inválida.'], 403);
        }

        // Honeypot check
        if (!empty($_POST['website'])) {
            wp_send_json_error(['message' => 'Solicitud rechazada.'], 403);
        }

        // Rate limiting via transients (5 req/min per IP)
        $ip = self::get_client_ip();
        $rate_key = 'pqrrs_rate_' . md5($ip);
        $count = (int) get_transient($rate_key);
        if ($count >= 5) {
            wp_send_json_error(['message' => 'Demasiadas solicitudes. Espere un minuto.'], 429);
        }
        set_transient($rate_key, $count + 1, 60);

        // Turnstile verification
        $turnstile = EEPPJ_PQRRS_Turnstile::verify(
            $_POST['cf-turnstile-response'] ?? '',
            $ip
        );
        if ($turnstile !== true) {
            wp_send_json_error(['message' => $turnstile], 400);
        }

        // Sanitize fields
        $nombre  = sanitize_text_field($_POST['nombre'] ?? '');
        $cedula  = sanitize_text_field($_POST['cedula'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $telefono = sanitize_text_field($_POST['telefono'] ?? '');
        $tipo    = sanitize_text_field($_POST['tipo'] ?? '');
        $asunto  = sanitize_text_field($_POST['asunto'] ?? '');
        $mensaje = sanitize_textarea_field($_POST['mensaje'] ?? '');

        // Validate required fields
        $errors = [];
        if (empty($nombre)) $errors[] = 'Nombre es obligatorio.';
        if (empty($email) || !is_email($email)) $errors[] = 'Correo electrónico válido es obligatorio.';
        if (!in_array($tipo, ['peticion', 'queja', 'reclamo', 'recurso', 'sugerencia'], true)) {
            $errors[] = 'Tipo de solicitud inválido.';
        }
        if (empty($asunto)) $errors[] = 'Asunto es obligatorio.';
        if (empty($mensaje)) $errors[] = 'Mensaje es obligatorio.';

        if (!empty($errors)) {
            wp_send_json_error(['message' => implode(' ', $errors)], 400);
        }

        // File upload validation
        $archivo_id = null;
        if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validation = EEPPJ_PQRRS_Validator::validate($_FILES['archivo']);
            if ($validation !== true) {
                wp_send_json_error(['message' => $validation], 400);
            }

            // Upload to WP Media Library
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload('archivo', 0);
            if (is_wp_error($attachment_id)) {
                wp_send_json_error(['message' => 'Error al guardar el archivo.'], 500);
            }
            $archivo_id = $attachment_id;
        }

        // Generate submission ID
        $submission_id = substr(bin2hex(random_bytes(4)), 0, 8);

        // Insert into database
        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';
        $inserted = $wpdb->insert($table, [
            'submission_id' => $submission_id,
            'nombre'        => $nombre,
            'cedula'        => $cedula,
            'email'         => $email,
            'telefono'      => $telefono,
            'tipo'          => $tipo,
            'asunto'        => $asunto,
            'mensaje'       => $mensaje,
            'archivo_id'    => $archivo_id,
            'ip_address'    => $ip,
            'created_at'    => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s']);

        if ($inserted === false) {
            wp_send_json_error(['message' => 'Error al guardar la solicitud.'], 500);
        }

        // Send notifications
        EEPPJ_PQRRS_Email::notify([
            'submission_id' => $submission_id,
            'nombre'        => $nombre,
            'email'         => $email,
            'tipo'          => $tipo,
            'asunto'        => $asunto,
        ]);

        wp_send_json_success([
            'message' => 'Su solicitud ha sido recibida exitosamente. Número de radicado: ' . $submission_id,
            'id'      => $submission_id,
        ]);
    }

    private static function get_client_ip() {
        $trusted_header = get_option('eeppj_pqrrs_trusted_ip_header', '');

        // Only check the admin-configured trusted header, then fall back to REMOTE_ADDR.
        // This prevents IP spoofing via untrusted X-Forwarded-For / CF-Connecting-IP headers.
        $headers = array('REMOTE_ADDR');
        if (!empty($trusted_header)) {
            array_unshift($headers, $trusted_header);
        }

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = explode(',', sanitize_text_field($_SERVER[$header]))[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback: accept REMOTE_ADDR even if private (local dev / intranet).
        $remote = isset($_SERVER['REMOTE_ADDR']) ? trim(sanitize_text_field($_SERVER['REMOTE_ADDR'])) : '';
        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            return $remote;
        }

        return '0.0.0.0';
    }
}
