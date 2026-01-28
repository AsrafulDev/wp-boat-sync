(function ($) {
	'use strict';

	if (typeof WPBS_WORKER === 'undefined') {
		return;
	}

	var modal, overlay, closeBtn, stopBtn;
	var statusLine, doneEl, pendingEl, processingEl, failedEl;
	var progressOuter, progressInner;
	var intervalId = null;
	var initialRemaining = null;
	var isRunning = false;
	var inFlight = false;
	var consecutiveAjaxErrors = 0;

	function openModal() {
		if (!overlay) return;
		overlay.style.display = 'block';
	}

	function closeModal() {
		stop();
		if (overlay) overlay.style.display = 'none';
	}

	function setStatus(text) {
		if (statusLine) statusLine.textContent = text;
	}

	function setCounts(counts) {
		var d = counts && typeof counts.done !== 'undefined' ? parseInt(counts.done, 10) : 0;
		var p = counts && typeof counts.pending !== 'undefined' ? parseInt(counts.pending, 10) : 0;
		var pr = counts && typeof counts.processing !== 'undefined' ? parseInt(counts.processing, 10) : 0;
		var f = counts && typeof counts.failed !== 'undefined' ? parseInt(counts.failed, 10) : 0;

		if (doneEl) doneEl.textContent = String(d);
		if (pendingEl) pendingEl.textContent = String(p);
		if (processingEl) processingEl.textContent = String(pr);
		if (failedEl) failedEl.textContent = String(f);

		var remaining = Math.max(0, p + pr);
		if (initialRemaining === null) {
			initialRemaining = remaining;
		}

		var pct = 100;
		if (initialRemaining > 0) {
			pct = Math.round(((initialRemaining - remaining) / initialRemaining) * 100);
			pct = Math.max(0, Math.min(100, pct));
		}
		if (progressInner) {
			progressInner.style.width = pct + '%';
		}

		return remaining;
	}

	function ajaxPost(action, data) {
		data = data || {};
		data.action = action;
		data.nonce = WPBS_WORKER.nonce;

		return $.ajax({
			url: WPBS_WORKER.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: data,
			timeout: 20000
		});
	}

	function stop() {
		isRunning = false;
		inFlight = false;
		consecutiveAjaxErrors = 0;
		if (intervalId) {
			clearInterval(intervalId);
			intervalId = null;
		}
	}

	function status() {
		return ajaxPost('wpbs_queue_worker_status', {}).done(function (resp) {
			if (!resp || !resp.success || !resp.data) return;
			setCounts(resp.data.queueCounts);
		});
	}

	function tick() {
		if (!isRunning) return;
		if (inFlight) return;
		inFlight = true;

		ajaxPost('wpbs_queue_worker_tick', {
			batchSize: WPBS_WORKER.batchSize || 1
		}).done(function (resp) {
			if (!resp || !resp.success || !resp.data) {
				consecutiveAjaxErrors++;
				setStatus('Error: invalid response (will retry).');
				return;
			}
			consecutiveAjaxErrors = 0;

			var remaining = setCounts(resp.data.queueCounts);
			setStatus('Processing… (updated ' + new Date().toLocaleTimeString() + ')');

			if (remaining <= 0) {
				setStatus('Queue complete.');
				stop();
			}
		}).fail(function (xhr) {
			consecutiveAjaxErrors++;
			var msg = 'Request failed.';
			if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				msg = xhr.responseJSON.data.message;
			}
			setStatus('Error: ' + msg + ' (retrying)');
		}).always(function () {
			inFlight = false;
		});
	}

	function start() {
		if (isRunning) return;
		isRunning = true;
		inFlight = false;
		consecutiveAjaxErrors = 0;
		initialRemaining = null;
		if (progressInner) progressInner.style.width = '0%';

		openModal();
		setStatus('Loading queue status…');

		status().always(function () {
			setStatus('Starting worker…');
			tick();
			intervalId = setInterval(tick, WPBS_WORKER.tickMs || 1000);
		});
	}

	$(function () {
		overlay = document.getElementById('wpbs-worker-overlay');
		modal = document.getElementById('wpbs-worker-modal');
		closeBtn = document.getElementById('wpbs-worker-close');
		stopBtn = document.getElementById('wpbs-worker-stop');

		statusLine = document.getElementById('wpbs-worker-status');
		doneEl = document.getElementById('wpbs-worker-done');
		pendingEl = document.getElementById('wpbs-worker-pending');
		processingEl = document.getElementById('wpbs-worker-processing');
		failedEl = document.getElementById('wpbs-worker-failed');
		progressOuter = document.getElementById('wpbs-worker-progress');
		progressInner = document.getElementById('wpbs-worker-progress-bar');

		$(document).on('click', '#wpbs-run-queue-worker', function (e) {
			e.preventDefault();
			start();
		});

		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (stopBtn) stopBtn.addEventListener('click', function () {
			// Stop polling + close popup. This does NOT cancel jobs; WP-Cron/queue continues.
			closeModal();
		});

		if (overlay) {
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) {
					closeModal();
				}
			});
		}
	});
})(jQuery);
