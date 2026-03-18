/**
 * EEPPJ Carousel — Frontend controller
 * Apple-style autoplay with progress-bar dots
 */
(function () {
  'use strict';

  document.querySelectorAll('.eeppj-carousel').forEach(initCarousel);

  function initCarousel(el) {
    var track = el.querySelector('.eeppj-carousel__track');
    var dots = el.querySelectorAll('.eeppj-carousel__dot');
    var playPauseBtn = el.querySelector('.eeppj-carousel__playpause');
    var slideCount = parseInt(el.dataset.count, 10) || dots.length;
    var autoplayMs = parseInt(el.dataset.autoplay, 10) || 5000;

    if (slideCount < 2) return;

    var current = 0;
    var isPlaying = true;
    var timer = null;
    var isTransitioning = false;
    var progressStart = 0;     // timestamp when current progress started
    var pausedElapsed = 0;     // ms elapsed when paused (to resume from)
    var isHovering = false;

    // Touch/swipe support
    var touchStartX = 0;
    var touchThreshold = 50;

    // Set the CSS custom property for progress duration
    el.style.setProperty('--progress-duration', autoplayMs + 'ms');

    function startProgress() {
      var activeDot = dots[current];
      if (!activeDot) return;

      // Reset: remove classes, force reflow, then add
      activeDot.classList.remove('is-running', 'is-paused');
      activeDot.style.removeProperty('--paused-width');

      // If resuming from a pause, shorten the duration for the remaining time
      var remaining = autoplayMs - pausedElapsed;
      if (pausedElapsed > 0) {
        // Set a partial starting width so the bar picks up where it left off
        var pct = (pausedElapsed / autoplayMs) * 100;
        activeDot.style.setProperty('--progress-duration', remaining + 'ms');
        // Force the ::after to start at the paused width
        var afterEl = activeDot;
        afterEl.style.setProperty('--progress-start', pct + '%');
      } else {
        activeDot.style.setProperty('--progress-duration', autoplayMs + 'ms');
        activeDot.style.removeProperty('--progress-start');
      }

      // Force reflow so the transition restarts
      void activeDot.offsetWidth;

      activeDot.classList.add('is-running');
      progressStart = performance.now() - pausedElapsed;
    }

    function stopProgress(freeze) {
      var activeDot = dots[current];
      if (!activeDot) return;

      if (freeze) {
        // Calculate how far along we are
        pausedElapsed = performance.now() - progressStart;
        if (pausedElapsed > autoplayMs) pausedElapsed = autoplayMs;
        var pct = (pausedElapsed / autoplayMs) * 100;

        // Freeze the bar at current position
        activeDot.classList.remove('is-running');
        activeDot.classList.add('is-paused');
        activeDot.style.setProperty('--paused-width', pct + '%');
      } else {
        activeDot.classList.remove('is-running', 'is-paused');
        activeDot.style.removeProperty('--paused-width');
        pausedElapsed = 0;
      }
    }

    function resetProgress() {
      // Clear all dots
      dots.forEach(function (dot) {
        dot.classList.remove('is-running', 'is-paused');
        dot.style.removeProperty('--paused-width');
        dot.style.removeProperty('--progress-start');
        dot.style.removeProperty('--progress-duration');
      });
      pausedElapsed = 0;
    }

    function goTo(index, resetTimer) {
      if (isTransitioning && index !== current) return;

      // Stop progress on old dot
      resetProgress();

      current = ((index % slideCount) + slideCount) % slideCount;
      isTransitioning = true;

      track.style.transform = 'translateX(-' + (current * (100 / slideCount)) + '%)';

      // Update dots
      dots.forEach(function (dot, i) {
        var isActive = i === current;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      // Announce for screen readers
      var activeSlide = track.children[current];
      if (activeSlide) {
        activeSlide.setAttribute('aria-hidden', 'false');
        for (var j = 0; j < track.children.length; j++) {
          if (j !== current) track.children[j].setAttribute('aria-hidden', 'true');
        }
      }

      // Clear transition lock after slide animation completes
      setTimeout(function () {
        isTransitioning = false;
      }, 650);

      // Start progress on new dot (after a small delay for the slide transition)
      pausedElapsed = 0;
      if (isPlaying && !isHovering) {
        setTimeout(function () {
          startProgress();
        }, 50);
      }

      if (resetTimer !== false) {
        restartTimer();
      }
    }

    function next() {
      goTo(current + 1, false);
    }

    function startTimer() {
      if (timer) clearInterval(timer);
      timer = setInterval(next, autoplayMs);
    }

    function stopTimer() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function restartTimer() {
      if (isPlaying) {
        stopTimer();
        startTimer();
      }
    }

    function togglePlay() {
      isPlaying = !isPlaying;
      if (playPauseBtn) {
        playPauseBtn.classList.toggle('is-playing', isPlaying);
        playPauseBtn.setAttribute('aria-label', isPlaying ? 'Pausar carrusel' : 'Reproducir carrusel');
      }
      if (isPlaying) {
        startProgress();
        startTimer();
      } else {
        stopProgress(true);
        stopTimer();
      }
    }

    // Bind dot clicks
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var index = parseInt(dot.dataset.index, 10);
        goTo(index);
      });
    });

    // Bind play/pause
    if (playPauseBtn) {
      playPauseBtn.addEventListener('click', togglePlay);
    }

    // Touch support
    el.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    el.addEventListener('touchend', function (e) {
      var touchEndX = e.changedTouches[0].screenX;
      var diff = touchStartX - touchEndX;
      if (Math.abs(diff) > touchThreshold) {
        goTo(diff > 0 ? current + 1 : current - 1);
      }
    }, { passive: true });

    // Keyboard navigation
    el.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        goTo(current + 1);
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        e.preventDefault();
        goTo(current - 1);
      } else if (e.key === ' ') {
        e.preventDefault();
        togglePlay();
      }
    });

    // Pause on hover — freeze progress, resume on leave
    el.addEventListener('mouseenter', function () {
      isHovering = true;
      if (isPlaying) {
        stopProgress(true);
        stopTimer();
      }
    });

    el.addEventListener('mouseleave', function () {
      isHovering = false;
      if (isPlaying) {
        startProgress();
        startTimer();
      }
    });

    // Pause when tab is not visible
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        if (isPlaying) {
          stopProgress(true);
          stopTimer();
        }
      } else if (isPlaying && !isHovering) {
        startProgress();
        startTimer();
      }
    });

    // Initialize
    goTo(0, false);
    if (isPlaying) {
      startProgress();
      startTimer();
    }
  }
})();
