<?php
/**
 * PQRRS Form template — rendered by shortcode [eeppj_pqrrs]
 * Matches the Astro site design at /pqrrs/
 *
 * @package eeppj-pqrrs
 */

defined('ABSPATH') || exit;
?>
<div class="pqrrs-form-wrapper">
  <div id="pqrrs-status" class="pqrrs-status" style="display: none;"></div>

  <form id="pqrrs-form" class="pqrrs-form" novalidate>
    <input type="hidden" name="action" value="eeppj_pqrrs_submit" />
    <input type="hidden" name="eeppj_pqrrs_nonce" value="<?php echo esc_attr($nonce ?? wp_create_nonce('eeppj_pqrrs_submit')); ?>" />

    <!-- Honeypot -->
    <div style="position: absolute; left: -9999px;" aria-hidden="true">
      <label for="website">No completar</label>
      <input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
    </div>

    <div class="pqrrs-grid">
      <div class="pqrrs-field">
        <label for="pqrrs-nombre">Nombre completo *</label>
        <input type="text" id="pqrrs-nombre" name="nombre" required minlength="3" placeholder="Ingrese su nombre" class="pqrrs-input" />
        <p class="pqrrs-field-error" data-for="nombre"></p>
      </div>

      <div class="pqrrs-field">
        <label for="pqrrs-cedula">Cédula</label>
        <input type="text" id="pqrrs-cedula" name="cedula" inputmode="numeric" pattern="[0-9]*" placeholder="Número de documento" class="pqrrs-input" />
      </div>

      <div class="pqrrs-field">
        <label for="pqrrs-email">Correo electrónico *</label>
        <input type="email" id="pqrrs-email" name="email" required placeholder="correo@ejemplo.com" class="pqrrs-input" />
        <p class="pqrrs-field-error" data-for="email"></p>
      </div>

      <div class="pqrrs-field">
        <label for="pqrrs-telefono">Teléfono</label>
        <input type="tel" id="pqrrs-telefono" name="telefono" placeholder="Ej: 604 852 3764" class="pqrrs-input" />
      </div>
    </div>

    <div class="pqrrs-field">
      <label for="pqrrs-tipo">Tipo de solicitud *</label>
      <select id="pqrrs-tipo" name="tipo" required class="pqrrs-input">
        <option value="">Seleccione el tipo...</option>
        <option value="peticion">Petición</option>
        <option value="queja">Queja</option>
        <option value="reclamo">Reclamo</option>
        <option value="recurso">Recurso</option>
        <option value="sugerencia">Sugerencia</option>
      </select>
      <p class="pqrrs-field-error" data-for="tipo"></p>
    </div>

    <div class="pqrrs-field">
      <label for="pqrrs-asunto">Asunto *</label>
      <input type="text" id="pqrrs-asunto" name="asunto" required minlength="5" placeholder="Resumen breve de su solicitud" class="pqrrs-input" />
      <p class="pqrrs-field-error" data-for="asunto"></p>
    </div>

    <div class="pqrrs-field">
      <label for="pqrrs-mensaje">Mensaje *</label>
      <textarea id="pqrrs-mensaje" name="mensaje" rows="5" required minlength="20" placeholder="Describa su solicitud en detalle..." class="pqrrs-input" style="resize: vertical;"></textarea>
      <p class="pqrrs-field-error" data-for="mensaje"></p>
    </div>

    <div class="pqrrs-field">
      <label for="pqrrs-archivo">
        Adjuntar archivo <span class="pqrrs-label-muted">(PDF, JPG, PNG, DOCX — máx. <?php echo esc_html($max_upload); ?>MB)</span>
      </label>
      <input type="file" id="pqrrs-archivo" name="archivo" accept=".pdf,.png,.jpg,.jpeg,.docx" class="pqrrs-file-input" />
      <p class="pqrrs-field-error" data-for="archivo"></p>
    </div>

    <?php if (!empty($site_key)) : ?>
      <div class="pqrrs-field">
        <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
      </div>
    <?php endif; ?>

    <button type="submit" class="btn-primary pqrrs-submit" id="pqrrs-submit">
      <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
      </svg>
      Enviar Solicitud
    </button>
  </form>
</div>
