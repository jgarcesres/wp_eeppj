<?php
/**
 * PQRRS Admin — settings page + submissions list with status workflow
 *
 * Statuses: pendiente → en_progreso → completada / descartada
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;

class EEPPJ_PQRRS_Admin {

    private static $statuses = [
        'pendiente'   => ['label' => 'Pendiente',   'color' => '#d97706', 'bg' => '#fffbeb', 'icon' => '&#9679;'],
        'en_progreso' => ['label' => 'En Progreso',  'color' => '#2563eb', 'bg' => '#eff6ff', 'icon' => '&#8635;'],
        'completada'  => ['label' => 'Completada',   'color' => '#16a34a', 'bg' => '#f0fdf4', 'icon' => '&#10003;'],
        'descartada'  => ['label' => 'Descartada',   'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => '&#10005;'],
    ];

    // Valid transitions: current_status => [allowed next statuses]
    private static $transitions = [
        'pendiente'   => ['en_progreso', 'descartada'],
        'en_progreso' => ['completada', 'descartada', 'pendiente'],
        'completada'  => ['en_progreso'],  // reopen
        'descartada'  => ['pendiente'],    // reopen
    ];

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menus']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_styles']);
        add_action('wp_ajax_eeppj_pqrrs_delete', [__CLASS__, 'handle_delete']);
        add_action('wp_ajax_eeppj_pqrrs_status', [__CLASS__, 'handle_status_change']);
        add_action('wp_ajax_eeppj_pqrrs_notes', [__CLASS__, 'handle_save_notes']);
    }

    public static function add_menus() {
        add_menu_page(
            'PQRRS', 'PQRRS', 'manage_options', 'eeppj-pqrrs',
            [__CLASS__, 'render_submissions'], 'dashicons-email-alt', 30
        );
        add_submenu_page(
            'eeppj-pqrrs', 'Solicitudes PQRRS', 'Solicitudes', 'manage_options',
            'eeppj-pqrrs', [__CLASS__, 'render_submissions']
        );
        add_submenu_page(
            'eeppj-pqrrs', 'Ajustes PQRRS', 'Ajustes', 'manage_options',
            'eeppj-pqrrs-settings', [__CLASS__, 'render_settings']
        );
    }

    public static function register_settings() {
        register_setting('eeppj_pqrrs_settings', 'eeppj_pqrrs_turnstile_site_key');
        register_setting('eeppj_pqrrs_settings', 'eeppj_pqrrs_turnstile_secret');
        register_setting('eeppj_pqrrs_settings', 'eeppj_pqrrs_notification_email');
        register_setting('eeppj_pqrrs_settings', 'eeppj_pqrrs_webhook_url');
        register_setting('eeppj_pqrrs_settings', 'eeppj_pqrrs_max_upload', [
            'type' => 'integer', 'default' => 5, 'sanitize_callback' => 'absint',
        ]);
    }

    public static function enqueue_admin_styles($hook) {
        if (strpos($hook, 'eeppj-pqrrs') === false) return;
        wp_enqueue_style('eeppj-pqrrs-admin', EEPPJ_PQRRS_URL . 'assets/css/pqrrs-admin.css', [], EEPPJ_PQRRS_VERSION);
    }

    public static function render_settings() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap">
          <h1>Ajustes PQRRS</h1>
          <form method="post" action="options.php">
            <?php settings_fields('eeppj_pqrrs_settings'); ?>
            <table class="form-table">
              <tr>
                <th>Turnstile Site Key</th>
                <td><input type="text" name="eeppj_pqrrs_turnstile_site_key" value="<?php echo esc_attr(get_option('eeppj_pqrrs_turnstile_site_key')); ?>" class="regular-text" /></td>
              </tr>
              <tr>
                <th>Turnstile Secret Key</th>
                <td><input type="password" name="eeppj_pqrrs_turnstile_secret" value="<?php echo esc_attr(get_option('eeppj_pqrrs_turnstile_secret')); ?>" class="regular-text" /></td>
              </tr>
              <tr>
                <th>Email de Notificación</th>
                <td>
                  <input type="email" name="eeppj_pqrrs_notification_email" value="<?php echo esc_attr(get_option('eeppj_pqrrs_notification_email', get_option('admin_email'))); ?>" class="regular-text" />
                  <p class="description">Se enviarán notificaciones de nuevas solicitudes a este correo.</p>
                </td>
              </tr>
              <tr>
                <th>Webhook URL (opcional)</th>
                <td>
                  <input type="url" name="eeppj_pqrrs_webhook_url" value="<?php echo esc_attr(get_option('eeppj_pqrrs_webhook_url')); ?>" class="regular-text" />
                  <p class="description">Discord o Slack webhook para notificaciones adicionales.</p>
                </td>
              </tr>
              <tr>
                <th>Tamaño máximo de archivo (MB)</th>
                <td><input type="number" name="eeppj_pqrrs_max_upload" value="<?php echo esc_attr(get_option('eeppj_pqrrs_max_upload', 5)); ?>" min="1" max="25" class="small-text" /></td>
              </tr>
            </table>
            <?php submit_button('Guardar Ajustes'); ?>
          </form>
        </div>
        <?php
    }

    public static function render_submissions() {
        if (!current_user_can('manage_options')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        // Stats by type
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $tipo_counts = $wpdb->get_results("SELECT tipo, COUNT(*) as cnt FROM $table GROUP BY tipo", OBJECT_K);

        // Stats by status
        $status_counts = $wpdb->get_results("SELECT status, COUNT(*) as cnt FROM $table GROUP BY status", OBJECT_K);

        $tipos = ['peticion', 'queja', 'reclamo', 'recurso', 'sugerencia'];
        $tipo_labels = [
            'peticion' => 'Peticiones', 'queja' => 'Quejas', 'reclamo' => 'Reclamos',
            'recurso' => 'Recursos', 'sugerencia' => 'Sugerencias',
        ];
        $tipo_colors = [
            'peticion' => '#3182ce', 'queja' => '#e53e3e', 'reclamo' => '#dd6b20',
            'recurso' => '#805ad5', 'sugerencia' => '#38a169',
        ];

        // Filters
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        $filter_tipo = sanitize_text_field($_GET['tipo'] ?? '');
        $filter_status = sanitize_text_field($_GET['status'] ?? '');

        $where_parts = [];
        if ($filter_tipo) $where_parts[] = $wpdb->prepare("tipo = %s", $filter_tipo);
        if ($filter_status) $where_parts[] = $wpdb->prepare("status = %s", $filter_status);
        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

        $submissions = $wpdb->get_results(
            "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset"
        );
        $total_filtered = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
        $total_pages = (int) ceil($total_filtered / $per_page);

        $nonce = wp_create_nonce('eeppj_pqrrs_status');
        $notes_nonce = wp_create_nonce('eeppj_pqrrs_notes');
        ?>
        <div class="wrap eeppj-admin">
          <h1>Solicitudes PQRRS</h1>

          <!-- Status Stats -->
          <div class="eeppj-stats">
            <div class="eeppj-stat-card">
              <div class="eeppj-stat-number"><?php echo esc_html($total); ?></div>
              <div class="eeppj-stat-label">Total</div>
            </div>
            <?php foreach (self::$statuses as $skey => $sinfo) :
                $cnt = isset($status_counts[$skey]) ? $status_counts[$skey]->cnt : 0;
            ?>
              <div class="eeppj-stat-card" style="border-top: 3px solid <?php echo esc_attr($sinfo['color']); ?>;">
                <div class="eeppj-stat-number" style="color: <?php echo esc_attr($sinfo['color']); ?>;"><?php echo esc_html($cnt); ?></div>
                <div class="eeppj-stat-label"><?php echo esc_html($sinfo['label']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Filters: Status tabs + Type pills -->
          <div class="eeppj-filter-group">
            <div class="eeppj-filter" style="margin-bottom: 0.5rem;">
              <strong style="font-size: 0.75rem; color: #6b7280; margin-right: 0.25rem;">Estado:</strong>
              <a href="<?php echo esc_url(admin_url('admin.php?page=eeppj-pqrrs' . ($filter_tipo ? '&tipo=' . $filter_tipo : ''))); ?>" class="<?php echo $filter_status ? '' : 'active'; ?>">Todos</a>
              <?php foreach (self::$statuses as $skey => $sinfo) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=eeppj-pqrrs&status=' . $skey . ($filter_tipo ? '&tipo=' . $filter_tipo : ''))); ?>"
                   class="<?php echo $filter_status === $skey ? 'active' : ''; ?>"
                   style="--badge-color: <?php echo esc_attr($sinfo['color']); ?>;">
                  <?php echo esc_html($sinfo['label']); ?>
                </a>
              <?php endforeach; ?>
            </div>
            <div class="eeppj-filter">
              <strong style="font-size: 0.75rem; color: #6b7280; margin-right: 0.25rem;">Tipo:</strong>
              <a href="<?php echo esc_url(admin_url('admin.php?page=eeppj-pqrrs' . ($filter_status ? '&status=' . $filter_status : ''))); ?>" class="<?php echo $filter_tipo ? '' : 'active'; ?>">Todos</a>
              <?php foreach ($tipos as $t) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=eeppj-pqrrs&tipo=' . $t . ($filter_status ? '&status=' . $filter_status : ''))); ?>"
                   class="<?php echo $filter_tipo === $t ? 'active' : ''; ?>"
                   style="--badge-color: <?php echo esc_attr($tipo_colors[$t]); ?>;">
                  <?php echo esc_html($tipo_labels[$t]); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Submissions Table -->
          <table class="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <th style="width:84px;">Radicado</th>
                <th style="width:76px;">Tipo</th>
                <th style="width:140px;">Estado</th>
                <th style="width:140px;">Nombre</th>
                <th>Asunto</th>
                <th style="width:130px;">Fecha</th>
                <th style="width:130px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($submissions)) : ?>
                <tr><td colspan="7" style="text-align:center;color:#666;">No hay solicitudes.</td></tr>
              <?php endif; ?>
              <?php foreach ($submissions as $s) :
                  $st = self::$statuses[$s->status] ?? self::$statuses['pendiente'];
                  $allowed = self::$transitions[$s->status] ?? [];
              ?>
                <tr>
                  <td><code><?php echo esc_html($s->submission_id); ?></code></td>
                  <td>
                    <span class="eeppj-badge" style="background: <?php echo esc_attr($tipo_colors[$s->tipo] ?? '#666'); ?>;">
                      <?php echo esc_html(ucfirst($s->tipo)); ?>
                    </span>
                  </td>
                  <td>
                    <?php if (!empty($allowed)) : ?>
                      <select class="eeppj-status-select" data-id="<?php echo esc_attr($s->id); ?>" style="border-color: <?php echo esc_attr($st['color']); ?>; color: <?php echo esc_attr($st['color']); ?>;">
                        <option value="<?php echo esc_attr($s->status); ?>" selected><?php echo esc_html($st['icon'] . ' ' . $st['label']); ?></option>
                        <?php foreach ($allowed as $next) :
                            $ninfo = self::$statuses[$next];
                        ?>
                          <option value="<?php echo esc_attr($next); ?>"><?php echo esc_html($ninfo['icon'] . ' ' . $ninfo['label']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php else : ?>
                      <span class="eeppj-status-badge" style="color:<?php echo esc_attr($st['color']); ?>;background:<?php echo esc_attr($st['bg']); ?>;">
                        <?php echo $st['icon'] . ' ' . esc_html($st['label']); ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo esc_html($s->nombre); ?></td>
                  <td><?php echo esc_html(wp_trim_words($s->asunto, 8)); ?></td>
                  <td><?php echo esc_html(wp_date('d/m/Y H:i', strtotime($s->created_at))); ?></td>
                  <td>
                    <button type="button" class="button button-small eeppj-view-btn"
                      data-id="<?php echo esc_attr($s->id); ?>"
                      data-sid="<?php echo esc_attr($s->submission_id); ?>"
                      data-nombre="<?php echo esc_attr($s->nombre); ?>"
                      data-cedula="<?php echo esc_attr($s->cedula); ?>"
                      data-email="<?php echo esc_attr($s->email); ?>"
                      data-telefono="<?php echo esc_attr($s->telefono); ?>"
                      data-tipo="<?php echo esc_attr($s->tipo); ?>"
                      data-status="<?php echo esc_attr($s->status); ?>"
                      data-asunto="<?php echo esc_attr($s->asunto); ?>"
                      data-mensaje="<?php echo esc_attr($s->mensaje); ?>"
                      data-notes="<?php echo esc_attr($s->admin_notes ?? ''); ?>"
                      data-archivo="<?php echo $s->archivo_id ? esc_url(wp_get_attachment_url($s->archivo_id)) : ''; ?>"
                      data-fecha="<?php echo esc_attr(wp_date('d/m/Y H:i', strtotime($s->created_at))); ?>"
                      data-updated="<?php echo $s->updated_at ? esc_attr(wp_date('d/m/Y H:i', strtotime($s->updated_at))) : ''; ?>">
                      Ver
                    </button>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=eeppj_pqrrs_delete&id=' . $s->id), 'eeppj_pqrrs_delete_' . $s->id)); ?>"
                       class="button button-small button-link-delete"
                       onclick="return confirm('¿Eliminar esta solicitud permanentemente?');">
                      Eliminar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if ($total_pages > 1) : ?>
            <div class="tablenav bottom">
              <div class="tablenav-pages">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                  <?php if ($i === $page) : ?>
                    <span class="tablenav-pages-navspan button disabled"><?php echo $i; ?></span>
                  <?php else : ?>
                    <a class="button" href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo $i; ?></a>
                  <?php endif; ?>
                <?php endfor; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Detail Modal -->
        <div id="eeppj-modal" style="display:none; position:fixed; inset:0; z-index:100000; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
          <div style="background:#fff; border-radius:12px; max-width:640px; width:92%; max-height:85vh; overflow-y:auto; padding:2rem; position:relative;">
            <button id="eeppj-modal-close" style="position:absolute;top:0.75rem;right:0.75rem;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;">&times;</button>
            <h2 style="margin-top:0;margin-bottom:1rem;">Solicitud <code id="modal-sid"></code></h2>

            <div id="modal-status-bar" style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;">
              <span id="modal-status-text" style="font-weight:600;font-size:0.875rem;"></span>
              <span id="modal-updated" style="font-size:0.75rem;color:#6b7280;margin-left:auto;"></span>
            </div>

            <table class="form-table" id="modal-table" style="margin-top:0;">
              <tr><th style="width:100px;">Tipo</th><td id="modal-tipo"></td></tr>
              <tr><th>Nombre</th><td id="modal-nombre"></td></tr>
              <tr><th>Cédula</th><td id="modal-cedula"></td></tr>
              <tr><th>Email</th><td id="modal-email"></td></tr>
              <tr><th>Teléfono</th><td id="modal-telefono"></td></tr>
              <tr><th>Asunto</th><td id="modal-asunto"></td></tr>
              <tr><th>Mensaje</th><td id="modal-mensaje" style="white-space:pre-wrap;"></td></tr>
              <tr id="modal-archivo-row"><th>Archivo</th><td id="modal-archivo"></td></tr>
              <tr><th>Creada</th><td id="modal-fecha"></td></tr>
            </table>

            <!-- Admin Notes -->
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e5e7eb;">
              <label for="modal-notes" style="display:block;font-weight:600;font-size:0.875rem;color:#374151;margin-bottom:0.5rem;">Notas internas</label>
              <textarea id="modal-notes" rows="3" style="width:100%;padding:0.5rem 0.75rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.875rem;font-family:inherit;resize:vertical;" placeholder="Notas visibles solo para administradores..."></textarea>
              <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.5rem;">
                <button id="modal-save-notes" class="button button-primary button-small">Guardar notas</button>
                <span id="modal-notes-status" style="font-size:0.75rem;color:#16a34a;display:none;">Guardado</span>
              </div>
            </div>
          </div>
        </div>

        <script>
        (function(){
          var modal = document.getElementById('eeppj-modal');
          var fields = ['sid','tipo','nombre','cedula','email','telefono','asunto','mensaje','fecha'];
          var statusInfo = <?php echo wp_json_encode(self::$statuses); ?>;
          var currentModalId = null;
          var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
          var statusNonce = '<?php echo esc_attr($nonce); ?>';
          var notesNonce = '<?php echo esc_attr($notes_nonce); ?>';

          // View button -> open modal
          document.querySelectorAll('.eeppj-view-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
              currentModalId = btn.dataset.id;
              fields.forEach(function(f){ document.getElementById('modal-'+f).textContent = btn.dataset[f] || '-'; });

              // Status bar
              var st = statusInfo[btn.dataset.status] || statusInfo['pendiente'];
              var bar = document.getElementById('modal-status-bar');
              bar.style.background = st.bg;
              bar.style.border = '1px solid ' + st.color + '33';
              document.getElementById('modal-status-text').innerHTML = st.icon + ' ' + st.label;
              document.getElementById('modal-status-text').style.color = st.color;

              var updated = btn.dataset.updated;
              document.getElementById('modal-updated').textContent = updated ? 'Actualizado: ' + updated : '';

              // Archivo
              var archivoRow = document.getElementById('modal-archivo-row');
              var archivoTd = document.getElementById('modal-archivo');
              if(btn.dataset.archivo){
                archivoRow.style.display = '';
                archivoTd.innerHTML = '<a href="'+btn.dataset.archivo+'" target="_blank" class="button button-small">Descargar</a>';
              } else {
                archivoRow.style.display = 'none';
              }

              // Notes
              document.getElementById('modal-notes').value = btn.dataset.notes || '';
              document.getElementById('modal-notes-status').style.display = 'none';

              modal.style.display = 'flex';
            });
          });

          // Close modal
          document.getElementById('eeppj-modal-close').addEventListener('click', function(){ modal.style.display='none'; });
          modal.addEventListener('click', function(e){ if(e.target===modal) modal.style.display='none'; });

          // Status change via select dropdown in table
          document.querySelectorAll('.eeppj-status-select').forEach(function(sel){
            sel.addEventListener('change', function(){
              var id = sel.dataset.id;
              var newStatus = sel.value;
              sel.disabled = true;

              var xhr = new XMLHttpRequest();
              xhr.open('POST', ajaxUrl);
              xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
              xhr.onload = function(){
                try {
                  var res = JSON.parse(xhr.responseText);
                  if(res.success){ location.reload(); }
                  else { alert(res.data ? res.data.message : 'Error'); sel.disabled = false; }
                } catch(e){ alert('Error'); sel.disabled = false; }
              };
              xhr.send('action=eeppj_pqrrs_status&id='+id+'&status='+encodeURIComponent(newStatus)+'&_wpnonce='+statusNonce);
            });
          });

          // Save admin notes
          document.getElementById('modal-save-notes').addEventListener('click', function(){
            if(!currentModalId) return;
            var notes = document.getElementById('modal-notes').value;
            var statusEl = document.getElementById('modal-notes-status');
            var btn = this;
            btn.disabled = true;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxUrl);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function(){
              btn.disabled = false;
              try {
                var res = JSON.parse(xhr.responseText);
                if(res.success){
                  statusEl.textContent = 'Guardado';
                  statusEl.style.color = '#16a34a';
                  statusEl.style.display = 'inline';
                  // Update the data attribute on the view button
                  var viewBtn = document.querySelector('.eeppj-view-btn[data-id="'+currentModalId+'"]');
                  if(viewBtn) viewBtn.dataset.notes = notes;
                  setTimeout(function(){ statusEl.style.display='none'; }, 2000);
                } else {
                  statusEl.textContent = 'Error al guardar';
                  statusEl.style.color = '#dc2626';
                  statusEl.style.display = 'inline';
                }
              } catch(e){ alert('Error'); }
            };
            xhr.send('action=eeppj_pqrrs_notes&id='+currentModalId+'&notes='+encodeURIComponent(notes)+'&_wpnonce='+notesNonce);
          });
        })();
        </script>
        <?php
    }

    public static function handle_status_change() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'] ?? '', 'eeppj_pqrrs_status')) {
            wp_send_json_error(['message' => 'No autorizado.'], 403);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $new_status = sanitize_text_field($_POST['status'] ?? '');

        if (!$id || !isset(self::$statuses[$new_status])) {
            wp_send_json_error(['message' => 'Parámetros inválidos.'], 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        // Verify transition is allowed
        $current_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id = %d", $id));
        if (!$current_status) {
            wp_send_json_error(['message' => 'Solicitud no encontrada.'], 404);
        }

        $allowed = self::$transitions[$current_status] ?? [];
        if (!in_array($new_status, $allowed, true)) {
            wp_send_json_error(['message' => 'Transición no permitida de "' . $current_status . '" a "' . $new_status . '".'], 400);
        }

        $wpdb->update(
            $table,
            ['status' => $new_status, 'updated_at' => current_time('mysql')],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        wp_send_json_success(['status' => $new_status]);
    }

    public static function handle_save_notes() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'] ?? '', 'eeppj_pqrrs_notes')) {
            wp_send_json_error(['message' => 'No autorizado.'], 403);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$id) {
            wp_send_json_error(['message' => 'ID inválido.'], 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        $wpdb->update(
            $table,
            ['admin_notes' => $notes, 'updated_at' => current_time('mysql')],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        wp_send_json_success();
    }

    public static function handle_delete() {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id || !current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'eeppj_pqrrs_delete_' . $id)) {
            wp_die('Acción no autorizada.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eeppj_pqrrs';

        $archivo_id = $wpdb->get_var($wpdb->prepare("SELECT archivo_id FROM $table WHERE id = %d", $id));
        if ($archivo_id) {
            wp_delete_attachment($archivo_id, true);
        }

        $wpdb->delete($table, ['id' => $id], ['%d']);
        wp_redirect(admin_url('admin.php?page=eeppj-pqrrs'));
        exit;
    }
}
