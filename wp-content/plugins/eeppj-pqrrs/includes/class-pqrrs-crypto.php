<?php
/**
 * PQRRS Crypto — AES-256-CBC encryption for PII fields (cedula)
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Crypto {

    private static $cipher = 'aes-256-cbc';
    private static $option_name = 'eeppj_pqrrs_encryption_key';

    /**
     * Encrypt plaintext using AES-256-CBC + HMAC-SHA256 (encrypt-then-MAC).
     *
     * @param string $plaintext
     * @return string base64(iv):base64(hmac):base64(ciphertext) or original if key unavailable
     */
    public static function encrypt($plaintext) {
        if ($plaintext === '' || $plaintext === null) {
            return '';
        }

        $key = self::get_key();
        if ($key === false) {
            return $plaintext;
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $ciphertext = openssl_encrypt($plaintext, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            return $plaintext;
        }

        $hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);

        return base64_encode($iv) . ':' . base64_encode($hmac) . ':' . base64_encode($ciphertext);
    }

    /**
     * Decrypt stored value. Detects encrypted format (contains two ':').
     * Returns as-is if plaintext or anonymized.
     *
     * @param string $stored
     * @return string
     */
    public static function decrypt($stored) {
        if ($stored === '' || $stored === null || $stored === '[ANONIMIZADO]') {
            return $stored === null ? '' : $stored;
        }

        // Not encrypted if it doesn't contain exactly two ':'
        $parts = explode(':', $stored);
        if (count($parts) !== 3) {
            return $stored;
        }

        $key = self::get_key();
        if ($key === false) {
            return '[ERROR: clave no disponible]';
        }

        $iv = base64_decode($parts[0], true);
        $hmac = base64_decode($parts[1], true);
        $ciphertext = base64_decode($parts[2], true);

        if ($iv === false || $hmac === false || $ciphertext === false) {
            return $stored;
        }

        // Verify HMAC
        $expected_hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        if (!hash_equals($expected_hmac, $hmac)) {
            return '[ERROR: HMAC inválido]';
        }

        $plaintext = openssl_decrypt($ciphertext, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            return '[ERROR: descifrado fallido]';
        }

        return $plaintext;
    }

    /**
     * Check if encryption is configured and usable.
     *
     * @return bool
     */
    public static function is_configured() {
        return self::get_key() !== false;
    }

    /**
     * Retrieve the encryption key.
     * Checks constant first, then DB option (decrypted with AUTH_KEY).
     *
     * @return string|false 32-byte key or false
     */
    public static function get_key() {
        // Advanced users can define the key in wp-config.php
        if (defined('EEPPJ_PQRRS_ENCRYPTION_KEY')) {
            $key = base64_decode(EEPPJ_PQRRS_ENCRYPTION_KEY, true);
            if ($key !== false && strlen($key) === 32) {
                return $key;
            }
        }

        // Fall back to DB option encrypted with AUTH_KEY
        $stored = get_option(self::$option_name, '');
        if (empty($stored)) {
            return false;
        }

        if (!defined('AUTH_KEY') || AUTH_KEY === '' || AUTH_KEY === 'put your unique phrase here') {
            return false;
        }

        $wrapping_key = substr(hash('sha256', AUTH_KEY, true), 0, 32);
        $wrapping_iv = substr(hash('sha256', 'eeppj_pqrrs_iv' . AUTH_KEY, true), 0, 16);

        $decoded = base64_decode($stored, true);
        if ($decoded === false) {
            return false;
        }

        $key = openssl_decrypt($decoded, self::$cipher, $wrapping_key, OPENSSL_RAW_DATA, $wrapping_iv);

        if ($key === false || strlen($key) !== 32) {
            return false;
        }

        return $key;
    }

    /**
     * Generate a new 32-byte encryption key, encrypt with AUTH_KEY, store as option.
     * Does nothing if a key already exists.
     *
     * @return bool true if key was generated or already exists
     */
    public static function generate_key() {
        if (self::is_configured()) {
            return true;
        }

        if (!defined('AUTH_KEY') || AUTH_KEY === '' || AUTH_KEY === 'put your unique phrase here') {
            return false;
        }

        $key = openssl_random_pseudo_bytes(32);

        $wrapping_key = substr(hash('sha256', AUTH_KEY, true), 0, 32);
        $wrapping_iv = substr(hash('sha256', 'eeppj_pqrrs_iv' . AUTH_KEY, true), 0, 16);

        $encrypted = openssl_encrypt($key, self::$cipher, $wrapping_key, OPENSSL_RAW_DATA, $wrapping_iv);

        if ($encrypted === false) {
            return false;
        }

        update_option(self::$option_name, base64_encode($encrypted));
        return true;
    }
}
