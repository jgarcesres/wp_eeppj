<?php
/**
 * PQRRS Reports — monthly summary + detail views with CSV export
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Reports {

    private static $statuses = [
        'pendiente'   => ['label' => 'Pendiente',   'color' => '#d97706', 'bg' => '#fffbeb'],
        'en_progreso' => ['label' => 'En Progreso',  'color' => '#2563eb', 'bg' => '#eff6ff'],
        'completada'  => ['label' => 'Completada',   'color' => '#16a34a', 'bg' => '#f0fdf4'],
        'descartada'  => ['label' => 'Descartada',   'color' => '#6b7280', 'bg' => '#f3f4f6'],
    ];

    private static $tipos = ['peticion', 'queja', 'reclamo', 'recurso', 'sugerencia'];

    private static $tipo_labels = [
        'peticion' => 'Peticiones', 'queja' => 'Quejas', 'reclamo' => 'Reclamos',
        'recurso' => 'Recursos', 'sugerencia' => 'Sugerencias',
    ];

    private static $tipo_colors = [
        'peticion' => '#3182ce', 'queja' => '#e53e3e', 'reclamo' => '#dd6b20',
        'recurso' => '#805ad5', 'sugerencia' => '#38a169',
    ];

    private static $month_names = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_submenu']);
        add_action('admin_post_eeppj_pqrrs_csv', [__CLASS__, 'handle_csv_export']);
    }

    /**
     * Mask cedula showing only last 4 digits: ****1234
     */
    private static function mask_cedula($cedula) {
        if ($cedula === '' || $cedula === null || $cedula === '[ANONIMIZADO]') {
            return $cedula === null ? '' : $cedula;
        }
        $len = strlen($cedula);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - 4) . substr($cedula, -4);
    }

    public static function add_submenu() {
        add_submenu_page(
            'eeppj-pqrrs', 'Reportes PQRRS', 'Reportes', 'manage_options',
            'eeppj-pqrrs-reports', [__CLASS__, 'render_reports']
        );
    }

    public static function render_reports() {
        if (!current_user_can('manage_options')) return;

        $mes = sanitize_text_field($_GET['mes'] ?? '');
        if ($mes && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            self::render_month_detail($mes);
        } else {
            self::render_summary();
        }
    }

    private static function get_month_name($year_month) {
        $parts = explode('-', $year_month);
        $m = (int) $parts[1];
        $y = $parts[0];
        return (self::$month_names[$m] ?? $m) . ' ' . $y;
    }

    private static function render_summary() {
        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        // Default range: last 12 months
        $default_hasta = date('Y-m');
        $default_desde = date('Y-m', strtotime('-11 months'));

        $desde = sanitize_text_field($_GET['desde'] ?? $default_desde);
        $hasta = sanitize_text_field($_GET['hasta'] ?? $default_hasta);

        // Validate format (only valid months 01-12)
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $desde)) $desde = $default_desde;
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $hasta)) $hasta = $default_hasta;

        // Auto-swap if inverted
        if ($desde > $hasta) {
            list($desde, $hasta) = array($hasta, $desde);
        }

        $date_start = $desde . '-01';
        // End of "hasta" month: first day of next month
        $date_end = date('Y-m-01', strtotime($hasta . '-01 +1 month'));

        // Query grouped by month + tipo
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(created_at, '%%Y-%%m') AS mes, tipo, COUNT(*) AS total
             FROM $table
             WHERE created_at >= %s AND created_at < %s
             GROUP BY mes, tipo
             ORDER BY mes DESC",
            $date_start, $date_end
        ));

        if ($wpdb->last_error) {
            echo '<div class="notice notice-error"><p>' . esc_html('Error de base de datos: ' . $wpdb->last_error) . '</p></div>';
            return;
        }

        // Build lookup: $data[month][tipo] = count
        $data = [];
        foreach ($rows as $r) {
            $data[$r->mes][$r->tipo] = (int) $r->total;
        }

        // Enumerate all months in range (fill gaps)
        $all_months = [];
        $cursor = new DateTime($hasta . '-01');
        $limit = new DateTime($desde . '-01');
        while ($cursor >= $limit) {
            $all_months[] = $cursor->format('Y-m');
            $cursor->modify('-1 month');
        }

        // Stat cards: totals across the entire range
        $grand_total = 0;
        $tipo_totals = array_fill_keys(self::$tipos, 0);
        foreach ($data as $month_data) {
            foreach (self::$tipos as $t) {
                $cnt = isset($month_data[$t]) ? $month_data[$t] : 0;
                $tipo_totals[$t] += $cnt;
                $grand_total += $cnt;
            }
        }

        $csv_url = wp_nonce_url(
            admin_url('admin-post.php?action=eeppj_pqrrs_csv&mode=summary&desde=' . $desde . '&hasta=' . $hasta),
            'eeppj_pqrrs_csv'
        );
        ?>
        <div class="wrap eeppj-admin">
          <h1>Reportes PQRRS</h1>

          <!-- Date range controls -->
          <form method="get" class="eeppj-report-controls">
            <input type="hidden" name="page" value="eeppj-pqrrs-reports" />
            <label>
              <span style="font-size:0.75rem;font-weight:600;color:#57606a;">Desde</span>
              <input type="month" name="desde" value="<?php echo esc_attr($desde); ?>" />
            </label>
            <label>
              <span style="font-size:0.75rem;font-weight:600;color:#57606a;">Hasta</span>
              <input type="month" name="hasta" value="<?php echo esc_attr($hasta); ?>" />
            </label>
            <button type="submit" class="button button-primary" style="align-self:flex-end;">Consultar</button>
            <a href="<?php echo esc_url($csv_url); ?>" class="button" style="align-self:flex-end;">Exportar CSV</a>
          </form>

          <!-- Stat cards -->
          <div class="eeppj-stats">
            <div class="eeppj-stat-card">
              <div class="eeppj-stat-number"><?php echo esc_html($grand_total); ?></div>
              <div class="eeppj-stat-label">Total</div>
            </div>
            <?php foreach (self::$tipos as $t) : ?>
              <div class="eeppj-stat-card" style="border-top: 3px solid <?php echo esc_attr(self::$tipo_colors[$t]); ?>;">
                <div class="eeppj-stat-number" style="color: <?php echo esc_attr(self::$tipo_colors[$t]); ?>;"><?php echo esc_html($tipo_totals[$t]); ?></div>
                <div class="eeppj-stat-label"><?php echo esc_html(self::$tipo_labels[$t]); ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Summary table -->
          <table class="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <th>Mes</th>
                <?php foreach (self::$tipos as $t) : ?>
                  <th style="width:100px;text-align:right;"><?php echo esc_html(self::$tipo_labels[$t]); ?></th>
                <?php endforeach; ?>
                <th style="width:80px;text-align:right;">Total</th>
                <th style="width:100px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($all_months as $ym) :
                  $month_data = isset($data[$ym]) ? $data[$ym] : [];
                  $row_total = 0;
              ?>
                <tr>
                  <td><strong><?php echo esc_html(self::get_month_name($ym)); ?></strong></td>
                  <?php foreach (self::$tipos as $t) :
                      $cnt = isset($month_data[$t]) ? $month_data[$t] : 0;
                      $row_total += $cnt;
                  ?>
                    <td class="<?php echo $cnt === 0 ? 'eeppj-num-zero' : 'eeppj-num'; ?>" style="text-align:right;">
                      <?php echo esc_html($cnt); ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="eeppj-num" style="text-align:right;font-weight:600;"><?php echo esc_html($row_total); ?></td>
                  <td>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=eeppj-pqrrs-reports&mes=' . $ym)); ?>" class="button button-small">
                      Ver detalle
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php
    }

    private static function render_month_detail($mes) {
        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        $date_start = $mes . '-01';
        $date_end = date('Y-m-01', strtotime($date_start . ' +1 month'));

        // Filters
        $filter_tipo = sanitize_text_field($_GET['tipo'] ?? '');
        $filter_status = sanitize_text_field($_GET['status'] ?? '');

        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        // Stats for the month (unfiltered)
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE created_at >= %s AND created_at < %s",
            $date_start, $date_end
        ));
        $status_counts = $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) as cnt FROM $table WHERE created_at >= %s AND created_at < %s GROUP BY status",
            $date_start, $date_end
        ), OBJECT_K);

        if ($wpdb->last_error) {
            echo '<div class="wrap eeppj-admin"><div class="notice notice-error"><p>' . esc_html('Error de base de datos: ' . $wpdb->last_error) . '</p></div></div>';
            return;
        }

        // Build WHERE for filtered list
        $where_parts = [
            $wpdb->prepare("created_at >= %s", $date_start),
            $wpdb->prepare("created_at < %s", $date_end),
        ];
        if ($filter_tipo && in_array($filter_tipo, self::$tipos, true)) {
            $where_parts[] = $wpdb->prepare("tipo = %s", $filter_tipo);
        }
        if ($filter_status && isset(self::$statuses[$filter_status])) {
            $where_parts[] = $wpdb->prepare("status = %s", $filter_status);
        }
        $where = 'WHERE ' . implode(' AND ', $where_parts);

        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page, $offset
            )
        );
        $total_filtered = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
        $total_pages = (int) ceil($total_filtered / $per_page);

        $base_url = admin_url('admin.php?page=eeppj-pqrrs-reports&mes=' . $mes);
        $csv_params = 'admin-post.php?action=eeppj_pqrrs_csv&mode=detail&mes=' . $mes;
        if ($filter_tipo) $csv_params .= '&tipo=' . $filter_tipo;
        if ($filter_status) $csv_params .= '&status=' . $filter_status;
        $csv_url = wp_nonce_url(admin_url($csv_params), 'eeppj_pqrrs_csv');
        ?>
        <div class="wrap eeppj-admin">
          <a href="<?php echo esc_url(admin_url('admin.php?page=eeppj-pqrrs-reports')); ?>" class="eeppj-back-link">&larr; Volver a reportes</a>
          <h1>Reporte: <?php echo esc_html(self::get_month_name($mes)); ?></h1>

          <!-- Stat cards: total + per-status -->
          <div class="eeppj-stats">
            <div class="eeppj-stat-card">
              <div class="eeppj-stat-number"><?php echo esc_html($total); ?></div>
              <div class="eeppj-stat-label">Total</div>
            </div>
            <?php foreach (self::$statuses as $skey => $sinfo) :
                $cnt = isset($status_counts[$skey]) ? (int) $status_counts[$skey]->cnt : 0;
            ?>
              <div class="eeppj-stat-card" style="border-top: 3px solid <?php echo esc_attr($sinfo['color']); ?>;">
                <div class="eeppj-stat-number" style="color: <?php echo esc_attr($sinfo['color']); ?>;"><?php echo esc_html($cnt); ?></div>
                <div class="eeppj-stat-label"><?php echo esc_html($sinfo['label']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Filters -->
          <div class="eeppj-filter-group">
            <div class="eeppj-filter" style="margin-bottom: 0.5rem;">
              <strong style="font-size: 0.75rem; color: #6b7280; margin-right: 0.25rem;">Tipo:</strong>
              <a href="<?php echo esc_url(add_query_arg('tipo', '', remove_query_arg('paged', $base_url . ($filter_status ? '&status=' . $filter_status : '')))); ?>"
                 class="<?php echo $filter_tipo ? '' : 'active'; ?>">Todos</a>
              <?php foreach (self::$tipos as $t) : ?>
                <a href="<?php echo esc_url(add_query_arg('tipo', $t, remove_query_arg('paged', $base_url . ($filter_status ? '&status=' . $filter_status : '')))); ?>"
                   class="<?php echo $filter_tipo === $t ? 'active' : ''; ?>"
                   style="--badge-color: <?php echo esc_attr(self::$tipo_colors[$t]); ?>;">
                  <?php echo esc_html(self::$tipo_labels[$t]); ?>
                </a>
              <?php endforeach; ?>
            </div>
            <div class="eeppj-filter">
              <strong style="font-size: 0.75rem; color: #6b7280; margin-right: 0.25rem;">Estado:</strong>
              <a href="<?php echo esc_url(add_query_arg('status', '', remove_query_arg('paged', $base_url . ($filter_tipo ? '&tipo=' . $filter_tipo : '')))); ?>"
                 class="<?php echo $filter_status ? '' : 'active'; ?>">Todos</a>
              <?php foreach (self::$statuses as $skey => $sinfo) : ?>
                <a href="<?php echo esc_url(add_query_arg('status', $skey, remove_query_arg('paged', $base_url . ($filter_tipo ? '&tipo=' . $filter_tipo : '')))); ?>"
                   class="<?php echo $filter_status === $skey ? 'active' : ''; ?>"
                   style="--badge-color: <?php echo esc_attr($sinfo['color']); ?>;">
                  <?php echo esc_html($sinfo['label']); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="margin-bottom:1rem;padding:0.75rem 1rem;background:#fffbeb;border:1px solid #fbbf24;border-radius:8px;font-size:0.8125rem;color:#92400e;">
            <strong>Ley 1581/2012 — Datos personales:</strong> El CSV estándar enmascara las cédulas. Use "Exportar completo" solo cuando sea estrictamente necesario y asegúrese de proteger el archivo resultante.
          </div>
          <div style="margin-bottom:1rem;display:flex;gap:0.5rem;">
            <a href="<?php echo esc_url($csv_url); ?>" class="button">Exportar CSV</a>
            <?php
            $full_csv_params = $csv_params . '&full=1';
            $full_csv_url = wp_nonce_url(admin_url($full_csv_params), 'eeppj_pqrrs_csv');
            ?>
            <a href="<?php echo esc_url($full_csv_url); ?>" class="button"
               onclick="return confirm('Este archivo contendrá datos personales completos (cédulas sin enmascarar) protegidos por la Ley 1581 de 2012.\n\nUsted es responsable de la custodia y tratamiento adecuado de estos datos.\n\n¿Desea continuar?');">
              Exportar CSV completo
            </a>
          </div>

          <!-- Submissions table (read-only) -->
          <table class="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <th style="width:84px;">Radicado</th>
                <th style="width:90px;">Tipo</th>
                <th style="width:150px;">Nombre</th>
                <th style="width:180px;">Email</th>
                <th>Asunto</th>
                <th style="width:100px;">Estado</th>
                <th style="width:130px;">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($submissions)) : ?>
                <tr><td colspan="7" style="text-align:center;color:#666;">No hay solicitudes en este período.</td></tr>
              <?php endif; ?>
              <?php foreach ($submissions as $s) :
                  $st = isset(self::$statuses[$s->status])
                      ? self::$statuses[$s->status]
                      : ['label' => ucfirst($s->status), 'color' => '#9ca3af', 'bg' => '#f9fafb'];
              ?>
                <tr>
                  <td><code><?php echo esc_html($s->submission_id); ?></code></td>
                  <td>
                    <span class="eeppj-badge" style="background: <?php echo esc_attr(self::$tipo_colors[$s->tipo] ?? '#666'); ?>;">
                      <?php echo esc_html(ucfirst($s->tipo)); ?>
                    </span>
                  </td>
                  <td><?php echo esc_html($s->nombre); ?></td>
                  <td><?php echo esc_html($s->email); ?></td>
                  <td><?php echo esc_html(wp_trim_words($s->asunto, 8)); ?></td>
                  <td>
                    <span class="eeppj-status-badge" style="color:<?php echo esc_attr($st['color']); ?>;background:<?php echo esc_attr($st['bg']); ?>;">
                      <?php echo esc_html($st['label']); ?>
                    </span>
                  </td>
                  <td><?php echo esc_html(wp_date('d/m/Y H:i', strtotime($s->created_at))); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if ($total_pages > 1) : ?>
            <div class="tablenav bottom">
              <div class="tablenav-pages">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                  <?php if ($i === $page) : ?>
                    <span class="tablenav-pages-navspan button disabled"><?php echo esc_html($i); ?></span>
                  <?php else : ?>
                    <a class="button" href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo esc_html($i); ?></a>
                  <?php endif; ?>
                <?php endfor; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_csv_export() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'eeppj_pqrrs_csv')) {
            wp_die('No autorizado.', 'No autorizado', ['response' => 403]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';
        $mode = sanitize_text_field($_GET['mode'] ?? 'summary');

        if ($mode === 'detail') {
            self::export_detail_csv($wpdb, $table);
        } else {
            self::export_summary_csv($wpdb, $table);
        }
        exit;
    }

    private static function export_summary_csv($wpdb, $table) {
        $desde = sanitize_text_field($_GET['desde'] ?? date('Y-m', strtotime('-11 months')));
        $hasta = sanitize_text_field($_GET['hasta'] ?? date('Y-m'));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $desde) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $hasta)) {
            wp_die('Parámetros inválidos.');
        }

        $date_start = $desde . '-01';
        $date_end = date('Y-m-01', strtotime($hasta . '-01 +1 month'));

        if ($desde > $hasta) {
            list($desde, $hasta) = array($hasta, $desde);
        }

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(created_at, '%%Y-%%m') AS mes, tipo, COUNT(*) AS total
             FROM $table
             WHERE created_at >= %s AND created_at < %s
             GROUP BY mes, tipo
             ORDER BY mes DESC",
            $date_start, $date_end
        ));

        if ($wpdb->last_error) {
            wp_die('Error de base de datos: ' . esc_html($wpdb->last_error), 'Error', ['response' => 500]);
        }

        $data = [];
        foreach ($rows as $r) {
            $data[$r->mes][$r->tipo] = (int) $r->total;
        }

        // Enumerate all months
        $all_months = [];
        $cursor = new DateTime($hasta . '-01');
        $limit = new DateTime($desde . '-01');
        while ($cursor >= $limit) {
            $all_months[] = $cursor->format('Y-m');
            $cursor->modify('-1 month');
        }

        $filename = 'pqrrs-resumen-' . $desde . '-a-' . $hasta . '.csv';
        self::send_csv_headers($filename);

        $out = fopen('php://output', 'w');
        if ($out === false) {
            wp_die('Error interno al generar el archivo CSV.', 'Error', ['response' => 500]);
        }
        // UTF-8 BOM
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Mes', 'Peticiones', 'Quejas', 'Reclamos', 'Recursos', 'Sugerencias', 'Total']);

        foreach ($all_months as $ym) {
            $month_data = isset($data[$ym]) ? $data[$ym] : [];
            $row_total = 0;
            $row = [self::get_month_name($ym)];
            foreach (self::$tipos as $t) {
                $cnt = isset($month_data[$t]) ? $month_data[$t] : 0;
                $row[] = $cnt;
                $row_total += $cnt;
            }
            $row[] = $row_total;
            fputcsv($out, $row);
        }

        fclose($out);
    }

    private static function export_detail_csv($wpdb, $table) {
        $mes = sanitize_text_field($_GET['mes'] ?? '');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            wp_die('Parámetros inválidos.');
        }

        $full_export = isset($_GET['full']) && $_GET['full'] === '1';

        $date_start = $mes . '-01';
        $date_end = date('Y-m-01', strtotime($date_start . ' +1 month'));

        // Apply optional tipo/status filters (same as detail view)
        $filter_tipo = sanitize_text_field($_GET['tipo'] ?? '');
        $filter_status = sanitize_text_field($_GET['status'] ?? '');

        $where_parts = [
            $wpdb->prepare("created_at >= %s", $date_start),
            $wpdb->prepare("created_at < %s", $date_end),
        ];
        if ($filter_tipo && in_array($filter_tipo, self::$tipos, true)) {
            $where_parts[] = $wpdb->prepare("tipo = %s", $filter_tipo);
        }
        if ($filter_status && isset(self::$statuses[$filter_status])) {
            $where_parts[] = $wpdb->prepare("status = %s", $filter_status);
        }
        $where = 'WHERE ' . implode(' AND ', $where_parts);

        $submissions = $wpdb->get_results(
            "SELECT submission_id, tipo, nombre, cedula, email, telefono, asunto, status, created_at
             FROM $table $where ORDER BY created_at DESC"
        );

        if ($wpdb->last_error) {
            wp_die('Error de base de datos: ' . esc_html($wpdb->last_error), 'Error', ['response' => 500]);
        }

        $filename = 'pqrrs-detalle-' . $mes . '.csv';
        self::send_csv_headers($filename);

        $out = fopen('php://output', 'w');
        if ($out === false) {
            wp_die('Error interno al generar el archivo CSV.', 'Error', ['response' => 500]);
        }
        // UTF-8 BOM
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Radicado', 'Tipo', 'Nombre', 'Cedula', 'Email', 'Telefono', 'Asunto', 'Estado', 'Fecha']);

        $status_labels = [];
        foreach (self::$statuses as $k => $v) {
            $status_labels[$k] = $v['label'];
        }

        foreach ($submissions as $s) {
            $decrypted_cedula = EEPPJ_PQRRS_Crypto::decrypt($s->cedula);
            $cedula_value = $full_export ? $decrypted_cedula : self::mask_cedula($decrypted_cedula);

            fputcsv($out, [
                $s->submission_id,
                ucfirst($s->tipo),
                $s->nombre,
                $cedula_value,
                $s->email,
                $s->telefono,
                $s->asunto,
                isset($status_labels[$s->status]) ? $status_labels[$s->status] : $s->status,
                wp_date('d/m/Y H:i', strtotime($s->created_at)),
            ]);
        }

        fclose($out);
    }

    private static function send_csv_headers($filename) {
        $filename = sanitize_file_name($filename);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
