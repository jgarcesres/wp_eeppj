<?php
/**
 * Document list — displays links with type-based icons
 *
 * Expected $args['items'] = array of ['label' => string, 'url' => string, 'type' => 'page'|'document'|'external']
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

$items = $args['items'] ?? [];

if (empty($items)) return;
?>
<ul style="list-style: none; padding: 0;" class="space-y-2">
  <?php foreach ($items as $item) :
      $type = $item['type'] ?? 'page';
      $icon_type = ($type === 'document') ? 'doc' : $type;
      $target = ($type === 'external') ? ' target="_blank" rel="noopener"' : '';
      $url = $item['url'];
      // Internal URLs: prepend home_url if relative
      if ($type !== 'external' && strpos($url, 'http') !== 0) {
          $url = home_url($url);
      }
  ?>
    <li>
      <a href="<?php echo esc_url($url); ?>"<?php echo $target; ?>
         style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border-radius: 0.5rem; transition: background-color 0.15s; text-decoration: none;"
         class="doc-link group">
        <?php echo eeppj_doc_icon($icon_type); ?>
        <span style="font-size: 0.875rem; color: #374151; transition: color 0.15s;" class="doc-link-text group-hover-text-green">
          <?php echo esc_html($item['label']); ?>
        </span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>

<style>
  .doc-link:hover { background-color: rgba(140, 192, 75, 0.05); }
  .doc-link:hover .doc-link-text { color: var(--color-brand-green); }
</style>
