/**
 * EEPPJ Cookie Consent Banner
 */
(function () {
  'use strict';

  var banner = document.getElementById('cookie-banner');
  var acceptBtn = document.getElementById('cookie-accept');

  if (!localStorage.getItem('cookie-consent') && banner) {
    banner.classList.remove('hidden');
  }

  if (acceptBtn) {
    acceptBtn.addEventListener('click', function () {
      localStorage.setItem('cookie-consent', 'accepted');
      if (banner) banner.classList.add('hidden');
    });
  }
})();
