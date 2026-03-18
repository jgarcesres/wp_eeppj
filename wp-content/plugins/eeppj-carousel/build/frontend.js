/**
 * EEPPJ Carousel — Frontend controller
 * Apple-style autoplay with progress-bar dots, looping, hover pause/resume
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
    var advanceTimer = null;
    var transitionTimer = null;
    var startProgressTimer = null;
    var isTransitioning = false;
    var pausedElapsed = 0;
    var isHovering = false;

    // Touch/swipe
    var touchStartX = 0;
    var touchThreshold = 50;

    el.style.setProperty('--progress-duration', autoplayMs + 'ms');

    function getActiveDot() {
      return dots[current] || null;
    }

    function startProgress() {
      var activeDot = getActiveDot();
      if (!activeDot) return;

      activeDot.classList.remove('is-running', 'is-paused');
      void activeDot.offsetWidth;
      activeDot.classList.add('is-running');

      pausedElapsed = 0;
      scheduleAdvance(autoplayMs);
    }

    function pauseProgress() {
      var activeDot = getActiveDot();
      if (!activeDot || !activeDot.classList.contains('is-running')) return;

      cancelAdvance();
      activeDot.classList.remove('is-running');
      activeDot.classList.add('is-paused');

      var remaining = getRemainingTime();
      pausedElapsed = remaining > 0 ? autoplayMs - remaining : 0;
    }

    function resumeProgress() {
      var activeDot = getActiveDot();
      if (!activeDot || !activeDot.classList.contains('is-paused')) return;

      activeDot.classList.remove('is-paused');
      activeDot.classList.add('is-running');

      var remaining = autoplayMs - pausedElapsed;
      if (remaining <= 0) {
        advance();
        return;
      }
      scheduleAdvance(remaining);
    }

    function resetProgress() {
      cancelAdvance();
      cancelDeferred();
      dots.forEach(function (dot) {
        dot.classList.remove('is-running', 'is-paused');
      });
      pausedElapsed = 0;
    }

    var advanceScheduledAt = 0;
    var advanceDuration = 0;

    function getRemainingTime() {
      if (!advanceTimer) return 0;
      var elapsed = performance.now() - advanceScheduledAt;
      return Math.max(0, advanceDuration - elapsed);
    }

    function scheduleAdvance(ms) {
      cancelAdvance();
      advanceScheduledAt = performance.now();
      advanceDuration = ms;
      advanceTimer = setTimeout(function () {
        advanceTimer = null;
        goTo(current + 1);
      }, ms);
    }

    function cancelAdvance() {
      if (advanceTimer) {
        clearTimeout(advanceTimer);
        advanceTimer = null;
      }
    }

    function cancelDeferred() {
      if (transitionTimer) {
        clearTimeout(transitionTimer);
        transitionTimer = null;
      }
      if (startProgressTimer) {
        clearTimeout(startProgressTimer);
        startProgressTimer = null;
      }
    }

    function goTo(index) {
      var target = ((index % slideCount) + slideCount) % slideCount;
      if (target === current && !isTransitioning) return;
      if (isTransitioning && target !== current) return;

      resetProgress();

      current = target;
      isTransitioning = true;

      track.style.transform = 'translateX(-' + (current * (100 / slideCount)) + '%)';

      dots.forEach(function (dot, i) {
        var isActive = i === current;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      var activeSlide = track.children[current];
      if (activeSlide) {
        activeSlide.setAttribute('aria-hidden', 'false');
        for (var j = 0; j < track.children.length; j++) {
          if (j !== current) track.children[j].setAttribute('aria-hidden', 'true');
        }
      }

      transitionTimer = setTimeout(function () {
        transitionTimer = null;
        isTransitioning = false;
      }, 650);

      if (isPlaying && !isHovering) {
        startProgressTimer = setTimeout(function () {
          startProgressTimer = null;
          startProgress();
        }, 50);
      }
    }

    function setPlayState(playing) {
      isPlaying = playing;
      if (playPauseBtn) {
        playPauseBtn.classList.toggle('is-playing', isPlaying);
        playPauseBtn.setAttribute('aria-label', isPlaying ? 'Pausar carrusel' : 'Reproducir carrusel');
      }
      if (isPlaying) {
        startProgress();
      } else {
        pauseProgress();
      }
    }

    // Dot clicks
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        goTo(parseInt(dot.dataset.index, 10));
      });
    });

    // Play/pause button
    if (playPauseBtn) {
      playPauseBtn.addEventListener('click', function () {
        setPlayState(!isPlaying);
      });
    }

    // Touch
    el.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    el.addEventListener('touchend', function (e) {
      var diff = touchStartX - e.changedTouches[0].screenX;
      if (Math.abs(diff) > touchThreshold) {
        goTo(diff > 0 ? current + 1 : current - 1);
      }
    }, { passive: true });

    // Keyboard
    el.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        goTo(current + 1);
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        e.preventDefault();
        goTo(current - 1);
      } else if (e.key === ' ') {
        e.preventDefault();
        setPlayState(!isPlaying);
      }
    });

    // Hover: pause animation, resume on leave
    el.addEventListener('mouseenter', function () {
      isHovering = true;
      if (isPlaying) {
        pauseProgress();
      }
    });

    el.addEventListener('mouseleave', function () {
      isHovering = false;
      if (isPlaying) {
        resumeProgress();
      }
    });

    // Visibility: pause when tab hidden (scoped to this carousel)
    function onVisibilityChange() {
      if (document.hidden) {
        if (isPlaying) pauseProgress();
      } else if (isPlaying && !isHovering) {
        resumeProgress();
      }
    }
    document.addEventListener('visibilitychange', onVisibilityChange);

    // Init
    goTo(0);
    if (isPlaying) {
      startProgress();
    }
  }
})();
