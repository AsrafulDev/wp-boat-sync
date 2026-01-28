(function () {
  'use strict';

  function initGallery(root) {
    var mainLink = root.querySelector('[data-wpbs-main-link], #wpbs-main-link, .wpbs-gallery__main-link');
    var mainImg = root.querySelector('[data-wpbs-main-img], #wpbs-main-img, .wpbs-gallery__main-img');
    var thumbs = root.querySelectorAll('[data-wpbs-thumb], .wpbs-gallery__thumb');
    var prevBtn = root.querySelector('[data-wpbs-nav="prev"], .wpbs-gallery__nav--prev');
    var nextBtn = root.querySelector('[data-wpbs-nav="next"], .wpbs-gallery__nav--next');
    var counter = root.querySelector('#wpbs-img-index');

    if (!mainImg || !thumbs || thumbs.length === 0) {
      return;
    }

    var currentIndex = 0;
    var totalImages = thumbs.length;

    function setActive(index) {
      if (index < 0) index = totalImages - 1;
      if (index >= totalImages) index = 0;
      currentIndex = index;

      thumbs.forEach(function (btn, i) {
        btn.classList.toggle('is-active', i === currentIndex);
        btn.setAttribute('aria-current', i === currentIndex ? 'true' : 'false');
      });

      var btn = thumbs[currentIndex];
      var large = btn.getAttribute('data-large') || btn.getAttribute('data-full');
      var full = btn.getAttribute('data-full') || large;
      var alt = btn.getAttribute('data-alt') || '';

      if (large && mainImg) {
        mainImg.setAttribute('src', large);
        if (alt) mainImg.setAttribute('alt', alt);
      }

      if (mainLink && full) {
        mainLink.setAttribute('href', full);
      }

      if (counter) {
        counter.textContent = currentIndex + 1;
      }

      // Scroll thumbnail into view
      if (btn.scrollIntoView) {
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      }
    }

    thumbs.forEach(function (btn, i) {
      btn.addEventListener('click', function () {
        setActive(i);
      });
    });

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        setActive(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        setActive(currentIndex + 1);
      });
    }

    // Keyboard navigation
    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') {
        setActive(currentIndex - 1);
      } else if (e.key === 'ArrowRight') {
        setActive(currentIndex + 1);
      }
    });

    // Initialize
    setActive(0);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-wpbs-gallery], .wpbs-gallery').forEach(initGallery);
  });
})();
