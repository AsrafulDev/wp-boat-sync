(function ($) {
	'use strict';

	if (typeof WPBS_QUEUE === 'undefined') {
		return;
	}

	var runnerEl = null;
	var statusEl = null;
	var cancelBtn = null;
	var refreshBtn = null;

	var pollTimer = null;
	var activeToken = null;
	var isRunning = false;

	function setStatus(text) {
		if (!statusEl) return;
		statusEl.textContent = text;
	}

	function showRunner() {
		if (!runnerEl) return;
		runnerEl.style.display = 'block';
	}

	function stopPolling() {
		if (pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
		isRunning = false;
		activeToken = null;
	}

	function finishUI(message) {
		setStatus(message);
		if (refreshBtn) {
			refreshBtn.style.display = 'inline-block';
		}
	}

	function ajaxPost(action, data) {
		data = data || {};
		data.action = action;
		data.nonce = WPBS_QUEUE.nonce;

		return $.ajax({
			url: WPBS_QUEUE.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: data,
			timeout: 20000
		});
	}

	function tick() {
		if (!activeToken || !isRunning) {
			return;
		}

		ajaxPost('wpbs_queue_tick', {
			token: activeToken,
			batchSize: WPBS_QUEUE.batchSize || 3
		}).done(function (resp) {
			if (!resp || !resp.success || !resp.data) {
				finishUI('Error: invalid response.');
				stopPolling();
				return;
			}

			var d = resp.data;
			setStatus('Ran: ' + (d.ran || 0) + ' | Done: ' + (d.done || 0) + ' | Failed: ' + (d.failed || 0) + ' | Remaining: ' + (d.remaining || 0));

			if (d.finished) {
				stopPolling();
				finishUI('Finished. Ran: ' + (d.ran || 0) + ', done: ' + (d.done || 0) + ', failed: ' + (d.failed || 0) + '.');
			}
		}).fail(function (xhr) {
			stopPolling();
			var msg = 'Request failed.';
			if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				msg = xhr.responseJSON.data.message;
			}
			finishUI('Error: ' + msg);
		});
	}

	function start(jobIds) {
		if (!jobIds || !jobIds.length) {
			return;
		}
		if (isRunning) {
			return;
		}

		isRunning = true;
		showRunner();
		setStatus('Starting…');
		if (refreshBtn) {
			refreshBtn.style.display = 'none';
		}

		ajaxPost('wpbs_queue_start', { jobIds: jobIds }).done(function (resp) {
			if (!resp || !resp.success || !resp.data || !resp.data.token) {
				finishUI('Error: invalid response.');
				stopPolling();
				return;
			}

			activeToken = resp.data.token;
			setStatus('Queued. Remaining: ' + (resp.data.remaining || jobIds.length) + '.');

			// First tick immediately, then every 2 seconds.
			tick();
			pollTimer = setInterval(tick, WPBS_QUEUE.tickMs || 2000);
		}).fail(function (xhr) {
			stopPolling();
			var msg = 'Request failed.';
			if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				msg = xhr.responseJSON.data.message;
			}
			finishUI('Error: ' + msg);
		});
	}

	function getSelectedIds() {
		var ids = [];
		$('input[name="queue_ids[]"]:checked').each(function () {
			var v = parseInt($(this).val(), 10);
			if (v) ids.push(v);
		});
		return ids;
	}

	$(function () {
		runnerEl = document.getElementById('wpbs-queue-runner');
		statusEl = document.getElementById('wpbs-queue-runner-status');
		cancelBtn = document.getElementById('wpbs-queue-runner-cancel');
		refreshBtn = document.getElementById('wpbs-queue-runner-refresh');

		$(document).on('click', 'a.wpbs-queue-run', function (e) {
			e.preventDefault();
			var id = parseInt($(this).data('job-id'), 10);
			if (!id) return;
			start([id]);
		});

		// Intercept bulk run action and do it via AJAX.
		$(document).on('submit', 'form', function (e) {
			var $form = $(this);
			// Only act on the queue page form.
			if ($form.find('input[name="page"][value="wpbs-queue"]').length === 0) {
				return;
			}

			var action = $form.find('select[name="action"]').val();
			var action2 = $form.find('select[name="action2"]').val();
			var chosen = (action && action !== '-1') ? action : action2;

			if (chosen !== 'run_selected') {
				return;
			}

			e.preventDefault();
			var ids = getSelectedIds();
			if (!ids.length) {
				alert('Select at least one job.');
				return;
			}
			start(ids);
		});

		if (cancelBtn) {
			cancelBtn.addEventListener('click', function () {
				stopPolling();
				finishUI('Stopped.');
			});
		}

		if (refreshBtn) {
			refreshBtn.addEventListener('click', function () {
				window.location.reload();
			});
		}
	});
})(jQuery);
