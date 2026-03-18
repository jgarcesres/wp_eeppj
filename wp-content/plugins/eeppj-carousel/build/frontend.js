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
    var isTransitioning = false;
    var progressStart = 0;
    var pausedElapsed = 0;
    var isHovering = false;

    // Touch/swipe
    var touchStartX = 0;
    var touchThreshold = 50;

    el.style.setProperty('--progress-duration', autoplayMs + 'ms');

    function startProgress() {
      var activeDot = dots[current];
      if (!activeDot) return;

      // Fresh start: reset animation and apply running state
      activeDot.classList.remove('is-running', 'is-paused');
      activeDot.style.setProperty('--progress-duration', autoplayMs + 'ms');
      void activeDot.offsetWidth; // force reflow to restart animation
      activeDot.classList.add('is-running');

      progressStart = performance.now();
      pausedElapsed = 0;
      scheduleAdvance(autoplayMs);
    }

    function pauseProgress() {
      var activeDot = dots[current];
      if (!activeDot) return;

      cancelAdvance();

      // Switch from running to paused — CSS animation-play-state freezes in place
      if (activeDot.classList.contains('is-running')) {
        activeDot.classList.remove('is-running');
        activeDot.classList.add('is-paused');
        pausedElapsed = performance.now() - progressStart;
        if (pausedElapsed > autoplayMs) pausedElapsed = autoplayMs;
      }
    }

    function resumeProgress() {
      var activeDot = dots[current];
      if (!activeDot) return;

      // Switch from paused back to running — CSS animation-play-state resumes
      if (activeDot.classList.contains('is-paused')) {
        activeDot.classList.remove('is-paused');
        activeDot.classList.add('is-running');

        var remaining = autoplayMs - pausedElapsed;
        if (remaining <= 0) {
          advance();
          return;
        }
        progressStart = performance.now() - pausedElapsed;
        scheduleAdvance(remaining);
      }
    }

    function resetProgress() {
      cancelAdvance();
      dots.forEach(function (dot) {
        dot.classList.remove('is-running', 'is-paused');
      });
      pausedElapsed = 0;
    }

    function scheduleAdvance(ms) {
      cancelAdvance();
      advanceTimer = setTimeout(function () {
        advanceTimer = null;
        advance();
      }, ms);
    }

    function cancelAdvance() {
      if (advanceTimer) {
        clearTimeout(advanceTimer);
        advanceTimer = null;
      }
    }

    function advance() {
      goTo(current + 1);
    }

    function goTo(index) {
      if (isTransitioning && index !== current) return;

      resetProgress();

      current = ((index % slideCount) + slideCount) % slideCount;
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

      setTimeout(function () {
        isTransitioning = false;
      }, 650);

      if (isPlaying && !isHovering) {
        setTimeout(function () {
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

    function togglePlay() {
      setPlayState(!isPlaying);
    }

    // Dot clicks
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        goTo(parseInt(dot.dataset.index, 10));
      });
    });

    // Play/pause button
    if (playPauseBtn) {
      playPauseBtn.addEventListener('click', togglePlay);
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
        togglePlay();
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

    // Visibility: pause when tab hidden
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        if (isPlaying) {
          pauseProgress();
        }
      } else if (isPlaying && !isHovering) {
        resumeProgress();
      }
    });

    // Init
    goTo(0);
    if (isPlaying) {
      startProgress();
    }
  }
})();
