/**
 * PQRRS Form — client-side validation + AJAX submit
 * Matches Astro /pqrrs/ behavior with field-level errors
 */
(function () {
  'use strict';

  var form = document.getElementById('pqrrs-form');
  var statusEl = document.getElementById('pqrrs-status');
  var submitBtn = document.getElementById('pqrrs-submit');

  if (!form) return;

  var rules = {
    nombre:  { message: 'Ingrese su nombre completo (mínimo 3 caracteres)', validate: function(v) { return v.length >= 3; } },
    email:   { message: 'Ingrese un correo electrónico válido', validate: function(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); } },
    tipo:    { message: 'Seleccione el tipo de solicitud', validate: function(v) { return v !== ''; } },
    asunto:  { message: 'Ingrese el asunto (mínimo 5 caracteres)', validate: function(v) { return v.length >= 5; } },
    mensaje: { message: 'Describa su solicitud (mínimo 20 caracteres)', validate: function(v) { return v.length >= 20; } },
  };

  function showFieldError(name, message) {
    var field = form.querySelector('[name="' + name + '"]');
    var errorEl = form.querySelector('[data-for="' + name + '"]');
    if (field) field.classList.add('error');
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.add('visible');
    }
  }

  function clearErrors() {
    var fields = form.querySelectorAll('.error');
    for (var i = 0; i < fields.length; i++) fields[i].classList.remove('error');
    var errors = form.querySelectorAll('.pqrrs-field-error');
    for (var j = 0; j < errors.length; j++) {
      errors[j].classList.remove('visible');
      errors[j].textContent = '';
    }
  }

  function validateForm() {
    clearErrors();
    var valid = true;
    var firstInvalid = null;

    for (var name in rules) {
      var rule = rules[name];
      var field = form.querySelector('[name="' + name + '"]');
      if (!field) continue;
      var value = field.value.trim();
      if (!rule.validate(value)) {
        showFieldError(name, rule.message);
        if (!firstInvalid) firstInvalid = field;
        valid = false;
      }
    }

    // File validation
    var fileInput = form.querySelector('[name="archivo"]');
    if (fileInput && fileInput.files && fileInput.files.length > 0) {
      var file = fileInput.files[0];
      if (file.size > 5 * 1024 * 1024) {
        showFieldError('archivo', 'El archivo no puede superar 5MB');
        valid = false;
      }
      var allowed = ['.pdf', '.jpg', '.jpeg', '.png', '.docx'];
      var ext = '.' + file.name.split('.').pop().toLowerCase();
      if (allowed.indexOf(ext) === -1) {
        showFieldError('archivo', 'Formato no permitido. Use PDF, JPG, PNG o DOCX');
        valid = false;
      }
    }

    if (firstInvalid) firstInvalid.focus();
    return valid;
  }

  // Clear individual field errors on input
  var inputs = form.querySelectorAll('.pqrrs-input');
  for (var k = 0; k < inputs.length; k++) {
    inputs[k].addEventListener('input', function () {
      this.classList.remove('error');
      var name = this.name;
      var errorEl = form.querySelector('[data-for="' + name + '"]');
      if (errorEl) errorEl.classList.remove('visible');
    });
  }

  function escHtml(str) {
    if (str == null) return '';
    var el = document.createElement('span');
    el.textContent = String(str);
    return el.innerHTML;
  }

  function showStatus(html, type) {
    statusEl.innerHTML = html;
    statusEl.className = 'pqrrs-status ' + type;
    statusEl.style.display = '';
    statusEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideStatus() {
    statusEl.style.display = 'none';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    hideStatus();

    if (!validateForm()) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="pqrrs-spinner"></span> Enviando...';

    showStatus('<span class="pqrrs-spinner"></span> Procesando su solicitud...', 'loading');

    var formData = new FormData(form);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', eeppjPqrrs.ajaxUrl);
    xhr.onload = function () {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<svg style="width:1.25rem;height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Enviar Solicitud';

      try {
        var response = JSON.parse(xhr.responseText);
        if (response.success) {
          showStatus(
            '<div style="display:flex;align-items:flex-start;gap:0.75rem;">' +
              '<svg style="width:1.25rem;height:1.25rem;color:#16a34a;flex-shrink:0;margin-top:0.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
              '<div><p style="font-weight:600;">Solicitud enviada exitosamente</p>' +
              '<p style="margin-top:0.25rem;color:#15803d;">Su PQRRS ha sido radicada. Recibirá respuesta en los plazos establecidos por la ley.</p></div>' +
            '</div>',
            'success'
          );
          form.reset();
          if (typeof turnstile !== 'undefined') turnstile.reset();
        } else {
          throw new Error(response.data ? response.data.message : 'Error del servidor');
        }
      } catch (err) {
        showStatus(
          '<div style="display:flex;align-items:flex-start;gap:0.75rem;">' +
            '<svg style="width:1.25rem;height:1.25rem;color:#dc2626;flex-shrink:0;margin-top:0.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
            '<div><p style="font-weight:600;">Error al enviar</p>' +
            '<p style="margin-top:0.25rem;color:#b91c1c;">' + escHtml(err.message || 'No se pudo procesar su solicitud. Intente nuevamente o comuníquese por teléfono al +60 (4) 852 37 64.') + '</p></div>' +
          '</div>',
          'error'
        );
      }
    };
    xhr.onerror = function () {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<svg style="width:1.25rem;height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Enviar Solicitud';
      showStatus(
        '<div style="display:flex;align-items:flex-start;gap:0.75rem;">' +
          '<svg style="width:1.25rem;height:1.25rem;color:#dc2626;flex-shrink:0;margin-top:0.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
          '<div><p style="font-weight:600;">Error de conexión</p>' +
          '<p style="margin-top:0.25rem;color:#b91c1c;">Verifique su conexión a internet e intente de nuevo.</p></div>' +
        '</div>',
        'error'
      );
    };
    xhr.send(formData);
  });
})();
