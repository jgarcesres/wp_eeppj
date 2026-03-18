<?php
/**
 * Cloudflare Turnstile verification
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Turnstile {
    private static $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verify a Turnstile token
     *
     * @param string $token The cf-turnstile-response token
     * @param string $ip    Remote IP address
     * @return true|string  True if valid, error message if not
     */
    public static function verify($token, $ip = '') {
        $secret   = get_option('eeppj_pqrrs_turnstile_secret');
        $site_key = get_option('eeppj_pqrrs_turnstile_site_key');
        $require  = get_option('eeppj_pqrrs_require_turnstile', '1');
        $keys_missing = empty($secret) || empty($site_key);

        if ($keys_missing) {
            if ($require === '1') {
                return 'CAPTCHA no configurado. Contacte al administrador del sitio.';
            }
            // Turnstile explicitly disabled — skip verification (dev/testing only).
            return true;
        }

        if (empty($token)) {
            return 'Por favor complete la verificación CAPTCHA.';
        }

        $response = wp_remote_post(self::$verify_url, [
            'body' => [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            error_log('EEPPJ PQRRS Turnstile verification failed: ' . $response->get_error_message());
            return 'Error al verificar CAPTCHA. Intente de nuevo.';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['success'])) {
            $error_codes = isset($body['error-codes']) ? implode(', ', $body['error-codes']) : 'unknown';
            error_log('EEPPJ PQRRS Turnstile rejected token. Error codes: ' . $error_codes);
            return 'Verificación CAPTCHA fallida. Intente de nuevo.';
        }

        return true;
    }
}
