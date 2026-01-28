(function ($) {
	'use strict';

	if (typeof WPBS_DEDUPE === 'undefined') {
		return;
	}

	var overlay, closeBtn, stopBtn;
	var statusLine, progressInner;
	var totalEl, processedEl, deletedEl, reparentedEl;
	var intervalId = null;
	var token = null;
	var running = false;
	var inFlight = false;

	function openModal() {
		if (overlay) overlay.style.display = 'block';
	}

	function closeModal() {
		stop();
		if (overlay) overlay.style.display = 'none';
	}

	function setStatus(text) {
		if (statusLine) statusLine.textContent = text;
	}

	function stop() {
		running = false;
		inFlight = false;
		if (intervalId) {
			clearInterval(intervalId);
			intervalId = null;
		}
	}

	function ajaxPost(action, data) {
		data = data || {};
		data.action = action;
		data.nonce = WPBS_DEDUPE.nonce;
		return $.ajax({
			url: WPBS_DEDUPE.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: data,
			timeout: 20000
		});
	}

	function updateUI(d) {
		var total = parseInt(d.totalGroups || 0, 10);
		var processed = parseInt(d.processedGroups || 0, 10);
		var deleted = parseInt(d.deletedPosts || 0, 10);
		var reparented = parseInt(d.reparentedAttachments || 0, 10);

		if (totalEl) totalEl.textContent = String(total);
		if (processedEl) processedEl.textContent = String(processed);
		if (deletedEl) deletedEl.textContent = String(deleted);
		if (reparentedEl) reparentedEl.textContent = String(reparented);

		var pct = total > 0 ? Math.round((processed / total) * 100) : 100;
		pct = Math.max(0, Math.min(100, pct));
		if (progressInner) progressInner.style.width = pct + '%';
	}

	function tick() {
		if (!running || !token || inFlight) return;
		inFlight = true;

		ajaxPost('wpbs_dedupe_tick', {
			token: token,
			batchSize: WPBS_DEDUPE.batchSize || 10
		}).done(function (resp) {
			if (!resp || !resp.success || !resp.data) {
				setStatus('Error: invalid response (retrying).');
				return;
			}

			updateUI(resp.data);

			if (resp.data.finished) {
				setStatus('Finished.');
				stop();
				return;
			}

			setStatus('Running… (updated ' + new Date().toLocaleTimeString() + ')');
		}).fail(function (xhr) {
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
		if (running) return;
		running = true;
		inFlight = false;
		token = null;

		openModal();
		setStatus('Starting…');
		if (progressInner) progressInner.style.width = '0%';

		ajaxPost('wpbs_dedupe_start', {}).done(function (resp) {
			if (!resp || !resp.success || !resp.data || !resp.data.token) {
				setStatus('Error: invalid response.');
				stop();
				return;
			}

			token = resp.data.token;
			updateUI(resp.data);
			setStatus('Running…');
			tick();
			intervalId = setInterval(tick, WPBS_DEDUPE.tickMs || 1000);
		}).fail(function (xhr) {
			var msg = 'Request failed.';
			if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				msg = xhr.responseJSON.data.message;
			}
			setStatus('Error: ' + msg);
			stop();
		});
	}

	$(function () {
		overlay = document.getElementById('wpbs-dedupe-overlay');
		closeBtn = document.getElementById('wpbs-dedupe-close');
		stopBtn = document.getElementById('wpbs-dedupe-stop');
		statusLine = document.getElementById('wpbs-dedupe-status');
		progressInner = document.getElementById('wpbs-dedupe-progress-bar');
		totalEl = document.getElementById('wpbs-dedupe-total');
		processedEl = document.getElementById('wpbs-dedupe-processed');
		deletedEl = document.getElementById('wpbs-dedupe-deleted');
		reparentedEl = document.getElementById('wpbs-dedupe-reparented');

		$(document).on('click', '#wpbs-dedupe-now', function (e) {
			e.preventDefault();
			if (!confirm('This will permanently delete duplicate boat posts. Continue?')) {
				return;
			}
			start();
		});

		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (stopBtn) stopBtn.addEventListener('click', function () {
			// Stop polling + close popup. Does not schedule background work.
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
