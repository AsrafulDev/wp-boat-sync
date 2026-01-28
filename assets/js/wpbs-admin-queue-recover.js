(function ($) {
	'use strict';

	if (typeof WPBS_QUEUE_RECOVER === 'undefined') {
		return;
	}

	function setStatus(text) {
		var el = document.getElementById('wpbs-queue-recover-status');
		if (el) el.textContent = text;
	}

	function ajaxRecover(staleSeconds) {
		return $.ajax({
			url: WPBS_QUEUE_RECOVER.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			timeout: 20000,
			data: {
				action: 'wpbs_queue_recover_processing',
				nonce: WPBS_QUEUE_RECOVER.nonce,
				staleSeconds: staleSeconds
			}
		});
	}

	$(function () {
		$(document).on('click', '#wpbs-queue-recover-btn', function (e) {
			e.preventDefault();

			var stale = parseInt(WPBS_QUEUE_RECOVER.staleSeconds || 60, 10);
			if (!confirm('Reset stuck processing jobs back to pending?\n\nThis is safe if jobs are stuck (e.g. timeouts).')) {
				return;
			}

			setStatus('Resetting…');

			ajaxRecover(stale).done(function (resp) {
				if (!resp || !resp.success || !resp.data) {
					setStatus('Error: invalid response.');
					return;
				}
				var recovered = parseInt(resp.data.recovered || 0, 10);
				setStatus('Recovered ' + recovered + ' job(s). Refresh dashboard/queue to see updated counts.');
			}).fail(function (xhr) {
				var msg = 'Request failed.';
				if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					msg = xhr.responseJSON.data.message;
				}
				setStatus('Error: ' + msg);
			});
		});
	});
})(jQuery);
