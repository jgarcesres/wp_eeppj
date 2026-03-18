<?php
/**
 * Title: Banner Hero con Fondo
 * Slug: eeppj/hero-banner
 * Categories: eeppj
 * Description: Sección hero con fondo de color, título y texto descriptivo.
 * Keywords: hero, banner, encabezado
 */
?>
<!-- wp:group {"style":{"color":{"background":"#1e4b75"},"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="background-color:#1e4b75;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--50)">

<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"2.25rem"}},"textColor":"surface"} -->
<h1 class="wp-block-heading has-surface-color has-text-color" style="font-size:2.25rem">Título de la Sección</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"1.125rem"}},"textColor":"surface-muted"} -->
<p class="has-surface-muted-color has-text-color" style="font-size:1.125rem">Descripción breve de la sección. Puede incluir información relevante para los ciudadanos de Jericó sobre los servicios o trámites disponibles.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"brand-green","textColor":"surface","style":{"border":{"radius":"0.5rem"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-surface-color has-brand-green-background-color has-text-color has-background wp-element-button" style="border-radius:0.5rem">Más Información</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"0.5rem"},"color":{"text":"#ffffff"}},"borderColor":"surface"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-text-color has-border-color has-surface-border-color wp-element-button" style="border-radius:0.5rem;color:#ffffff">Contáctenos</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
