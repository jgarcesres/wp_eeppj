<?php
/**
 * EEPPJ Update Manager — admin page for force-checking and applying
 * updates to the EEPPJ theme and plugins from GitHub Releases.
 *
 * @package eeppj
 */

if (!defined('ABSPATH')) {
    exit;
}

class EEPPJ_Updater_Admin {

    /** @var array Component definitions. */
    private $components = array();

    public function __construct() {
        $this->components = array(
            array(
                'name'       => 'EEPPJ Theme',
                'slug'       => 'eeppj',
                'type'       => 'theme',
                'asset'      => 'eeppj-theme.zip',
                'file'       => null,
                'version_cb' => array($this, 'get_theme_version'),
            ),
            array(
                'name'       => 'EEPPJ PQRRS',
                'slug'       => 'eeppj-pqrrs/eeppj-pqrrs.php',
                'type'       => 'plugin',
                'asset'      => 'eeppj-pqrrs.zip',
                'file'       => 'eeppj-pqrrs/eeppj-pqrrs.php',
                'version_cb' => array($this, 'get_plugin_version'),
            ),
            array(
                'name'       => 'EEPPJ Carousel',
                'slug'       => 'eeppj-carousel/eeppj-carousel.php',
                'type'       => 'plugin',
                'asset'      => 'eeppj-carousel.zip',
                'file'       => 'eeppj-carousel/eeppj-carousel.php',
                'version_cb' => array($this, 'get_plugin_version'),
            ),
        );

        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('wp_ajax_eeppj_check_updates', array($this, 'ajax_check_updates'));
        add_action('wp_ajax_eeppj_apply_update', array($this, 'ajax_apply_update'));
    }

    public function add_menu_page() {
        add_management_page(
            'EEPPJ Updates',
            'EEPPJ Updates',
            'update_plugins',
            'eeppj-updates',
            array($this, 'render_page')
        );
    }

    public function get_theme_version($component) {
        $theme = wp_get_theme('eeppj');
        return $theme->exists() ? $theme->get('Version') : null;
    }

    public function get_plugin_version($component) {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $file = WP_PLUGIN_DIR . '/' . $component['file'];
        if (!file_exists($file)) {
            return null;
        }
        $data = get_plugin_data($file, false, false);
        return !empty($data['Version']) ? $data['Version'] : null;
    }

    private function get_latest_release_info($component) {
        $transient_key = 'eeppj_gh_update_' . md5($component['slug']);
        $cached = get_transient($transient_key);

        if ($cached !== false) {
            return $cached;
        }

        $url = 'https://api.github.com/repos/jgarcesres/wp_eeppj/releases/latest';
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version'),
            ),
        ));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data) || empty($data['tag_name'])) {
            return null;
        }

        set_transient($transient_key, $data, 21600);
        return $data;
    }

    private function parse_version($release) {
        if (empty($release['tag_name'])) {
            return null;
        }
        $v = $release['tag_name'];
        if (strpos($v, 'v') === 0 || strpos($v, 'V') === 0) {
            $v = substr($v, 1);
        }
        return preg_match('/^\d+\.\d+/', $v) ? $v : null;
    }

    private function get_asset_url($release, $asset_name) {
        if (empty($release['assets']) || !is_array($release['assets'])) {
            return null;
        }
        foreach ($release['assets'] as $asset) {
            if (isset($asset['name']) && $asset['name'] === $asset_name) {
                return isset($asset['browser_download_url']) ? $asset['browser_download_url'] : null;
            }
        }
        return null;
    }

    public function ajax_check_updates() {
        check_ajax_referer('eeppj_updates_nonce', 'nonce');

        if (!current_user_can('update_plugins')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes.'));
        }

        // Clear all EEPPJ transients to force fresh check
        foreach ($this->components as $c) {
            delete_transient('eeppj_gh_update_' . md5($c['slug']));
        }

        // Fetch fresh release data
        $release = $this->get_latest_release_info($this->components[0]);
        $remote_version = $release ? $this->parse_version($release) : null;

        $results = array();
        foreach ($this->components as $c) {
            $current = call_user_func($c['version_cb'], $c);
            $has_asset = $release ? ($this->get_asset_url($release, $c['asset']) !== null) : false;
            $update_available = ($current && $remote_version && $has_asset)
                ? version_compare($remote_version, $current, '>')
                : false;

            $results[] = array(
                'name'             => $c['name'],
                'slug'             => $c['slug'],
                'type'             => $c['type'],
                'installed'        => $current ? $current : 'Not installed',
                'remote'           => $remote_version ? $remote_version : 'Unknown',
                'update_available' => $update_available,
                'has_asset'        => $has_asset,
            );
        }

        wp_send_json_success(array(
            'components'     => $results,
            'release_url'    => $release ? $release['html_url'] : null,
            'release_name'   => $release ? ($release['name'] ? $release['name'] : $release['tag_name']) : null,
            'published_at'   => $release ? $release['published_at'] : null,
        ));
    }

    public function ajax_apply_update() {
        check_ajax_referer('eeppj_updates_nonce', 'nonce');

        if (!current_user_can('update_plugins')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes.'));
        }

        $slug = isset($_POST['component_slug']) ? sanitize_text_field($_POST['component_slug']) : '';
        $type = isset($_POST['component_type']) ? sanitize_text_field($_POST['component_type']) : '';

        $component = null;
        foreach ($this->components as $c) {
            if ($c['slug'] === $slug && $c['type'] === $type) {
                $component = $c;
                break;
            }
        }

        if (!$component) {
            wp_send_json_error(array('message' => 'Componente no encontrado.'));
        }

        // Clear transient to get fresh data
        delete_transient('eeppj_gh_update_' . md5($slug));

        $release = $this->get_latest_release_info($component);
        if (!$release) {
            wp_send_json_error(array('message' => 'No se pudo contactar GitHub.'));
        }

        $download_url = $this->get_asset_url($release, $component['asset']);
        if (!$download_url) {
            wp_send_json_error(array('message' => 'Asset ZIP no encontrado en el release.'));
        }

        $remote_version = $this->parse_version($release);

        // Inject update info into WP transient so the upgrader can find it
        if ($type === 'plugin') {
            $transient = get_site_transient('update_plugins');
            if (!is_object($transient)) {
                $transient = new stdClass();
            }
            $transient->response[$slug] = (object) array(
                'slug'        => dirname($slug),
                'plugin'      => $slug,
                'new_version' => $remote_version,
                'package'     => $download_url,
            );
            set_site_transient('update_plugins', $transient);
        } else {
            $transient = get_site_transient('update_themes');
            if (!is_object($transient)) {
                $transient = new stdClass();
            }
            $transient->response[$slug] = array(
                'theme'       => $slug,
                'new_version' => $remote_version,
                'package'     => $download_url,
            );
            set_site_transient('update_themes', $transient);
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

        $skin = new WP_Ajax_Upgrader_Skin();

        if ($type === 'plugin') {
            $upgrader = new Plugin_Upgrader($skin);
            $result = $upgrader->upgrade($slug, array('clear_update_cache' => true));
        } else {
            $upgrader = new Theme_Upgrader($skin);
            $result = $upgrader->upgrade($slug, array('clear_update_cache' => true));
        }

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        if ($result === false) {
            $errors = $skin->get_errors();
            $msg = is_wp_error($errors) ? $errors->get_error_message() : 'Error desconocido durante la actualización.';
            wp_send_json_error(array('message' => $msg));
        }

        // Clear transients again after update
        delete_transient('eeppj_gh_update_' . md5($slug));
        delete_site_transient('update_plugins');
        delete_site_transient('update_themes');

        $new_version = call_user_func($component['version_cb'], $component);

        wp_send_json_success(array(
            'message'     => $component['name'] . ' actualizado correctamente.',
            'new_version' => $new_version,
        ));
    }

    public function render_page() {
        $nonce = wp_create_nonce('eeppj_updates_nonce');
        ?>
        <div class="wrap">
            <h1>EEPPJ — Actualización de componentes</h1>
            <p class="description">Revisa y actualiza el tema y los plugins de EEPPJ desde GitHub Releases.</p>

            <div id="eeppj-updates-container" style="margin-top: 20px;">
                <button type="button" id="eeppj-check-btn" class="button button-primary" style="margin-bottom: 16px;">
                    Verificar actualizaciones
                </button>
                <span id="eeppj-check-spinner" class="spinner" style="float: none; margin-top: 0;"></span>

                <div id="eeppj-release-info" style="display:none; margin-bottom: 16px; padding: 10px 14px; background: #f0f0f1; border-left: 4px solid #2271b1; border-radius: 2px;">
                </div>

                <table class="widefat striped" id="eeppj-components-table" style="display: none;">
                    <thead>
                        <tr>
                            <th>Componente</th>
                            <th>Tipo</th>
                            <th>Versión instalada</th>
                            <th>Versión disponible</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="eeppj-components-body">
                    </tbody>
                </table>

                <div id="eeppj-update-log" style="display:none; margin-top: 16px; padding: 12px 16px; border-radius: 4px;"></div>
            </div>
        </div>

        <script>
        (function($) {
            var nonce = <?php echo wp_json_encode($nonce); ?>;
            var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

            function showLog(message, type) {
                var $log = $('#eeppj-update-log');
                var colors = {
                    success: { bg: '#f0fdf4', border: '#16a34a', color: '#166534' },
                    error:   { bg: '#fef2f2', border: '#dc2626', color: '#991b1b' },
                    info:    { bg: '#eff6ff', border: '#2563eb', color: '#1e40af' }
                };
                var c = colors[type] || colors.info;
                $log.html(message)
                    .css({ display: 'block', background: c.bg, borderLeft: '4px solid ' + c.border, color: c.color })
                    .hide().fadeIn(200);
            }

            $('#eeppj-check-btn').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true);
                $('#eeppj-check-spinner').addClass('is-active');
                $('#eeppj-update-log').hide();

                $.post(ajaxUrl, {
                    action: 'eeppj_check_updates',
                    nonce: nonce
                }, function(res) {
                    $btn.prop('disabled', false);
                    $('#eeppj-check-spinner').removeClass('is-active');

                    if (!res.success) {
                        showLog(res.data.message || 'Error al verificar.', 'error');
                        return;
                    }

                    var d = res.data;

                    // Release info
                    if (d.release_name) {
                        var date = d.published_at ? new Date(d.published_at).toLocaleDateString('es-CO') : '';
                        $('#eeppj-release-info').html(
                            '<strong>Último release:</strong> ' + $('<span>').text(d.release_name).html() +
                            (date ? ' &mdash; ' + date : '') +
                            (d.release_url ? ' &mdash; <a href="' + d.release_url + '" target="_blank">Ver en GitHub ↗</a>' : '')
                        ).show();
                    }

                    // Components table
                    var $tbody = $('#eeppj-components-body').empty();
                    $.each(d.components, function(i, c) {
                        var statusHtml, actionHtml;
                        if (c.installed === 'Not installed') {
                            statusHtml = '<span style="color:#9ca3af;">No instalado</span>';
                            actionHtml = '—';
                        } else if (c.update_available) {
                            statusHtml = '<span style="color:#d97706; font-weight:600;">Actualización disponible</span>';
                            actionHtml = '<button type="button" class="button button-primary eeppj-update-btn" ' +
                                'data-slug="' + c.slug + '" data-type="' + c.type + '" data-name="' + c.name + '">' +
                                'Actualizar</button>';
                        } else {
                            statusHtml = '<span style="color:#16a34a;">Al día ✓</span>';
                            actionHtml = '—';
                        }

                        $tbody.append(
                            '<tr>' +
                            '<td><strong>' + $('<span>').text(c.name).html() + '</strong></td>' +
                            '<td>' + (c.type === 'theme' ? 'Tema' : 'Plugin') + '</td>' +
                            '<td><code>' + c.installed + '</code></td>' +
                            '<td><code>' + c.remote + '</code></td>' +
                            '<td>' + statusHtml + '</td>' +
                            '<td>' + actionHtml + '</td>' +
                            '</tr>'
                        );
                    });
                    $('#eeppj-components-table').show();

                }).fail(function() {
                    $btn.prop('disabled', false);
                    $('#eeppj-check-spinner').removeClass('is-active');
                    showLog('Error de conexión al verificar actualizaciones.', 'error');
                });
            });

            $(document).on('click', '.eeppj-update-btn', function() {
                var $btn = $(this);
                var slug = $btn.data('slug');
                var type = $btn.data('type');
                var name = $btn.data('name');

                $btn.prop('disabled', true).text('Actualizando...');
                showLog('Actualizando ' + name + '...', 'info');

                $.post(ajaxUrl, {
                    action: 'eeppj_apply_update',
                    nonce: nonce,
                    component_slug: slug,
                    component_type: type
                }, function(res) {
                    if (res.success) {
                        showLog(res.data.message + (res.data.new_version ? ' (v' + res.data.new_version + ')' : ''), 'success');
                        $btn.closest('tr').find('td:eq(2) code').text(res.data.new_version || '?');
                        $btn.closest('tr').find('td:eq(4)').html('<span style="color:#16a34a;">Al día ✓</span>');
                        $btn.closest('td').html('—');
                    } else {
                        showLog('Error: ' + (res.data.message || 'Error desconocido.'), 'error');
                        $btn.prop('disabled', false).text('Reintentar');
                    }
                }).fail(function() {
                    showLog('Error de conexión durante la actualización.', 'error');
                    $btn.prop('disabled', false).text('Reintentar');
                });
            });

            // Auto-check on page load
            $('#eeppj-check-btn').trigger('click');

        })(jQuery);
        </script>
        <?php
    }
}
