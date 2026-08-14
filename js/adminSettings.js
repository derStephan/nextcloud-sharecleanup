(function() {
    'use strict';

    var container = document.getElementById('sharecleanup-settings');
    if (!container) {
        return;
    }

    var daysInput;
    var dryRunCheckbox;
    var savedMsg;
    var savedTimer;

    function t(app, text) {
        return OC.L10N.translate(app, text);
    }

    function render() {
        container.innerHTML =
            '<div class="section">' +
                '<h2>' + t('sharecleanup', 'Share Cleanup') + '</h2>' +
                '<p class="settings-hint">' + t('sharecleanup',
                    'Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.') + '</p>' +
                '<p>' +
                    '<label for="sharecleanup-days">' + t('sharecleanup', 'Maximum share age (days)') + '</label>' +
                    '<input type="number" id="sharecleanup-days" min="1" max="3650" value="365">' +
                '</p>' +
                '<p>' +
                    '<label>' +
                        '<input type="checkbox" id="sharecleanup-dry-run" checked> ' +
                        t('sharecleanup', 'Dry-run mode (only log, end nothing)') +
                    '</label>' +
                '</p>' +
                '<p class="settings-hint">' + t('sharecleanup',
                    'Enabled by default. Test with "occ sharecleanup:run --dry-run" and check the log, then disable.') + '</p>' +
                '<p>' +
                    '<button id="sharecleanup-save" class="button primary">' + t('sharecleanup', 'Save') + '</button>' +
                    '<span id="sharecleanup-saved" style="margin-left: 10px; color: var(--color-success); display: none;"></span>' +
                '</p>' +
                '<p class="settings-hint">' + t('sharecleanup',
                    'Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]') + '</p>' +
            '</div>';

        daysInput = document.getElementById('sharecleanup-days');
        dryRunCheckbox = document.getElementById('sharecleanup-dry-run');
        savedMsg = document.getElementById('sharecleanup-saved');

        document.getElementById('sharecleanup-save').addEventListener('click', save);
    }

    function save() {
        var days = parseInt(daysInput.value, 10);
        if (isNaN(days) || days < 1) {
            daysInput.setCustomValidity(t('sharecleanup', 'Please enter a number of days (minimum 1).'));
            daysInput.reportValidity();
            return;
        }
        daysInput.setCustomValidity('');

        fetch(OC.generateUrl('/apps/sharecleanup/settings/admin'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'OCS-APIREQUEST': 'true',
                'requesttoken': OC.requestToken
            },
            body: JSON.stringify({
                maxAgeDays: days,
                dryRun: dryRunCheckbox.checked
            })
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function() {
            savedMsg.textContent = t('sharecleanup', 'Settings saved.');
            savedMsg.style.display = 'inline';
            clearTimeout(savedTimer);
            savedTimer = setTimeout(function() { savedMsg.style.display = 'none'; }, 4000);
        })
        .catch(function(err) {
            savedMsg.textContent = t('sharecleanup', 'Error saving settings: ') + err.message;
            savedMsg.style.display = 'inline';
            savedMsg.style.color = 'var(--color-error)';
        });
    }

    render();
})();
