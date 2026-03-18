/**
 * EEPPJ Header — mobile menu toggle, search overlay, scroll lock
 */
(function () {
  'use strict';

  var mobileToggle = document.getElementById('mobile-menu-toggle');
  var mobileMenu = document.getElementById('mobile-menu');
  var searchToggle = document.getElementById('search-toggle');
  var searchOverlay = document.getElementById('search-overlay');
  var savedScrollY = 0;

  function lockScroll() {
    savedScrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = '-' + savedScrollY + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';
  }

  function unlockScroll() {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    window.scrollTo(0, savedScrollY);
  }

  function updateMenuPosition() {
    if (mobileMenu && mobileToggle) {
      var header = mobileToggle.closest('header');
      if (header) {
        var rect = header.getBoundingClientRect();
        mobileMenu.style.top = rect.bottom + 'px';
      }
    }
  }

  if (mobileToggle) {
    mobileToggle.addEventListener('click', function () {
      var expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
      mobileToggle.setAttribute('aria-expanded', String(!expanded));
      mobileMenu.classList.toggle('hidden');
      if (searchOverlay) searchOverlay.classList.add('hidden');
      if (!expanded) {
        updateMenuPosition();
        lockScroll();
      } else {
        unlockScroll();
      }
    });
  }

  if (searchToggle) {
    searchToggle.addEventListener('click', function () {
      if (searchOverlay) searchOverlay.classList.toggle('hidden');
      if (mobileMenu) mobileMenu.classList.add('hidden');
      unlockScroll();
      var input = searchOverlay ? searchOverlay.querySelector('input') : null;
      if (input) input.focus();
    });
  }
})();
