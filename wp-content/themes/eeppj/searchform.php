<?php
/**
 * Search form template — styled to match site design
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;
?>
<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display: flex; gap: 0.5rem;">
  <label class="sr-only" for="search-field"><?php esc_html_e('Buscar:', 'eeppj'); ?></label>
  <input
    type="search"
    id="search-field"
    name="s"
    value="<?php echo get_search_query(); ?>"
    placeholder="Buscar en el sitio..."
    style="flex: 1; padding: 0.625rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; font-family: var(--font-sans); outline: none; transition: border-color 0.15s;"
    onfocus="this.style.borderColor='var(--color-brand-green)'"
    onblur="this.style.borderColor='#e5e7eb'"
  />
  <button type="submit" class="btn-primary" style="padding: 0.625rem 1.25rem; font-size: 0.875rem;">
    Buscar
  </button>
</form>
