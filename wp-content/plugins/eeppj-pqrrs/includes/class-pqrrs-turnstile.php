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
        $secret = get_option('eeppj_pqrrs_turnstile_secret');

        if (empty($secret)) {
            // No Turnstile configured — skip verification.
            // In production, configure keys in PQRRS > Ajustes to enforce CAPTCHA.
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
            return 'Error al verificar CAPTCHA. Intente de nuevo.';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['success'])) {
            return 'Verificación CAPTCHA fallida. Intente de nuevo.';
        }

        return true;
    }
}
