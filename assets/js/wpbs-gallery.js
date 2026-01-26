(function () {
  'use strict';

  function initGallery(root) {
    var mainLink = root.querySelector('[data-wpbs-main-link]');
    var mainImg = root.querySelector('[data-wpbs-main-img]');
    var thumbs = root.querySelectorAll('[data-wpbs-thumb]');

    if (!mainImg || !thumbs || thumbs.length === 0) {
      return;
    }

    function setActive(btn) {
      thumbs.forEach(function (b) {
        b.classList.toggle('is-active', b === btn);
        b.setAttribute('aria-current', b === btn ? 'true' : 'false');
      });
    }

    thumbs.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var large = btn.getAttribute('data-large');
        var full = btn.getAttribute('data-full');
        var alt = btn.getAttribute('data-alt') || '';
        var srcset = btn.getAttribute('data-srcset') || '';
        var sizes = btn.getAttribute('data-sizes') || '';

        if (large) {
          mainImg.setAttribute('src', large);
        }
        if (alt) {
          mainImg.setAttribute('alt', alt);
        }
        if (srcset) {
          mainImg.setAttribute('srcset', srcset);
        } else {
          mainImg.removeAttribute('srcset');
        }
        if (sizes) {
          mainImg.setAttribute('sizes', sizes);
        } else {
          mainImg.removeAttribute('sizes');
        }

        if (mainLink && full) {
          mainLink.setAttribute('href', full);
        }

        setActive(btn);
      });
    });

    // Default active state.
    setActive(thumbs[0]);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-wpbs-gallery]').forEach(initGallery);
  });
})();
