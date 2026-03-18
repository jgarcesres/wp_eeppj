<?php
/**
 * File validation — MIME type + magic bytes (same checks as CF Worker)
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Validator {
    /**
     * Allowed file types with their magic byte signatures
     */
    private static $signatures = [
        'application/pdf'  => ["\x25\x50\x44\x46"],  // %PDF
        'image/png'        => ["\x89\x50\x4E\x47"],   // .PNG
        'image/jpeg'       => ["\xFF\xD8\xFF"],        // JFIF/EXIF
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ["\x50\x4B\x03\x04"], // ZIP (DOCX)
    ];

    private static $allowed_extensions = ['pdf', 'png', 'jpg', 'jpeg', 'docx'];

    /**
     * Validate an uploaded file
     *
     * @param array $file $_FILES entry
     * @return true|string True if valid, error message string if not
     */
    public static function validate($file) {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            if (isset($file['error']) && $file['error'] === UPLOAD_ERR_NO_FILE) {
                return true; // No file is OK (optional)
            }
            return 'Error al subir el archivo.';
        }

        // Check size (default 5MB)
        $max_size = (int) get_option('eeppj_pqrrs_max_upload', 5) * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return 'El archivo excede el tamaño máximo permitido (' . (int) get_option('eeppj_pqrrs_max_upload', 5) . ' MB).';
        }

        // Check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::$allowed_extensions, true)) {
            return 'Tipo de archivo no permitido. Solo se aceptan: PDF, PNG, JPG, DOCX.';
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime, self::$signatures)) {
            return 'El tipo MIME del archivo no es válido.';
        }

        // Check magic bytes
        $handle = fopen($file['tmp_name'], 'rb');
        if (!$handle) {
            return 'No se pudo leer el archivo.';
        }
        $bytes = fread($handle, 8);
        fclose($handle);

        $valid_sig = false;
        foreach (self::$signatures[$mime] as $sig) {
            if (substr($bytes, 0, strlen($sig)) === $sig) {
                $valid_sig = true;
                break;
            }
        }

        if (!$valid_sig) {
            return 'El contenido del archivo no coincide con su tipo declarado.';
        }

        return true;
    }
}
