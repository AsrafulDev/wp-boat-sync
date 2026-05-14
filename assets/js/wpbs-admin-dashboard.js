(function () {
  'use strict';

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function init() {
    if (typeof window.WPBS_DASH === 'undefined') {
      return;
    }
    if (typeof window.Chart === 'undefined') {
      return;
    }

    var data = window.WPBS_DASH;

    // Sync activity line chart
    var syncCanvas = $('#wpbsChartSync');
    if (syncCanvas && data.syncSeries && data.syncSeries.labels) {
      new Chart(syncCanvas.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.syncSeries.labels,
          datasets: [
            {
              label: 'Boats synced',
              data: data.syncSeries.values,
              borderColor: '#0b5fff',
              backgroundColor: 'rgba(11,95,255,0.12)',
              pointRadius: 2,
              tension: 0.25,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          },
          plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
          }
        }
      });
    }

    // Status pie chart from boat_status taxonomy term counts
    var statusCanvas = $('#wpbsChartStatus');
    if (statusCanvas && data.statusCounts) {
      var sc = data.statusCounts;
      var colorMap = {
        active: '#16a34a',
        available: '#3b82f6',
        missing: '#f59e0b',
        'sale-pending': '#8b5cf6',
        sold: '#ef4444'
      };
      var fallbackColors = ['#16a34a', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#06b6d4', '#f97316'];
      var labels = [], values = [], colors = [];
      var total = 0;
      var colorIdx = 0;

      Object.keys(sc).forEach(function(slug) {
        var info = sc[slug];
        var count = info.count || 0;
        if (count > 0) {
          total += count;
        }
      });

      Object.keys(sc).forEach(function(slug) {
        var info = sc[slug];
        var count = info.count || 0;
        var pct = total > 0 ? Math.round((count / total) * 100) : 0;
        labels.push(info.name + ' (' + pct + '%)');
        values.push(count);
        colors.push(colorMap[slug] || fallbackColors[colorIdx % fallbackColors.length]);
        colorIdx++;
      });

      if (labels.length === 0) {
        labels = ['No data'];
        values = [1];
        colors = ['#d1d5db'];
      }

      new Chart(statusCanvas.getContext('2d'), {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [
            {
              data: values,
              backgroundColor: colors,
              borderWidth: 1,
              borderColor: '#fff'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' },
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  return ctx.label.split(' (')[0] + ': ' + ctx.raw;
                }
              }
            }
          }
        }
      });
    }

    // Queue chart (hourly if available)
    var queueCanvas = $('#wpbsChartQueue');
    if (queueCanvas && data.queueHourly && data.queueHourly.labels) {
      new Chart(queueCanvas.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.queueHourly.labels,
          datasets: [
            {
              label: 'Complete',
              data: data.queueHourly.done || [],
              borderColor: '#16a34a',
              backgroundColor: 'rgba(22,163,74,0.12)',
              pointRadius: 1,
              tension: 0.25,
              fill: false
            },
            {
              label: 'Processing',
              data: data.queueHourly.processing || [],
              borderColor: '#0b5fff',
              backgroundColor: 'rgba(11,95,255,0.12)',
              pointRadius: 1,
              tension: 0.25,
              fill: false
            },
            {
              label: 'Pending',
              data: data.queueHourly.pending || [],
              borderColor: '#f59e0b',
              backgroundColor: 'rgba(245,158,11,0.12)',
              pointRadius: 1,
              tension: 0.25,
              fill: false
            },
            {
              label: 'Failed',
              data: data.queueHourly.failed || [],
              borderColor: '#ef4444',
              backgroundColor: 'rgba(239,68,68,0.12)',
              pointRadius: 1,
              tension: 0.25,
              fill: false
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          },
          plugins: {
            legend: { position: 'bottom' },
            tooltip: { mode: 'index', intersect: false }
          }
        }
      });
    } else if (queueCanvas && data.queueCounts) {
      // Fallback: current snapshot
      new Chart(queueCanvas.getContext('2d'), {
        type: 'bar',
        data: {
          labels: ['Complete', 'Processing', 'Pending', 'Failed'],
          datasets: [
            {
              label: 'Jobs',
              data: [data.queueCounts.done || 0, data.queueCounts.processing || 0, data.queueCounts.pending || 0, data.queueCounts.failed || 0],
              backgroundColor: ['#16a34a', '#0b5fff', '#f59e0b', '#ef4444']
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          },
          plugins: { legend: { display: false } }
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();
