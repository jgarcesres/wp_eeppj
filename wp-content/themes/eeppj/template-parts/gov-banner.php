<?php
/**
 * Gov.co compliance banner — required for Colombian public entities
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;
?>
<div class="gov-banner" style="background-color: #3366CC; color: #fff; font-size: 0.75rem;">
  <div class="max-w-7xl px-4" style="padding-top: 0.375rem; padding-bottom: 0.375rem; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <svg style="height: 1.25rem; width: auto;" viewBox="0 0 120 30" fill="none" xmlns="http://www.w3.org/2000/svg">
        <text x="0" y="22" font-family="system-ui, sans-serif" font-size="18" font-weight="700" fill="white">GOV.CO</text>
      </svg>
      <span class="gov-banner-text">Portal oficial del Estado Colombiano</span>
    </div>
    <a href="https://www.gov.co" target="_blank" rel="noopener" style="color: #fff; text-decoration: underline;">
      gov.co
    </a>
  </div>
</div>

<style>
  .gov-banner-text { display: none; }
  @media (min-width: 640px) { .gov-banner-text { display: inline; } }
</style>
