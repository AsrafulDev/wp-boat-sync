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
    Lightbox for Gallery
  --------------------------------------------------------------*/
  function initLightbox() {
    var lightbox = document.getElementById('wpbs-lightbox');
    if (!lightbox) return;

    var overlay = lightbox.querySelector('.wpbs-lightbox__overlay');
    var closeBtn = lightbox.querySelector('.wpbs-lightbox__close');
    var prevBtn = lightbox.querySelector('.wpbs-lightbox__nav--prev');
    var nextBtn = lightbox.querySelector('.wpbs-lightbox__nav--next');
    var mainImg = lightbox.querySelector('.wpbs-lightbox__main-img');
    var counter = lightbox.querySelector('.wpbs-lightbox__counter');
    var thumbsContainer = lightbox.querySelector('.wpbs-lightbox__thumbs');
    
    var currentIndex = 0;
    var images = [];
    var galleryRoot = null;

    // Open lightbox
    function openLightbox(startIndex, galleryElement) {
      galleryRoot = galleryElement;
      
      // Get all gallery images
      images = [];
      var thumbs = galleryRoot.querySelectorAll('.wpbs-gallery__thumb');
      thumbs.forEach(function (thumb) {
        var full = thumb.getAttribute('data-full') || thumb.getAttribute('data-large');
        var alt = thumb.getAttribute('data-alt') || '';
        if (full) {
          images.push({ url: full, alt: alt });
        }
      });

      if (images.length === 0) return;

      currentIndex = startIndex || 0;
      lightbox.style.display = 'block';
      document.body.style.overflow = 'hidden';
      
      showImage(currentIndex);
    }

    // Close lightbox
    function closeLightbox() {
      lightbox.style.display = 'none';
      document.body.style.overflow = '';
    }

    // Show image at index
    function showImage(index) {
      if (index < 0) index = images.length - 1;
      if (index >= images.length) index = 0;
      currentIndex = index;

      var img = images[currentIndex];
      if (mainImg && img) {
        mainImg.setAttribute('src', img.url);
        mainImg.setAttribute('alt', img.alt);
      }

      if (counter) {
        counter.textContent = (currentIndex + 1) + ' / ' + images.length;
      }

      // Update thumbnail active state
      if (thumbsContainer) {
        var lightboxThumbs = thumbsContainer.querySelectorAll('.wpbs-lightbox__thumb');
        lightboxThumbs.forEach(function (thumb, i) {
          thumb.classList.toggle('is-active', i === currentIndex);
        });
      }

      // Disable prev/next buttons at boundaries
      if (prevBtn) prevBtn.disabled = false;
      if (nextBtn) nextBtn.disabled = false;
    }

    // Event listeners
    if (closeBtn) {
      closeBtn.addEventListener('click', closeLightbox);
    }

    if (overlay) {
      overlay.addEventListener('click', closeLightbox);
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        showImage(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        showImage(currentIndex + 1);
      });
    }

    // Keyboard navigation
    document.addEventListener('keydown', function (e) {
      if (lightbox.style.display !== 'block') return;
      
      if (e.key === 'Escape') {
        closeLightbox();
      } else if (e.key === 'ArrowLeft') {
        showImage(currentIndex - 1);
      } else if (e.key === 'ArrowRight') {
        showImage(currentIndex + 1);
      }
    });

    // Thumbnail clicks in lightbox
    if (thumbsContainer) {
      thumbsContainer.addEventListener('click', function (e) {
        var thumb = e.target.closest('.wpbs-lightbox__thumb');
        if (thumb) {
          var index = parseInt(thumb.getAttribute('data-index'));
          if (!isNaN(index)) {
            showImage(index);
          }
        }
      });
    }

    // Listen for open lightbox triggers
    document.addEventListener('click', function (e) {
      var trigger = e.target.closest('[data-wpbs-open-lightbox]');
      if (trigger) {
        e.preventDefault();
        var gallery = trigger.closest('[data-wpbs-gallery]');
        if (gallery) {
          openLightbox(0, gallery);
        }
      }
      
      // Also handle clicks on main gallery image
      var mainLink = e.target.closest('.wpbs-gallery__main-link');
      if (mainLink) {
        e.preventDefault();
        var gallery = mainLink.closest('[data-wpbs-gallery]');
        var index = parseInt(mainLink.getAttribute('data-index')) || 0;
        if (gallery) {
          openLightbox(index, gallery);
        }
      }
    });
  }

  /*--------------------------------------------------------------
    AJAX Filter System
  --------------------------------------------------------------*/
  function initFilterSystem(container) {
    var filterBar = container.querySelector('.wpbs-filter-bar');
    var grid = container.querySelector('[data-wpbs-grid]');
    var loadingOverlay = container.querySelector('[data-wpbs-filter-loading]');
    var pagination = container.querySelector('[data-wpbs-pagination]');
    var resultsHeader = container.querySelector('[data-wpbs-results-header]');

    if (!filterBar || !grid) return;

    var currentPage = 1;
    var isLoading = false;

    // Get filter values from URL on load
    function parseUrlParams() {
      var params = new URLSearchParams(window.location.search);
      var filters = {};

      // Select fields
      ['category', 'builder', 'location', 'orderby'].forEach(function (key) {
        if (params.has(key)) {
          var select = filterBar.querySelector('[data-wpbs-filter="' + key + '"]');
          if (select) {
            select.value = params.get(key);
            filters[key] = params.get(key);
          }
        }
      });

      // Number inputs
      ['length_min', 'length_max', 'year_min', 'year_max', 'price_min', 'price_max'].forEach(function (key) {
        if (params.has(key)) {
          var input = filterBar.querySelector('[data-wpbs-filter="' + key + '"]');
          if (input) {
            input.value = params.get(key);
            filters[key] = params.get(key);
          }
        }
      });

      // Checkboxes
      ['condition_new', 'condition_used', 'featured'].forEach(function (key) {
        if (params.get(key) === '1') {
          var checkbox = filterBar.querySelector('[data-wpbs-filter="' + key + '"]');
          if (checkbox) {
            checkbox.checked = true;
            filters[key] = '1';
          }
        }
      });

      // Page
      if (params.has('paged')) {
        currentPage = parseInt(params.get('paged')) || 1;
      }

      return filters;
    }

    // Collect filter values from form
    function collectFilters() {
      var filters = {};

      // Select and text inputs from filter bar
      filterBar.querySelectorAll('[data-wpbs-filter]').forEach(function (el) {
        var key = el.getAttribute('data-wpbs-filter');
        if (el.type === 'checkbox') {
          if (el.checked) {
            filters[key] = '1';
          }
        } else if (el.value && el.value !== '') {
          filters[key] = el.value;
        }
      });

      // Also check sort dropdown outside filter bar
      var sortSelect = container.querySelector('.wpbs-archive-sort [data-wpbs-filter="orderby"]');
      if (sortSelect && sortSelect.value) {
        filters.orderby = sortSelect.value;
      }

      // Check for range slider values
      container.querySelectorAll('[data-wpbs-range]').forEach(function (slider) {
        var minInput = slider.querySelector('[data-wpbs-range-min]');
        var maxInput = slider.querySelector('[data-wpbs-range-max]');
        if (minInput && minInput.value) {
          filters[minInput.getAttribute('data-wpbs-filter')] = minInput.value;
        }
        if (maxInput && maxInput.value) {
          filters[maxInput.getAttribute('data-wpbs-filter')] = maxInput.value;
        }
      });

      return filters;
    }

    // Update URL with current filters
    function updateUrl(filters, page) {
      var url = new URL(window.location.href);
      var params = url.searchParams;

      // Clear existing filter params
      ['category', 'builder', 'location', 'orderby',
       'length_min', 'length_max', 'year_min', 'year_max',
       'price_min', 'price_max', 'condition_new', 'condition_used',
       'featured', 'paged'].forEach(function (key) {
        params.delete(key);
      });

      // Add active filters
      for (var key in filters) {
        if (filters[key]) {
          params.set(key, filters[key]);
        }
      }

      // Add page if not first
      if (page > 1) {
        params.set('paged', page);
      }

      // Update URL without reload
      var newUrl = url.pathname + (params.toString() ? '?' + params.toString() : '');
      window.history.pushState({ filters: filters, page: page }, '', newUrl);
    }

    // Show/hide loading state
    function setLoading(loading) {
      isLoading = loading;
      if (loadingOverlay) {
        loadingOverlay.style.display = loading ? 'flex' : 'none';
      }
      if (grid) {
        grid.style.opacity = loading ? '0.5' : '1';
        grid.style.pointerEvents = loading ? 'none' : 'auto';
      }
    }

    // Perform AJAX request
    function fetchBoats(filters, page) {
      if (isLoading) return;
      
      setLoading(true);

      var formData = new FormData();
      formData.append('action', 'wpbs_filter_boats');
      formData.append('nonce', wpbsFilter.nonce);
      formData.append('paged', page);

      for (var key in filters) {
        formData.append(key, filters[key]);
      }

      fetch(wpbsFilter.ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        setLoading(false);

        if (data.success) {
          // Update grid
          grid.innerHTML = data.data.html;

          // Re-init card sliders in new content
          grid.querySelectorAll('.wpbs-card-slider').forEach(initCardSlider);

          // Update total count displays
          container.querySelectorAll('[data-wpbs-total-count]').forEach(function(el) {
            el.textContent = data.data.total + ' boats';
          });

          // Update pagination
          if (pagination) {
            updatePagination(page, data.data.maxPages || 1, data.data.total);
          }

          // Scroll to top of container
          container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
          console.error('Filter error:', data);
          grid.innerHTML = '<div class="wpbs-no-results"><p>Error loading results. Please try again.</p></div>';
        }
      })
      .catch(function (error) {
        setLoading(false);
        console.error('Filter fetch error:', error);
        grid.innerHTML = '<div class="wpbs-no-results"><p>Error loading results. Please try again.</p></div>';
      });
    }

    // Update pagination buttons
    function updatePagination(page, maxPages, total) {
      var prevBtn = pagination.querySelector('[data-wpbs-page="prev"]');
      var nextBtn = pagination.querySelector('[data-wpbs-page="next"]');
      var info = pagination.querySelector('.wpbs-pagination__info');

      if (prevBtn) {
        prevBtn.disabled = page <= 1;
      }
      if (nextBtn) {
        nextBtn.disabled = page >= maxPages;
      }
      if (info) {
        info.textContent = 'Page ' + page + ' of ' + maxPages + ' (' + total + ' boats)';
      }

      currentPage = page;
    }

    // Debounce timer for auto-load
    var debounceTimer = null;
    function debouncedFetch() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        currentPage = 1;
        var filters = collectFilters();
        updateUrl(filters, 1);
        fetchBoats(filters, 1);
      }, 1000);
    }

    // Event: Search button click
    var searchBtn = filterBar.querySelector('[data-wpbs-filter-submit]');
    if (searchBtn) {
      searchBtn.addEventListener('click', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        currentPage = 1;
        var filters = collectFilters();
        updateUrl(filters, 1);
        fetchBoats(filters, 1);
      });
    }

    // Event: Clear filters
    var clearBtn = filterBar.querySelector('[data-wpbs-filter-clear]');
    if (clearBtn) {
      clearBtn.addEventListener('click', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        
        // Reset all inputs
        filterBar.querySelectorAll('[data-wpbs-filter]').forEach(function (el) {
          if (el.type === 'checkbox') {
            el.checked = false;
          } else if (el.tagName === 'SELECT') {
            el.selectedIndex = 0;
          } else {
            el.value = '';
          }
        });

        // Reset range sliders
        container.querySelectorAll('[data-wpbs-range]').forEach(function (wrapper) {
          var rangeMin = wrapper.querySelector('.wpbs-range__input--min');
          var rangeMax = wrapper.querySelector('.wpbs-range__input--max');
          if (rangeMin) rangeMin.value = rangeMin.min;
          if (rangeMax) rangeMax.value = rangeMax.max;
          // Trigger update
          rangeMin && rangeMin.dispatchEvent(new Event('input'));
        });

        currentPage = 1;
        updateUrl({}, 1);
        fetchBoats({}, 1);
      });
    }

    // Event: Auto-load on select/checkbox change (with 1s debounce)
    filterBar.querySelectorAll('select[data-wpbs-filter]').forEach(function (select) {
      select.addEventListener('change', debouncedFetch);
    });
    filterBar.querySelectorAll('input[type="checkbox"][data-wpbs-filter]').forEach(function (checkbox) {
      checkbox.addEventListener('change', debouncedFetch);
    });

    // Event: Pagination
    if (pagination) {
      pagination.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-wpbs-page]');
        if (!btn || btn.disabled) return;
        
        e.preventDefault();
        clearTimeout(debounceTimer);
        
        var direction = btn.getAttribute('data-wpbs-page');
        var newPage = currentPage;

        if (direction === 'prev' && currentPage > 1) {
          newPage = currentPage - 1;
        } else if (direction === 'next') {
          newPage = currentPage + 1;
        }

        if (newPage !== currentPage) {
          var filters = collectFilters();
          updateUrl(filters, newPage);
          fetchBoats(filters, newPage);
        }
      });
    }

    // Event: Enter key on inputs (immediate)
    filterBar.querySelectorAll('input[data-wpbs-filter]').forEach(function (input) {
      input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(debounceTimer);
          currentPage = 1;
          var filters = collectFilters();
          updateUrl(filters, 1);
          fetchBoats(filters, 1);
        }
      });
    });

    // Event: Sort dropdown change (outside filter bar) - immediate
    var sortSelect = container.querySelector('.wpbs-archive-sort [data-wpbs-filter="orderby"]');
    if (sortSelect) {
      sortSelect.addEventListener('change', function () {
        clearTimeout(debounceTimer);
        currentPage = 1;
        var filters = collectFilters();
        // Also get orderby from sort dropdown
        filters.orderby = sortSelect.value;
        updateUrl(filters, 1);
        fetchBoats(filters, 1);
      });
    }

    // Event: Browser back/forward
    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.filters !== undefined) {
        // Restore form state
        parseUrlParams();
        currentPage = e.state.page || 1;
        fetchBoats(e.state.filters, currentPage);
      }
    });

    // Initialize from URL params on load
    var initialFilters = parseUrlParams();
    if (Object.keys(initialFilters).length > 0 || currentPage > 1) {
      // Already have filters in URL, they were applied server-side
      // Just ensure pagination state is correct
    }

    // Hide loading overlay initially
    if (loadingOverlay) {
      loadingOverlay.style.display = 'none';
    }

    // Initialize range sliders
    initRangeSliders(container, debouncedFetch);
  }

  /*--------------------------------------------------------------
    Dual Range Slider
  --------------------------------------------------------------*/
  function initRangeSliders(container, onChangeCallback) {
    container.querySelectorAll('[data-wpbs-range]').forEach(function (wrapper) {
      var track = wrapper.querySelector('.wpbs-range__track');
      var rangeMin = wrapper.querySelector('.wpbs-range__input--min');
      var rangeMax = wrapper.querySelector('.wpbs-range__input--max');
      var minDisplay = wrapper.querySelector('.wpbs-range__value--min');
      var maxDisplay = wrapper.querySelector('.wpbs-range__value--max');
      var minHidden = wrapper.querySelector('[data-wpbs-range-min]');
      var maxHidden = wrapper.querySelector('[data-wpbs-range-max]');

      if (!rangeMin || !rangeMax) return;

      var min = parseFloat(rangeMin.min) || 0;
      var max = parseFloat(rangeMax.max) || 100;
      var step = parseFloat(rangeMin.step) || 1;
      var formatType = wrapper.getAttribute('data-wpbs-range') || 'number';

      function formatValue(val, type) {
        val = parseFloat(val);
        if (type === 'price') {
          if (val >= 1000000) {
            return '$' + (val / 1000000).toFixed(1) + 'M';
          } else if (val >= 1000) {
            return '$' + (val / 1000).toFixed(0) + 'K';
          }
          return '$' + val.toLocaleString();
        } else if (type === 'length') {
          return val + ' ft';
        }
        return val.toString();
      }

      function updateTrack() {
        var minVal = parseFloat(rangeMin.value);
        var maxVal = parseFloat(rangeMax.value);
        var percentMin = ((minVal - min) / (max - min)) * 100;
        var percentMax = ((maxVal - min) / (max - min)) * 100;

        if (track) {
          track.style.left = percentMin + '%';
          track.style.width = (percentMax - percentMin) + '%';
        }
      }

      function updateValues(triggerCallback) {
        var minVal = parseFloat(rangeMin.value);
        var maxVal = parseFloat(rangeMax.value);

        // Prevent overlap
        if (minVal > maxVal - step) {
          rangeMin.value = maxVal - step;
          minVal = parseFloat(rangeMin.value);
        }
        if (maxVal < minVal + step) {
          rangeMax.value = minVal + step;
          maxVal = parseFloat(rangeMax.value);
        }

        // Update displays
        if (minDisplay) minDisplay.textContent = formatValue(minVal, formatType);
        if (maxDisplay) maxDisplay.textContent = formatValue(maxVal, formatType);

        // Update hidden inputs
        if (minHidden) minHidden.value = minVal;
        if (maxHidden) maxHidden.value = maxVal;

        updateTrack();

        // Trigger auto-load callback on change (debounced)
        if (triggerCallback && onChangeCallback) {
          onChangeCallback();
        }
      }

      rangeMin.addEventListener('input', function() { updateValues(true); });
      rangeMax.addEventListener('input', function() { updateValues(true); });

      // Initialize without triggering callback
      updateValues(false);
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
    
    // Lightbox for galleries
    initLightbox();

    // AJAX Filter system
    document.querySelectorAll('[data-wpbs-filter-container]').forEach(initFilterSystem);
  });
})();
