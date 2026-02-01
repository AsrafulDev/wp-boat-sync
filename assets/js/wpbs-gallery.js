(function () {
  'use strict';

  /*--------------------------------------------------------------
    Card Slider (Archive Page)
  --------------------------------------------------------------*/
  function initCardSlider(slider) {
    var slides = slider.querySelectorAll('.wpbs-card-slider__slide');
    var dots = slider.querySelectorAll('.wpbs-card-slider__dot');
    var prevBtn = slider.querySelector('.wpbs-card-slider__nav--prev');
    var nextBtn = slider.querySelector('.wpbs-card-slider__nav--next');
    var currentIndex = 0;
    var total = slides.length;

    if (total <= 1) {
      if (prevBtn) prevBtn.style.display = 'none';
      if (nextBtn) nextBtn.style.display = 'none';
      return;
    }

    function showSlide(index) {
      if (index < 0) index = total - 1;
      if (index >= total) index = 0;
      currentIndex = index;

      slides.forEach(function (s, i) {
        s.classList.toggle('is-active', i === currentIndex);
      });
      dots.forEach(function (d, i) {
        d.classList.toggle('is-active', i === currentIndex);
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showSlide(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showSlide(currentIndex + 1);
      });
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showSlide(i);
      });
    });

    showSlide(0);
  }

  /*--------------------------------------------------------------
    Single Boat Gallery
  --------------------------------------------------------------*/
  function initGallery(root) {
    var mainLink = root.querySelector('.wpbs-gallery__main-link, #wpbs-main-link');
    var mainImg = root.querySelector('.wpbs-gallery__main-img, #wpbs-main-img');
    var thumbs = root.querySelectorAll('.wpbs-gallery__thumb');
    var prevBtn = root.querySelector('.wpbs-gallery__nav--prev, [data-wpbs-nav="prev"]');
    var nextBtn = root.querySelector('.wpbs-gallery__nav--next, [data-wpbs-nav="next"]');
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
      var type = btn.getAttribute('data-type') || 'image';
      var large = btn.getAttribute('data-large') || btn.getAttribute('data-full');
      var full = btn.getAttribute('data-full') || large;
      var alt = btn.getAttribute('data-alt') || '';

      // Only update main image for image types
      if (type === 'image' && large && mainImg) {
        mainImg.setAttribute('src', large);
        if (alt) mainImg.setAttribute('alt', alt);
      }

      if (mainLink && full) {
        mainLink.setAttribute('data-index', currentIndex);
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
      prevBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        setActive(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
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

  /*--------------------------------------------------------------
    Show More / Show Less Toggle
  --------------------------------------------------------------*/
  function initShowMoreToggle() {
    document.querySelectorAll('[data-wpbs-toggle-expand]').forEach(function (btn) {
      var container = btn.previousElementSibling;
      if (!container || !container.hasAttribute('data-wpbs-expandable')) {
        // Try parent
        var parent = btn.parentElement;
        container = parent ? parent.querySelector('[data-wpbs-expandable]') : null;
      }
      
      if (!container) return;

      // Check if content is actually overflowing
      var checkOverflow = function() {
        var isOverflowing = container.scrollHeight > container.clientHeight + 10;
        btn.style.display = isOverflowing || container.classList.contains('is-expanded') ? 'inline-block' : 'none';
      };

      // Initial check
      setTimeout(checkOverflow, 100);

      btn.addEventListener('click', function () {
        var isExpanded = container.classList.toggle('is-expanded');
        btn.textContent = isExpanded ? 'Show Less' : 'Show More';
      });
    });
  }

  /*--------------------------------------------------------------
    Init on DOM Ready
  --------------------------------------------------------------*/
  document.addEventListener('DOMContentLoaded', function () {
    // Card sliders (archive page)
    document.querySelectorAll('.wpbs-card-slider').forEach(initCardSlider);

    // Single boat gallery
    document.querySelectorAll('[data-wpbs-gallery], .wpbs-gallery').forEach(initGallery);

    // Show more toggle
    initShowMoreToggle();
  });
})();
