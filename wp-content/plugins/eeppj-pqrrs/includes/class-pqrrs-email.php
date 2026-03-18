<?php
/**
 * Email + webhook notifications for PQRRS submissions
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Email {
    /**
     * Send notification for a new submission
     *
     * @param array $data Submission data (submission_id, nombre, email, tipo, asunto)
     */
    public static function notify($data) {
        // Email notification
        $to = get_option('eeppj_pqrrs_notification_email', get_option('admin_email'));
        if (!empty($to)) {
            $tipo_labels = [
                'peticion'   => 'Petición',
                'queja'      => 'Queja',
                'reclamo'    => 'Reclamo',
                'recurso'    => 'Recurso',
                'sugerencia' => 'Sugerencia',
            ];
            $tipo_label = $tipo_labels[$data['tipo']] ?? $data['tipo'];

            $subject = sprintf('[EEPPJ PQRRS] Nueva %s — %s', $tipo_label, $data['submission_id']);
            $body = sprintf(
                "Se ha recibido una nueva solicitud PQRRS:\n\n" .
                "Radicado: %s\n" .
                "Tipo: %s\n" .
                "Nombre: %s\n" .
                "Email: %s\n" .
                "Asunto: %s\n\n" .
                "Revise la solicitud en: %s",
                $data['submission_id'],
                $tipo_label,
                $data['nombre'],
                $data['email'],
                $data['asunto'],
                admin_url('admin.php?page=eeppj-pqrrs')
            );

            wp_mail($to, $subject, $body);
        }

        // Optional webhook (Discord/Slack)
        $webhook_url = get_option('eeppj_pqrrs_webhook_url');
        if (!empty($webhook_url)) {
            wp_safe_remote_post($webhook_url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode([
                    'content' => sprintf(
                        "📩 Nueva PQRRS: **%s** — %s\nDe: %s (%s)\nAsunto: %s",
                        strtoupper($data['tipo']),
                        $data['submission_id'],
                        $data['nombre'],
                        $data['email'],
                        $data['asunto']
                    ),
                ]),
                'timeout' => 5,
            ]);
        }
    }
}
