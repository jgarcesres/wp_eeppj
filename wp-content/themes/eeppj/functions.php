<?php
/**
 * EEPPJ Theme Functions
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

/* ====== Theme Setup ====== */
function eeppj_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 56,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    register_nav_menus([
        'primary' => __('Navegación Principal', 'eeppj'),
        'footer'  => __('Mapa del Sitio (Footer)', 'eeppj'),
    ]);

    set_post_thumbnail_size(800, 400, true);

    // Editor styles — makes Gutenberg match the front-end
    add_editor_style([
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap',
        'assets/css/editor-style.css',
    ]);

}
add_action('after_setup_theme', 'eeppj_setup');

/* ====== Block Patterns ====== */
function eeppj_register_block_patterns() {
    register_block_pattern_category('eeppj', [
        'label' => __('EEPPJ', 'eeppj'),
    ]);

    $patterns_dir = get_template_directory() . '/patterns';
    if (!is_dir($patterns_dir)) return;

    foreach (glob($patterns_dir . '/*.php') as $file) {
        $headers = get_file_data($file, [
            'title'       => 'Title',
            'slug'        => 'Slug',
            'categories'  => 'Categories',
            'description' => 'Description',
            'keywords'    => 'Keywords',
        ]);

        if (empty($headers['title']) || empty($headers['slug'])) continue;

        ob_start();
        include $file;
        $content = ob_get_clean();

        // Strip the PHP header comment block from the content
        $content = preg_replace('/^<\?php\s*\/\*\*.*?\*\/\s*\?>\s*/s', '', $content);

        $args = [
            'title'       => $headers['title'],
            'content'     => $content,
            'categories'  => array_map('trim', explode(',', $headers['categories'])),
        ];
        if (!empty($headers['description'])) $args['description'] = $headers['description'];
        if (!empty($headers['keywords']))    $args['keywords'] = array_map('trim', explode(',', $headers['keywords']));

        register_block_pattern($headers['slug'], $args);
    }
}
add_action('init', 'eeppj_register_block_patterns');

/* ====== Auto-create navigation menus on theme switch ====== */
function eeppj_create_default_menus() {
    // Only run once
    if (get_option('eeppj_menus_created')) return;

    // Primary navigation (matches Astro Header.astro navItems)
    $primary_menu_id = wp_create_nav_menu('Navegación Principal');
    if (!is_wp_error($primary_menu_id)) {
        $items = [
            ['title' => 'Inicio', 'url' => home_url('/'), 'parent' => 0],
            ['title' => 'Institucional', 'url' => home_url('/institucional'), 'parent' => 0, 'children' => [
                ['title' => 'Nuestra Empresa', 'url' => home_url('/nuestraempresa')],
                ['title' => 'Historia', 'url' => home_url('/historia')],
                ['title' => 'Estaciones de Trabajo', 'url' => home_url('/estaciones-de-trabajo')],
            ]],
            ['title' => 'Servicios', 'url' => home_url('/servicios'), 'parent' => 0, 'children' => [
                ['title' => 'Acueducto', 'url' => home_url('/acueducto')],
                ['title' => 'Alcantarillado', 'url' => home_url('/alcantarillado')],
                ['title' => 'Aseo', 'url' => home_url('/aseo')],
                ['title' => 'Alumbrado Público', 'url' => home_url('/alumbrado-publico')],
            ]],
            ['title' => 'Trámites', 'url' => home_url('/tramites'), 'parent' => 0, 'children' => [
                ['title' => 'Guía de Trámites y Documentos', 'url' => home_url('/guia-de-documentos')],
                ['title' => 'PQRRS', 'url' => home_url('/pqrrs')],
            ]],
            ['title' => 'Transparencia', 'url' => home_url('/transparencia'), 'parent' => 0],
            ['title' => 'Control Interno', 'url' => home_url('/control-interno-2'), 'parent' => 0, 'children' => [
                ['title' => 'Trabaja con Nosotros', 'url' => home_url('/trabaja-con-nosotros')],
                ['title' => 'Facturación', 'url' => home_url('/facturacion')],
                ['title' => 'Contenidos Generales', 'url' => home_url('/contenidos-generales')],
                ['title' => 'MIPG', 'url' => home_url('/modelo-integrado-de-planeacion-y-gestion')],
            ]],
            ['title' => 'Contáctenos', 'url' => home_url('/contactenos'), 'parent' => 0],
            ['title' => 'Noticias', 'url' => home_url('/blog'), 'parent' => 0],
        ];

        foreach ($items as $item) {
            $parent_id = wp_update_nav_menu_item($primary_menu_id, 0, [
                'menu-item-title'  => $item['title'],
                'menu-item-url'    => $item['url'],
                'menu-item-status' => 'publish',
                'menu-item-type'   => 'custom',
            ]);
            if (!empty($item['children']) && !is_wp_error($parent_id)) {
                foreach ($item['children'] as $child) {
                    wp_update_nav_menu_item($primary_menu_id, 0, [
                        'menu-item-title'     => $child['title'],
                        'menu-item-url'       => $child['url'],
                        'menu-item-parent-id' => $parent_id,
                        'menu-item-status'    => 'publish',
                        'menu-item-type'      => 'custom',
                    ]);
                }
            }
        }

        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['primary'] = $primary_menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    // Footer navigation
    $footer_menu_id = wp_create_nav_menu('Mapa del Sitio');
    if (!is_wp_error($footer_menu_id)) {
        $footer_items = [
            ['title' => 'Institucional', 'url' => home_url('/nuestraempresa')],
            ['title' => 'Servicios', 'url' => home_url('/servicios')],
            ['title' => 'Transparencia', 'url' => home_url('/transparencia')],
            ['title' => 'Contáctenos', 'url' => home_url('/contactenos')],
            ['title' => 'Noticias', 'url' => home_url('/blog')],
            ['title' => 'PQRRS', 'url' => home_url('/pqrrs')],
        ];
        foreach ($footer_items as $item) {
            wp_update_nav_menu_item($footer_menu_id, 0, [
                'menu-item-title'  => $item['title'],
                'menu-item-url'    => $item['url'],
                'menu-item-status' => 'publish',
                'menu-item-type'   => 'custom',
            ]);
        }
        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['footer'] = $footer_menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    update_option('eeppj_menus_created', true);
}
add_action('after_switch_theme', 'eeppj_create_default_menus');

/* ====== Enqueue Styles & Scripts ====== */
function eeppj_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style(
        'eeppj-google-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Montserrat:wght@600;700;800;900&display=swap',
        [],
        null
    );

    // Main stylesheet
    wp_enqueue_style(
        'eeppj-main',
        get_template_directory_uri() . '/assets/css/main.css',
        ['eeppj-google-fonts'],
        wp_get_theme()->get('Version')
    );

    // Animations
    wp_enqueue_style(
        'eeppj-animations',
        get_template_directory_uri() . '/assets/css/animations.css',
        ['eeppj-main'],
        wp_get_theme()->get('Version')
    );

    // Header JS (mobile menu, search overlay)
    wp_enqueue_script(
        'eeppj-header',
        get_template_directory_uri() . '/assets/js/header.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );

    // Cookie consent
    wp_enqueue_script(
        'eeppj-cookie-consent',
        get_template_directory_uri() . '/assets/js/cookie-consent.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'eeppj_enqueue_assets');

/* ====== Security Headers ====== */
function eeppj_security_headers() {
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
add_action('send_headers', 'eeppj_security_headers');

/* ====== Custom Nav Walker for Dropdowns ====== */
class EEPPJ_Nav_Walker extends Walker_Nav_Menu {
    private $current_item_has_children = false;

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes) || in_array('current-menu-ancestor', $classes);

        if ($depth === 0) {
            $this->current_item_has_children = in_array('menu-item-has-children', $classes);
            $output .= '<div class="nav-item relative group">';

            $active_class = $is_active
                ? 'text-brand-green bg-brand-green-5'
                : 'text-gray-700 hover-text-brand-green hover-bg-gray-50';

            $output .= sprintf(
                '<a href="%s" class="nav-link px-3 py-2 text-sm font-medium rounded-md transition-colors %s">%s',
                esc_url($item->url),
                esc_attr($active_class),
                esc_html($item->title)
            );

            if ($this->current_item_has_children) {
                $output .= '<svg style="display:inline-block;width:0.75rem;height:0.75rem;margin-left:0.25rem;vertical-align:middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                $output .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>';
                $output .= '</svg>';
            }

            $output .= '</a>';
        } else {
            $output .= sprintf(
                '<a href="%s" class="nav-dropdown-link" style="display:block;padding:0.5rem 1rem;font-size:0.875rem;color:#374151;text-decoration:none;white-space:nowrap;">%s</a>',
                esc_url($item->url),
                esc_html($item->title)
            );
        }
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<div class="nav-dropdown absolute left-0 top-full pt-1 hidden group-hover-show">';
        $output .= '<div class="bg-white rounded-lg shadow-lg border border-gray-100 py-2 min-w-[200px]">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</div></div>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</div>';
        }
    }
}

/* ====== Custom Mobile Nav Walker ====== */
class EEPPJ_Mobile_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes) || in_array('current-menu-ancestor', $classes);

        if ($depth === 0) {
            $active_class = $is_active
                ? 'text-brand-green bg-brand-green-5'
                : 'text-gray-700 hover-bg-gray-50';
            $bg = $is_active ? 'background:rgba(140,192,75,0.05);color:var(--color-brand-green);' : 'color:#374151;';
            $output .= sprintf(
                '<a href="%s" style="display:block;padding:0.75rem 1rem;font-size:1rem;font-weight:500;border-radius:0.5rem;text-decoration:none;%s">%s</a>',
                esc_url($item->url),
                $bg,
                esc_html($item->title)
            );
        } else {
            $output .= sprintf(
                '<a href="%s" style="display:block;padding:0.5rem 1rem 0.5rem 2rem;font-size:0.875rem;color:#4b5563;text-decoration:none;">%s</a>',
                esc_url($item->url),
                esc_html($item->title)
            );
        }
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        // No wrapper for mobile — children render inline
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        // No wrapper
    }
}

/* ====== Excerpt length ====== */
function eeppj_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'eeppj_excerpt_length');

function eeppj_excerpt_more($more) {
    return '&hellip;';
}
add_filter('excerpt_more', 'eeppj_excerpt_more');

/* ====== Widget areas ====== */
function eeppj_widgets_init() {
    register_sidebar([
        'name'          => __('Footer Widget Area', 'eeppj'),
        'id'            => 'footer-1',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="text-white font-heading font-bold text-lg mb-4">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Destacados Homepage', 'eeppj'),
        'id'            => 'homepage-carousel',
        'description'   => __('Carrusel o contenido destacado en la página principal. Aparece entre las estadísticas y las noticias.', 'eeppj'),
        'before_widget' => '<div class="widget homepage-carousel-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ]);
}
add_action('widgets_init', 'eeppj_widgets_init');

/* ====== Add Open Graph meta tags ====== */
function eeppj_og_meta() {
    if (is_singular()) {
        global $post;
        $title = get_the_title() . ' | EEPPJ';
        $description = has_excerpt($post->ID)
            ? get_the_excerpt($post->ID)
            : wp_trim_words(strip_tags($post->post_content), 30);
        $url = get_permalink();
        $image = get_the_post_thumbnail_url($post->ID, 'large');

        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
        }
        echo '<meta property="og:locale" content="es_CO" />' . "\n";
    }
}
add_action('wp_head', 'eeppj_og_meta', 5);

/* ====== Custom title separator ====== */
function eeppj_document_title_separator($sep) {
    return '|';
}
add_filter('document_title_separator', 'eeppj_document_title_separator');

function eeppj_document_title_parts($title) {
    $title['tagline'] = '';
    if (!isset($title['site'])) {
        $title['site'] = 'EEPPJ';
    }
    return $title;
}
add_filter('document_title_parts', 'eeppj_document_title_parts');

/* ====== SVG Icon Helpers ====== */
function eeppj_quick_icon($icon) {
    $icons = [
        'clipboard' => '<svg style="width:1.25rem;height:1.25rem;color:var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
        'eye' => '<svg style="width:1.25rem;height:1.25rem;color:var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
        'chat' => '<svg style="width:1.25rem;height:1.25rem;color:var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
        'card' => '<svg style="width:1.25rem;height:1.25rem;color:var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
    ];
    return $icons[$icon] ?? '';
}

function eeppj_service_icon($icon) {
    $icons = [
        'water' => '<svg style="width:1.5rem;height:1.5rem;color:var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4 5-7 8.5-7 11.5a7 7 0 1014 0C19 11.5 16 8 12 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.5c2.5 0 4.5-2 4.5-4.5" opacity="0.5"/></svg>',
        'pipe' => '<svg style="width:1.5rem;height:1.5rem;color:var(--color-brand-blue);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5v-2a2 2 0 00-2-2h-11a2 2 0 00-2 2v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 9.5V6a2 2 0 012-2h7a2 2 0 012 2v3.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 13.5v4a2 2 0 002 2h11a2 2 0 002-2v-4"/><path stroke-linecap="round" d="M9 15.5v2M12 15.5v2M15 15.5v2" opacity="0.5"/></svg>',
        'leaf' => '<svg style="width:1.5rem;height:1.5rem;color:var(--color-brand-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4-2-7-5.5-7-10C5 6 9 3 12 3c3 0 7 3 7 8 0 4.5-3 8-7 10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21V11"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l3-3 3 3" opacity="0.5"/></svg>',
        'light' => '<svg style="width:1.5rem;height:1.5rem;color:#f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7a5 5 0 013 9.16V18a1 1 0 01-1 1h-4a1 1 0 01-1-1v-1.84A5 5 0 0112 7z"/></svg>',
    ];
    return $icons[$icon] ?? '';
}

function eeppj_doc_icon($type) {
    $icons = [
        'doc' => '<svg style="width:1.25rem;height:1.25rem;margin-top:0.125rem;color:var(--color-brand-red);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
        'external' => '<svg style="width:1.25rem;height:1.25rem;margin-top:0.125rem;color:var(--color-brand-blue);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>',
        'page' => '<svg style="width:1.25rem;height:1.25rem;margin-top:0.125rem;color:var(--color-brand-green);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>',
    ];
    return $icons[$type] ?? $icons['page'];
}

/* ====== GitHub Auto-Updater ====== */
require_once get_template_directory() . '/includes/class-github-updater.php';

/* ====== Admin Update Manager ====== */
if (is_admin()) {
    require_once get_template_directory() . '/includes/class-eeppj-updater-admin.php';
    new EEPPJ_Updater_Admin();
}
new EEPPJ_Theme_GitHub_Updater(
    'eeppj',
    'jgarcesres/wp_eeppj',
    wp_get_theme()->get('Version'),
    'theme',
    'eeppj-theme.zip'
);
