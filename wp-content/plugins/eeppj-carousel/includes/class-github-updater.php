<?php
/**
 * GitHub-based auto-updater for WordPress themes and plugins.
 *
 * Checks the GitHub Releases API for new versions and integrates with
 * WordPress's built-in update mechanism. Works for both plugins and themes.
 *
 * @package eeppj
 * @since 1.0.0
 */

// @codeCoverageIgnoreStart
if (!defined('ABSPATH')) {
    exit;
}
// @codeCoverageIgnoreEnd

class EEPPJ_Carousel_GitHub_Updater {

    /** @var string Plugin slug (e.g. 'eeppj-pqrrs/eeppj-pqrrs.php') or theme slug (e.g. 'eeppj'). */
    private $slug;

    /** @var string GitHub repository in 'owner/repo' format. */
    private $github_repo;

    /** @var string Current installed version. */
    private $current_version;

    /** @var string Component type: 'plugin' or 'theme'. */
    private $type;

    /** @var string Filename of the ZIP asset attached to the GitHub release. */
    private $asset_name;

    /** @var string Transient key for caching the API response. */
    private $transient_key;

    /** @var int Cache duration in seconds (6 hours). */
    private $cache_duration = 21600;

    /**
     * @param string $slug            Plugin file (relative to plugins/) or theme directory name.
     * @param string $github_repo     GitHub repo in 'owner/repo' format.
     * @param string $current_version Installed version string.
     * @param string $type            'plugin' or 'theme'.
     * @param string $asset_name      Filename of the release asset ZIP.
     */
    public function __construct($slug, $github_repo, $current_version, $type, $asset_name) {
        $this->slug            = $slug;
        $this->github_repo     = $github_repo;
        $this->current_version = $current_version;
        $this->type            = $type;
        $this->asset_name      = $asset_name;
        $this->transient_key   = 'eeppj_gh_update_' . md5($slug);

        $this->register_hooks();
    }

    /**
     * Register the appropriate WordPress filter hooks.
     *
     * @return void
     */
    private function register_hooks() {
        if ($this->type === 'plugin') {
            add_filter('pre_set_site_transient_update_plugins', array($this, 'check_plugin_update'));
            add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        } elseif ($this->type === 'theme') {
            add_filter('pre_set_site_transient_update_themes', array($this, 'check_theme_update'));
        }
    }

    /**
     * Check for plugin updates and inject into the update transient.
     *
     * @param object $transient The update_plugins transient data.
     * @return object Modified transient data.
     */
    public function check_plugin_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();
        if (!$release) {
            return $transient;
        }

        $remote_version = $this->parse_version($release);
        if (!$remote_version) {
            return $transient;
        }

        $download_url = $this->get_asset_url($release);
        if (!$download_url) {
            return $transient;
        }

        if (version_compare($remote_version, $this->current_version, '>')) {
            $transient->response[$this->slug] = (object) array(
                'slug'        => dirname($this->slug),
                'plugin'      => $this->slug,
                'new_version' => $remote_version,
                'url'         => 'https://github.com/' . $this->github_repo,
                'package'     => $download_url,
                'tested'      => '6.8',
                'requires'    => '5.6',
            );
        }

        return $transient;
    }

    /**
     * Provide plugin information for the WordPress plugin details modal.
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string             $action The API action being performed.
     * @param object             $args   Plugin API arguments.
     * @return false|object
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        $plugin_slug = dirname($this->slug);
        if (!isset($args->slug) || $args->slug !== $plugin_slug) {
            return $result;
        }

        $release = $this->get_latest_release();
        if (!$release) {
            return $result;
        }

        $remote_version = $this->parse_version($release);
        $download_url   = $this->get_asset_url($release);

        if (!$remote_version || !$download_url) {
            return $result;
        }

        $info = (object) array(
            'name'            => isset($release['name']) ? $release['name'] : $plugin_slug,
            'slug'            => $plugin_slug,
            'version'         => $remote_version,
            'author'          => '<a href="https://github.com/' . esc_attr($this->github_repo) . '">EEPPJ</a>',
            'homepage'        => 'https://github.com/' . $this->github_repo,
            'download_link'   => $download_url,
            'requires'        => '5.6',
            'tested'          => '6.8',
            'requires_php'    => '7.4',
            'sections'        => array(
                'description'  => isset($release['body']) ? nl2br(esc_html($release['body'])) : '',
                'changelog'    => isset($release['body']) ? nl2br(esc_html($release['body'])) : '',
            ),
            'last_updated'    => isset($release['published_at']) ? $release['published_at'] : '',
        );

        return $info;
    }

    /**
     * Check for theme updates and inject into the update transient.
     *
     * @param object $transient The update_themes transient data.
     * @return object Modified transient data.
     */
    public function check_theme_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();
        if (!$release) {
            return $transient;
        }

        $remote_version = $this->parse_version($release);
        if (!$remote_version) {
            return $transient;
        }

        $download_url = $this->get_asset_url($release);
        if (!$download_url) {
            return $transient;
        }

        if (version_compare($remote_version, $this->current_version, '>')) {
            $transient->response[$this->slug] = array(
                'theme'       => $this->slug,
                'new_version' => $remote_version,
                'url'         => 'https://github.com/' . $this->github_repo,
                'package'     => $download_url,
                'requires'    => '5.6',
            );
        }

        return $transient;
    }

    /**
     * Fetch the latest release data from GitHub, with transient caching.
     *
     * @return array|null Release data array, or null on failure.
     */
    private function get_latest_release() {
        $cached = get_transient($this->transient_key);
        if ($cached !== false) {
            return $cached;
        }

        // Scan recent releases to find the latest one containing our asset.
        // Each component releases independently with its own tag prefix.
        $url = sprintf(
            'https://api.github.com/repos/%s/releases?per_page=10',
            $this->github_repo
        );

        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
            ),
        ));

        if (is_wp_error($response)) {
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $releases = json_decode($body, true);

        if (!is_array($releases)) {
            return null;
        }

        // Find the release with the highest version that has our asset.
        $best_release = null;
        $best_version = '0.0.0';

        foreach ($releases as $release) {
            if (empty($release['tag_name']) || empty($release['assets'])) {
                continue;
            }
            $has_asset = false;
            foreach ($release['assets'] as $asset) {
                if (isset($asset['name']) && $asset['name'] === $this->asset_name) {
                    $has_asset = true;
                    break;
                }
            }
            if (!$has_asset) {
                continue;
            }
            $ver = $this->parse_version($release);
            if ($ver && version_compare($ver, $best_version, '>')) {
                $best_version = $ver;
                $best_release = $release;
            }
        }

        if ($best_release) {
            set_transient($this->transient_key, $best_release, $this->cache_duration);
        }

        return $best_release;
    }

    /**
     * Parse a semantic version from the release tag name.
     *
     * Strips a leading 'v' if present (e.g. 'v1.3.0' → '1.3.0').
     *
     * @param array $release Release data from GitHub API.
     * @return string|null Version string, or null if not parseable.
     */
    private function parse_version($release) {
        if (empty($release['tag_name'])) {
            return null;
        }

        $version = $release['tag_name'];

        // Strip component prefix (e.g. 'carousel/v1.4.0' → 'v1.4.0').
        $slash = strrpos($version, '/');
        if ($slash !== false) {
            $version = substr($version, $slash + 1);
        }

        // Strip leading 'v' or 'V'.
        if (strpos($version, 'v') === 0 || strpos($version, 'V') === 0) {
            $version = substr($version, 1);
        }

        // Basic sanity check — must look like a version number.
        if (!preg_match('/^\d+\.\d+/', $version)) {
            return null;
        }

        return $version;
    }

    /**
     * Find the download URL for the matching asset ZIP in the release.
     *
     * @param array $release Release data from GitHub API.
     * @return string|null Browser download URL, or null if asset not found.
     */
    private function get_asset_url($release) {
        if (empty($release['assets']) || !is_array($release['assets'])) {
            return null;
        }

        foreach ($release['assets'] as $asset) {
            if (isset($asset['name']) && $asset['name'] === $this->asset_name) {
                $url = isset($asset['browser_download_url']) ? $asset['browser_download_url'] : null;
                if ($url && strpos($url, 'https://github.com/') !== 0) {
                    return null;
                }
                return $url;
            }
        }

        return null;
    }
}
