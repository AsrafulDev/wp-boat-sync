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

    // Status doughnut chart
    var statusCanvas = $('#wpbsChartStatus');
    if (statusCanvas && data.statusCounts) {
      new Chart(statusCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels: ['Active', 'Sold/Other'],
          datasets: [
            {
              data: [data.statusCounts.active || 0, data.statusCounts.sold || 0],
              backgroundColor: ['#16a34a', '#ef4444'],
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
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
